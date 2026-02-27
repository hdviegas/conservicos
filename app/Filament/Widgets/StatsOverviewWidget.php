<?php

namespace App\Filament\Widgets;

use App\Enums\ComplianceStatus;
use App\Models\Absence;
use App\Models\Employee;
use App\Models\EmployeeExam;
use App\Models\EmployeeTraining;
use App\Models\PayrollEntry;
use App\Models\PayrollPeriod;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        $companyId = $this->filters['company_id'] ?? null;

        $activeEmployees = Employee::where('active', true)
            ->whereNull('termination_date')
            ->when($companyId, fn ($q) => $q->where('company_id', $companyId))
            ->count();

        $currentMonth = now()->month;
        $currentYear  = now()->year;

        $periodIds = PayrollPeriod::where('month', $currentMonth)
            ->where('year', $currentYear)
            ->when($companyId, fn ($q) => $q->where('company_id', $companyId))
            ->pluck('id');

        $overtimeMinutes = (int) PayrollEntry::whereIn('payroll_period_id', $periodIds)
            ->selectRaw('COALESCE(SUM(overtime_50_hours + overtime_100_hours), 0) as total')
            ->value('total');

        $overtimeHours     = intdiv($overtimeMinutes, 60);
        $overtimeMin       = $overtimeMinutes % 60;
        $overtimeFormatted = "{$overtimeHours}h {$overtimeMin}min";

        $absenceBase = Absence::whereYear('date', $currentYear)
            ->whereMonth('date', $currentMonth)
            ->when($companyId, fn ($q) => $q->whereHas('employee', fn ($e) => $e->where('company_id', $companyId)));

        $totalAbsences       = (clone $absenceBase)->count();
        $justifiedAbsences   = (clone $absenceBase)->where('justified', true)->count();
        $unjustifiedAbsences = $totalAbsences - $justifiedAbsences;

        $examBase     = EmployeeExam::when($companyId, fn ($q) => $q->whereHas('employee', fn ($e) => $e->where('company_id', $companyId)));
        $trainingBase = EmployeeTraining::when($companyId, fn ($q) => $q->whereHas('employee', fn ($e) => $e->where('company_id', $companyId)));

        $expiredCount  = (clone $examBase)->where('status', ComplianceStatus::Expired)->count()
            + (clone $trainingBase)->where('status', ComplianceStatus::Expired)->count();

        $expiringCount = (clone $examBase)->whereIn('status', [ComplianceStatus::Expiring15d, ComplianceStatus::Expiring30d])->count()
            + (clone $trainingBase)->whereIn('status', [ComplianceStatus::Expiring15d, ComplianceStatus::Expiring30d])->count();

        $complianceTotal = $expiredCount + $expiringCount;
        $complianceColor = $expiredCount > 0 ? 'danger' : ($expiringCount > 0 ? 'warning' : 'success');
        $complianceDesc  = $expiredCount > 0
            ? "{$expiredCount} vencidos, {$expiringCount} a vencer"
            : "{$expiringCount} a vencer nos próximos 30 dias";

        return [
            Stat::make('Funcionários Ativos', $activeEmployees)
                ->description('Total de funcionários ativos')
                ->color('success')
                ->icon('heroicon-o-users'),

            Stat::make('Horas Extras (Mês)', $overtimeFormatted)
                ->description('Total HE 50% + 100% no mês atual')
                ->color('info')
                ->icon('heroicon-o-clock'),

            Stat::make('Faltas (Mês)', $totalAbsences)
                ->description("{$justifiedAbsences} justificadas, {$unjustifiedAbsences} injustificadas")
                ->color($totalAbsences > 0 ? 'warning' : 'success')
                ->icon('heroicon-o-calendar-days'),

            Stat::make('Exames Vencendo', $complianceTotal)
                ->description($complianceDesc)
                ->color($complianceColor)
                ->icon('heroicon-o-shield-exclamation'),
        ];
    }
}
