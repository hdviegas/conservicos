<?php

namespace App\Filament\Imports;

use App\Models\Company;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Position;
use App\Models\WorkSchedule;
use Carbon\Carbon;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

class EmployeeImporter extends Importer
{
    protected static ?string $model = Employee::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('external_id')
                ->label('ID Funcionário')
                ->rules(['nullable', 'string', 'max:20']),

            ImportColumn::make('cpf')
                ->label('CPF')
                ->requiredMapping()
                ->rules(['required', 'string', 'max:14']),

            ImportColumn::make('pis')
                ->label('PIS')
                ->rules(['nullable', 'string', 'max:15']),

            ImportColumn::make('name')
                ->label('Nome')
                ->requiredMapping()
                ->rules(['required', 'string', 'max:255']),

            ImportColumn::make('social_name')
                ->label('Nome Social')
                ->rules(['nullable', 'string', 'max:255']),

            ImportColumn::make('matricula')
                ->label('Matrícula')
                ->rules(['nullable', 'string', 'max:20']),

            ImportColumn::make('folha')
                ->label('Folha')
                ->rules(['nullable', 'string', 'max:20']),

            ImportColumn::make('ctps')
                ->label('CTPS')
                ->rules(['nullable', 'string', 'max:30']),

            ImportColumn::make('gender')
                ->label('Gênero')
                ->rules(['nullable', 'string', 'max:20']),

            ImportColumn::make('marital_status')
                ->label('Estado Civil')
                ->rules(['nullable', 'string', 'max:30']),

            ImportColumn::make('blood_type')
                ->label('Tipo Sanguíneo')
                ->rules(['nullable', 'string', 'max:5']),

            ImportColumn::make('phone')
                ->label('Telefone')
                ->rules(['nullable', 'string', 'max:20']),

            ImportColumn::make('emergency_phone')
                ->label('Telefone de Emergência')
                ->rules(['nullable', 'string', 'max:20']),

            ImportColumn::make('email')
                ->label('E-mail')
                ->rules(['nullable', 'email', 'max:255']),

            ImportColumn::make('address')
                ->label('Endereço')
                ->rules(['nullable', 'string', 'max:500']),

            ImportColumn::make('neighborhood')
                ->label('Bairro')
                ->rules(['nullable', 'string', 'max:100']),

            ImportColumn::make('city')
                ->label('Cidade')
                ->rules(['nullable', 'string', 'max:100']),

            ImportColumn::make('state')
                ->label('UF')
                ->rules(['nullable', 'string', 'max:2']),

            ImportColumn::make('zip_code')
                ->label('CEP')
                ->rules(['nullable', 'string', 'max:10']),

            ImportColumn::make('nationality')
                ->label('Nacionalidade')
                ->rules(['nullable', 'string', 'max:50']),

            ImportColumn::make('naturality')
                ->label('Naturalidade')
                ->rules(['nullable', 'string', 'max:100']),

            ImportColumn::make('father_name')
                ->label('Nome do Pai')
                ->rules(['nullable', 'string', 'max:255']),

            ImportColumn::make('mother_name')
                ->label('Nome da Mãe')
                ->rules(['nullable', 'string', 'max:255']),

            ImportColumn::make('cnh')
                ->label('CNH')
                ->rules(['nullable', 'string', 'max:20']),

            ImportColumn::make('cnh_category')
                ->label('Categoria CNH')
                ->rules(['nullable', 'string', 'max:5']),

            ImportColumn::make('observations')
                ->label('Observações')
                ->rules(['nullable', 'string']),

            // Relationship columns resolved via fillRecordUsing
            ImportColumn::make('company_id')
                ->label('Empresa')
                ->fillRecordUsing(function (Employee $record, ?string $state): void {
                    if (blank($state)) {
                        return;
                    }
                    $company = Company::where('name', 'like', '%' . trim($state) . '%')->first();
                    if ($company) {
                        $record->company_id = $company->id;
                    }
                }),

