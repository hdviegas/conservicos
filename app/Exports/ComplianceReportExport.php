<?php

namespace App\Exports;

use App\Enums\ComplianceStatus;
use App\Models\EmployeeExam;
use App\Models\EmployeeTraining;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ComplianceReportExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    /** @var array<string> */
    private array $statuses;

    public function __construct(
        private readonly ?int $companyId = null,
        private readonly ?string $statusFilter = null,
    ) {
        $this->statuses = $this->resolveStatuses();
    }

    public function collection(): Collection
    {
        $examsQuery = EmployeeExam::query()
            ->with(['employee.position', 'employee.company', 'exam'])
            ->whereIn('status', $this->statuses);

        $trainingsQuery = EmployeeTraining::query()
            ->with(['employee.position', 'employee.company', 'training'])
            ->whereIn('status', $this->statuses);

        if ($this->companyId) {
            $examsQuery->whereHas('employee', fn ($q) => $q->where('company_id', $this->companyId));
            $trainingsQuery->whereHas('employee', fn ($q) => $q->where('company_id', $this->companyId));
        }

        $exams = $examsQuery->get()->map(fn ($e) => [
            'type'             => 'Exame',
            'employee'         => $e->employee,
            'item_name'        => $e->exam->name ?? '',
            'performed_date'   => $e->performed_date,
            'expiration_date'  => $e->expiration_date,
            'status'           => $e->status,
        ]);

        $trainings = $trainingsQuery->get()->map(fn ($t) => [
            'type'             => 'Treinamento',
            'employee'         => $t->employee,
            'item_name'        => $t->training->name ?? '',
            'performed_date'   => $t->performed_date,
            'expiration_date'  => $t->expiration_date,
            'status'           => $t->status,
        ]);

        return $exams->merge($trainings)->sortBy(fn ($item) => $item['employee']->name ?? '');
    }

    public function headings(): array
    {
        return [
            'Funcionário',
            'Cargo',
            'Empresa',
            'Tipo',
            'Nome do Item',
            'Data Realização',
            'Data Vencimento',
            'Status',
        ];
    }

    public function map($row): array
    {
        $employee = $row['employee'];

        return [
            $employee->name ?? '',
            $employee->position->name ?? '',
            $employee->company->name ?? '',
            $row['type'],
            $row['item_name'],
            $row['performed_date'] ? \Carbon\Carbon::parse($row['performed_date'])->format('d/m/Y') : '',
            $row['expiration_date'] ? \Carbon\Carbon::parse($row['expiration_date'])->format('d/m/Y') : '',
            $row['status'] instanceof ComplianceStatus ? $row['status']->label() : $row['status'],
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '7e22ce']]],
        ];
    }

    private function resolveStatuses(): array
    {
        return match ($this->statusFilter) {
            'expired'      => [ComplianceStatus::Expired->value],
            'expiring_15d' => [ComplianceStatus::Expiring15d->value],
            'expiring_30d' => [ComplianceStatus::Expiring30d->value],
            default        => [
                ComplianceStatus::Expired->value,
                ComplianceStatus::Expiring15d->value,
                ComplianceStatus::Expiring30d->value,
            ],
        };
    }
}
