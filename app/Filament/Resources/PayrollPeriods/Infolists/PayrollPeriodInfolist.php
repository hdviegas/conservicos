<?php

namespace App\Filament\Resources\PayrollPeriods\Infolists;

use App\Enums\PayrollStatus;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PayrollPeriodInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Informações do Período')
                ->schema([
                    TextEntry::make('company.name')
                        ->label('Empresa')
                        ->columnSpan(3),
                    TextEntry::make('period_label')
                        ->label('Período'),
                    TextEntry::make('status')
                        ->label('Status')
                        ->badge()
                        ->formatStateUsing(fn ($state) =>
                            $state instanceof PayrollStatus ? $state->label() : $state
                        )
                        ->color(fn ($state) =>
                            $state instanceof PayrollStatus ? $state->color() : 'gray'
                        ),
                    TextEntry::make('entries_count')
                        ->label('Funcionários')
                        ->state(fn ($record) => $record->entries()->count()),
                    TextEntry::make('calculated_at')
                        ->label('Calculado em')
                        ->dateTime('d/m/Y H:i')
                        ->placeholder('—'),
                    TextEntry::make('closed_at')
                        ->label('Fechado em')
                        ->dateTime('d/m/Y H:i')
                        ->placeholder('—'),
                    TextEntry::make('notes')
                        ->label('Observações')
                        ->placeholder('—')
                        ->columnSpan(3),
                ])
                ->columns(6)
                ->columnSpan('full'),
                //->compact(),
        ]);
    }
}
