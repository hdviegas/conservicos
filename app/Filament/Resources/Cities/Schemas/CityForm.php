<?php

namespace App\Filament\Resources\Cities\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CityForm
{
    public static function configure(Schema $schema): Schema
    {
        $states = [
            'AC' => 'AC - Acre', 'AL' => 'AL - Alagoas', 'AP' => 'AP - Amapá',
            'AM' => 'AM - Amazonas', 'BA' => 'BA - Bahia', 'CE' => 'CE - Ceará',
            'DF' => 'DF - Distrito Federal', 'ES' => 'ES - Espírito Santo',
            'GO' => 'GO - Goiás', 'MA' => 'MA - Maranhão', 'MT' => 'MT - Mato Grosso',
            'MS' => 'MS - Mato Grosso do Sul', 'MG' => 'MG - Minas Gerais',
            'PA' => 'PA - Pará', 'PB' => 'PB - Paraíba', 'PR' => 'PR - Paraná',
            'PE' => 'PE - Pernambuco', 'PI' => 'PI - Piauí', 'RJ' => 'RJ - Rio de Janeiro',
            'RN' => 'RN - Rio Grande do Norte', 'RS' => 'RS - Rio Grande do Sul',
            'RO' => 'RO - Rondônia', 'RR' => 'RR - Roraima', 'SC' => 'SC - Santa Catarina',
            'SP' => 'SP - São Paulo', 'SE' => 'SE - Sergipe', 'TO' => 'TO - Tocantins',
        ];

        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Cidade')
                    ->required()
                    ->maxLength(255),
                Select::make('state')
                    ->label('UF')
                    ->options($states)
                    ->required()
                    ->searchable(),
                TextInput::make('ibge_code')
                    ->label('Código IBGE')
                    ->maxLength(10),
            ]);
    }
}
