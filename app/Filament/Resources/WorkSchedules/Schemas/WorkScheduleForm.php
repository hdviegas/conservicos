<?php

namespace App\Filament\Resources\WorkSchedules\Schemas;

use App\Enums\WorkScheduleType;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class WorkScheduleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nome da Escala')
                    ->required()
                    ->maxLength(255)
                    ->columnSpan(2),
                Select::make('type')
                    ->label('Tipo')
                    ->options(collect(WorkScheduleType::cases())->mapWithKeys(
                        fn (WorkScheduleType $type) => [$type->value => $type->label()]
                    ))
                    ->required()
                    ->live(),
                Select::make('weekly_work_days')
                    ->label('Dias Trabalhados por Semana')
                    ->helperText('Usado no cálculo de vale transporte. Deixe vazio para 12x36 e 6x2 (calculado automaticamente).')
                    ->options([
                        5 => '5 dias (Segunda a Sexta)',
                        6 => '6 dias (Segunda a Sábado)',
                    ])
                    ->visible(fn (Get $get) => $get('type') === WorkScheduleType::Regular->value)
                    ->nullable(),
                Toggle::make('is_night_shift')
                    ->label('Turno Noturno')
                    ->default(false),
                TextInput::make('daily_hours')
                    ->label('Horas Diárias')
                    ->numeric()
                    ->step(0.5)
                    ->suffix('h'),
                TextInput::make('weekly_hours')
                    ->label('Horas Semanais')
                    ->numeric()
                    ->step(0.5)
                    ->suffix('h'),
                TimePicker::make('start_time')
                    ->label('Horário Início'),
                TimePicker::make('end_time')
                    ->label('Horário Fim'),
                Textarea::make('description')
                    ->label('Descrição')
                    ->columnSpan(2),
                Toggle::make('active')
                    ->label('Ativa')
                    ->default(true),
            ])
            ->columns(2);
    }
}
