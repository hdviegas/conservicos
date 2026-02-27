<?php

namespace App\Exports;

use App\Models\Employee;
use App\Models\HoursBankEntry;
use App\Services\HoursBankService;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class HoursBankExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    public function __construct(
        private readonly int $month,
        private readonly int $year,
        private readonly ?int $companyId = null,
    ) {}

    public function collection()
    {
        $query = Employee::query()
            ->with(['position', 'company'])
            ->where('active', true)
            ->whereNull('termination_date')
            ->orderBy('name');

        if ($this->companyId) {
            $query->where('company_id', $this->companyId);
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'Matrícula',
            'Nome',
            'Empresa',
            'Cargo',
            'Saldo (min)',
            'Saldo (h:mm)',
            'Créditos (min)',
            'Débitos (min)',
            'Última Movimentação',
        ];
    }

    public function map($employee): array
    {
        $service = app(HoursBankService::class);
        $balance = $service->getBalance($employee);

        $credits = (int) HoursBankEntry::where('employee_id', $employee->id)
            ->where('type', \App\Enums\HoursBankType::Credit)
            ->sum('minutes');

        $debits = (int) HoursBankEntry::where('employee_id', $employee->id)
            ->where('type', \App\Enums\HoursBankType::Debit)
            ->sum('minutes');

        $lastEntry = HoursBankEntry::where('employee_id', $employee->id)
            ->orderByDesc('date')
            ->first();

        $sign = $balance < 0 ? '-' : '';
        $abs  = abs($balance);
        $balanceFormatted = sprintf('%s%02d:%02d', $sign, intdiv($abs, 60), $abs % 60);

        return [
            $employee->matricula ?? '',
            $employee->name,
            $employee->company->name ?? '',
            $employee->position->name ?? '',
            $balance,
            $balanceFormatted,
            $credits,
            $debits,
            $lastEntry ? \Carbon\Carbon::parse($lastEntry->date)->format('d/m/Y') : '',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '0f766e']]],
        ];
    }
}
