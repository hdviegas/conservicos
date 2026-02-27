<?php

namespace App\Exports;

use App\Models\PayrollEntry;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PayrollExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    public function __construct(
        private readonly int $companyId,
        private readonly int $month,
        private readonly int $year,
    ) {}

    public function query()
    {
        return PayrollEntry::query()
            ->with(['employee.position', 'employee.company', 'employee.department'])
            ->whereHas('payrollPeriod', fn ($q) => $q
                ->where('company_id', $this->companyId)
                ->where('month', $this->month)
                ->where('year', $this->year)
            )
            ->join('employees', 'payroll_entries.employee_id', '=', 'employees.id')
            ->orderBy('employees.name');
    }

    public function headings(): array
    {
        return [
            'Matrícula',
            'Nome',
            'Empresa',
            'Cargo',
            'Salário Base',
            'Dias Trabalhados',
            'Faltas',
            'Faltas Justificadas',
            'Horas Normais (h)',
            'Horas Noturnas (h)',
            'HE 50% (h)',
            'HE 50% (R$)',
            'HE 100% (h)',
            'HE 100% (R$)',
            'Adic. Noturno (h)',
            'Adic. Noturno (R$)',
            'DSR Base',
            'Desconto DSR',
            'DSR Final',
            'Vale Transporte (dias)',
            'VT (R$)',
            'Banco de Horas (saldo)',
            'Observações',
        ];
    }

    public function map($entry): array
    {
        return [
            $entry->employee->matricula ?? '',
            $entry->employee->name ?? '',
            $entry->employee->company->name ?? '',
            $entry->employee->position->name ?? '',
            'R$ ' . number_format((float) $entry->base_salary, 2, ',', '.'),
            $entry->total_worked_days,
            $entry->total_absence_days,
            $entry->total_justified_absence_days,
            $this->minutesToHours($entry->total_normal_hours),
            $this->minutesToHours($entry->total_night_hours),
            $this->minutesToHours($entry->overtime_50_hours),
            'R$ ' . number_format((float) $entry->overtime_50_value, 2, ',', '.'),
            $this->minutesToHours($entry->overtime_100_hours),
            'R$ ' . number_format((float) $entry->overtime_100_value, 2, ',', '.'),
            $this->minutesToHours($entry->night_differential_hours),
            'R$ ' . number_format((float) $entry->night_differential_value, 2, ',', '.'),
            'R$ ' . number_format((float) $entry->dsr_base_value, 2, ',', '.'),
            'R$ ' . number_format((float) $entry->dsr_discount_value, 2, ',', '.'),
            'R$ ' . number_format((float) $entry->dsr_final_value, 2, ',', '.'),
            $entry->transport_voucher_days,
            'R$ ' . number_format((float) $entry->transport_voucher_total, 2, ',', '.'),
            $this->minutesToSignedHours($entry->hours_bank_balance),
            $entry->observations ?? '',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '1e40af']],
            ],
        ];
    }

    private function minutesToHours(int $minutes): string
    {
        $h   = intdiv(abs($minutes), 60);
        $min = abs($minutes) % 60;
        return sprintf('%02d:%02d', $h, $min);
    }

    private function minutesToSignedHours(int $minutes): string
    {
        $sign = $minutes < 0 ? '-' : '';
        $h    = intdiv(abs($minutes), 60);
        $min  = abs($minutes) % 60;
        return sprintf('%s%02d:%02d', $sign, $h, $min);
    }
}
