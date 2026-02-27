<?php

namespace App\Filament\Resources\Absences\Schemas;

use App\Enums\AbsenceType;
use App\Models\Employee;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class AbsenceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('employee_id')
                ->label('Funcionário')
                ->options(Employee::where('active', true)->orderBy('name')->pluck('name', 'id'))
                ->required()
                ->searchable()
                ->preload(),
            DatePicker::make('date')
                ->label('Data')
                ->required()
                ->displayFormat('d/m/Y'),
            Select::make('type')
                ->label('Tipo')
                ->options(collect(AbsenceType::cases())->mapWithKeys(
                    fn (AbsenceType $t) => [$t->value => $t->label()]
                ))
                ->required()
                ->live()
                ->afterStateUpdated(function (?string $state, Set $set) {
                    $type = AbsenceType::tryFrom((string) $state);
                    if ($type) {
                        $set('justified', $type->isJustified());
                    }
                }),
            Toggle::make('justified')
                ->label('Justificada')
                ->default(false),
            TextInput::make('cid_code')
                ->label('CID')
                ->maxLength(10)
                ->visible(fn (Get $get) => $get('type') === AbsenceType::MedicalCertificate->value),
            TextInput::make('days_count')
                ->label('Quantidade de Dias')
                ->numeric()
                ->required()
                ->default(1)
                ->minValue(1),
            Textarea::make('justification_text')
                ->label('Justificativa')
                ->maxLength(1000)
                ->columnSpanFull(),
            FileUpload::make('attachment_path')
                ->label('Atestado / Documento')
                ->disk('local')
                ->directory('absences/attachments')
                ->visibility('private')
                ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])
                ->maxSize(5120)
                ->columnSpanFull(),
            Textarea::make('notes')
                ->label('Observações')
                ->maxLength(500)
                ->columnSpanFull(),
        ])->columns(2);
    }
}
