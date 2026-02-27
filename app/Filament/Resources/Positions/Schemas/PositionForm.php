<?php

namespace App\Filament\Resources\Positions\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class PositionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Cargo')
                    ->required()
                    ->maxLength(255)
                    ->columnSpan(2),
                TextInput::make('weekly_hours')
                    ->label('Carga Horária Semanal (h)')
                    ->numeric()
                    ->step(0.5)
                    ->minValue(0),
                TextInput::make('base_salary')
                    ->label('Salário Base (R$)')
                    ->numeric()
                    ->step(0.01)
                    ->minValue(0)
                    ->prefix('R$'),
                Textarea::make('description')
                    ->label('Descrição')
                    ->maxLength(65535)
                    ->columnSpan(2),
                Toggle::make('active')
                    ->label('Ativo')
                    ->default(true),
            ])
            ->columns(2);
    }
}
