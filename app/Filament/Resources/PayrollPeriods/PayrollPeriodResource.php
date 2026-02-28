<?php

namespace App\Filament\Resources\PayrollPeriods;

use App\Filament\Resources\PayrollPeriods\Infolists\PayrollPeriodInfolist;
use App\Filament\Resources\PayrollPeriods\Pages\CreatePayrollPeriod;
use App\Filament\Resources\PayrollPeriods\Pages\ListPayrollPeriods;
use App\Filament\Resources\PayrollPeriods\Pages\ViewPayrollPeriod;
use App\Filament\Resources\PayrollPeriods\RelationManagers\PayrollEntriesRelationManager;
use App\Filament\Resources\PayrollPeriods\Schemas\PayrollPeriodForm;
use App\Filament\Resources\PayrollPeriods\Tables\PayrollPeriodsTable;
use App\Models\PayrollPeriod;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PayrollPeriodResource extends Resource
{
    protected static ?string $model = PayrollPeriod::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalculator;

    protected static ?string $navigationLabel = 'Folha de Pagamento';

    protected static ?string $modelLabel = 'Folha de Pagamento';

    protected static ?string $pluralModelLabel = 'Folha de Pagamento';

    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): ?string
    {
        return 'Folha de Pagamento';
    }

    public static function form(Schema $schema): Schema
    {
        return PayrollPeriodForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PayrollPeriodsTable::configure($table);
    }

    public static function infolist(Schema $schema): Schema
    {
        return PayrollPeriodInfolist::configure($schema);
    }

    public static function getRelations(): array
    {
        return [
            PayrollEntriesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListPayrollPeriods::route('/'),
            'create' => CreatePayrollPeriod::route('/create'),
            'view'   => ViewPayrollPeriod::route('/{record}'),
        ];
    }
}
