<?php

namespace App\Filament\Resources\EmployeeTrainings\Pages;

use App\Filament\Resources\EmployeeTrainings\EmployeeTrainingResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditEmployeeTraining extends EditRecord
{
    protected static string $resource = EmployeeTrainingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
