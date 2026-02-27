<?php

namespace App\Filament\Resources\Vacations\Tables;

use App\Enums\VacationStatus;
use Carbon\Carbon;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class VacationsTable
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
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('acquisition_period_start')
                    ->label('Período Aquisitivo')
                    ->formatStateUsing(fn ($state, $record) =>
                        Carbon::parse($state)->format('m/Y') . ' - ' . Carbon::parse($record->acquisition_period_end)->format('m/Y')
                    )
                    ->sortable(),
                TextColumn::make('scheduled_start')
                    ->label('Programado')
                    ->formatStateUsing(fn ($state, $record) =>
                        $state ? Carbon::parse($state)->format('d/m/Y') . ' - ' . ($record->scheduled_end ? Carbon::parse($record->scheduled_end)->format('d/m/Y') : '?') : '-'
                    )
                    ->sortable(),
                TextColumn::make('days_enjoyed')
                    ->label('Dias Gozados')
                    ->sortable(),
                TextColumn::make('days_sold')
                    ->label('Dias Vendidos')
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state instanceof VacationStatus ? $state->label() : $state)
                    ->color(fn ($state) => match ($state instanceof VacationStatus ? $state : VacationStatus::tryFrom((string) $state)) {
                        VacationStatus::Pending => 'gray',
                        VacationStatus::Scheduled => 'info',
                        VacationStatus::InProgress => 'warning',
                        VacationStatus::Completed => 'success',
                        VacationStatus::Expired => 'danger',
                        default => 'gray',
                    }),
                IconColumn::make('expiring_soon')
                    ->label('Alerta')
                    ->getStateUsing(fn ($record) =>
                        $record->acquisition_period_end &&
                        $record->status !== VacationStatus::Completed &&
                        Carbon::parse($record->acquisition_period_end)->diffInDays(now(), false) >= -30 &&
                        Carbon::parse($record->acquisition_period_end)->diffInDays(now(), false) <= 0
                    )
                    ->trueIcon('heroicon-o-exclamation-triangle')
                    ->falseIcon('heroicon-o-minus')
                    ->trueColor('warning')
                    ->falseColor('gray'),
            ])
            ->filters([
                SelectFilter::make('employee_id')
                    ->label('Funcionário')
                    ->relationship('employee', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('status')
                    ->label('Status')
                    ->options(collect(VacationStatus::cases())->mapWithKeys(
                        fn (VacationStatus $s) => [$s->value => $s->label()]
                    )),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->defaultSort('acquisition_period_end', 'desc');
    }
}
