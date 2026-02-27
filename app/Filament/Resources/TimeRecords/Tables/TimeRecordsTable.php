<?php

namespace App\Filament\Resources\TimeRecords\Tables;

use App\Enums\DayType;
use App\Models\Company;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class TimeRecordsTable
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

                TextColumn::make('day_type')
                    ->label('Tipo')
                    ->badge()
                    ->formatStateUsing(fn ($state) =>
                        $state instanceof DayType ? $state->label() : $state
                    )
                    ->color(fn ($state) =>
                        $state instanceof DayType ? $state->color() : 'gray'
                    ),

                TextColumn::make('entry_1')
                    ->label('Entrada 1')
                    ->time('H:i'),

                TextColumn::make('exit_1')
                    ->label('Saída 1')
                    ->time('H:i'),

                TextColumn::make('entry_2')
                    ->label('Entrada 2')
                    ->time('H:i')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('exit_2')
                    ->label('Saída 2')
                    ->time('H:i')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('total_normal_hours')
                    ->label('Normal')
                    ->formatStateUsing(fn ($state) =>
                        sprintf('%02d:%02d', intdiv((int) $state, 60), (int) $state % 60)
                    ),

                TextColumn::make('overtime_50')
                    ->label('Extra 50%')
                    ->formatStateUsing(fn ($state) =>
                        (int) $state > 0
                            ? sprintf('%02d:%02d', intdiv((int) $state, 60), (int) $state % 60)
                            : '-'
                    )
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('overtime_100')
                    ->label('Extra 100%')
                    ->formatStateUsing(fn ($state) =>
                        (int) $state > 0
                            ? sprintf('%02d:%02d', intdiv((int) $state, 60), (int) $state % 60)
                            : '-'
                    )
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('total_night_hours')
                    ->label('Noturno')
                    ->formatStateUsing(fn ($state) =>
                        (int) $state > 0
                            ? sprintf('%02d:%02d', intdiv((int) $state, 60), (int) $state % 60)
                            : '-'
                    )
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('holiday_name')
                    ->label('Feriado')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('employee_id')
                    ->label('Funcionário')
                    ->relationship('employee', 'name')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('day_type')
                    ->label('Tipo de Dia')
                    ->options(
                        collect(DayType::cases())->mapWithKeys(
                            fn (DayType $t) => [$t->value => $t->label()]
                        )->all()
                    ),

                SelectFilter::make('company')
                    ->label('Empresa')
                    ->options(Company::pluck('name', 'id'))
                    ->query(fn ($query, $data) =>
                        $query->when(
                            $data['value'] ?? null,
                            fn ($q, $v) => $q->whereHas(
                                'employee',
                                fn ($eq) => $eq->where('company_id', $v)
                            )
                        )
                    ),
            ])
            ->defaultSort('date', 'desc');
    }
}
