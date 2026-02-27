<?php

namespace App\Filament\Resources\TransportVouchers\Schemas;

use App\Enums\TransportVoucherStatus;
use App\Models\Employee;
use App\Models\TransportVoucherType;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class TransportVoucherForm
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
            Select::make('transport_voucher_type_id')
                ->label('Tipo de Vale Transporte')
                ->options(TransportVoucherType::where('active', true)->orderBy('name')->pluck('name', 'id'))
                ->required()
                ->searchable()
                ->preload()
                ->live()
                ->afterStateUpdated(function (?int $state, Get $get, Set $set) {
                    if ($state) {
                        $type = TransportVoucherType::find($state);
                        if ($type) {
                            $set('daily_value', $type->daily_value);
                            $days = (int) $get('worked_days');
                            $set('total_value', round((float) $type->daily_value * $days, 2));
                        }
                    }
                }),
            DatePicker::make('period_start')
                ->label('Início do Período')
                ->required()
                ->displayFormat('d/m/Y'),
            DatePicker::make('period_end')
                ->label('Fim do Período')
                ->required()
                ->displayFormat('d/m/Y'),
            TextInput::make('worked_days')
                ->label('Dias Trabalhados')
                ->numeric()
                ->required()
                ->default(0)
                ->minValue(0)
                ->live()
                ->afterStateUpdated(function (?int $state, Get $get, Set $set) {
                    $daily = (float) $get('daily_value');
                    $set('total_value', round($daily * ($state ?? 0), 2));
                }),
            TextInput::make('daily_value')
                ->label('Valor Diário (R$)')
                ->numeric()
                ->required()
                ->prefix('R$')
                ->step('0.01')
                ->live()
                ->afterStateUpdated(function (?float $state, Get $get, Set $set) {
                    $days = (int) $get('worked_days');
                    $set('total_value', round(($state ?? 0) * $days, 2));
                }),
            TextInput::make('total_value')
                ->label('Valor Total (R$)')
                ->numeric()
                ->readOnly()
                ->prefix('R$')
                ->default(0),
            Select::make('status')
                ->label('Status')
                ->options(collect(TransportVoucherStatus::cases())->mapWithKeys(
                    fn (TransportVoucherStatus $s) => [$s->value => $s->label()]
                ))
                ->required()
                ->default(TransportVoucherStatus::Pending->value),
        ])->columns(2);
    }
}
