<?php

namespace App\Filament\Resources\TransportVoucherTypes\Pages;

use App\Filament\Resources\TransportVoucherTypes\TransportVoucherTypeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTransportVoucherTypes extends ListRecords
{
    protected static string $resource = TransportVoucherTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Novo Tipo de VT'),
        ];
    }
}
