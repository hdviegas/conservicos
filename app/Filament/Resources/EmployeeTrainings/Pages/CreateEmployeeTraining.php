<?php

namespace App\Filament\Resources\EmployeeTrainings\Pages;

use App\Filament\Resources\EmployeeTrainings\EmployeeTrainingResource;
use Filament\Resources\Pages\CreateRecord;

class CreateEmployeeTraining extends CreateRecord
{
    protected static string $resource = EmployeeTrainingResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
