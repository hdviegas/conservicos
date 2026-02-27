<?php

namespace App\Services;

use App\Enums\CnabFormat;
use App\Enums\PaymentBatchStatus;
use App\Enums\PaymentMethod;
use App\Models\Company;
use App\Models\CompanyBankAccount;
use App\Models\PaymentBatch;
use App\Models\PaymentBatchItem;
use Illuminate\Support\Facades\Storage;

class CnabGeneratorService
{
    private const BANK_NAMES = [
        '001' => 'BANCO DO BRASIL S.A.',
        '104' => 'CAIXA ECONOMICA FEDERAL',
    ];

    public function generate(PaymentBatch $batch): string
    {
        $filePath = $batch->cnab_format === CnabFormat::Cnab240
            ? $this->generateCnab240($batch)
            : $this->generateCnab400($batch);

        $batch->update([
            'file_path'    => $filePath,
            'status'       => PaymentBatchStatus::Generated,
            'generated_by' => auth()->id(),
            'generated_at' => now(),
        ]);

        return $filePath;
    }

    private function generateCnab240(PaymentBatch $batch): string
    {
        $company    = $batch->company;
        $bankAccount = CompanyBankAccount::where('company_id', $company->id)
            ->where('bank_code', $batch->bank_code)
            ->where('active', true)
            ->first();

        $items = $batch->items()->with('employee')->get();
        $lines = [];

        $lines[] = $this->buildCnab240FileHeader($batch, $company, $bankAccount);
        $lines[] = $this->buildCnab240LoteHeader($batch, $company, $bankAccount, 1);

        $sequenceInLot = 1;
        foreach ($items as $item) {
            $lines[] = $this->buildCnab240SegmentA($batch, $item, $sequenceInLot);
            $sequenceInLot++;
            $lines[] = $this->buildCnab240SegmentB($batch, $item, $sequenceInLot);
            $sequenceInLot++;
        }

        $lotRecords = count($items) * 2 + 2;
        $lines[]    = $this->buildCnab240LoteTrailer($batch, 1, $lotRecords, $items->sum('amount'));
        $lines[]    = $this->buildCnab240FileTrailer($batch, 1, count($lines) + 1);

        return $this->saveFile($batch, implode("\r\n", $lines));
    }

    private function generateCnab400(PaymentBatch $batch): string
    {
        $company     = $batch->company;
        $bankAccount = CompanyBankAccount::where('company_id', $company->id)
            ->where('bank_code', $batch->bank_code)
            ->where('active', true)
            ->first();

        $items    = $batch->items()->with('employee')->get();
        $lines    = [];
        $sequence = 1;

        $lines[] = $this->buildCnab400Header($batch, $company, $bankAccount);
        $sequence++;

        foreach ($items as $item) {
            $lines[] = $this->buildCnab400Detail($batch, $item, $sequence);
            $sequence++;
        }

        $lines[] = $this->buildCnab400Trailer($batch, count($items), $items->sum('amount'), $sequence);

        return $this->saveFile($batch, implode("\r\n", $lines));
    }

    private function buildCnab240FileHeader(PaymentBatch $batch, Company $company, ?CompanyBankAccount $account): string
    {
        $bankCode    = str_pad($batch->bank_code, 3, '0', STR_PAD_LEFT);
        $cnpj        = str_pad($this->onlyDigits($company->cnpj), 14, '0', STR_PAD_LEFT);
        $covenant    = str_pad($account?->covenant_code ?? '', 20, ' ', STR_PAD_RIGHT);
        $agency      = str_pad($account?->agency ?? '00000', 5, '0', STR_PAD_LEFT);
        $agencyDigit = $account?->agency_digit ?? ' ';
        $account_num = str_pad($account?->account_number ?? '000000000000', 12, '0', STR_PAD_LEFT);
        $accountDig  = $account?->account_digit ?? ' ';
        $companyName = str_pad(mb_strtoupper($this->removeAccents($company->name)), 30, ' ', STR_PAD_RIGHT);
        $bankName    = str_pad(self::BANK_NAMES[$batch->bank_code] ?? 'BANCO', 30, ' ', STR_PAD_RIGHT);
        $date        = now()->format('dmY');
        $time        = now()->format('His');

        $line = $bankCode;                              // 1-3
        $line .= '0001';                                // 4-7 lote
        $line .= '0';                                   // 8 tipo registro
        $line .= str_repeat(' ', 9);                    // 9-17 brancos
        $line .= '1';                                   // 18 tipo inscrição PJ
        $line .= $cnpj;                                 // 19-32
        $line .= $covenant;                             // 33-52
        $line .= $agency;                               // 53-57
        $line .= substr($agencyDigit . ' ', 0, 1);      // 58
        $line .= $account_num;                          // 59-70
        $line .= substr($accountDig . ' ', 0, 1);       // 71
        $line .= ' ';                                   // 72
        $line .= $companyName;                          // 73-102
        $line .= $bankName;                             // 103-132
        $line .= str_repeat(' ', 10);                   // 133-142
        $line .= '1';                                   // 143 remessa
        $line .= $date;                                 // 144-151
        $line .= $time;                                 // 152-157
        $line .= '000001';                              // 158-163 sequência
        $line .= '040';                                 // 164-166 versão
        $line .= '01600';                               // 167-171 densidade
        $line .= str_repeat(' ', 20);                   // 172-191
        $line .= str_repeat(' ', 20);                   // 192-211
        $line .= str_repeat(' ', 29);                   // 212-240

        return $this->padLine($line, 240);
    }

