<?php

namespace App\Filament\Resources\HoursBankEntries\Pages;

use App\Filament\Resources\HoursBankEntries\HoursBankEntryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListHoursBankEntries extends ListRecords
{
    protected static string $resource = HoursBankEntryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Lançamento Manual'),
        ];
    }
}
