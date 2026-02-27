<?php

namespace App\Filament\Resources\CompanyBankAccounts\Schemas;

use App\Enums\AccountType;
use App\Models\Company;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CompanyBankAccountForm
{
    private const BANK_OPTIONS = [
        '001' => '001 - Banco do Brasil',
        '104' => '104 - Caixa Econômica Federal',
        '237' => '237 - Bradesco',
        '341' => '341 - Itaú',
        '033' => '033 - Santander',
        '756' => '756 - Sicoob',
        '748' => '748 - Sicredi',
    ];

    private const BANK_NAMES = [
        '001' => 'BANCO DO BRASIL',
        '104' => 'CAIXA ECONOMICA FEDERAL',
        '237' => 'BRADESCO',
        '341' => 'ITAU',
        '033' => 'SANTANDER',
        '756' => 'SICOOB',
        '748' => 'SICREDI',
    ];

    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('company_id')
                ->label('Empresa')
                ->options(Company::query()->pluck('name', 'id'))
                ->searchable()
                ->required(),

            Select::make('bank_code')
                ->label('Banco')
                ->options(self::BANK_OPTIONS)
                ->required()
                ->live()
                ->afterStateUpdated(function ($state, callable $set) {
                    if ($state && isset(self::BANK_NAMES[$state])) {
                        $set('bank_name', self::BANK_NAMES[$state]);
                    }
                }),

            TextInput::make('bank_name')
                ->label('Nome do Banco')
                ->required()
                ->maxLength(100),

            TextInput::make('agency')
                ->label('Agência')
                ->required()
                ->maxLength(10),

            TextInput::make('agency_digit')
                ->label('Dígito da Agência')
                ->maxLength(2),

            TextInput::make('account_number')
                ->label('Número da Conta')
                ->required()
                ->maxLength(15),

            TextInput::make('account_digit')
                ->label('Dígito da Conta')
                ->maxLength(2),

            Select::make('account_type')
                ->label('Tipo de Conta')
                ->options([
                    AccountType::Checking->value => 'Conta Corrente',
                    AccountType::Savings->value  => 'Conta Poupança',
                ])
                ->required(),

            TextInput::make('covenant_code')
                ->label('Código do Convênio')
                ->maxLength(20),

            Toggle::make('is_default')
                ->label('Conta Padrão')
                ->default(false),

            Toggle::make('active')
                ->label('Ativa')
                ->default(true),
        ]);
    }
}
