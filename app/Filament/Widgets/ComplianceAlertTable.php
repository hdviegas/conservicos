<?php

namespace App\Filament\Widgets;

use App\Enums\ComplianceStatus;
use App\Models\EmployeeExam;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\TableWidget as BaseWidget;

class ComplianceAlertTable extends BaseWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 11;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Alertas de Compliance';

    public function table(Table $table): Table
    {
        $companyId = $this->filters['company_id'] ?? null;

        return $table
            ->query(
                fn () => EmployeeExam::query()
                    ->with(['employee.position', 'exam'])
                    ->whereIn('status', [
                        ComplianceStatus::Expired->value,
                        ComplianceStatus::Expiring15d->value,
                        ComplianceStatus::Expiring30d->value,
                    ])
                    ->when($companyId, fn ($q) => $q->whereHas('employee', fn ($e) => $e->where('company_id', $companyId)))
                    ->orderByRaw("FIELD(status, 'expired', 'expiring_15d', 'expiring_30d')")
                    ->orderBy('expiration_date', 'asc')
            )
            ->columns([
                TextColumn::make('employee.name')
                    ->label('Funcionário')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('employee.position.name')
                    ->label('Cargo')
                    ->sortable(),
                TextColumn::make('exam.name')
                    ->label('Item Pendente')
                    ->searchable(),
                TextColumn::make('item_type')
                    ->label('Tipo')
                    ->badge()
                    ->getStateUsing(fn () => 'Exame')
                    ->color('info'),
                TextColumn::make('expiration_date')
                    ->label('Vencimento')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state instanceof ComplianceStatus ? $state->label() : $state)
                    ->color(fn ($state) => $state instanceof ComplianceStatus ? $state->color() : 'gray'),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        ComplianceStatus::Expired->value => ComplianceStatus::Expired->label(),
                        ComplianceStatus::Expiring15d->value => ComplianceStatus::Expiring15d->label(),
                        ComplianceStatus::Expiring30d->value => ComplianceStatus::Expiring30d->label(),
                    ]),
            ]);
    }
}
