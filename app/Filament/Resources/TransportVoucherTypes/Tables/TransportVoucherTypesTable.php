<?php

namespace App\Filament\Resources\TransportVoucherTypes\Tables;

use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class TransportVoucherTypesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('description')
                    ->label('Descrição')
                    ->limit(60)
                    ->toggleable(),
                TextColumn::make('daily_value')
                    ->label('Valor Diário')
                    ->money('BRL')
                    ->sortable(),
                IconColumn::make('active')
                    ->label('Ativo')
                    ->boolean()
                    ->trueColor('success')
                    ->falseColor('danger'),
                TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('active')
                    ->label('Status')
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
