<?php

namespace App\Filament\Resources\EmployeeExams\Pages;

use App\Filament\Resources\EmployeeExams\EmployeeExamResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditEmployeeExam extends EditRecord
{
    protected static string $resource = EmployeeExamResource::class;

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
