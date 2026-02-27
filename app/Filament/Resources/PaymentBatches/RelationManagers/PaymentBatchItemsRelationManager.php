<?php

namespace App\Filament\Resources\PaymentBatches\RelationManagers;

use App\Enums\PaymentBatchStatus;
use App\Enums\PaymentItemStatus;
use App\Enums\PaymentMethod;
use App\Models\Employee;
use App\Models\PaymentBatch;
use App\Models\PaymentBatchItem;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PaymentBatchItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    protected static ?string $title = 'Itens do Lote';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('employee_id')
                ->label('Funcionário')
                ->options(
                    Employee::query()->orderBy('name')->pluck('name', 'id')
                )
                ->searchable()
                ->required(),

            TextInput::make('amount')
                ->label('Valor (R$)')
                ->numeric()
                ->prefix('R$')
                ->required(),

            Select::make('payment_method')
                ->label('Forma de Pagamento')
                ->options(collect(PaymentMethod::cases())
                    ->mapWithKeys(fn ($c) => [$c->value => $c->label()])
                    ->toArray())
                ->required(),

            TextInput::make('bank_code')
                ->label('Banco')
                ->maxLength(5),

            TextInput::make('agency')
                ->label('Agência')
                ->maxLength(10),

            TextInput::make('agency_digit')
                ->label('Dígito Agência')
                ->maxLength(2),

            TextInput::make('account_number')
                ->label('Conta')
                ->maxLength(15),

            TextInput::make('account_digit')
                ->label('Dígito Conta')
                ->maxLength(2),

            TextInput::make('pix_key')
                ->label('Chave PIX')
                ->maxLength(255),

            Select::make('status')
                ->label('Status')
                ->options(collect(PaymentItemStatus::cases())
                    ->mapWithKeys(fn ($c) => [$c->value => $c->label()])
                    ->toArray())
                ->required(),

            TextInput::make('rejection_reason')
                ->label('Motivo da Rejeição')
                ->maxLength(255),

            Textarea::make('notes')
                ->label('Observações')
                ->rows(2)
                ->columnSpanFull(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('employee.name')
                    ->label('Funcionário')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('amount')
                    ->label('Valor')
                    ->money('BRL'),

                TextColumn::make('payment_method')
                    ->label('Forma Pgto')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state instanceof PaymentMethod ? $state->label() : $state)
                    ->color(fn ($state) => $state === PaymentMethod::Pix ? 'success' : 'info'),

                TextColumn::make('bank_account')
                    ->label('Banco / Conta')
                    ->state(fn (PaymentBatchItem $record) =>
                        $record->payment_method === PaymentMethod::Pix
                            ? 'PIX: ' . ($record->pix_key ?? '-')
                            : ($record->bank_code ?? '-') . ' / ' . ($record->account_number ?? '-')
                    ),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state instanceof PaymentItemStatus ? $state->label() : $state)
                    ->color(fn ($state) => $state instanceof PaymentItemStatus ? $state->color() : 'gray'),

                TextColumn::make('rejection_reason')
                    ->label('Motivo Rejeição')
                    ->limit(40)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordActions([
                EditAction::make()
                    ->visible(fn (PaymentBatchItem $record) =>
                        $record->paymentBatch->status === PaymentBatchStatus::Draft
                    ),
                DeleteAction::make()
                    ->visible(fn (PaymentBatchItem $record) =>
                        $record->paymentBatch->status === PaymentBatchStatus::Draft
                    ),
            ]);
    }
}
