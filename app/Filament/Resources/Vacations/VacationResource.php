<?php

namespace App\Filament\Resources\Vacations;

use App\Filament\Resources\Vacations\Pages\CreateVacation;
use App\Filament\Resources\Vacations\Pages\EditVacation;
use App\Filament\Resources\Vacations\Pages\ListVacations;
use App\Filament\Resources\Vacations\Schemas\VacationForm;
use App\Filament\Resources\Vacations\Tables\VacationsTable;
use App\Models\Vacation;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class VacationResource extends Resource
{
    protected static ?string $model = Vacation::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSun;

    protected static ?string $navigationLabel = 'Férias';

    protected static ?string $modelLabel = 'Férias';

    protected static ?string $pluralModelLabel = 'Férias';

    protected static ?int $navigationSort = 2;

    public static function getNavigationGroup(): ?string
    {
        return 'Benefícios';
    }

    public static function form(Schema $schema): Schema
    {
        return VacationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return VacationsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListVacations::route('/'),
            'create' => CreateVacation::route('/create'),
            'edit' => EditVacation::route('/{record}/edit'),
        ];
    }
}
