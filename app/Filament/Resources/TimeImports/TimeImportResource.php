<?php

namespace App\Filament\Resources\TimeImports;

use App\Filament\Resources\TimeImports\Infolists\TimeImportInfolist;
use App\Filament\Resources\TimeImports\Pages\CreateTimeImport;
use App\Filament\Resources\TimeImports\Pages\ListTimeImports;
use App\Filament\Resources\TimeImports\Pages\ViewTimeImport;
use App\Filament\Resources\TimeImports\Schemas\TimeImportForm;
use App\Filament\Resources\TimeImports\Tables\TimeImportsTable;
use App\Models\TimeImport;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class TimeImportResource extends Resource
{
    protected static ?string $model = TimeImport::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowUpTray;

    protected static ?string $navigationLabel = 'Importar Ponto';

    protected static ?string $modelLabel = 'Importação';

    protected static ?string $pluralModelLabel = 'Importações de Ponto';

    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): ?string
    {
        return 'Controle de Ponto';
    }

    public static function form(Schema $schema): Schema
    {
        return TimeImportForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TimeImportsTable::configure($table);
    }

    public static function infolist(Schema $schema): Schema
    {
        return TimeImportInfolist::configure($schema);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListTimeImports::route('/'),
            'create' => CreateTimeImport::route('/create'),
            'view'   => ViewTimeImport::route('/{record}'),
        ];
    }
}
