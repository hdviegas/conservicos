<?php

namespace App\Services;

use App\Enums\DayType;
use App\Enums\TransportVoucherStatus;
use App\Models\Employee;
use App\Models\TransportVoucher;
use Carbon\Carbon;

class TransportVoucherService
{
    /**
     * Calculate and persist a transport voucher for a given employee and period,
     * using the employee's configured transport voucher type.
     */
    public function calculateForPeriod(
        Employee $employee,
        Carbon $periodStart,
        Carbon $periodEnd
    ): ?TransportVoucher {
        if (! $employee->transport_voucher_type_id) {
            return null;
        }

        $employee->loadMissing('transportVoucherType');

        $workedDays = $employee->timeRecords()
            ->whereBetween('date', [$periodStart->toDateString(), $periodEnd->toDateString()])
            ->where('day_type', DayType::Worked->value)
            ->count();

        $dailyValue = (float) $employee->transportVoucherType->daily_value;

        return TransportVoucher::updateOrCreate(
            [
                'employee_id' => $employee->id,
                'period_start' => $periodStart->toDateString(),
                'period_end' => $periodEnd->toDateString(),
            ],
            [
                'transport_voucher_type_id' => $employee->transport_voucher_type_id,
                'worked_days' => $workedDays,
                'daily_value' => $dailyValue,
                'total_value' => round($dailyValue * $workedDays, 2),
                'generated_at' => now(),
                'status' => TransportVoucherStatus::Pending,
            ]
        );
    }

    public function markAsPaid(TransportVoucher $voucher): TransportVoucher
    {
        $voucher->update(['status' => TransportVoucherStatus::Paid]);

        return $voucher;
    }

    public function cancel(TransportVoucher $voucher): TransportVoucher
    {
        $voucher->update(['status' => TransportVoucherStatus::Cancelled]);

        return $voucher;
    }
}
