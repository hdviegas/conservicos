<?php

namespace App\Filament\Resources\TimeImports\Infolists;

use App\Enums\ImportStatus;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TimeImportInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Detalhes da Importação')
                ->schema([
                    TextEntry::make('company.name')
                        ->label('Empresa'),
                    TextEntry::make('period_month')
                        ->label('Período')
                        ->formatStateUsing(fn ($record) =>
                            str_pad((string) $record->period_month, 2, '0', STR_PAD_LEFT) . '/' . $record->period_year
                        ),
                    TextEntry::make('original_filename')
                        ->label('Arquivo'),
                    TextEntry::make('records_count')
                        ->label('Registros Importados')
                        ->numeric(),
                    TextEntry::make('status')
                        ->label('Status')
                        ->badge()
                        ->formatStateUsing(fn ($state) =>
                            $state instanceof ImportStatus ? $state->label() : $state
                        )
                        ->color(fn ($state) =>
                            $state instanceof ImportStatus ? $state->color() : 'gray'
                        ),
                    TextEntry::make('imported_at')
                        ->label('Processado em')
                        ->dateTime('d/m/Y H:i'),
                    TextEntry::make('user.name')
                        ->label('Enviado por'),
                    TextEntry::make('created_at')
                        ->label('Criado em')
                        ->dateTime('d/m/Y H:i'),
                ])
                ->columns(2),

            Section::make('Erros de Importação')
                ->schema([
                    TextEntry::make('errors')
                        ->label('')
                        ->formatStateUsing(fn ($state) =>
                            is_array($state) ? implode("\n", $state) : ($state ?? 'Nenhum erro registrado')
                        )
                        ->columnSpanFull(),
                ])
                ->visible(fn ($record) => ! empty($record->errors)),
        ]);
    }
}
