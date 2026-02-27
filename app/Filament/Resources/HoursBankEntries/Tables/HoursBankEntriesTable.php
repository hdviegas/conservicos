<?php

namespace App\Filament\Resources\HoursBankEntries\Tables;

use App\Enums\HoursBankSource;
use App\Enums\HoursBankType;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class HoursBankEntriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('employee.name')
                    ->label('Funcionário')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('date')
                    ->label('Data')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('type')
                    ->label('Tipo')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state instanceof HoursBankType ? $state->label() : $state)
                    ->color(fn ($state) => $state instanceof HoursBankType ? $state->color() : 'gray'),
                TextColumn::make('minutes')
                    ->label('Horas')
                    ->formatStateUsing(fn ($state) =>
                        sprintf('%02d:%02d', intdiv((int) $state, 60), (int) $state % 60)
                    ),
                TextColumn::make('source')
                    ->label('Origem')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state instanceof HoursBankSource ? $state->label() : $state)
                    ->color('gray'),
                TextColumn::make('description')
                    ->label('Descrição')
                    ->limit(50)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('employee_id')
                    ->label('Funcionário')
                    ->relationship('employee', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('type')
                    ->label('Tipo')
                    ->options([
                        HoursBankType::Credit->value => HoursBankType::Credit->label(),
                        HoursBankType::Debit->value  => HoursBankType::Debit->label(),
                    ]),
                SelectFilter::make('source')
                    ->label('Origem')
                    ->options(collect(HoursBankSource::cases())->mapWithKeys(
                        fn (HoursBankSource $s) => [$s->value => $s->label()]
                    )),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->defaultSort('date', 'desc');
    }
}
