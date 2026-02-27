<?php

namespace App\Filament\Widgets;

use App\Enums\VacationStatus;
use App\Models\Vacation;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\TableWidget as BaseWidget;

class UpcomingVacationsWidget extends BaseWidget
{
    use InteractsWithPageFilters;

    protected static ?string $heading = 'Férias Próximas (30 dias)';

    protected static ?int $sort = 5;

    protected int|string|array $columnSpan = 1;

    public function table(Table $table): Table
    {
        $companyId = $this->filters['company_id'] ?? null;

        return $table
            ->query(
                fn () => Vacation::query()
                    ->with(['employee.department'])
                    ->whereIn('status', [VacationStatus::Scheduled, VacationStatus::Pending])
                    ->whereNotNull('scheduled_start')
                    ->whereBetween('scheduled_start', [
                        now()->toDateString(),
                        now()->addDays(30)->toDateString(),
                    ])
                    ->when($companyId, fn ($q) => $q->whereHas('employee', fn ($e) => $e->where('company_id', $companyId)))
                    ->orderBy('scheduled_start', 'asc')
            )
            ->columns([
                TextColumn::make('employee.name')
                    ->label('Funcionário')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('employee.department.name')
                    ->label('Departamento')
                    ->sortable(),

                TextColumn::make('scheduled_start')
                    ->label('Início')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('scheduled_end')
                    ->label('Fim')
                    ->date('d/m/Y'),

                TextColumn::make('days_enjoyed')
                    ->label('Dias')
                    ->numeric()
                    ->alignCenter(),
            ])
            ->emptyStateHeading('Nenhuma férias nos próximos 30 dias')
            ->emptyStateDescription('Não há férias programadas para o período.')
            ->paginated(false);
    }
}
