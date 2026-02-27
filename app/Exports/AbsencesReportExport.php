<?php

namespace App\Exports;

use App\Models\Absence;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AbsencesReportExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    public function __construct(
        private readonly int $month,
        private readonly int $year,
        private readonly ?int $companyId = null,
    ) {}

    public function query()
    {
        $query = Absence::query()
            ->with(['employee.position', 'employee.company'])
            ->whereYear('date', $this->year)
            ->whereMonth('date', $this->month)
            ->join('employees', 'absences.employee_id', '=', 'employees.id')
            ->orderBy('employees.name')
            ->orderBy('absences.date')
            ->select('absences.*');

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
            'Data',
            'Tipo de Falta',
            'Justificada',
            'CID',
            'Dias',
            'Observações',
        ];
    }

    public function map($absence): array
    {
        return [
            $absence->employee->matricula ?? '',
            $absence->employee->name ?? '',
            $absence->date->format('d/m/Y'),
            $absence->type instanceof \App\Enums\AbsenceType ? $absence->type->label() : $absence->type,
            $absence->justified ? 'Sim' : 'Não',
            $absence->cid_code ?? '',
            $absence->days_count,
            $absence->notes ?? '',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'b91c1c']]],
        ];
    }
}
