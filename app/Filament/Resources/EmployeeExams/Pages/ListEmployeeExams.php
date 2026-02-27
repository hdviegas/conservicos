<?php

namespace App\Filament\Resources\EmployeeExams\Pages;

use App\Filament\Resources\EmployeeExams\EmployeeExamResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListEmployeeExams extends ListRecords
{
    protected static string $resource = EmployeeExamResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Novo Exame'),
        ];
    }
}
