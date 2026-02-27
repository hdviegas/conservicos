<?php

namespace App\Filament\Widgets;

use App\Models\PayrollEntry;
use App\Models\PayrollPeriod;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class MonthlyOvertimeChartWidget extends ChartWidget
{
    use InteractsWithPageFilters;

    protected ?string $heading = 'Horas Extras por Mês';

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 1;

    protected function getData(): array
    {
        $ptBrMonths = ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'];
        $companyId  = $this->filters['company_id'] ?? null;

        $labels  = [];
        $data50  = [];
        $data100 = [];

        for ($i = 5; $i >= 0; $i--) {
            $date  = now()->subMonths($i);
            $month = (int) $date->format('n');
            $year  = (int) $date->format('Y');

            $labels[] = $ptBrMonths[$month - 1] . '/' . $date->format('y');

            $periodIds = PayrollPeriod::where('month', $month)
                ->where('year', $year)
                ->when($companyId, fn ($q) => $q->where('company_id', $companyId))
                ->pluck('id');

            $totals = PayrollEntry::whereIn('payroll_period_id', $periodIds)
                ->selectRaw('COALESCE(SUM(overtime_50_hours), 0) as total_50, COALESCE(SUM(overtime_100_hours), 0) as total_100')
                ->first();

            $data50[]  = $totals ? round(($totals->total_50 ?? 0) / 60, 1) : 0;
            $data100[] = $totals ? round(($totals->total_100 ?? 0) / 60, 1) : 0;
        }

        return [
            'datasets' => [
                [
                    'label'           => 'Horas Extras 50%',
                    'data'            => $data50,
                    'backgroundColor' => '#3b82f6',
                ],
                [
                    'label'           => 'Horas Extras 100%',
                    'data'            => $data100,
                    'backgroundColor' => '#f97316',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
