<?php

namespace App\Filament\Resources\TimeRecords\Pages;

use App\Filament\Resources\TimeRecords\TimeRecordResource;
use Filament\Resources\Pages\ListRecords;

class ListTimeRecords extends ListRecords
{
    protected static string $resource = TimeRecordResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
