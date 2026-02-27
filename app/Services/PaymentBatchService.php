<?php

namespace App\Services;

use App\Enums\PaymentBatchType;
use App\Enums\PaymentItemStatus;
use App\Enums\PaymentMethod;
use App\Enums\PayrollStatus;
use App\Models\Employee;
use App\Models\PaymentBatch;
use App\Models\PaymentBatchItem;
use App\Models\PayrollPeriod;
use App\Models\TransportVoucher;
use Illuminate\Support\Facades\DB;

class PaymentBatchService
{
    public function populate(PaymentBatch $batch): void
    {
        DB::transaction(function () use ($batch) {
            match ($batch->type) {
                PaymentBatchType::Salary         => $this->populateFromSalary($batch),
                PaymentBatchType::TransportVoucher => $this->populateFromTransportVoucher($batch),
                default => throw new \RuntimeException(
                    "Tipo de lote '{$batch->type->label()}' não suportado para população automática. Adicione os itens manualmente."
                ),
            };

            $this->recalculateTotals($batch);
        });
    }

    private function populateFromSalary(PaymentBatch $batch): void
    {
        $period = PayrollPeriod::where('company_id', $batch->company_id)
            ->where('month', $batch->reference_month)
            ->where('year', $batch->reference_year)
            ->whereIn('status', [PayrollStatus::Calculated, PayrollStatus::Closed])
            ->first();

        if (!$period) {
            throw new \RuntimeException(
                'Folha de pagamento não calculada para este período. ' .
                'Calcule a folha antes de criar o lote de salário.'
            );
        }

        $batch->items()->delete();

        foreach ($period->entries()->with('employee')->get() as $entry) {
            $employee = $entry->employee;
            $method   = $this->resolvePaymentMethod($employee);
            $amount   = (float) ($entry->gross_additions > 0 ? $entry->gross_additions : $entry->base_salary);

            PaymentBatchItem::create([
                'payment_batch_id' => $batch->id,
                'employee_id'      => $employee->id,
                'amount'           => $amount,
                'payment_method'   => $method,
                'bank_code'        => $method === PaymentMethod::BankTransfer ? $employee->bank_code : null,
                'agency'           => $method === PaymentMethod::BankTransfer ? $employee->agency : null,
                'agency_digit'     => $method === PaymentMethod::BankTransfer ? $employee->agency_digit : null,
                'account_number'   => $method === PaymentMethod::BankTransfer ? $employee->account_number : null,
                'account_digit'    => $method === PaymentMethod::BankTransfer ? $employee->account_digit : null,
                'account_type'     => $method === PaymentMethod::BankTransfer ? $employee->account_type?->value : null,
                'pix_key'          => $method === PaymentMethod::Pix ? $employee->pix_key : null,
                'status'           => PaymentItemStatus::Pending,
                'reference_id'     => $entry->id,
            ]);
        }
    }

    private function populateFromTransportVoucher(PaymentBatch $batch): void
    {
        $vouchers = TransportVoucher::whereHas('employee', fn ($q) => $q->where('company_id', $batch->company_id))
            ->whereMonth('period_start', $batch->reference_month)
            ->whereYear('period_start', $batch->reference_year)
            ->with('employee')
            ->get();

        if ($vouchers->isEmpty()) {
            throw new \RuntimeException(
                'Nenhum vale-transporte encontrado para este período e empresa.'
            );
        }

        $batch->items()->delete();

        foreach ($vouchers as $voucher) {
            $employee = $voucher->employee;
            $method   = $this->resolvePaymentMethod($employee);

            PaymentBatchItem::create([
                'payment_batch_id' => $batch->id,
                'employee_id'      => $employee->id,
                'amount'           => (float) $voucher->total_value,
                'payment_method'   => $method,
                'bank_code'        => $method === PaymentMethod::BankTransfer ? $employee->bank_code : null,
                'agency'           => $method === PaymentMethod::BankTransfer ? $employee->agency : null,
                'agency_digit'     => $method === PaymentMethod::BankTransfer ? $employee->agency_digit : null,
                'account_number'   => $method === PaymentMethod::BankTransfer ? $employee->account_number : null,
                'account_digit'    => $method === PaymentMethod::BankTransfer ? $employee->account_digit : null,
                'account_type'     => $method === PaymentMethod::BankTransfer ? $employee->account_type?->value : null,
                'pix_key'          => $method === PaymentMethod::Pix ? $employee->pix_key : null,
                'status'           => PaymentItemStatus::Pending,
                'reference_id'     => $voucher->id,
            ]);
        }
    }

    private function resolvePaymentMethod(Employee $employee): PaymentMethod
    {
        return (!empty($employee->pix_key)) ? PaymentMethod::Pix : PaymentMethod::BankTransfer;
    }

    private function recalculateTotals(PaymentBatch $batch): void
    {
        $batch->refresh();
        $totals = $batch->items()->selectRaw('COUNT(*) as cnt, SUM(amount) as total')->first();

        $batch->update([
            'total_records' => (int) ($totals->cnt ?? 0),
            'total_amount'  => (float) ($totals->total ?? 0),
        ]);
    }

    /**
     * Returns array of employee names with missing bank data.
     *
     * @return string[]
     */
    public function validateBankData(PaymentBatch $batch): array
    {
        $missing = [];

        foreach ($batch->items()->with('employee')->get() as $item) {
            if ($item->payment_method === PaymentMethod::Pix) {
                if (empty($item->pix_key)) {
                    $missing[] = $item->employee->name . ' (chave PIX ausente)';
                }
            } else {
                if (empty($item->bank_code) || empty($item->agency) || empty($item->account_number)) {
                    $missing[] = $item->employee->name . ' (dados bancários incompletos)';
                }
            }
        }

        return $missing;
    }
}
