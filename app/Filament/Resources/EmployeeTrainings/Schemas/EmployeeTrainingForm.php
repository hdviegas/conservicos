<?php

namespace App\Filament\Resources\EmployeeTrainings\Schemas;

use App\Enums\ComplianceStatus;
use App\Models\Employee;
use App\Models\Training;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class EmployeeTrainingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('employee_id')
                ->label('Funcionário')
                ->options(Employee::orderBy('name')->pluck('name', 'id'))
                ->required()
                ->searchable()
                ->preload(),
            Select::make('training_id')
                ->label('Treinamento')
                ->options(Training::active()->orderBy('name')->pluck('name', 'id'))
                ->required()
                ->searchable()
                ->preload(),
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
                ->directory('employee-trainings')
                ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])
                ->maxSize(5120),
            FileUpload::make('certificate_path')
                ->label('Certificado')
                ->disk('public')
                ->directory('employee-certificates')
                ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])
                ->maxSize(5120),
            Textarea::make('notes')
                ->label('Observações')
                ->maxLength(1000)
                ->columnSpanFull(),
        ])->columns(2);
    }
}
