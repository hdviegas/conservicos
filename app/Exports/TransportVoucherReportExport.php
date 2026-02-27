<?php

namespace App\Exports;

use App\Models\TransportVoucher;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TransportVoucherReportExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    public function __construct(
        private readonly int $month,
        private readonly int $year,
        private readonly ?int $companyId = null,
    ) {}

    public function query()
    {
        $query = TransportVoucher::query()
            ->with(['employee.company'])
            ->where('month', $this->month)
            ->where('year', $this->year)
            ->join('employees', 'transport_vouchers.employee_id', '=', 'employees.id')
            ->orderBy('employees.name')
            ->select('transport_vouchers.*');

        if ($this->companyId) {
            $query->where('employees.company_id', $this->companyId);
        }

        return $query;
    }

    public function headings(): array
    {
        return [
            'Matrícula',
            'Nome',
            'Empresa',
            'Dias Trabalhados',
            'Valor Diário',
            'Total VT',
        ];
    }

    public function map($voucher): array
    {
        return [
            $voucher->employee->matricula ?? '',
            $voucher->employee->name ?? '',
            $voucher->employee->company->name ?? '',
            $voucher->total_days,
            'R$ ' . number_format((float) $voucher->daily_value, 2, ',', '.'),
            'R$ ' . number_format((float) $voucher->total_value, 2, ',', '.'),
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '047857']]],
        ];
    }
}
