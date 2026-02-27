<?php

namespace App\Filament\Resources\Employees\RelationManagers;

use App\Enums\ComplianceStatus;
use App\Enums\ExamResult;
use App\Models\Exam;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class EmployeeExamsRelationManager extends RelationManager
{
    protected static string $relationship = 'employeeExams';

    protected static ?string $title = 'Exames Ocupacionais';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('exam_id')
                ->label('Exame')
                ->options(
                    Exam::active()->orderBy('name')->get()->mapWithKeys(
                        fn (Exam $e) => [$e->id => $e->name . ' (' . $e->category->label() . ')']
                    )
                )
                ->required()
                ->searchable()
                ->columnSpanFull(),
            DatePicker::make('performed_date')
                ->label('Data de Realização')
                ->required()
                ->displayFormat('d/m/Y'),
            DatePicker::make('expiration_date')
                ->label('Data de Vencimento')
                ->displayFormat('d/m/Y'),
            TextInput::make('provider')
                ->label('Clínica / Prestador')
                ->maxLength(255),
            TextInput::make('doctor_name')
                ->label('Médico Responsável')
                ->maxLength(255),
            TextInput::make('crm')
                ->label('CRM')
                ->maxLength(20),
            Select::make('status')
                ->label('Status')
                ->options(collect(ComplianceStatus::cases())->mapWithKeys(
                    fn (ComplianceStatus $s) => [$s->value => $s->label()]
                ))
                ->required()
                ->default(ComplianceStatus::Valid->value),
            Select::make('result')
                ->label('Resultado')
                ->options(collect(ExamResult::cases())->mapWithKeys(
                    fn (ExamResult $r) => [$r->value => $r->label()]
                ))
                ->live(),
            Textarea::make('restrictions')
                ->label('Restrições')
                ->columnSpanFull()
                ->visible(fn (Get $get) => $get('result') === ExamResult::FitWithRestrictions->value),
            FileUpload::make('attachment_path')
                ->label('ASO / Anexo')
                ->disk('public')
                ->directory('employee-exams')
                ->columnSpanFull(),
            Textarea::make('notes')
                ->label('Observações')
                ->columnSpanFull(),
        ])->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('exam.name')
                    ->label('Exame')
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
                IconColumn::make('attachment_path')
                    ->label('Anexo')
                    ->boolean()
                    ->trueIcon('heroicon-o-paper-clip')
                    ->falseIcon('heroicon-o-minus'),
            ])
            ->headerActions([
                CreateAction::make()->label('Adicionar Exame'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->defaultSort('expiration_date', 'asc');
    }
}
