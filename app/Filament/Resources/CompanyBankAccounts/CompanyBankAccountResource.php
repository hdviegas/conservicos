<?php

namespace App\Filament\Resources\CompanyBankAccounts;

use App\Filament\Resources\CompanyBankAccounts\Pages\CreateCompanyBankAccount;
use App\Filament\Resources\CompanyBankAccounts\Pages\EditCompanyBankAccount;
use App\Filament\Resources\CompanyBankAccounts\Pages\ListCompanyBankAccounts;
use App\Filament\Resources\CompanyBankAccounts\Schemas\CompanyBankAccountForm;
use App\Filament\Resources\CompanyBankAccounts\Tables\CompanyBankAccountsTable;
use App\Models\CompanyBankAccount;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CompanyBankAccountResource extends Resource
{
    protected static ?string $model = CompanyBankAccount::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingLibrary;

    protected static ?string $navigationLabel = 'Contas Bancárias';

    protected static ?string $modelLabel = 'Conta Bancária';

    protected static ?string $pluralModelLabel = 'Contas Bancárias';

    protected static ?int $navigationSort = 10;

    public static function getNavigationGroup(): ?string
    {
        return 'Configurações';
    }

    public static function form(Schema $schema): Schema
    {
        return CompanyBankAccountForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CompanyBankAccountsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListCompanyBankAccounts::route('/'),
            'create' => CreateCompanyBankAccount::route('/create'),
            'edit'   => EditCompanyBankAccount::route('/{record}/edit'),
        ];
    }
}
