<?php

namespace App\Filament\Widgets;

use App\Enums\PaymentBatchStatus;
use App\Enums\PaymentBatchType;
use App\Models\PaymentBatch;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\TableWidget as BaseWidget;

class PendingPaymentsWidget extends BaseWidget
{
    use InteractsWithPageFilters;

    protected static ?string $heading = 'Pagamentos Pendentes';

    protected static ?int $sort = 6;

    protected int|string|array $columnSpan = 1;

    public function table(Table $table): Table
    {
        $companyId = $this->filters['company_id'] ?? null;

        return $table
            ->query(
                fn () => PaymentBatch::query()
                    ->with('company')
                    ->whereIn('status', [
                        PaymentBatchStatus::Draft,
                        PaymentBatchStatus::Generated,
                    ])
                    ->when($companyId, fn ($q) => $q->where('company_id', $companyId))
                    ->orderBy('payment_date', 'asc')
            )
            ->columns([
                TextColumn::make('company.name')
                    ->label('Empresa')
                    ->sortable(),

                TextColumn::make('type')
                    ->label('Tipo')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state instanceof PaymentBatchType ? $state->label() : $state)
                    ->color('info'),

                TextColumn::make('period')
                    ->label('Período')
                    ->getStateUsing(fn (PaymentBatch $record): string =>
                        str_pad((string) $record->reference_month, 2, '0', STR_PAD_LEFT) . '/' . $record->reference_year
                    ),

                TextColumn::make('payment_date')
                    ->label('Data Pagamento')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('total_amount')
                    ->label('Total')
                    ->getStateUsing(fn (PaymentBatch $record): string =>
                        'R$ ' . number_format((float) $record->total_amount, 2, ',', '.')
                    )
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state instanceof PaymentBatchStatus ? $state->label() : $state)
                    ->color(fn ($state) => $state instanceof PaymentBatchStatus ? $state->color() : 'gray'),
            ])
            ->emptyStateHeading('Nenhum pagamento pendente')
            ->emptyStateDescription('Não há lotes em rascunho ou gerados.')
            ->paginated(false);
    }
}
