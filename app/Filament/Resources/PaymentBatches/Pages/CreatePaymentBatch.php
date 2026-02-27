<?php

namespace App\Filament\Resources\PaymentBatches\Pages;

use App\Filament\Resources\PaymentBatches\PaymentBatchResource;
use App\Services\PaymentBatchService;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreatePaymentBatch extends CreateRecord
{
    protected static string $resource = PaymentBatchResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['status'] = \App\Enums\PaymentBatchStatus::Draft->value;
        return $data;
    }

    protected function afterCreate(): void
    {
        try {
            app(PaymentBatchService::class)->populate($this->record);

            Notification::make()
                ->title('Lote populado com sucesso')
                ->body("{$this->record->total_records} registro(s) adicionado(s). Revise e ajuste os itens conforme necessário.")
                ->success()
                ->send();
        } catch (\Throwable $e) {
            Notification::make()
                ->title('Erro ao popular lote')
                ->body($e->getMessage())
                ->danger()
                ->persistent()
                ->send();
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('edit', ['record' => $this->record]);
    }
}
