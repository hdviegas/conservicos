<?php

namespace App\Exports;

use App\Models\PayrollEntry;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class OvertimeReportExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    public function __construct(
        private readonly int $companyId,
        private readonly int $month,
        private readonly int $year,
    ) {}

    public function query()
    {
        return PayrollEntry::query()
            ->with(['employee.position'])
            ->whereHas('payrollPeriod', fn ($q) => $q
                ->where('company_id', $this->companyId)
                ->where('month', $this->month)
                ->where('year', $this->year)
            )
            ->where(fn ($q) => $q
                ->where('overtime_50_hours', '>', 0)
                ->orWhere('overtime_100_hours', '>', 0)
            )
            ->join('employees', 'payroll_entries.employee_id', '=', 'employees.id')
            ->orderBy('employees.name');
    }

    public function headings(): array
    {
        return [
            'Matrícula',
            'Nome',
            'HE 50% (min)',
            'HE 50% (h:mm)',
            'HE 100% (min)',
            'HE 100% (h:mm)',
            'Total HE (min)',
            'Total HE (h:mm)',
        ];
    }

    public function map($entry): array
    {
        $total = $entry->overtime_50_hours + $entry->overtime_100_hours;

        return [
            $entry->employee->matricula ?? '',
            $entry->employee->name ?? '',
            $entry->overtime_50_hours,
            $this->minutesToHours($entry->overtime_50_hours),
            $entry->overtime_100_hours,
            $this->minutesToHours($entry->overtime_100_hours),
            $total,
            $this->minutesToHours($total),
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '1d4ed8']]],
        ];
    }

    private function minutesToHours(int $minutes): string
    {
        return sprintf('%02d:%02d', intdiv($minutes, 60), $minutes % 60);
    }
}
