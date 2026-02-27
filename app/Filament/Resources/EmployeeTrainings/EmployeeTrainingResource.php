<?php

namespace App\Filament\Resources\EmployeeTrainings;

use App\Filament\Resources\EmployeeTrainings\Pages\CreateEmployeeTraining;
use App\Filament\Resources\EmployeeTrainings\Pages\EditEmployeeTraining;
use App\Filament\Resources\EmployeeTrainings\Pages\ListEmployeeTrainings;
use App\Filament\Resources\EmployeeTrainings\Schemas\EmployeeTrainingForm;
use App\Filament\Resources\EmployeeTrainings\Tables\EmployeeTrainingsTable;
use App\Models\EmployeeTraining;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class EmployeeTrainingResource extends Resource
{
    protected static ?string $model = EmployeeTraining::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAcademicCap;

    protected static ?string $navigationLabel = 'Treinamentos de Funcionários';

    protected static ?string $modelLabel = 'Treinamento de Funcionário';

    protected static ?string $pluralModelLabel = 'Treinamentos de Funcionários';

    protected static ?int $navigationSort = 4;

    public static function getNavigationGroup(): ?string
    {
        return 'Compliance';
    }

    public static function form(Schema $schema): Schema
    {
        return EmployeeTrainingForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EmployeeTrainingsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEmployeeTrainings::route('/'),
            'create' => CreateEmployeeTraining::route('/create'),
            'edit' => EditEmployeeTraining::route('/{record}/edit'),
        ];
    }
}
