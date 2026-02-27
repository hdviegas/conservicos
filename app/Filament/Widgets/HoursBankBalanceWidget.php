<?php

namespace App\Filament\Widgets;

use App\Enums\HoursBankType;
use App\Models\HoursBankEntry;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class HoursBankBalanceWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $totalCredit = HoursBankEntry::where('type', HoursBankType::Credit->value)->sum('minutes');
        $totalDebit  = HoursBankEntry::where('type', HoursBankType::Debit->value)->sum('minutes');
        $balance     = $totalCredit - $totalDebit;

        $formatMinutes = fn (int $m) => sprintf('%02d:%02d', intdiv($m, 60), $m % 60);

        return [
            Stat::make('Total Créditos', $formatMinutes((int) $totalCredit))
                ->description('Horas extras acumuladas')
                ->color('success'),
            Stat::make('Total Débitos', $formatMinutes((int) $totalDebit))
                ->description('Folgas e banco negativo')
                ->color('danger'),
            Stat::make('Saldo Geral', $formatMinutes(abs((int) $balance)))
                ->description($balance >= 0 ? 'Saldo positivo' : 'Saldo negativo')
                ->color($balance >= 0 ? 'success' : 'danger'),
        ];
    }
}