            ImportColumn::make('department_id')
                ->label('Departamento')
                ->fillRecordUsing(function (Employee $record, ?string $state): void {
                    if (blank($state)) {
                        return;
                    }
                    $dept = Department::where('name', trim($state))->first();
                    if ($dept) {
                        $record->department_id = $dept->id;
                    }
                }),

            ImportColumn::make('position_id')
                ->label('Cargo')
                ->fillRecordUsing(function (Employee $record, ?string $state): void {
                    if (blank($state)) {
                        return;
                    }
                    $position = Position::where('name', trim($state))->first();
                    if ($position) {
                        $record->position_id = $position->id;
                    }
                }),

            ImportColumn::make('work_schedule_id')
                ->label('Horário de Trabalho')
                ->fillRecordUsing(function (Employee $record, ?string $state): void {
                    if (blank($state)) {
                        return;
                    }
                    $schedule = WorkSchedule::where('name', trim($state))->first();
                    if ($schedule) {
                        $record->work_schedule_id = $schedule->id;
                    }
                }),

            // Date columns resolved via castStateUsing
            ImportColumn::make('admission_date')
                ->label('Data de Admissão')
                ->castStateUsing(function (?string $state): ?string {
                    if (blank($state)) {
                        return null;
                    }
                    try {
                        return Carbon::createFromFormat('d/m/Y', trim($state))->format('Y-m-d');
                    } catch (\Exception) {
                        return null;
                    }
                }),

            ImportColumn::make('birth_date')
                ->label('Data de Nascimento')
                ->castStateUsing(function (?string $state): ?string {
                    if (blank($state)) {
                        return null;
                    }
                    try {
                        return Carbon::createFromFormat('d/m/Y', trim($state))->format('Y-m-d');
                    } catch (\Exception) {
                        return null;
                    }
                }),

            ImportColumn::make('termination_date')
                ->label('Data de Demissão')
                ->castStateUsing(function (?string $state): ?string {
                    if (blank($state)) {
                        return null;
                    }
                    try {
                        return Carbon::createFromFormat('d/m/Y', trim($state))->format('Y-m-d');
                    } catch (\Exception) {
                        return null;
                    }
                }),

            ImportColumn::make('cnh_expiration')
                ->label('Vencimento da CNH')
                ->castStateUsing(function (?string $state): ?string {
                    if (blank($state)) {
                        return null;
                    }
                    try {
                        return Carbon::createFromFormat('d/m/Y', trim($state))->format('Y-m-d');
                    } catch (\Exception) {
                        return null;
                    }
                }),

            ImportColumn::make('hours_bank_start_date')
                ->label('Data de Início do Banco de Horas')
                ->castStateUsing(function (?string $state): ?string {
                    if (blank($state)) {
                        return null;
                    }
                    try {
                        return Carbon::createFromFormat('d/m/Y', trim($state))->format('Y-m-d');
                    } catch (\Exception) {
                        return null;
                    }
                }),

            ImportColumn::make('active')
                ->label('Ativo')
                ->castStateUsing(function (?string $state): bool {
                    if (blank($state)) {
                        return true;
                    }
                    return in_array(strtolower(trim($state)), ['true', '1', 'sim', 'yes']);
                }),
        ];
    }

    public function resolveRecord(): ?Employee
    {
        $cpf = preg_replace('/\D+/', '', (string) ($this->data['cpf'] ?? '')) ?? '';

        return Employee::withTrashed()->firstOrNew(['cpf' => $cpf]);
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'A importação de funcionários foi concluída. '
            . number_format($import->successful_rows) . ' '
            . str('funcionário')->plural($import->successful_rows)
            . ' importado(s) com sucesso.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' '
                . str('linha')->plural($failedRowsCount)
                . ' falhou ao importar.';
        }

        return $body;
    }
}
