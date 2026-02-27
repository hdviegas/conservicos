<?php

namespace App\Filament\Resources\Companies\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CompanyForm
{
    public static function configure(Schema $schema): Schema
    {
        $states = [
            'AC' => 'AC', 'AL' => 'AL', 'AP' => 'AP', 'AM' => 'AM',
            'BA' => 'BA', 'CE' => 'CE', 'DF' => 'DF', 'ES' => 'ES',
            'GO' => 'GO', 'MA' => 'MA', 'MT' => 'MT', 'MS' => 'MS',
            'MG' => 'MG', 'PA' => 'PA', 'PB' => 'PB', 'PR' => 'PR',
            'PE' => 'PE', 'PI' => 'PI', 'RJ' => 'RJ', 'RN' => 'RN',
            'RS' => 'RS', 'RO' => 'RO', 'RR' => 'RR', 'SC' => 'SC',
            'SP' => 'SP', 'SE' => 'SE', 'TO' => 'TO',
        ];

        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Razão Social')
                    ->required()
                    ->maxLength(255)
                    ->columnSpan(2),
                TextInput::make('trade_name')
                    ->label('Nome Fantasia')
                    ->maxLength(255)
                    ->columnSpan(2),
                TextInput::make('cnpj')
                    ->label('CNPJ')
                    ->required()
                    ->mask('99.999.999/9999-99')
                    ->maxLength(18),
                TextInput::make('inscricao_estadual')
                    ->label('Inscrição Estadual')
                    ->maxLength(20),
                TextInput::make('phone')
                    ->label('Telefone')
                    ->tel()
                    ->maxLength(20),
                TextInput::make('email')
                    ->label('E-mail')
                    ->email()
                    ->maxLength(255),
                Textarea::make('address')
                    ->label('Endereço')
                    ->maxLength(500)
                    ->columnSpan(2),
                TextInput::make('city')
                    ->label('Cidade')
                    ->maxLength(100),
                Select::make('state')
                    ->label('UF')
                    ->options($states),
                Toggle::make('active')
                    ->label('Ativa')
                    ->default(true),
            ])
            ->columns(2);
    }
}
