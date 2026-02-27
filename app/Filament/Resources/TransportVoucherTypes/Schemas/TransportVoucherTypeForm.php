<?php

namespace App\Filament\Resources\TransportVoucherTypes\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class TransportVoucherTypeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->label('Nome')
                ->required()
                ->maxLength(255),
            TextInput::make('description')
                ->label('Descrição')
                ->maxLength(500)
                ->columnSpanFull(),
            TextInput::make('daily_value')
                ->label('Valor Diário (R$)')
                ->required()
                ->numeric()
                ->prefix('R$')
                ->step('0.01')
                ->minValue(0),
            Toggle::make('active')
                ->label('Ativo')
                ->default(true),
        ])->columns(2);
    }
}
