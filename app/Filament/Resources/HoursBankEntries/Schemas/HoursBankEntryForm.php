<?php

namespace App\Filament\Resources\HoursBankEntries\Schemas;

use App\Enums\HoursBankSource;
use App\Enums\HoursBankType;
use App\Models\Employee;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class HoursBankEntryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('employee_id')
                ->label('Funcionário')
                ->options(Employee::where('active', true)->orderBy('name')->pluck('name', 'id'))
                ->required()
                ->searchable()
                ->preload(),
            DatePicker::make('date')
                ->label('Data')
                ->required()
                ->displayFormat('d/m/Y'),
            Select::make('type')
                ->label('Tipo')
                ->options([
                    HoursBankType::Credit->value => HoursBankType::Credit->label(),
                    HoursBankType::Debit->value  => HoursBankType::Debit->label(),
                ])
                ->required(),
            TextInput::make('minutes')
                ->label('Minutos')
                ->numeric()
                ->required()
                ->minValue(1)
                ->helperText('Informe a quantidade em minutos (ex: 90 = 1h30min)'),
            Select::make('source')
                ->label('Origem')
                ->options(collect(HoursBankSource::cases())->mapWithKeys(
                    fn (HoursBankSource $s) => [$s->value => $s->label()]
                ))
                ->required()
                ->default(HoursBankSource::Manual->value),
            Textarea::make('description')
                ->label('Descrição')
                ->maxLength(500)
                ->columnSpanFull(),
        ])->columns(2);
    }
}
