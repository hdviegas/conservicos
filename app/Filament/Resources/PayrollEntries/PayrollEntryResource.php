<?php

namespace App\Filament\Resources\PayrollEntries;

use App\Filament\Resources\PayrollEntries\Pages\ListPayrollEntries;
use App\Filament\Resources\PayrollEntries\Tables\PayrollEntriesTable;
use App\Models\PayrollEntry;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PayrollEntryResource extends Resource
{
    protected static ?string $model = PayrollEntry::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static ?string $navigationLabel = 'Entradas da Folha';

    protected static ?string $modelLabel = 'Entrada da Folha';

    protected static ?string $pluralModelLabel = 'Entradas da Folha';

    protected static bool $shouldRegisterNavigation = false;

    protected static ?int $navigationSort = 2;

    public static function getNavigationGroup(): ?string
    {
        return 'Folha de Pagamento';
    }

    public static function table(Table $table): Table
    {
        return PayrollEntriesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPayrollEntries::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
