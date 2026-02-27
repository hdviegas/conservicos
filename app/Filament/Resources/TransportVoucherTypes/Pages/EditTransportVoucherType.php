<?php

namespace App\Filament\Resources\TransportVoucherTypes\Pages;

use App\Filament\Resources\TransportVoucherTypes\TransportVoucherTypeResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditTransportVoucherType extends EditRecord
{
    protected static string $resource = TransportVoucherTypeResource::class;

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
