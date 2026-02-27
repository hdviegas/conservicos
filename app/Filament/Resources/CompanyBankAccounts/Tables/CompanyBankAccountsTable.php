<?php

namespace App\Filament\Resources\CompanyBankAccounts\Tables;

use App\Enums\AccountType;
use App\Models\CompanyBankAccount;
use Filament\Actions\EditAction;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CompanyBankAccountsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('company.name')
                    ->label('Empresa')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('bank_code')
                    ->label('Banco')
                    ->badge()
                    ->color('primary'),

                TextColumn::make('bank_name')
                    ->label('Nome do Banco')
                    ->searchable(),

                TextColumn::make('agency_account')
                    ->label('Agência / Conta')
                    ->state(fn (CompanyBankAccount $record) =>
                        $record->agency . ($record->agency_digit ? '-' . $record->agency_digit : '') .
                        ' / ' .
                        $record->account_number . ($record->account_digit ? '-' . $record->account_digit : '')
                    ),

                TextColumn::make('account_type')
                    ->label('Tipo')
                    ->badge()
                    ->formatStateUsing(fn ($state) =>
                        $state instanceof AccountType
                            ? match($state) {
                                AccountType::Checking => 'Corrente',
                                AccountType::Savings  => 'Poupança',
                            }
                            : $state
                    )
                    ->color('info'),

                IconColumn::make('is_default')
                    ->label('Padrão')
                    ->boolean(),

                IconColumn::make('active')
                    ->label('Ativa')
                    ->boolean(),
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('toggle_active')
                    ->label(fn (CompanyBankAccount $record) => $record->active ? 'Desativar' : 'Ativar')
                    ->icon(fn (CompanyBankAccount $record) => $record->active ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                    ->color(fn (CompanyBankAccount $record) => $record->active ? 'danger' : 'success')
                    ->action(fn (CompanyBankAccount $record) => $record->update(['active' => !$record->active])),
                DeleteAction::make(),
            ])
            ->defaultSort('company_id');
    }
}
