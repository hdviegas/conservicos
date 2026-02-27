<?php

namespace App\Filament\Resources\PayrollPeriods\Pages;

use App\Enums\PayrollStatus;
use App\Filament\Resources\PayrollPeriods\PayrollPeriodResource;
use App\Services\PayrollCalculatorService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewPayrollPeriod extends ViewRecord
{
    protected static string $resource = PayrollPeriodResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('calculate')
                ->label('Calcular Folha')
                ->icon('heroicon-o-calculator')
                ->color('primary')
                ->visible(fn () => in_array(
                    $this->record->status,
                    [PayrollStatus::Draft, PayrollStatus::Calculated]
                ))
                ->requiresConfirmation()
                ->modalHeading('Calcular Folha de Pagamento')
                ->modalDescription('O cálculo será executado agora. Entradas anteriores serão recalculadas.')
                ->action(function () {
                    try {
                        app(PayrollCalculatorService::class)->calculate($this->record);
                        Notification::make()->title('Folha calculada com sucesso!')->success()->send();
                        $this->refreshFormData(['status', 'calculated_at']);
                    } catch (\Throwable $e) {
                        Notification::make()
                            ->title('Erro ao calcular folha')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
            Action::make('close')
                ->label('Fechar Período')
                ->icon('heroicon-o-lock-closed')
                ->color('success')
                ->visible(fn () => in_array(
                    $this->record->status,
                    [PayrollStatus::Calculated, PayrollStatus::Reviewed]
                ))
                ->requiresConfirmation()
                ->modalHeading('Fechar Período')
                ->modalDescription('Após fechar, o período não poderá ser recalculado. Confirma?')
                ->action(function () {
                    $this->record->update([
                        'status'    => PayrollStatus::Closed,
                        'closed_at' => now(),
                        'closed_by' => auth()->id(),
                    ]);
                    Notification::make()->title('Período fechado!')->success()->send();
                }),
            Action::make('reopen')
                ->label('Reabrir')
                ->icon('heroicon-o-lock-open')
                ->color('warning')
                ->visible(fn () => $this->record->status === PayrollStatus::Closed)
                ->requiresConfirmation()
                ->action(function () {
                    $this->record->update([
                        'status'    => PayrollStatus::Calculated,
                        'closed_at' => null,
                        'closed_by' => null,
                    ]);
                    Notification::make()->title('Período reaberto!')->success()->send();
                }),
        ];
    }
}
