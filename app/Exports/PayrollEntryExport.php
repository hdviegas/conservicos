<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PayrollEntryExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    public function __construct(
        private readonly Collection $records
    ) {}

    public function collection(): Collection
    {
        return $this->records;
    }

    public function headings(): array
    {
        return [
            'Funcionário',
            'CPF',
            'Empresa',
            'Departamento',
            'Cargo',
            'Período',
            'Salário Base',
            'Dias Trabalhados',
            'Faltas',
            'Faltas Justificadas',
            'Domingos/Feriados',
            'Horas Normais',
            'Horas Noturnas',
            'Extra 50% (H)',
            'Extra 50% (R$)',
            'Extra 100% (H)',
            'Extra 100% (R$)',
            'Ad. Noturno (R$)',
            'DSR Bruto',
            'Desc. DSR',
            'DSR Final',
            'VT - Dias',
            'VT - Diário',
            'VT - Total',
            'BH - Crédito',
            'BH - Débito',
            'BH - Saldo',
            'Total Proventos',
            'Total Descontos',
            'Observações',
        ];
    }

    public function map($entry): array
    {
        $fmt = fn (int $min) => sprintf('%02d:%02d', intdiv($min, 60), $min % 60);
        $fmtBalance = fn (int $min) => ($min >= 0 ? '+' : '-') . $fmt(abs($min));

        return [
            $entry->employee->name ?? '',
            $entry->employee->cpf ?? '',
            $entry->employee->company->name ?? '',
            $entry->employee->department->name ?? '',
            $entry->employee->position->name ?? '',
            str_pad((string) $entry->payrollPeriod->month, 2, '0', STR_PAD_LEFT) . '/' . $entry->payrollPeriod->year,
            number_format((float) $entry->base_salary, 2, ',', '.'),
            $entry->total_worked_days,
            $entry->total_absence_days,
            $entry->total_justified_absence_days,
            (int) $entry->total_sundays + (int) $entry->total_holidays,
            $fmt((int) $entry->total_normal_hours),
            $fmt((int) $entry->total_night_hours),
            $fmt((int) $entry->overtime_50_hours),
            number_format((float) $entry->overtime_50_value, 2, ',', '.'),
            $fmt((int) $entry->overtime_100_hours),
            number_format((float) $entry->overtime_100_value, 2, ',', '.'),
            number_format((float) $entry->night_differential_value, 2, ',', '.'),
            number_format((float) $entry->dsr_base_value, 2, ',', '.'),
            number_format((float) $entry->dsr_discount_value, 2, ',', '.'),
            number_format((float) $entry->dsr_final_value, 2, ',', '.'),
            $entry->transport_voucher_days,
            number_format((float) $entry->transport_voucher_daily_value, 2, ',', '.'),
            number_format((float) $entry->transport_voucher_total, 2, ',', '.'),
            $fmt((int) $entry->hours_bank_credit),
            $fmt((int) $entry->hours_bank_debit),
            $fmtBalance((int) $entry->hours_bank_balance),
            number_format((float) $entry->gross_additions, 2, ',', '.'),
            number_format((float) $entry->gross_deductions, 2, ',', '.'),
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
}
