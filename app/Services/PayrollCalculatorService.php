<?php

namespace App\Services;

use App\Enums\DayType;
use App\Enums\HoursBankType;
use App\Enums\PayrollStatus;
use App\Models\Employee;
use App\Models\HoursBankEntry;
use App\Models\PayrollEntry;
use App\Models\PayrollPeriod;
use App\Models\TimeRecord;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PayrollCalculatorService
{
    public function __construct(
        private readonly DsrCalculatorService $dsrCalculator,
        private readonly HoursBankService $hoursBankService,
    ) {}

    /**
     * Calcula a folha de pagamento para o período informado.
     * Itera todos os funcionários ativos da empresa e calcula cada PayrollEntry.
     */
    public function calculate(PayrollPeriod $payrollPeriod): void
    {
        $payrollPeriod->update(['status' => PayrollStatus::Calculating]);

        try {
            DB::transaction(function () use ($payrollPeriod) {
                $employees = Employee::where('company_id', $payrollPeriod->company_id)
                    ->where('active', true)
                    ->whereNull('termination_date')
                    ->with(['workSchedule', 'position'])
                    ->get();

                foreach ($employees as $employee) {
                    /** @var Employee $employee */
                    $this->calculateEmployee($payrollPeriod, $employee);
                }
            });

            $payrollPeriod->update([
                'status'        => PayrollStatus::Calculated,
                'calculated_at' => now(),
            ]);
        } catch (\Throwable $e) {
            $payrollPeriod->update(['status' => PayrollStatus::Draft]);
            throw $e;
        }
    }

    /**
     * Calcula o PayrollEntry para um único funcionário.
     */
    public function calculateEmployee(PayrollPeriod $payrollPeriod, Employee $employee): PayrollEntry
    {
        $month = $payrollPeriod->month;
        $year  = $payrollPeriod->year;

        $timeRecords = TimeRecord::where('employee_id', $employee->id)
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->get();

        // === SALÁRIO BASE ===
        $baseSalary   = (float) ($employee->position?->base_salary ?? 0);
        $weeklyHours  = (float) ($employee->workSchedule?->weekly_hours ?? 44);
        $monthlyHours = $weeklyHours * 52 / 12;
        $hourlyRate   = $monthlyHours > 0 ? $baseSalary / $monthlyHours : 0.0;

        // === TOTAIS DE DIAS ===
        $workedDays           = $timeRecords->filter(fn ($r) => $this->isDayType($r, DayType::Worked))->count();
        $absenceDays          = $timeRecords->filter(fn ($r) => $this->isDayType($r, DayType::Absence))->count();
        $justifiedAbsenceDays = $timeRecords->filter(fn ($r) => in_array(
            $this->getDayTypeValue($r),
            [DayType::MedicalLeave->value, DayType::WeddingLeave->value, DayType::OtherJustified->value]
        ))->count();
        $sundays  = $timeRecords->filter(fn ($r) => $this->isDayType($r, DayType::Sunday))->count();
        $holidays = $timeRecords->filter(fn ($r) => $this->isDayType($r, DayType::Holiday))->count();
        $folgas   = $timeRecords->filter(fn ($r) => in_array(
            $this->getDayTypeValue($r),
            [DayType::DayOff->value, DayType::BankHoursOff->value, DayType::Vacation->value]
        ))->count();

        // === HORAS (em minutos) ===
        $totalNormalMinutes = (int) $timeRecords->sum('total_normal_hours');
        $totalNightMinutes  = (int) $timeRecords->sum('total_night_hours');
        $overtime50Minutes  = (int) $timeRecords->sum('overtime_50');
        $overtime100Minutes = (int) $timeRecords->sum('overtime_100');

        // === HORAS EXTRAS ===
        $overtime50Value  = round(($overtime50Minutes / 60) * $hourlyRate * 1.5, 2);
        $overtime100Value = round(($overtime100Minutes / 60) * $hourlyRate * 2.0, 2);

        // === ADICIONAL NOTURNO (20% sobre hora normal) ===
        $nightDiffValue = round(($totalNightMinutes / 60) * $hourlyRate * 0.20, 2);

        // === DSR ===
        $dsr = $this->dsrCalculator->calculate($baseSalary, $month, $year, $timeRecords);

        // === VALE TRANSPORTE ===
        $vtDays       = $timeRecords->where('is_worked_day', true)->count();
        $vtDailyValue = $this->getTransportVoucherDailyValue($employee, $month, $year);
        $vtTotal      = round($vtDays * $vtDailyValue, 2);

        // === BANCO DE HORAS ===
        $bankBalance = $this->hoursBankService->getBalance($employee);

        $bankCredit = (int) HoursBankEntry::where('employee_id', $employee->id)
            ->where('type', HoursBankType::Credit->value)
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->sum('minutes');

        $bankDebit = (int) HoursBankEntry::where('employee_id', $employee->id)
            ->where('type', HoursBankType::Debit->value)
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->sum('minutes');

        // === TOTAIS ===
        $grossAdditions  = round($overtime50Value + $overtime100Value + $nightDiffValue + (float) $dsr['final'], 2);
        $grossDeductions = round(min($vtTotal, $baseSalary * 0.06), 2);

        return PayrollEntry::updateOrCreate(
            [
                'payroll_period_id' => $payrollPeriod->id,
                'employee_id'       => $employee->id,
            ],
            [
                'total_worked_days'             => $workedDays,
                'total_absence_days'            => $absenceDays,
                'total_justified_absence_days'  => $justifiedAbsenceDays,
                'total_sundays'                 => $sundays,
                'total_holidays'                => $holidays,
                'total_folgas'                  => $folgas,
                'total_normal_hours'            => $totalNormalMinutes,
                'total_night_hours'             => $totalNightMinutes,
                'overtime_50_hours'             => $overtime50Minutes,
                'overtime_50_value'             => $overtime50Value,
                'overtime_100_hours'            => $overtime100Minutes,
                'overtime_100_value'            => $overtime100Value,
                'night_differential_hours'      => $totalNightMinutes,
                'night_differential_value'      => $nightDiffValue,
                'dsr_base_value'                => $dsr['base'],
                'dsr_discount_value'            => $dsr['discount'],
                'dsr_final_value'               => $dsr['final'],
                'transport_voucher_days'        => $vtDays,
                'transport_voucher_daily_value' => $vtDailyValue,
                'transport_voucher_total'       => $vtTotal,
                'hours_bank_balance'            => $bankBalance,
                'hours_bank_credit'             => $bankCredit,
                'hours_bank_debit'              => $bankDebit,
                'base_salary'                   => $baseSalary,
                'gross_additions'               => $grossAdditions,
                'gross_deductions'              => $grossDeductions,
            ]
        );
    }

    private function isDayType(TimeRecord $record, DayType $type): bool
    {
        return $this->getDayTypeValue($record) === $type->value;
    }

    private function getDayTypeValue(TimeRecord $record): string
    {
        $type = $record->day_type;

        return $type instanceof DayType ? $type->value : (string) $type;
    }

    private function getTransportVoucherDailyValue(Employee $employee, int $month, int $year): float
    {
        // TransportVoucher model will be implemented in Sprint 6
        $modelClass = 'App\\Models\\TransportVoucher';

        try {
            if (class_exists($modelClass)) {
                $vt = $modelClass::where('employee_id', $employee->id)
                    ->where('month', $month)
                    ->where('year', $year)
                    ->first();

                if ($vt) {
                    return (float) $vt->daily_value;
                }
            }
        } catch (\Throwable) {
            // Table may not exist yet
        }

        return 0.0;
    }
}
