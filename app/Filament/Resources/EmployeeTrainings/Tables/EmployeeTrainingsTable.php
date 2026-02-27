<?php

namespace App\Filament\Resources\EmployeeTrainings\Tables;

use App\Enums\ComplianceStatus;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class EmployeeTrainingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('employee.name')
                    ->label('Funcionário')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('training.name')
                    ->label('Treinamento')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('performed_date')
                    ->label('Realizado em')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('expiration_date')
                    ->label('Vencimento')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state instanceof ComplianceStatus ? $state->label() : $state)
                    ->color(fn ($state) => $state instanceof ComplianceStatus ? $state->color() : 'gray'),
                TextColumn::make('hours_completed')
                    ->label('Horas')
                    ->formatStateUsing(fn ($state) => $state ? $state . 'h' : '-')
                    ->sortable(),
                IconColumn::make('certificate_path')
                    ->label('Certificado')
                    ->boolean()
                    ->trueIcon('heroicon-o-academic-cap')
                    ->falseIcon('heroicon-o-minus'),
            ])
            ->filters([
                SelectFilter::make('employee_id')
                    ->label('Funcionário')
                    ->relationship('employee', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('training_id')
                    ->label('Treinamento')
                    ->relationship('training', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('status')
                    ->label('Status')
                    ->options(collect(ComplianceStatus::cases())->mapWithKeys(
                        fn (ComplianceStatus $s) => [$s->value => $s->label()]
                    )),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->defaultSort('expiration_date', 'asc');
    }
}