    private function buildCnab240LoteHeader(PaymentBatch $batch, Company $company, ?CompanyBankAccount $account, int $loteNum): string
    {
        $bankCode    = str_pad($batch->bank_code, 3, '0', STR_PAD_LEFT);
        $lote        = str_pad((string) $loteNum, 4, '0', STR_PAD_LEFT);
        $cnpj        = str_pad($this->onlyDigits($company->cnpj), 14, '0', STR_PAD_LEFT);
        $companyName = str_pad(mb_strtoupper($this->removeAccents($company->name)), 30, ' ', STR_PAD_RIGHT);
        $agency      = str_pad($account?->agency ?? '00000', 5, '0', STR_PAD_LEFT);
        $agencyDigit = $account?->agency_digit ?? '0';
        $account_num = str_pad($account?->account_number ?? '000000000000', 12, '0', STR_PAD_LEFT);
        $accountDig  = $account?->account_digit ?? '0';
        $date        = $batch->payment_date->format('dmY');

        $line = $bankCode;                              // 1-3
        $line .= $lote;                                 // 4-7
        $line .= '1';                                   // 8 header lote
        $line .= 'C';                                   // 9 tipo operação crédito
        $line .= '98';                                  // 10-11 tipo pagamento
        $line .= '040';                                 // 12-14 versão layout
        $line .= ' ';                                   // 15
        $line .= '1';                                   // 16 tipo inscrição PJ
        $line .= $cnpj;                                 // 17-30
        $line .= str_pad($account?->covenant_code ?? '', 20, ' ', STR_PAD_RIGHT); // 31-50 convênio
        $line .= $agency;                               // 51-55
        $line .= substr($agencyDigit, 0, 1);            // 56
        $line .= $account_num;                          // 57-68
        $line .= substr($accountDig, 0, 1);             // 69
        $line .= ' ';                                   // 70
        $line .= $companyName;                          // 71-100
        $line .= str_repeat(' ', 40);                   // 101-140 finalidade lote
        $line .= str_repeat(' ', 30);                   // 141-170 histórico
        $line .= str_pad($company->address ?? '', 30, ' ', STR_PAD_RIGHT); // 171-200
        $line .= str_pad('', 5, ' ', STR_PAD_RIGHT);   // 201-205 número
        $line .= str_pad('', 15, ' ', STR_PAD_RIGHT);  // 206-220 complemento
        $line .= str_pad($company->city ?? '', 20, ' ', STR_PAD_RIGHT); // 221-240

        return $this->padLine($line, 240);
    }

