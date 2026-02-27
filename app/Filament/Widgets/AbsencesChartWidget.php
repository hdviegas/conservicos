<?php

namespace App\Filament\Widgets;

use App\Enums\AbsenceType;
use App\Models\Absence;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class AbsencesChartWidget extends ChartWidget
{
    use InteractsWithPageFilters;

    protected ?string $heading = 'Faltas por Tipo';

    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 1;

    protected function getData(): array
    {
        $ptBrMonths  = ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'];
        $companyId   = $this->filters['company_id'] ?? null;
        $labels      = [];
        $monthsRange = [];

        for ($i = 5; $i >= 0; $i--) {
            $date  = now()->subMonths($i);
            $month = (int) $date->format('n');
            $year  = (int) $date->format('Y');

            $labels[]      = $ptBrMonths[$month - 1] . '/' . $date->format('y');
            $monthsRange[] = ['month' => $month, 'year' => $year];
        }

        $colorMap = [
            AbsenceType::Unjustified->value        => '#ef4444',
            AbsenceType::MedicalCertificate->value => '#f59e0b',
            AbsenceType::WeddingLeave->value       => '#8b5cf6',
            AbsenceType::PaternityLeave->value     => '#3b82f6',
            AbsenceType::BereavementLeave->value   => '#6b7280',
            AbsenceType::OtherJustified->value     => '#10b981',
        ];

        $datasets = [];

        foreach (AbsenceType::cases() as $type) {
            $data = [];
            foreach ($monthsRange as $period) {
                $data[] = Absence::where('type', $type)
                    ->whereYear('date', $period['year'])
                    ->whereMonth('date', $period['month'])
                    ->when($companyId, fn ($q) => $q->whereHas('employee', fn ($e) => $e->where('company_id', $companyId)))
                    ->count();
            }

            if (array_sum($data) > 0) {
                $datasets[] = [
                    'label'           => $type->label(),
                    'data'            => $data,
                    'backgroundColor' => $colorMap[$type->value] ?? '#6b7280',
                ];
            }
        }

        if (empty($datasets)) {
            $datasets[] = [
                'label'           => 'Sem registros',
                'data'            => array_fill(0, 6, 0),
                'backgroundColor' => '#e5e7eb',
            ];
        }

        return [
            'datasets' => $datasets,
            'labels'   => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
