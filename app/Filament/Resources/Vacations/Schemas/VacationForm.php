<?php

namespace App\Filament\Resources\Vacations\Schemas;

use App\Enums\VacationStatus;
use App\Models\Employee;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class VacationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('employee_id')
                ->label('Funcionário')
                ->options(Employee::where('active', true)->orderBy('name')->pluck('name', 'id'))
                ->required()
                ->searchable()
                ->preload()
                ->columnSpanFull(),
            DatePicker::make('acquisition_period_start')
                ->label('Início do Período Aquisitivo')
                ->required()
                ->displayFormat('d/m/Y'),
            DatePicker::make('acquisition_period_end')
                ->label('Fim do Período Aquisitivo')
                ->required()
                ->displayFormat('d/m/Y'),
            DatePicker::make('scheduled_start')
                ->label('Início Programado')
                ->displayFormat('d/m/Y'),
            DatePicker::make('scheduled_end')
                ->label('Fim Programado')
                ->displayFormat('d/m/Y'),
            DatePicker::make('actual_start')
                ->label('Início Real')
                ->displayFormat('d/m/Y'),
            DatePicker::make('actual_end')
                ->label('Fim Real')
                ->displayFormat('d/m/Y'),
            TextInput::make('days_enjoyed')
                ->label('Dias Gozados')
                ->numeric()
                ->default(0)
                ->minValue(0),
            TextInput::make('days_sold')
                ->label('Dias Vendidos (Abono)')
                ->numeric()
                ->default(0)
                ->minValue(0),
            Select::make('status')
                ->label('Status')
                ->options(collect(VacationStatus::cases())->mapWithKeys(
                    fn (VacationStatus $s) => [$s->value => $s->label()]
                ))
                ->required()
                ->default(VacationStatus::Pending->value),
            Textarea::make('notes')
                ->label('Observações')
                ->columnSpanFull()
                ->maxLength(1000),
        ])->columns(2);
    }
}
