<?php

namespace App\Filament\Resources\TimeImports\Pages;

use App\Filament\Resources\TimeImports\TimeImportResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTimeImports extends ListRecords
{
    protected static string $resource = TimeImportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Nova Importação'),
        ];
    }
}
