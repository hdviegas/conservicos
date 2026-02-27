<?php

namespace App\Filament\Resources\PaymentBatches\Schemas;

use App\Enums\CnabFormat;
use App\Enums\PaymentBatchStatus;
use App\Enums\PaymentBatchType;
use App\Models\Company;
use App\Models\PaymentBatch;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Tabs;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PaymentBatchForm
{
    private const BANK_OPTIONS = [
        '001' => '001 - Banco do Brasil',
        '104' => '104 - Caixa Econômica Federal',
    ];

    private const MONTH_NAMES = [
        1  => 'Janeiro',
        2  => 'Fevereiro',
        3  => 'Março',
        4  => 'Abril',
        5  => 'Maio',
        6  => 'Junho',
        7  => 'Julho',
        8  => 'Agosto',
        9  => 'Setembro',
        10 => 'Outubro',
        11 => 'Novembro',
        12 => 'Dezembro',
    ];

    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Tabs::make('Lote')
                ->tabs([
                    Tabs\Tab::make('Configuração')
                        ->schema([
                            Select::make('company_id')
                                ->label('Empresa')
                                ->options(Company::query()->pluck('name', 'id'))
                                ->searchable()
                                ->required(),

                            Select::make('type')
                                ->label('Tipo de Pagamento')
                                ->options(collect(PaymentBatchType::cases())
                                    ->mapWithKeys(fn ($case) => [$case->value => $case->label()])
                                    ->toArray())
                                ->required()
                                ->live(),

                            Select::make('reference_month')
                                ->label('Mês de Referência')
                                ->options(self::MONTH_NAMES)
                                ->required(),

                            TextInput::make('reference_year')
                                ->label('Ano de Referência')
                                ->numeric()
                                ->minValue(2020)
                                ->maxValue(2099)
                                ->default(now()->year)
                                ->required(),

                            DatePicker::make('payment_date')
                                ->label('Data de Pagamento')
                                ->required()
                                ->displayFormat('d/m/Y'),

                            Select::make('bank_code')
                                ->label('Banco')
                                ->options(self::BANK_OPTIONS)
                                ->required(),

                            Select::make('cnab_format')
                                ->label('Formato CNAB')
                                ->options(collect(CnabFormat::cases())
                                    ->mapWithKeys(fn ($case) => [$case->value => $case->label()])
                                    ->toArray())
                                ->required(),

                            Textarea::make('notes')
                                ->label('Observações')
                                ->rows(3)
                                ->columnSpanFull(),
                        ])
                        ->columns(2),

                    Tabs\Tab::make('Revisão')
                        ->schema([
                            Placeholder::make('total_records_display')
                                ->label('Total de Registros')
                                ->content(fn (?PaymentBatch $record) => $record?->total_records ?? 0),

                            Placeholder::make('total_amount_display')
                                ->label('Valor Total')
                                ->content(fn (?PaymentBatch $record) =>
                                    $record ? 'R$ ' . number_format((float) $record->total_amount, 2, ',', '.') : 'R$ 0,00'
                                ),

                            Placeholder::make('status_display')
                                ->label('Status')
                                ->content(fn (?PaymentBatch $record) =>
                                    $record?->status instanceof PaymentBatchStatus
                                        ? $record->status->label()
                                        : 'Rascunho'
                                ),

                            Placeholder::make('file_path_display')
                                ->label('Arquivo CNAB')
                                ->content(fn (?PaymentBatch $record) => $record?->file_path ?? 'Não gerado'),
                        ])
                        ->visible(fn (?PaymentBatch $record) =>
                            $record !== null && $record->status !== PaymentBatchStatus::Draft
                        ),
                ])
                ->columnSpanFull(),
        ]);
    }
}
