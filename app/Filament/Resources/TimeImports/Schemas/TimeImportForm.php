<?php

namespace App\Filament\Resources\TimeImports\Schemas;

use App\Models\Company;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class TimeImportForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('company_id')
                ->label('Empresa')
                ->options(Company::where('active', true)->pluck('name', 'id'))
                ->required()
                ->searchable(),

            Select::make('period_month')
                ->label('Mês de Referência')
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

            TextInput::make('period_year')
                ->label('Ano de Referência')
                ->numeric()
                ->required()
                ->default(now()->year)
                ->minValue(2020)
                ->maxValue(2030),

            FileUpload::make('filename')
                ->label('Arquivo CSV do Ponto')
                ->required()
                ->acceptedFileTypes(['text/csv', 'text/plain', 'application/octet-stream'])
                ->storeFileNamesIn('original_filename')
                ->disk('local')
                ->directory('time-reports')
                ->visibility('private'),
        ]);
    }
}
