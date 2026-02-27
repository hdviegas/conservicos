<?php

namespace App\Filament\Resources\TransportVouchers\Pages;

use App\Filament\Resources\TransportVouchers\TransportVoucherResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditTransportVoucher extends EditRecord
{
    protected static string $resource = TransportVoucherResource::class;

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