    private function buildCnab240SegmentA(PaymentBatch $batch, PaymentBatchItem $item, int $sequence): string
    {
        $bankCode    = str_pad($batch->bank_code, 3, '0', STR_PAD_LEFT);
        $lote        = '0001';
        $seq         = str_pad((string) $sequence, 5, '0', STR_PAD_LEFT);
        $destBank    = str_pad($item->bank_code ?? '000', 3, '0', STR_PAD_LEFT);
        $destAgency  = str_pad($item->agency ?? '00000', 5, '0', STR_PAD_LEFT);
        $destAgDig   = substr(($item->agency_digit ?? '0') . '0', 0, 1);
        $destAccount = str_pad($item->account_number ?? '000000000000', 12, '0', STR_PAD_LEFT);
        $destAccDig  = substr(($item->account_digit ?? '0') . '0', 0, 1);
        $empName     = str_pad(mb_strtoupper($this->removeAccents($item->employee->name)), 35, ' ', STR_PAD_RIGHT);
        $docNum      = str_pad((string) $item->id, 20, '0', STR_PAD_LEFT);
        $payDate     = $batch->payment_date->format('dmY');
        $amountCents = str_pad((string) (int) round($item->amount * 100), 15, '0', STR_PAD_LEFT);
        $cpf         = str_pad($this->onlyDigits($item->employee->cpf ?? ''), 15, '0', STR_PAD_LEFT);

        $isPix       = $item->payment_method === PaymentMethod::Pix;
        $movType     = $isPix ? '45' : '01';

        if ($isPix) {
            $destBank    = str_pad($batch->bank_code, 3, '0', STR_PAD_LEFT);
            $destAgency  = '00000';
            $destAgDig   = '0';
            $destAccount = str_pad($item->pix_key ?? '', 12, ' ', STR_PAD_RIGHT);
            $destAccDig  = '0';
        }

        $line = $bankCode;                              // 1-3
        $line .= $lote;                                 // 4-7
        $line .= '3';                                   // 8
        $line .= $seq;                                  // 9-13
        $line .= 'A';                                   // 14
        $line .= $movType;                              // 15-16
        $line .= $destBank;                             // 17-19
        $line .= $destAgency;                           // 20-24
        $line .= $destAgDig;                            // 25
        $line .= $destAccount;                          // 26-37
        $line .= $destAccDig;                           // 38
        $line .= ' ';                                   // 39
        $line .= $empName;                              // 40-74
        $line .= $docNum;                               // 75-94
        $line .= $payDate;                              // 95-102
        $line .= 'BRL';                                 // 103-105
        $line .= str_pad('0', 14, '0', STR_PAD_LEFT);  // 106-119 valor câmbio
        $line .= $amountCents;                          // 120-134 (reusing slot per layout)
        $line .= str_repeat(' ', 15);                   // 135-149
        $line .= str_repeat(' ', 8);                    // 150-157 data efetiva
        $line .= str_pad('0', 7, '0', STR_PAD_LEFT);   // 158-164 valor real
        $line .= str_repeat(' ', 15);                   // 165-179
        $line .= $cpf;                                  // 180-194
        $line .= str_repeat(' ', 35);                   // 195-229
        $line .= str_pad('00001', 10, ' ', STR_PAD_RIGHT); // 230-239 finalidade
        $line .= ' ';                                   // 240

        return $this->padLine($line, 240);
    }

    private function buildCnab240SegmentB(PaymentBatch $batch, PaymentBatchItem $item, int $sequence): string
    {
        $bankCode  = str_pad($batch->bank_code, 3, '0', STR_PAD_LEFT);
        $lote      = '0001';
        $seq       = str_pad((string) $sequence, 5, '0', STR_PAD_LEFT);
        $cpf       = str_pad($this->onlyDigits($item->employee->cpf ?? ''), 14, '0', STR_PAD_LEFT);
        $address   = str_pad(mb_strtoupper($this->removeAccents($item->employee->address ?? '')), 30, ' ', STR_PAD_RIGHT);
        $city      = str_pad(mb_strtoupper($this->removeAccents($item->employee->city ?? '')), 15, ' ', STR_PAD_RIGHT);
        $state     = str_pad(mb_strtoupper($item->employee->state ?? ''), 2, ' ', STR_PAD_RIGHT);
        $zip       = str_pad($this->onlyDigits($item->employee->zip_code ?? ''), 8, '0', STR_PAD_LEFT);

        $line = $bankCode;                              // 1-3
        $line .= $lote;                                 // 4-7
        $line .= '3';                                   // 8
        $line .= $seq;                                  // 9-13
        $line .= 'B';                                   // 14
        $line .= str_repeat(' ', 3);                    // 15-17
        $line .= '2';                                   // 18 PF=2
        $line .= $cpf;                                  // 19-32
        $line .= $address;                              // 33-62
        $line .= str_pad('', 3, ' ', STR_PAD_RIGHT);   // 63-65 número
        $line .= str_pad('', 15, ' ', STR_PAD_RIGHT);  // 66-80 complemento
        $line .= str_pad('', 5, ' ', STR_PAD_RIGHT);   // 81-85 bairro
        $line .= $city;                                 // 86-100
        $line .= $state;                                // 101-102
        $line .= $zip;                                  // 103-110

        return $this->padLine($line, 240);
    }

