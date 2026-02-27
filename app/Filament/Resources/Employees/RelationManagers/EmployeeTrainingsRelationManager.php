<?php

namespace App\Filament\Resources\Employees\RelationManagers;

use App\Enums\ComplianceStatus;
use App\Models\Training;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class EmployeeTrainingsRelationManager extends RelationManager
{
    protected static string $relationship = 'employeeTrainings';

    protected static ?string $title = 'Treinamentos';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('training_id')
                ->label('Treinamento')
                ->options(Training::active()->orderBy('name')->pluck('name', 'id'))
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
            TextInput::make('instructor_name')
                ->label('Instrutor')
                ->maxLength(255),
            TextInput::make('institution')
                ->label('Instituição')
                ->maxLength(255),
            TextInput::make('hours_completed')
                ->label('Horas Realizadas')
                ->numeric()
                ->minValue(0),
            Select::make('status')
                ->label('Status')
                ->options(collect(ComplianceStatus::cases())->mapWithKeys(
                    fn (ComplianceStatus $s) => [$s->value => $s->label()]
                ))
                ->required()
                ->default(ComplianceStatus::Valid->value),
            TextInput::make('grade')
                ->label('Nota / Aproveitamento')
                ->maxLength(10),
            FileUpload::make('attachment_path')
                ->label('Comprovante')
                ->disk('public')
                ->directory('employee-trainings'),
            FileUpload::make('certificate_path')
                ->label('Certificado')
                ->disk('public')
                ->directory('employee-certificates'),
            Textarea::make('notes')
                ->label('Observações')
                ->columnSpanFull(),
        ])->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('training.name')
                    ->label('Treinamento')
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
                TextColumn::make('hours_completed')
                    ->label('Horas')
                    ->formatStateUsing(fn ($state) => $state ? $state . 'h' : '-'),
                IconColumn::make('certificate_path')
                    ->label('Certificado')
                    ->boolean()
                    ->trueIcon('heroicon-o-academic-cap')
                    ->falseIcon('heroicon-o-minus'),
            ])
            ->headerActions([
                CreateAction::make()->label('Adicionar Treinamento'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->defaultSort('expiration_date', 'asc');
    }
}
