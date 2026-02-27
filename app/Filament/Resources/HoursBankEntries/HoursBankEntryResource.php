<?php

namespace App\Filament\Resources\HoursBankEntries;

use App\Filament\Resources\HoursBankEntries\Pages\CreateHoursBankEntry;
use App\Filament\Resources\HoursBankEntries\Pages\EditHoursBankEntry;
use App\Filament\Resources\HoursBankEntries\Pages\ListHoursBankEntries;
use App\Filament\Resources\HoursBankEntries\Schemas\HoursBankEntryForm;
use App\Filament\Resources\HoursBankEntries\Tables\HoursBankEntriesTable;
use App\Models\HoursBankEntry;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class HoursBankEntryResource extends Resource
{
    protected static ?string $model = HoursBankEntry::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClock;

    protected static ?string $navigationLabel = 'Banco de Horas';

    protected static ?string $modelLabel = 'Entrada no Banco de Horas';

    protected static ?string $pluralModelLabel = 'Banco de Horas';

    protected static ?int $navigationSort = 4;

    public static function getNavigationGroup(): ?string
    {
        return 'Gestão de Pessoas';
    }

    public static function form(Schema $schema): Schema
    {
        return HoursBankEntryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return HoursBankEntriesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListHoursBankEntries::route('/'),
            'create' => CreateHoursBankEntry::route('/create'),
            'edit'   => EditHoursBankEntry::route('/{record}/edit'),
        ];
    }
}
