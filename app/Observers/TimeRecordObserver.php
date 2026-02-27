<?php

namespace App\Observers;

use App\Enums\AbsenceType;
use App\Enums\DayType;
use App\Enums\HoursBankSource;
use App\Models\Absence;
use App\Models\HoursBankEntry;
use App\Models\TimeRecord;
use App\Services\HoursBankService;

class TimeRecordObserver
{
    public function __construct(
        private readonly HoursBankService $hoursBankService
    ) {}

    public function created(TimeRecord $timeRecord): void
    {
        $this->handleAbsence($timeRecord);
        $this->handleHoursBank($timeRecord);
    }

    public function updated(TimeRecord $timeRecord): void
    {
        HoursBankEntry::where('reference_time_record_id', $timeRecord->id)->delete();

        $this->handleHoursBank($timeRecord);

        if (! Absence::where('employee_id', $timeRecord->employee_id)
            ->where('date', $timeRecord->date)
            ->exists()) {
            $this->handleAbsence($timeRecord);
        }
    }

    private function handleAbsence(TimeRecord $timeRecord): void
    {
        if ($timeRecord->day_type !== DayType::Absence) {
            return;
        }

        $exists = Absence::where('employee_id', $timeRecord->employee_id)
            ->where('date', $timeRecord->date)
            ->exists();

        if ($exists) {
            return;
        }

        Absence::create([
            'employee_id'        => $timeRecord->employee_id,
            'date'               => $timeRecord->date,
            'type'               => AbsenceType::Unjustified->value,
            'justified'          => false,
            'justification_text' => $timeRecord->justification,
            'days_count'         => 1,
        ]);
    }

    private function handleHoursBank(TimeRecord $timeRecord): void
    {
        $employee = $timeRecord->employee;

        if (! $employee) {
            return;
        }

        if ($timeRecord->overtime_50 > 0) {
            $this->hoursBankService->addCredit(
                $employee,
                $timeRecord->date,
                $timeRecord->overtime_50,
                HoursBankSource::OvertimeConversion,
                'Extra 50% importado do ponto',
                $timeRecord->id
            );
        }

        if ($timeRecord->overtime_100 > 0) {
            $this->hoursBankService->addCredit(
                $employee,
                $timeRecord->date,
                $timeRecord->overtime_100,
                HoursBankSource::OvertimeConversion,
                'Extra 100% importado do ponto',
                $timeRecord->id
            );
        }

        if ($timeRecord->day_type === DayType::BankHoursOff) {
            $dailyMinutes = $employee->workSchedule?->daily_hours
                ? (int) ($employee->workSchedule->daily_hours * 60)
                : 480;

            $this->hoursBankService->addDebit(
                $employee,
                $timeRecord->date,
                $dailyMinutes,
                HoursBankSource::BankHoursOff,
                'Folga BH importada do ponto',
                $timeRecord->id
            );
        }

        if ($timeRecord->negative_bank_hours > 0) {
            $this->hoursBankService->addDebit(
                $employee,
                $timeRecord->date,
                $timeRecord->negative_bank_hours,
                HoursBankSource::Import,
                'Banco negativo importado do ponto',
                $timeRecord->id
            );
        }
    }
}