    private function buildCnab240LoteTrailer(PaymentBatch $batch, int $loteNum, int $qtdRecords, float $totalAmount): string
    {
        $bankCode = str_pad($batch->bank_code, 3, '0', STR_PAD_LEFT);
        $lote     = str_pad((string) $loteNum, 4, '0', STR_PAD_LEFT);
        $qty      = str_pad((string) $qtdRecords, 6, '0', STR_PAD_LEFT);
        $total    = str_pad((string) (int) round($totalAmount * 100), 18, '0', STR_PAD_LEFT);

        $line = $bankCode;                              // 1-3
        $line .= $lote;                                 // 4-7
        $line .= '5';                                   // 8
        $line .= str_repeat(' ', 9);                    // 9-17
        $line .= $qty;                                  // 18-23
        $line .= $total;                                // 24-41
        $line .= str_repeat('0', 18);                   // 42-59 qtd debitos
        $line .= str_repeat(' ', 181);                  // 60-240

        return $this->padLine($line, 240);
    }

    private function buildCnab240FileTrailer(PaymentBatch $batch, int $qtdLotes, int $totalRecords): string
    {
        $bankCode = str_pad($batch->bank_code, 3, '0', STR_PAD_LEFT);
        $lotes    = str_pad((string) $qtdLotes, 6, '0', STR_PAD_LEFT);
        $total    = str_pad((string) $totalRecords, 6, '0', STR_PAD_LEFT);

        $line = $bankCode;                              // 1-3
        $line .= '9999';                                // 4-7
        $line .= '9';                                   // 8
        $line .= str_repeat(' ', 9);                    // 9-17
        $line .= $lotes;                                // 18-23
        $line .= $total;                                // 24-29
        $line .= str_repeat(' ', 211);                  // 30-240

        return $this->padLine($line, 240);
    }

    private function buildCnab400Header(PaymentBatch $batch, Company $company, ?CompanyBankAccount $account): string
    {
        $cnpj        = str_pad($this->onlyDigits($company->cnpj), 14, '0', STR_PAD_LEFT);
        $companyName = str_pad(mb_strtoupper($this->removeAccents($company->name)), 30, ' ', STR_PAD_RIGHT);
        $bankCode    = str_pad($batch->bank_code, 3, '0', STR_PAD_LEFT);
        $bankName    = str_pad(self::BANK_NAMES[$batch->bank_code] ?? 'BANCO', 15, ' ', STR_PAD_RIGHT);
        $date        = now()->format('dmY');
        $covenant    = str_pad($account?->covenant_code ?? '', 20, '0', STR_PAD_LEFT);
        $agency      = str_pad($account?->agency ?? '00000', 5, '0', STR_PAD_LEFT);
        $account_num = str_pad($account?->account_number ?? '00000000', 8, '0', STR_PAD_LEFT);

        $line = '0';                                    // pos 1
        $line .= '1';                                   // pos 2 remessa
        $line .= 'REMESSA';                             // pos 3-9
        $line .= str_repeat(' ', 7);                    // pos 10-16
        $line .= '98';                                  // pos 17-18 serviço crédito
        $line .= str_repeat(' ', 7);                    // pos 19-25
        $line .= $covenant;                             // pos 26-45
        $line .= $agency;                               // pos 46-50
        $line .= $account_num;                          // pos 51-58
        $line .= str_repeat(' ', 6);                    // pos 59-64
        $line .= $companyName;                          // pos 65-94
        $line .= $bankCode;                             // pos 95-97
        $line .= $bankName;                             // pos 98-112
        $line .= str_repeat(' ', 8);                    // pos 113-120
        $line .= $date;                                 // pos 121-128
        $line .= str_repeat(' ', 69);                   // pos 129-197
        $line .= '000001';                              // pos 198-203
        $line .= $cnpj;                                 // pos 204-217
        $line .= str_repeat(' ', 183);                  // pad to 400

        return $this->padLine($line, 400);
    }

