<?php

namespace App\Filament\Resources\EmployeeExams;

use App\Filament\Resources\EmployeeExams\Pages\CreateEmployeeExam;
use App\Filament\Resources\EmployeeExams\Pages\EditEmployeeExam;
use App\Filament\Resources\EmployeeExams\Pages\ListEmployeeExams;
use App\Filament\Resources\EmployeeExams\Schemas\EmployeeExamForm;
use App\Filament\Resources\EmployeeExams\Tables\EmployeeExamsTable;
use App\Models\EmployeeExam;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class EmployeeExamResource extends Resource
{
    protected static ?string $model = EmployeeExam::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBeaker;

    protected static ?string $navigationLabel = 'Exames de Funcionários';

    protected static ?string $modelLabel = 'Exame de Funcionário';

    protected static ?string $pluralModelLabel = 'Exames de Funcionários';

    protected static ?int $navigationSort = 2;

    public static function getNavigationGroup(): ?string
    {
        return 'Compliance';
    }

    public static function form(Schema $schema): Schema
    {
        return EmployeeExamForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EmployeeExamsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEmployeeExams::route('/'),
            'create' => CreateEmployeeExam::route('/create'),
            'edit' => EditEmployeeExam::route('/{record}/edit'),
        ];
    }
}
