<?php

namespace App\Filament\Resources\EmployeeExams\Pages;

use App\Filament\Resources\EmployeeExams\EmployeeExamResource;
use Filament\Resources\Pages\CreateRecord;

class CreateEmployeeExam extends CreateRecord
{
    protected static string $resource = EmployeeExamResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
