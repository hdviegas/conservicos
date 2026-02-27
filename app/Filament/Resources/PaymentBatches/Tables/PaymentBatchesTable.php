<?php

namespace App\Filament\Resources\PaymentBatches\Tables;

use App\Enums\PaymentBatchStatus;
use App\Models\PaymentBatch;
use App\Services\CnabGeneratorService;
use App\Services\CnabReturnReaderService;
use App\Services\PaymentBatchService;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PaymentBatchesTable
{
    private const MONTH_NAMES = [
        1  => 'Jan', 2  => 'Fev', 3  => 'Mar', 4  => 'Abr',
        5  => 'Mai', 6  => 'Jun', 7  => 'Jul', 8  => 'Ago',
        9  => 'Set', 10 => 'Out', 11 => 'Nov', 12 => 'Dez',
    ];

    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('company.name')
                    ->label('Empresa')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('type')
                    ->label('Tipo')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state->label())
                    ->color('primary'),

                TextColumn::make('period')
                    ->label('Período')
                    ->state(fn (PaymentBatch $record) =>
                        (self::MONTH_NAMES[$record->reference_month] ?? $record->reference_month) . '/' . $record->reference_year
                    ),

                TextColumn::make('payment_date')
                    ->label('Data Pgto')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('bank_code')
                    ->label('Banco')
                    ->badge()
                    ->color('info'),

                TextColumn::make('total_records')
                    ->label('Registros')
                    ->numeric(),

                TextColumn::make('total_amount')
                    ->label('Valor Total')
                    ->money('BRL')
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state instanceof PaymentBatchStatus ? $state->label() : $state)
                    ->color(fn ($state) => $state instanceof PaymentBatchStatus ? $state->color() : 'gray'),

                TextColumn::make('generated_at')
                    ->label('Gerado em')
                    ->dateTime('d/m/Y H:i')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordActions([
                EditAction::make()
                    ->visible(fn (PaymentBatch $record) => $record->status === PaymentBatchStatus::Draft),

                Action::make('generate_cnab')
                    ->label('Gerar Arquivo CNAB')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('success')
                    ->visible(fn (PaymentBatch $record) =>
                        $record->status === PaymentBatchStatus::Draft && $record->total_records > 0
                    )
                    ->requiresConfirmation()
                    ->modalHeading('Gerar Arquivo CNAB')
                    ->modalDescription('O arquivo CNAB será gerado. Verifique se todos os dados bancários estão preenchidos.')
                    ->action(function (PaymentBatch $record) {
                        try {
                            $missingData = app(PaymentBatchService::class)->validateBankData($record);

                            if (!empty($missingData)) {
                                Notification::make()
                                    ->title('Dados bancários incompletos')
                                    ->body('Os seguintes funcionários estão sem dados bancários: ' . implode(', ', $missingData))
                                    ->danger()
                                    ->persistent()
                                    ->send();
                                return;
                            }

                            $filePath = app(CnabGeneratorService::class)->generate($record);

                            Notification::make()
                                ->title('Arquivo CNAB gerado com sucesso!')
                                ->body('Arquivo: ' . basename($filePath))
                                ->success()
                                ->send();
                        } catch (\Throwable $e) {
                            Notification::make()
                                ->title('Erro ao gerar arquivo CNAB')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),

                Action::make('download_cnab')
                    ->label('Download CNAB')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('primary')
                    ->visible(fn (PaymentBatch $record) => $record->file_path !== null)
                    ->url(fn (PaymentBatch $record) =>
                        $record->file_path
                            ? route('payment-batches.download-cnab', $record)
                            : '#'
                    )
                    ->openUrlInNewTab(),

                Action::make('mark_sent')
                    ->label('Marcar como Enviado')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('info')
                    ->visible(fn (PaymentBatch $record) => $record->status === PaymentBatchStatus::Generated)
                    ->requiresConfirmation()
                    ->action(function (PaymentBatch $record) {
                        $record->update(['status' => PaymentBatchStatus::SentToBank]);
                        Notification::make()
                            ->title('Lote marcado como enviado ao banco!')
                            ->success()
                            ->send();
                    }),

                Action::make('upload_return')
                    ->label('Upload Retorno')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->color('warning')
                    ->visible(fn (PaymentBatch $record) => $record->status === PaymentBatchStatus::SentToBank)
                    ->form([
                        \Filament\Forms\Components\FileUpload::make('return_file')
                            ->label('Arquivo de Retorno (.RET)')
                            ->required()
                            ->disk('local')
                            ->directory('cnab/returns')
                            ->acceptedFileTypes(['application/octet-stream', 'text/plain'])
                            ->preserveFilenames(),
                    ])
                    ->action(function (PaymentBatch $record, array $data) {
                        try {
                            app(CnabReturnReaderService::class)->process($record, $data['return_file']);
                            Notification::make()
                                ->title('Arquivo de retorno processado com sucesso!')
                                ->success()
                                ->send();
                        } catch (\Throwable $e) {
                            Notification::make()
                                ->title('Erro ao processar retorno')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),

                DeleteAction::make()
                    ->visible(fn (PaymentBatch $record) => $record->status === PaymentBatchStatus::Draft),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
