<?php

namespace App\Filament\Resources\TimeImports\Pages;

use App\Enums\ImportStatus;
use App\Filament\Resources\TimeImports\TimeImportResource;
use App\Jobs\ProcessTimeReportImport;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;

class ViewTimeImport extends ViewRecord
{
    protected static string $resource = TimeImportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('reprocess')
                ->label('Reprocessar')
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->visible(fn () => in_array($this->record->status, [ImportStatus::Pending, ImportStatus::Failed]))
                ->requiresConfirmation()
                ->modalHeading('Reprocessar Importação')
                ->modalDescription('O arquivo será reprocessado em segundo plano. Os registros existentes serão atualizados.')
                ->action(function () {
                    ProcessTimeReportImport::dispatch($this->record->id);
                    $this->record->update(['status' => ImportStatus::Processing]);
                    $this->redirect(TimeImportResource::getUrl('view', ['record' => $this->record]));
                }),
        ];
    }
}
