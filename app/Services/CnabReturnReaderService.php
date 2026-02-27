<?php

namespace App\Services;

use App\Enums\CnabFormat;
use App\Enums\PaymentBatchStatus;
use App\Enums\PaymentItemStatus;
use App\Models\PaymentBatch;
use App\Models\PaymentBatchItem;
use Illuminate\Support\Facades\Storage;

class CnabReturnReaderService
{
    private const RETURN_CODES_240 = [
        '00'  => 'Crédito efetuado',
        '000' => 'Crédito efetuado',
        'AA'  => 'Conta destino inválida',
        'AB'  => 'Agência destino inválida',
        'AC'  => 'Tipo de conta inválido',
        'AD'  => 'Data de lançamento inválida',
        'AE'  => 'Tipo/Finalidade incompatíveis',
        'AF'  => 'Complemento de registro inválido',
        'AG'  => 'Código do banco destino inválido',
        'AJ'  => 'Tipo produto inválido',
        'AK'  => 'Código do convênio do pagador inválido',
        'AL'  => 'Agência/conta do favorecido bloqueada',
        'AM'  => 'Favorecido divergente',
        'AN'  => 'Data de vencimento inválida',
        'AO'  => 'Banco não aceita pagamento no dia',
        'AP'  => 'Valor da parcela inválido',
    ];

    private const RETURN_CODES_400 = [
        '00' => 'Crédito efetuado',
        '02' => 'Código do banco inválido',
        '03' => 'Código da empresa inválido',
        '04' => 'Agência inválida',
        '05' => 'Conta corrente inválida',
        '06' => 'Dígito verificador agência inválido',
        '07' => 'Dígito verificador conta inválido',
        '08' => 'Nome do beneficiário inválido',
        '10' => 'Saldo insuficiente',
        '12' => 'Data inválida',
        '17' => 'Conta encerrada',
        '18' => 'CNPJ/CPF do beneficiário inválido',
    ];

    public function process(PaymentBatch $batch, string $filePath): void
    {
        $content = Storage::get($filePath);

        if ($content === null) {
            throw new \RuntimeException("Arquivo de retorno não encontrado: {$filePath}");
        }

        $batch->update(['return_file_path' => $filePath]);

        if ($batch->cnab_format === CnabFormat::Cnab240) {
            $this->processCnab240($batch, $content);
        } else {
            $this->processCnab400($batch, $content);
        }

        $this->updateBatchStatus($batch);
    }

    private function processCnab240(PaymentBatch $batch, string $content): void
    {
        $lines = explode("\n", str_replace("\r\n", "\n", $content));

        foreach ($lines as $line) {
            $line = rtrim($line);

            if (strlen($line) < 240) {
                continue;
            }

            $tipoRegistro = substr($line, 7, 1);
            $segmento     = substr($line, 13, 1);

            if ($tipoRegistro !== '3' || $segmento !== 'A') {
                continue;
            }

            $returnCode = trim(substr($line, 230, 2));
            $docNum     = trim(substr($line, 74, 20));
            $itemId     = (int) ltrim($docNum, '0');

            if (!$itemId) {
                continue;
            }

            $item = PaymentBatchItem::where('id', $itemId)
                ->where('payment_batch_id', $batch->id)
                ->first();

            if (!$item) {
                continue;
            }

            if ($returnCode === '00' || $returnCode === '000') {
                $item->update(['status' => PaymentItemStatus::Paid]);
            } else {
                $description = self::RETURN_CODES_240[$returnCode] ?? "Código de retorno: {$returnCode}";
                $item->update([
                    'status'           => PaymentItemStatus::Rejected,
                    'rejection_reason' => $description,
                ]);
            }
        }
    }

    private function processCnab400(PaymentBatch $batch, string $content): void
    {
        $lines = explode("\n", str_replace("\r\n", "\n", $content));

        foreach ($lines as $line) {
            $line = rtrim($line);

            if (strlen($line) < 400) {
                continue;
            }

            $tipoRegistro = substr($line, 0, 1);

            if ($tipoRegistro !== '1') {
                continue;
            }

            $returnCode = trim(substr($line, 393, 2));
            $docNum     = trim(substr($line, 73, 10));
            $itemId     = (int) ltrim($docNum, '0');

            if (!$itemId) {
                continue;
            }

            $item = PaymentBatchItem::where('id', $itemId)
                ->where('payment_batch_id', $batch->id)
                ->first();

            if (!$item) {
                continue;
            }

            if ($returnCode === '00') {
                $item->update(['status' => PaymentItemStatus::Paid]);
            } else {
                $description = self::RETURN_CODES_400[$returnCode] ?? "Código de retorno: {$returnCode}";
                $item->update([
                    'status'           => PaymentItemStatus::Rejected,
                    'rejection_reason' => $description,
                ]);
            }
        }
    }

    private function updateBatchStatus(PaymentBatch $batch): void
    {
        $batch->refresh();
        $items = $batch->items;

        if ($items->isEmpty()) {
            return;
        }

        $hasRejected = $items->contains(fn (PaymentBatchItem $i) =>
            $i->status === PaymentItemStatus::Rejected
        );
        $allProcessed = $items->every(fn (PaymentBatchItem $i) =>
            in_array($i->status, [PaymentItemStatus::Paid, PaymentItemStatus::Rejected, PaymentItemStatus::Returned])
        );

        if ($allProcessed) {
            $notes = $batch->notes;
            if ($hasRejected) {
                $rejectedCount = $items->filter(fn ($i) => $i->status === PaymentItemStatus::Rejected)->count();
                $notes .= "\nRetorno processado: {$rejectedCount} item(s) rejeitado(s).";
            }

            $batch->update([
                'status' => PaymentBatchStatus::Processed,
                'notes'  => $notes,
            ]);
        }
    }
}
