<?php

namespace App\Filament\Resources\WorkSchedules\Tables;

use App\Enums\WorkScheduleType;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class WorkSchedulesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Escala')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('type')
                    ->label('Tipo')
                    ->badge()
                    ->formatStateUsing(fn (WorkScheduleType $state) => $state->label())
                    ->color(fn (WorkScheduleType $state) => $state->color()),
                TextColumn::make('daily_hours')
                    ->label('Horas/Dia')
                    ->suffix('h')
                    ->toggleable(),
                IconColumn::make('is_night_shift')
                    ->label('Noturno')
                    ->boolean()
                    ->trueColor('warning')
                    ->falseColor('gray'),
                IconColumn::make('active')
                    ->label('Ativa')
                    ->boolean()
                    ->trueColor('success')
                    ->falseColor('danger'),
                TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
