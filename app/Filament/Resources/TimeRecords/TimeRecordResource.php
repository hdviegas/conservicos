<?php

namespace App\Filament\Resources\TimeRecords;

use App\Filament\Resources\TimeRecords\Pages\ListTimeRecords;
use App\Filament\Resources\TimeRecords\Tables\TimeRecordsTable;
use App\Models\TimeRecord;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class TimeRecordResource extends Resource
{
    protected static ?string $model = TimeRecord::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClock;

    protected static ?string $navigationLabel = 'Registros de Ponto';

    protected static ?string $modelLabel = 'Registro de Ponto';

    protected static ?string $pluralModelLabel = 'Registros de Ponto';

    protected static ?int $navigationSort = 2;

    public static function getNavigationGroup(): ?string
    {
        return 'Controle de Ponto';
    }

    public static function table(Table $table): Table
    {
        return TimeRecordsTable::configure($table);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema;
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTimeRecords::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
