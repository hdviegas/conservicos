<?php

namespace App\Filament\Resources\PayrollEntries\Tables;

use App\Exports\PayrollEntryExport;
use App\Models\Company;
use App\Models\PayrollEntry;
use App\Models\PayrollPeriod;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Maatwebsite\Excel\Facades\Excel;

class PayrollEntriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('employee.name')
                    ->label('Funcionário')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('employee.company.name')
                    ->label('Empresa')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('payrollPeriod.period_label')
                    ->label('Período'),
                TextColumn::make('base_salary')
                    ->label('Salário Base')
                    ->money('BRL'),
                TextColumn::make('total_worked_days')
                    ->label('Dias Trab.'),
                TextColumn::make('total_absence_days')
                    ->label('Faltas'),
                TextColumn::make('overtime_50_hours')
                    ->label('Extra 50%')
                    ->formatStateUsing(fn ($state) =>
                        sprintf('%02d:%02d', intdiv((int) $state, 60), (int) $state % 60)
                    ),
                TextColumn::make('overtime_50_value')
                    ->label('R$ Extra 50%')
                    ->money('BRL'),
                TextColumn::make('overtime_100_hours')
                    ->label('Extra 100%')
                    ->formatStateUsing(fn ($state) =>
                        sprintf('%02d:%02d', intdiv((int) $state, 60), (int) $state % 60)
                    )
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('overtime_100_value')
                    ->label('R$ Extra 100%')
                    ->money('BRL')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('night_differential_value')
                    ->label('R$ Ad. Noturno')
                    ->money('BRL')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('dsr_final_value')
                    ->label('R$ DSR')
                    ->money('BRL'),
                TextColumn::make('transport_voucher_total')
                    ->label('R$ VT')
                    ->money('BRL'),
                TextColumn::make('gross_additions')
                    ->label('Total Proventos')
                    ->money('BRL'),
                TextColumn::make('hours_bank_balance')
                    ->label('Saldo BH')
                    ->formatStateUsing(function ($state) {
                        $state = (int) $state;
                        $sign  = $state < 0 ? '-' : '+';
                        $abs   = abs($state);

                        return $sign . sprintf('%02d:%02d', intdiv($abs, 60), $abs % 60);
                    })
                    ->color(fn ($state) => (int) $state >= 0 ? 'success' : 'danger'),
            ])
            ->filters([
                SelectFilter::make('payroll_period_id')
                    ->label('Período')
                    ->options(function () {
                        return PayrollPeriod::with('company')
                            ->orderByDesc('year')
                            ->orderByDesc('month')
                            ->get()
                            ->mapWithKeys(fn ($p) => [
                                $p->id => ($p->company->name ?? '?') . ' — ' .
                                    str_pad((string) $p->month, 2, '0', STR_PAD_LEFT) . '/' . $p->year,
                            ]);
                    })
                    ->searchable(),
                SelectFilter::make('company')
                    ->label('Empresa')
                    ->options(Company::pluck('name', 'id'))
                    ->query(fn ($query, array $data) =>
                        $query->when(
                            $data['value'] ?? null,
                            fn ($q, $v) => $q->whereHas(
                                'employee',
                                fn ($eq) => $eq->where('company_id', $v)
                            )
                        )
                    ),
            ])
            ->headerActions([
                Action::make('export')
                    ->label('Exportar XLSX')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->action(function ($livewire) {
                        $records = $livewire->getFilteredTableQuery()
                            ->with(['employee.company', 'employee.department', 'employee.position', 'payrollPeriod'])
                            ->get();

                        return Excel::download(
                            new PayrollEntryExport($records),
                            'folha_pagamento_' . now()->format('Y_m_d') . '.xlsx'
                        );
                    }),
            ])
            ->defaultSort('employee_id');
    }
}
