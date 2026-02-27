<?php

namespace App\Filament\Resources\TransportVouchers\Pages;

use App\Filament\Resources\TransportVouchers\TransportVoucherResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTransportVoucher extends CreateRecord
{
    protected static string $resource = TransportVoucherResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
