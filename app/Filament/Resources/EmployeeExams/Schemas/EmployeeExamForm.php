<?php

namespace App\Filament\Resources\EmployeeExams\Schemas;

use App\Enums\ComplianceStatus;
use App\Enums\ExamCategory;
use App\Enums\ExamResult;
use App\Models\Employee;
use App\Models\Exam;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class EmployeeExamForm
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
            Select::make('exam_id')
                ->label('Exame')
                ->options(
                    Exam::active()->orderBy('name')->get()->mapWithKeys(
                        fn (Exam $e) => [$e->id => $e->name . ' (' . $e->category->label() . ')']
                    )
                )
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
                ->maxLength(1000)
                ->columnSpanFull()
                ->visible(fn (Get $get) => $get('result') === ExamResult::FitWithRestrictions->value),
            FileUpload::make('attachment_path')
                ->label('ASO / Anexo')
                ->disk('public')
                ->directory('employee-exams')
                ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])
                ->maxSize(5120)
                ->columnSpanFull(),
            Textarea::make('notes')
                ->label('Observações')
                ->maxLength(1000)
                ->columnSpanFull(),
        ])->columns(2);
    }
}
