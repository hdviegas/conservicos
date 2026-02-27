<?php

namespace App\Services;

use App\Enums\DayType;
use App\Enums\HoursBankSource;
use App\Enums\HoursBankType;
use App\Models\Employee;
use App\Models\HoursBankEntry;
use App\Models\TimeImport;
use App\Models\TimeRecord;
use Carbon\Carbon;

class HoursBankService
{
    public function addCredit(
        Employee $employee,
        Carbon|string $date,
        int $minutes,
        HoursBankSource $source,
        ?string $description = null,
        ?int $referenceTimeRecordId = null
    ): HoursBankEntry {
        return HoursBankEntry::create([
            'employee_id'              => $employee->id,
            'date'                     => $date instanceof Carbon ? $date->format('Y-m-d') : $date,
            'type'                     => HoursBankType::Credit->value,
            'minutes'                  => abs($minutes),
            'source'                   => $source->value,
            'description'              => $description,
            'reference_time_record_id' => $referenceTimeRecordId,
        ]);
    }

    public function addDebit(
        Employee $employee,
        Carbon|string $date,
        int $minutes,
        HoursBankSource $source,
        ?string $description = null,
        ?int $referenceTimeRecordId = null
    ): HoursBankEntry {
        return HoursBankEntry::create([
            'employee_id'              => $employee->id,
            'date'                     => $date instanceof Carbon ? $date->format('Y-m-d') : $date,
            'type'                     => HoursBankType::Debit->value,
            'minutes'                  => abs($minutes),
            'source'                   => $source->value,
            'description'              => $description,
            'reference_time_record_id' => $referenceTimeRecordId,
        ]);
    }

    /**
     * Returns current hours bank balance in minutes.
     * Positive = credit, negative = debit.
     */
    public function getBalance(Employee $employee): int
    {
        $credits = HoursBankEntry::where('employee_id', $employee->id)
            ->where('type', HoursBankType::Credit->value)
            ->sum('minutes');

        $debits = HoursBankEntry::where('employee_id', $employee->id)
            ->where('type', HoursBankType::Debit->value)
            ->sum('minutes');

        return (int) $credits - (int) $debits;
    }

    public function getBalanceFormatted(Employee $employee): string
    {
        $balance = $this->getBalance($employee);
        $sign = $balance < 0 ? '-' : '';
        $abs = abs($balance);

        return sprintf('%s%02d:%02d', $sign, intdiv($abs, 60), $abs % 60);
    }

    /**
     * Batch-processes all time_records from an import to populate hours bank entries.
     * Used for manual reprocessing. When the Observer is registered, this is not needed
     * for regular imports (Observer handles each record individually).
     */
    public function processTimeRecords(TimeImport $timeImport): void
    {
        $records = TimeRecord::where('time_import_id', $timeImport->id)
            ->with('employee')
            ->get();

        foreach ($records as $record) {
            if (! $record->employee) {
                continue;
            }

            $employee = $record->employee;
            $date = $record->date;

            if ($record->overtime_50 > 0) {
                $this->addCredit(
                    $employee,
                    $date,
                    $record->overtime_50,
                    HoursBankSource::OvertimeConversion,
                    'Extra 50% importado do ponto',
                    $record->id
                );
            }

            if ($record->overtime_100 > 0) {
                $this->addCredit(
                    $employee,
                    $date,
                    $record->overtime_100,
                    HoursBankSource::OvertimeConversion,
                    'Extra 100% importado do ponto',
                    $record->id
                );
            }

            if ($record->day_type === DayType::BankHoursOff) {
                $dailyMinutes = $record->employee->workSchedule?->daily_hours
                    ? (int) ($record->employee->workSchedule->daily_hours * 60)
                    : 480;

                $this->addDebit(
                    $employee,
                    $date,
                    $dailyMinutes,
                    HoursBankSource::BankHoursOff,
                    'Folga BH importada do ponto',
                    $record->id
                );
            }

            if ($record->negative_bank_hours > 0) {
                $this->addDebit(
                    $employee,
                    $date,
                    $record->negative_bank_hours,
                    HoursBankSource::Import,
                    'Banco negativo importado do ponto',
                    $record->id
                );
            }
        }
    }
}
