<?php

namespace App\Exports;

use App\Enums\PaymentBatchStatus;
use App\Enums\PaymentBatchType;
use App\Models\PaymentBatch;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PaymentReportExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    public function __construct(
        private readonly int $month,
        private readonly int $year,
        private readonly ?int $companyId = null,
    ) {}

    public function query()
    {
        $query = PaymentBatch::query()
            ->with('company')
            ->where('reference_month', $this->month)
            ->where('reference_year', $this->year)
            ->orderBy('payment_date');

        if ($this->companyId) {
            $query->where('company_id', $this->companyId);
        }

        return $query;
    }

    public function headings(): array
    {
        return [
            'Lote',
            'Tipo',
            'Empresa',
            'Período',
            'Data Pagamento',
            'Total Registros',
            'Total (R$)',
            'Status',
            'Banco',
            'Arquivo Gerado',
        ];
    }

    public function map($batch): array
    {
        return [
            $batch->id,
            $batch->type instanceof PaymentBatchType ? $batch->type->label() : $batch->type,
            $batch->company->name ?? '',
            str_pad((string) $batch->reference_month, 2, '0', STR_PAD_LEFT) . '/' . $batch->reference_year,
            $batch->payment_date->format('d/m/Y'),
            $batch->total_records,
            'R$ ' . number_format((float) $batch->total_amount, 2, ',', '.'),
            $batch->status instanceof PaymentBatchStatus ? $batch->status->label() : $batch->status,
            $batch->bank_code,
            $batch->file_path ? basename($batch->file_path) : '',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '1e3a5f']]],
        ];
    }
}
