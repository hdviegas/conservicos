<?php

namespace App\Filament\Resources\Trainings\Tables;

use App\Enums\TrainingCategory;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class TrainingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('category')
                    ->label('Categoria')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state instanceof TrainingCategory ? $state->label() : $state)
                    ->color('info'),
                TextColumn::make('nr_reference')
                    ->label('NR')
                    ->sortable(),
                TextColumn::make('validity_months')
                    ->label('Validade')
                    ->formatStateUsing(fn ($state) => $state == 0 ? 'Único' : $state . ' meses')
                    ->sortable(),
                TextColumn::make('required_hours')
                    ->label('Carga Horária')
                    ->formatStateUsing(fn ($state) => $state ? $state . 'h' : '-')
                    ->sortable(),
                IconColumn::make('is_mandatory')
                    ->label('Obrigatório')
                    ->boolean(),
                IconColumn::make('active')
                    ->label('Ativo')
                    ->boolean(),
            ])
            ->filters([
                SelectFilter::make('category')
                    ->label('Categoria')
                    ->options(collect(TrainingCategory::cases())->mapWithKeys(
                        fn (TrainingCategory $c) => [$c->value => $c->label()]
                    )),
                TernaryFilter::make('active')
                    ->label('Ativo')
                    ->trueLabel('Ativos')
                    ->falseLabel('Inativos'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->defaultSort('name');
    }
}
