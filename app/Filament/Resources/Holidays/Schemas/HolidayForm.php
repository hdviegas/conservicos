<?php

namespace App\Filament\Resources\Holidays\Schemas;

use App\Models\City;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class HolidayForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Feriado')
                    ->required()
                    ->maxLength(255)
                    ->columnSpan(2),
                DatePicker::make('date')
                    ->label('Data')
                    ->required()
                    ->displayFormat('d/m/Y'),
                Select::make('city_id')
                    ->label('Município (vazio = Nacional)')
                    ->options(City::query()->orderBy('state')->orderBy('name')->get()->mapWithKeys(
                        fn (City $city) => [$city->id => "{$city->state} - {$city->name}"]
                    ))
                    ->searchable()
                    ->nullable()
                    ->placeholder('Nacional'),
                Toggle::make('recurring')
                    ->label('Recorrente (anual)')
                    ->default(false),
                Toggle::make('active')
                    ->label('Ativo')
                    ->default(true),
            ])
            ->columns(2);
    }
}
