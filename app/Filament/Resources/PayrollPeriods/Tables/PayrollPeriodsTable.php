<?php

namespace App\Filament\Resources\PayrollPeriods\Tables;

use App\Enums\PayrollStatus;
use App\Models\PayrollPeriod;
use App\Services\PayrollCalculatorService;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PayrollPeriodsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('company.name')
                    ->label('Empresa')
                    ->sortable(),
                TextColumn::make('period')
                    ->label('Período')
                    ->state(fn (PayrollPeriod $record) =>
                        str_pad((string) $record->month, 2, '0', STR_PAD_LEFT) . '/' . $record->year
                    )
                    ->sortable(['year', 'month']),
                TextColumn::make('entries_count')
                    ->label('Funcionários')
                    ->counts('entries'),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn ($state) =>
                        $state instanceof PayrollStatus ? $state->label() : $state
                    )
                    ->color(fn ($state) =>
                        $state instanceof PayrollStatus ? $state->color() : 'gray'
                    ),
                TextColumn::make('calculated_at')
                    ->label('Calculado em')
                    ->dateTime('d/m/Y H:i')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('closed_at')
                    ->label('Fechado em')
                    ->dateTime('d/m/Y H:i')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordActions([
                ViewAction::make(),
                Action::make('calculate')
                    ->label('Calcular Folha')
                    ->icon('heroicon-o-calculator')
                    ->color('primary')
                    ->visible(fn (PayrollPeriod $record) => in_array(
                        $record->status,
                        [PayrollStatus::Draft, PayrollStatus::Calculated]
                    ))
                    ->requiresConfirmation()
                    ->modalHeading('Calcular Folha de Pagamento')
                    ->modalDescription('O cálculo será executado agora. Entradas anteriores serão recalculadas.')
                    ->action(function (PayrollPeriod $record) {
                        try {
                            app(PayrollCalculatorService::class)->calculate($record);
                            Notification::make()
                                ->title('Folha calculada com sucesso!')
                                ->success()
                                ->send();
                        } catch (\Throwable $e) {
                            Notification::make()
                                ->title('Erro ao calcular folha')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
                Action::make('close')
                    ->label('Fechar Período')
                    ->icon('heroicon-o-lock-closed')
                    ->color('success')
                    ->visible(fn (PayrollPeriod $record) => in_array(
                        $record->status,
                        [PayrollStatus::Calculated, PayrollStatus::Reviewed]
                    ))
                    ->requiresConfirmation()
                    ->modalHeading('Fechar Período')
                    ->modalDescription('Após fechar, o período não poderá ser recalculado. Confirma?')
                    ->action(function (PayrollPeriod $record) {
                        $record->update([
                            'status'    => PayrollStatus::Closed,
                            'closed_at' => now(),
                            'closed_by' => auth()->id(),
                        ]);
                        Notification::make()
                            ->title('Período fechado com sucesso!')
                            ->success()
                            ->send();
                    }),
                Action::make('reopen')
                    ->label('Reabrir')
                    ->icon('heroicon-o-lock-open')
                    ->color('warning')
                    ->visible(fn (PayrollPeriod $record) => $record->status === PayrollStatus::Closed)
                    ->requiresConfirmation()
                    ->action(function (PayrollPeriod $record) {
                        $record->update([
                            'status'    => PayrollStatus::Calculated,
                            'closed_at' => null,
                            'closed_by' => null,
                        ]);
                        Notification::make()
                            ->title('Período reaberto!')
                            ->success()
                            ->send();
                    }),
            ])
            ->defaultSort('year', 'desc');
    }
}
