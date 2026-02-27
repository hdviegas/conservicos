<?php

namespace App\Filament\Resources\PaymentBatches\Pages;

use App\Filament\Resources\PaymentBatches\PaymentBatchResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPaymentBatch extends EditRecord
{
    protected static string $resource = PaymentBatchResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
