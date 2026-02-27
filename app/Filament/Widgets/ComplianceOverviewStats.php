<?php

namespace App\Filament\Widgets;

use App\Enums\ComplianceStatus;
use App\Models\EmployeeExam;
use App\Models\EmployeeTraining;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ComplianceOverviewStats extends StatsOverviewWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 10;

    protected function getStats(): array
    {
        $companyId = $this->filters['company_id'] ?? null;

        $examBase     = EmployeeExam::when($companyId, fn ($q) => $q->whereHas('employee', fn ($e) => $e->where('company_id', $companyId)));
        $trainingBase = EmployeeTraining::when($companyId, fn ($q) => $q->whereHas('employee', fn ($e) => $e->where('company_id', $companyId)));

        $totalExpired     = (clone $examBase)->where('status', ComplianceStatus::Expired)->count()
            + (clone $trainingBase)->where('status', ComplianceStatus::Expired)->count();
        $totalExpiring15d = (clone $examBase)->where('status', ComplianceStatus::Expiring15d)->count()
            + (clone $trainingBase)->where('status', ComplianceStatus::Expiring15d)->count();
        $totalExpiring30d = (clone $examBase)->where('status', ComplianceStatus::Expiring30d)->count()
            + (clone $trainingBase)->where('status', ComplianceStatus::Expiring30d)->count();

        return [
            Stat::make('Exames/Treinamentos Vencidos', $totalExpired)
                ->description('Itens com validade expirada')
                ->color('danger')
                ->icon('heroicon-o-exclamation-circle'),

            Stat::make('Vencendo em 15 dias', $totalExpiring15d)
                ->description('Atenção imediata necessária')
                ->color('warning')
                ->icon('heroicon-o-clock'),

            Stat::make('Vencendo em 30 dias', $totalExpiring30d)
                ->description('Renovação programada recomendada')
                ->color('warning')
                ->icon('heroicon-o-bell-alert'),
        ];
    }
}
