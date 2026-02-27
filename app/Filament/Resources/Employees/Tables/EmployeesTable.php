<?php

namespace App\Filament\Resources\Employees\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class EmployeesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('cpf')
                    ->label('CPF')
                    ->searchable(),
                TextColumn::make('company.name')
                    ->label('Empresa')
                    ->sortable()
                    ->badge(),
                TextColumn::make('department.name')
                    ->label('Departamento')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('position.name')
                    ->label('Cargo')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('transportVoucherType.name')
                    ->label('Vale Transporte')
                    ->sortable()
                    ->badge()
                    ->color('info')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('admission_date')
                    ->label('Admissão')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('termination_date')
                    ->label('Demissão')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('active')
                    ->label('Ativo')
                    ->boolean()
                    ->trueColor('success')
                    ->falseColor('danger'),
            ])
            ->filters([
                SelectFilter::make('company_id')
                    ->label('Empresa')
                    ->relationship('company', 'name'),
                SelectFilter::make('department_id')
                    ->label('Departamento')
                    ->relationship('department', 'name'),
                SelectFilter::make('position_id')
                    ->label('Cargo')
                    ->relationship('position', 'name'),
                TernaryFilter::make('active')
                    ->label('Status')
                    ->trueLabel('Ativos')
                    ->falseLabel('Demitidos')
                    ->queries(
                        true: fn (Builder $query) => $query->whereNull('termination_date'),
                        false: fn (Builder $query) => $query->whereNotNull('termination_date'),
                    ),
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
