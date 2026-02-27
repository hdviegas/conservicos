<?php

namespace App\Filament\Resources\PayrollPeriods\Schemas;

use App\Models\Company;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PayrollPeriodForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('company_id')
                ->label('Empresa')
                ->options(Company::where('active', true)->pluck('name', 'id'))
                ->required()
                ->searchable(),
            Select::make('month')
                ->label('Mês')
                ->options([
                    1  => 'Janeiro',
                    2  => 'Fevereiro',
                    3  => 'Março',
                    4  => 'Abril',
                    5  => 'Maio',
                    6  => 'Junho',
                    7  => 'Julho',
                    8  => 'Agosto',
                    9  => 'Setembro',
                    10 => 'Outubro',
                    11 => 'Novembro',
                    12 => 'Dezembro',
                ])
                ->required()
                ->default(now()->month),
            TextInput::make('year')
                ->label('Ano')
                ->numeric()
                ->required()
                ->default(now()->year)
                ->minValue(2020)
                ->maxValue(2030),
            Textarea::make('notes')
                ->label('Observações')
                ->columnSpanFull(),
        ])->columns(2);
    }
}
