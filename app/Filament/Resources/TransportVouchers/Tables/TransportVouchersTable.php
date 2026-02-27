<?php

namespace App\Filament\Resources\TransportVouchers\Tables;

use App\Enums\TransportVoucherStatus;
use App\Models\TransportVoucher;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class TransportVouchersTable
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
                    ->sortable()
                    ->badge()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('transportVoucherType.name')
                    ->label('Tipo de VT')
                    ->sortable()
                    ->badge(),
                TextColumn::make('period_start')
                    ->label('Período')
                    ->formatStateUsing(function ($state, TransportVoucher $record): string {
                        $start = Carbon::parse($record->period_start);
                        $end = Carbon::parse($record->period_end);

                        return $start->format('d/m/Y') . ' – ' . $end->format('d/m/Y');
                    })
                    ->sortable(),
                TextColumn::make('worked_days')
                    ->label('Dias')
                    ->sortable(),
                TextColumn::make('daily_value')
                    ->label('Valor Diário')
                    ->money('BRL')
                    ->sortable(),
                TextColumn::make('total_value')
                    ->label('Valor Total')
                    ->money('BRL')
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (TransportVoucherStatus $state): string => $state->color())
                    ->formatStateUsing(fn (TransportVoucherStatus $state): string => $state->label()),
                TextColumn::make('generated_at')
                    ->label('Gerado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('employee_id')
                    ->label('Funcionário')
                    ->relationship('employee', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('transport_voucher_type_id')
                    ->label('Tipo de VT')
                    ->relationship('transportVoucherType', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('status')
                    ->label('Status')
                    ->options(collect(TransportVoucherStatus::cases())->mapWithKeys(
                        fn (TransportVoucherStatus $s) => [$s->value => $s->label()]
                    )),
            ])
            ->recordActions([
                Action::make('markPaid')
                    ->label('Marcar como Pago')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Confirmar Pagamento')
                    ->modalDescription('Deseja marcar este vale transporte como pago?')
                    ->visible(fn (TransportVoucher $record): bool =>
                        $record->status === TransportVoucherStatus::Pending
                    )
                    ->action(function (TransportVoucher $record): void {
                        $record->update(['status' => TransportVoucherStatus::Paid]);
                        Notification::make()
                            ->title('Vale marcado como pago')
                            ->success()
                            ->send();
                    }),
                Action::make('cancel')
                    ->label('Cancelar')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Cancelar Vale Transporte')
                    ->modalDescription('Deseja cancelar este vale transporte?')
                    ->visible(fn (TransportVoucher $record): bool =>
                        $record->status !== TransportVoucherStatus::Cancelled
                    )
                    ->action(function (TransportVoucher $record): void {
                        $record->update(['status' => TransportVoucherStatus::Cancelled]);
                        Notification::make()
                            ->title('Vale cancelado')
                            ->warning()
                            ->send();
                    }),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->defaultSort('period_start', 'desc');
    }
}
