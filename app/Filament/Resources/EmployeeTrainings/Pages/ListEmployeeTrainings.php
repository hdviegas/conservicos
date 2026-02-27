<?php

namespace App\Filament\Resources\EmployeeTrainings\Pages;

use App\Filament\Resources\EmployeeTrainings\EmployeeTrainingResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListEmployeeTrainings extends ListRecords
{
    protected static string $resource = EmployeeTrainingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Novo Treinamento'),
        ];
    }
}
