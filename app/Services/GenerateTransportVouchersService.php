<?php

namespace App\Services;

use App\Enums\DayType;
use App\Enums\TransportVoucherStatus;
use App\Models\Employee;
use App\Models\TransportVoucher;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class GenerateTransportVouchersService
{
    public function __construct(
        private readonly WorkingDaysCalculatorService $workingDaysCalculator,
    ) {}

    /**
     * Generate or update transport vouchers for all active employees with a VT type.
     *
     * Formula (pre-paid, two-step):
     *
     *   Step 1 — user confirms in modal:
     *     baseline_days = Mon–Sat working days in period − holidays  (user may override)
     *
     *   Step 2 — per employee at generation time:
     *     schedule_days   = adjustForSchedule(baseline_days, employee.work_schedule)
     *                       • Regular Mon–Sat: baseline_days (unchanged)
     *                       • Regular Mon–Fri: recalculated for Mon–Fri minus holidays
     *                       • 12x36:           ceil(baseline_days / 2)
     *                       • 6x2:             floor(baseline_days × 6/8)
     *     prev_absences   = unjustified absences in the preceding period of same length
     *     billable_days   = max(0, schedule_days − prev_absences)
     *     total_value     = billable_days × daily_value
     *
     * @return array{vouchers: Collection, skipped: int}
     */
    public function generateForPeriod(Carbon $periodStart, Carbon $periodEnd, int $confirmedWorkableDays): array
    {
        $periodStart = $periodStart->startOfDay();
        $periodEnd   = $periodEnd->endOfDay();

        // Previous period: same duration, ending the day before periodStart.
        $periodDays      = (int) $periodStart->diffInDays($periodEnd);
        $prevPeriodEnd   = $periodStart->copy()->subDay();
        $prevPeriodStart = $prevPeriodEnd->copy()->subDays($periodDays);

        /** @var \Illuminate\Database\Eloquent\Collection<int, Employee> $employees */
        $employees = Employee::with(['transportVoucherType', 'workSchedule'])
            ->whereNull('termination_date')
            ->where('active', true)
            ->whereNotNull('transport_voucher_type_id')
            ->get();

        $vouchers = collect();
        $skipped  = 0;

        foreach ($employees as $employee) {
            /** @var Employee $employee */

            // Apply the employee's schedule pattern to the user-confirmed baseline.
            $workableDays = $this->workingDaysCalculator->adjustForSchedule(
                $employee->workSchedule,
                $confirmedWorkableDays,
                $periodStart->copy(),
                $periodEnd->copy(),
            );

            // Unjustified absences in the previous period.
            $previousAbsences = $employee->timeRecords()
                ->whereBetween('date', [$prevPeriodStart->toDateString(), $prevPeriodEnd->toDateString()])
                ->where('day_type', DayType::Absence->value)
                ->count();

            $billableDays = max(0, $workableDays - $previousAbsences);

            if ($billableDays === 0) {
                Log::info('TransportVoucher: skipping employee — 0 billable days', [
                    'employee_id'        => $employee->id,
                    'employee_name'      => $employee->name,
                    'workable_days'      => $workableDays,
                    'previous_absences'  => $previousAbsences,
                    'period'             => $periodStart->toDateString() . ' → ' . $periodEnd->toDateString(),
                    'previous_period'    => $prevPeriodStart->toDateString() . ' → ' . $prevPeriodEnd->toDateString(),
                ]);
                $skipped++;

                continue;
            }

            $dailyValue = (float) $employee->transportVoucherType->daily_value;
            $totalValue = round($dailyValue * $billableDays, 2);

            $voucher = TransportVoucher::updateOrCreate(
                [
                    'employee_id'  => $employee->id,
                    'period_start' => $periodStart->toDateString(),
                    'period_end'   => $periodEnd->toDateString(),
                ],
                [
                    'transport_voucher_type_id' => $employee->transport_voucher_type_id,
                    'worked_days'  => $billableDays,
                    'daily_value'  => $dailyValue,
                    'total_value'  => $totalValue,
                    'generated_at' => now(),
                    'status'       => TransportVoucherStatus::Pending,
                ]
            );

            $vouchers->push($voucher);
        }

        Log::info('TransportVoucher: generation completed', [
            'period'             => $periodStart->toDateString() . ' → ' . $periodEnd->toDateString(),
            'previous_period'    => $prevPeriodStart->toDateString() . ' → ' . $prevPeriodEnd->toDateString(),
            'generated'          => $vouchers->count(),
            'skipped_zero_days'  => $skipped,
        ]);

        return ['vouchers' => $vouchers, 'skipped' => $skipped];
    }
}
