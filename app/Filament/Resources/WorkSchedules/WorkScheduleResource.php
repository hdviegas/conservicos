<?php

namespace App\Filament\Resources\WorkSchedules;

use App\Filament\Resources\WorkSchedules\Pages\CreateWorkSchedule;
use App\Filament\Resources\WorkSchedules\Pages\EditWorkSchedule;
use App\Filament\Resources\WorkSchedules\Pages\ListWorkSchedules;
use App\Filament\Resources\WorkSchedules\Schemas\WorkScheduleForm;
use App\Filament\Resources\WorkSchedules\Tables\WorkSchedulesTable;
use App\Models\WorkSchedule;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class WorkScheduleResource extends Resource
{
    protected static ?string $model = WorkSchedule::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClock;

    protected static ?string $navigationLabel = 'Escalas de Trabalho';

    protected static ?string $modelLabel = 'Escala de Trabalho';

    protected static ?string $pluralModelLabel = 'Escalas de Trabalho';

    protected static ?int $navigationSort = 4;

    public static function getNavigationGroup(): ?string
    {
        return 'Configurações';
    }

    public static function form(Schema $schema): Schema
    {
        return WorkScheduleForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return WorkSchedulesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListWorkSchedules::route('/'),
            'create' => CreateWorkSchedule::route('/create'),
            'edit' => EditWorkSchedule::route('/{record}/edit'),
        ];
    }
}