    private function buildCnab400Detail(PaymentBatch $batch, PaymentBatchItem $item, int $sequence): string
    {
        $seq         = str_pad((string) $sequence, 6, '0', STR_PAD_LEFT);
        $destBank    = str_pad($item->bank_code ?? '000', 3, '0', STR_PAD_LEFT);
        $destAgency  = str_pad($item->agency ?? '00000', 5, '0', STR_PAD_LEFT);
        $destAccount = str_pad($item->account_number ?? '00000000', 13, '0', STR_PAD_LEFT);
        $destAccDig  = substr(($item->account_digit ?? '0') . '0', 0, 1);
        $empName     = str_pad(mb_strtoupper($this->removeAccents($item->employee->name)), 30, ' ', STR_PAD_RIGHT);
        $cpf         = str_pad($this->onlyDigits($item->employee->cpf ?? ''), 14, '0', STR_PAD_LEFT);
        $amountCents = str_pad((string) (int) round($item->amount * 100), 13, '0', STR_PAD_LEFT);
        $payDate     = $batch->payment_date->format('dmY');
        $docNum      = str_pad((string) $item->id, 10, '0', STR_PAD_LEFT);

        $isPix = $item->payment_method === PaymentMethod::Pix;
        if ($isPix) {
            $destBank    = str_pad($batch->bank_code, 3, '0', STR_PAD_LEFT);
            $destAgency  = '00000';
            $destAccount = str_pad($item->pix_key ?? '', 13, ' ', STR_PAD_RIGHT);
            $destAccDig  = '0';
        }

        $line = '1';                                    // pos 1
        $line .= $cpf;                                  // pos 2-15
        $line .= str_repeat(' ', 4);                    // pos 16-19
        $line .= $destBank;                             // pos 20-22
        $line .= $destAgency;                           // pos 23-27
        $line .= $destAccount;                          // pos 28-40
        $line .= $destAccDig;                           // pos 41
        $line .= str_repeat(' ', 2);                    // pos 42-43
        $line .= $empName;                              // pos 44-73
        $line .= $docNum;                               // pos 74-83
        $line .= $payDate;                              // pos 84-91
        $line .= 'BRL';                                 // pos 92-94
        $line .= $amountCents;                          // pos 95-107
        $line .= str_repeat(' ', 40);                   // pos 108-147
        $line .= '01';                                  // pos 148-149 finalidade TED
        $line .= str_repeat(' ', 244);                  // pad to 393
        $line .= $seq;                                  // pos 394-399
        $line .= '1';                                   // pos 400

        return $this->padLine($line, 400);
    }

    private function buildCnab400Trailer(PaymentBatch $batch, int $qtdRecords, float $totalAmount, int $sequence): string
    {
        $total   = str_pad((string) (int) round($totalAmount * 100), 13, '0', STR_PAD_LEFT);
        $qty     = str_pad((string) ($qtdRecords + 2), 6, '0', STR_PAD_LEFT);
        $seq     = str_pad((string) $sequence, 6, '0', STR_PAD_LEFT);

        $line = '9';                                    // pos 1
        $line .= str_repeat(' ', 393);                  // pos 2-394
        $line .= $total;                                // pos 395-407 -- repacked to fit
        $line .= str_repeat(' ', 0);                    // adjustment

        // Simple trailer: type, blanks, qty, total, seq
        $line = '9';
        $line .= str_repeat(' ', 286);
        $line .= $qty;
        $line .= $total;
        $line .= str_repeat(' ', 89);
        $line .= $seq;
        $line .= '9';

        return $this->padLine($line, 400);
    }

    private function saveFile(PaymentBatch $batch, string $content): string
    {
        $filename  = 'CNAB_' . $batch->id . '_' . now()->format('Ymd_His') . '.REM';
        $directory = 'cnab';
        $path      = $directory . '/' . $filename;

        Storage::put($path, $content);

        return $path;
    }

    private function padLine(string $line, int $length): string
    {
        return mb_str_pad(mb_substr($line, 0, $length), $length, ' ', STR_PAD_RIGHT);
    }

    private function onlyDigits(string $value): string
    {
        return preg_replace('/\D/', '', $value);
    }

    private function removeAccents(string $text): string
    {
        $from = ['á','à','ã','â','ä','é','è','ê','ë','í','ì','î','ï','ó','ò','õ','ô','ö',
                 'ú','ù','û','ü','ç','ñ','Á','À','Ã','Â','Ä','É','È','Ê','Ë','Í','Ì','Î',
                 'Ï','Ó','Ò','Õ','Ô','Ö','Ú','Ù','Û','Ü','Ç','Ñ'];
        $to   = ['a','a','a','a','a','e','e','e','e','i','i','i','i','o','o','o','o','o',
                 'u','u','u','u','c','n','A','A','A','A','A','E','E','E','E','I','I','I',
                 'I','O','O','O','O','O','U','U','U','U','C','N'];

        return str_replace($from, $to, $text);
    }
}
