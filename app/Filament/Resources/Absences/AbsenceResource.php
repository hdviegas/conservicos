<?php

namespace App\Filament\Resources\Absences;

use App\Filament\Resources\Absences\Pages\CreateAbsence;
use App\Filament\Resources\Absences\Pages\EditAbsence;
use App\Filament\Resources\Absences\Pages\ListAbsences;
use App\Filament\Resources\Absences\Schemas\AbsenceForm;
use App\Filament\Resources\Absences\Tables\AbsencesTable;
use App\Models\Absence;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class AbsenceResource extends Resource
{
    protected static ?string $model = Absence::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedXCircle;

    protected static ?string $navigationLabel = 'Faltas e Atestados';

    protected static ?string $modelLabel = 'Falta/Atestado';

    protected static ?string $pluralModelLabel = 'Faltas e Atestados';

    protected static ?int $navigationSort = 3;

    public static function getNavigationGroup(): ?string
    {
        return 'Gestão de Pessoas';
    }

    public static function form(Schema $schema): Schema
    {
        return AbsenceForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AbsencesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListAbsences::route('/'),
            'create' => CreateAbsence::route('/create'),
            'edit'   => EditAbsence::route('/{record}/edit'),
        ];
    }
}
