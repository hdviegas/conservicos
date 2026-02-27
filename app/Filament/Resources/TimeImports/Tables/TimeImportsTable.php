<?php

namespace App\Filament\Resources\TimeImports\Tables;

use App\Enums\ImportStatus;
use App\Jobs\ProcessTimeReportImport;
use App\Models\TimeImport;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TimeImportsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('company.name')
                    ->label('Empresa')
                    ->sortable(),

                TextColumn::make('period_month')
                    ->label('Período')
                    ->formatStateUsing(fn ($record) =>
                        str_pad((string) $record->period_month, 2, '0', STR_PAD_LEFT) . '/' . $record->period_year
                    ),

                TextColumn::make('original_filename')
                    ->label('Arquivo')
                    ->limit(40),

                TextColumn::make('records_count')
                    ->label('Registros')
                    ->numeric(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn ($state) =>
                        $state instanceof ImportStatus ? $state->label() : $state
                    )
                    ->color(fn ($state) =>
                        $state instanceof ImportStatus ? $state->color() : 'gray'
                    ),

                TextColumn::make('imported_at')
                    ->label('Processado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordActions([
                ViewAction::make(),

                Action::make('process')
                    ->label('Processar')
                    ->icon('heroicon-o-play')
                    ->color('success')
                    ->visible(fn (TimeImport $record) =>
                        in_array($record->status, [ImportStatus::Pending, ImportStatus::Failed])
                    )
                    ->requiresConfirmation()
                    ->modalHeading('Processar Importação')
                    ->modalDescription('O arquivo será processado em segundo plano. Acompanhe o status nesta tela.')
                    ->action(function (TimeImport $record) {
                        ProcessTimeReportImport::dispatch($record->id);
                        $record->update(['status' => ImportStatus::Processing]);
                    })
                    ->successNotificationTitle('Importação enviada para processamento'),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
