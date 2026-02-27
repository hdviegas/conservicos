<?php

namespace App\Filament\Resources\EmployeeExams\Tables;

use App\Enums\ComplianceStatus;
use App\Enums\ExamResult;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class EmployeeExamsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('employee.name')
                    ->label('Funcionário')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('exam.name')
                    ->label('Exame')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('performed_date')
                    ->label('Realizado em')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('expiration_date')
                    ->label('Vencimento')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state instanceof ComplianceStatus ? $state->label() : $state)
                    ->color(fn ($state) => $state instanceof ComplianceStatus ? $state->color() : 'gray'),
                TextColumn::make('result')
                    ->label('Resultado')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state instanceof ExamResult ? $state->label() : ($state ? $state : '-'))
                    ->color(fn ($state) => match ($state instanceof ExamResult ? $state : ExamResult::tryFrom((string) $state)) {
                        ExamResult::Fit => 'success',
                        ExamResult::Unfit => 'danger',
                        ExamResult::FitWithRestrictions => 'warning',
                        default => 'gray',
                    }),
                TextColumn::make('provider')
                    ->label('Clínica')
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('attachment_path')
                    ->label('Anexo')
                    ->boolean()
                    ->trueIcon('heroicon-o-paper-clip')
                    ->falseIcon('heroicon-o-minus'),
            ])
            ->filters([
                SelectFilter::make('employee_id')
                    ->label('Funcionário')
                    ->relationship('employee', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('exam_id')
                    ->label('Exame')
                    ->relationship('exam', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('status')
                    ->label('Status')
                    ->options(collect(ComplianceStatus::cases())->mapWithKeys(
                        fn (ComplianceStatus $s) => [$s->value => $s->label()]
                    )),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->defaultSort('expiration_date', 'asc');
    }
}
