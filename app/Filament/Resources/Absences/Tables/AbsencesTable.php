<?php

namespace App\Filament\Resources\Absences\Tables;

use App\Enums\AbsenceType;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class AbsencesTable
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
                TextColumn::make('date')
                    ->label('Data')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('type')
                    ->label('Tipo')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state instanceof AbsenceType ? $state->label() : $state)
                    ->color(fn ($state) => $state instanceof AbsenceType ? $state->color() : 'gray'),
                IconColumn::make('justified')
                    ->label('Justificada')
                    ->boolean(),
                TextColumn::make('cid_code')
                    ->label('CID')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('days_count')
                    ->label('Dias'),
                IconColumn::make('attachment_path')
                    ->label('Atestado')
                    ->boolean()
                    ->trueIcon('heroicon-o-paper-clip')
                    ->falseIcon('heroicon-o-minus'),
            ])
            ->filters([
                SelectFilter::make('employee_id')
                    ->label('Funcionário')
                    ->relationship('employee', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('type')
                    ->label('Tipo')
                    ->options(collect(AbsenceType::cases())->mapWithKeys(
                        fn (AbsenceType $t) => [$t->value => $t->label()]
                    )),
                TernaryFilter::make('justified')
                    ->label('Justificada')
                    ->trueLabel('Justificadas')
                    ->falseLabel('Injustificadas'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->defaultSort('date', 'desc');
    }
}
