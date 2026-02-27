<?php

namespace App\Filament\Resources\TransportVoucherTypes\Pages;

use App\Filament\Resources\TransportVoucherTypes\TransportVoucherTypeResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTransportVoucherType extends CreateRecord
{
    protected static string $resource = TransportVoucherTypeResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
