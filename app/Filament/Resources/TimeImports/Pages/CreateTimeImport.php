<?php

namespace App\Filament\Resources\TimeImports\Pages;

use App\Enums\ImportStatus;
use App\Enums\ImportType;
use App\Filament\Resources\TimeImports\TimeImportResource;
use App\Jobs\ProcessTimeReportImport;
use Filament\Resources\Pages\CreateRecord;

class CreateTimeImport extends CreateRecord
{
    protected static string $resource = TimeImportResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id']       = auth()->id();
        $data['type']          = ImportType::TimeReport->value;
        $data['status']        = ImportStatus::Pending->value;
        $data['records_count'] = 0;

        return $data;
    }

    protected function afterCreate(): void
    {
        ProcessTimeReportImport::dispatch($this->record->id);
        $this->record->update(['status' => ImportStatus::Processing->value]);
    }

    protected function getRedirectUrl(): string
    {
        return TimeImportResource::getUrl('index');
    }
}
