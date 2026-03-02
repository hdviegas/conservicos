<?php

namespace App\Services;

use App\Enums\DayType;
use App\Models\Employee;
use App\Models\TimeImport;
use App\Models\TimeRecord;
use Carbon\Carbon;

class TimeReportImporterService
{
    /**
     * Normaliza CPF para comparação (somente dígitos).
     */
    private function normalizeCpf(string $cpf): string
    {
        return preg_replace('/\D+/', '', trim($cpf)) ?? '';
    }

    /**
     * Detecta o formato do CSV verificando se as colunas Extra 50% e Extra 100% existem.
     */
    public function detectFormat(array $headers): bool
    {
        $normalizedHeaders = array_map(fn (string $h) => preg_replace('/\s+/', ' ', trim($h)), $headers);

        foreach ($normalizedHeaders as $header) {
            if (str_contains($header, 'Extra') && str_contains($header, '50%')) {
                return true;
            }
        }

        return false;
    }

    /**
     * Parseia o campo "Dia" no formato "DD/MM/YYYY DDD" (ex: "01/01/2026 QUI").
     * Retorna Carbon ou null se falhar.
     */
    public function parseDate(string $value): ?Carbon
    {
        $value = trim($value);

        if (preg_match('/^(\d{2}\/\d{2}\/\d{4})/', $value, $matches)) {
            try {
                return Carbon::createFromFormat('d/m/Y', $matches[1]);
            } catch (\Exception) {
                return null;
            }
        }

        return null;
    }

    /**
     * Identifica o DayType a partir dos campos de entrada/saída do CSV.
     * Regras conforme AGENT.md seção 3.4.
     *
     * @return array{day_type: DayType, holiday_name: ?string}
     */
    public function parseDayType(string $entry1, string $exit1): array
    {
        $entry1 = trim($entry1);
        $exit1  = trim($exit1);

        $field = $entry1 ?: $exit1;

        if (preg_match('/^Feriado:\s*(.+)$/i', $field, $matches)) {
            return ['day_type' => DayType::Holiday, 'holiday_name' => trim($matches[1])];
        }

        if (strtolower($field) === 'domingo') {
            return ['day_type' => DayType::Sunday, 'holiday_name' => null];
        }

        if (preg_match('/justificado\s+folga\s+bh/i', $field)) {
            return ['day_type' => DayType::BankHoursOff, 'holiday_name' => null];
        }

        if (preg_match('/justificado\s+f[eé]rias/i', $field)) {
            return ['day_type' => DayType::Vacation, 'holiday_name' => null];
        }

        if (preg_match('/justificado\s+atestado/i', $field)) {
            return ['day_type' => DayType::MedicalLeave, 'holiday_name' => null];
        }

        if (preg_match('/justificado\s+licen[cç]a\s+casamento/i', $field)) {
            return ['day_type' => DayType::WeddingLeave, 'holiday_name' => null];
        }

        if (preg_match('/^justificado/i', $field)) {
            return ['day_type' => DayType::OtherJustified, 'holiday_name' => null];
        }

        if (strtolower($field) === 'folga') {
            return ['day_type' => DayType::DayOff, 'holiday_name' => null];
        }

        if (strtolower($field) === 'falta') {
            return ['day_type' => DayType::Absence, 'holiday_name' => null];
        }

        if (preg_match('/^\d{1,2}:\d{2}/', $entry1) || preg_match('/^\d{1,2}:\d{2}/', $exit1)) {
            return ['day_type' => DayType::Worked, 'holiday_name' => null];
        }

        if (empty($entry1) && empty($exit1)) {
            return ['day_type' => DayType::DayOff, 'holiday_name' => null];
        }

        return ['day_type' => DayType::Worked, 'holiday_name' => null];
    }

    /**
     * Converte string "HH:MM" para inteiro em minutos.
     * Retorna 0 se inválido ou vazio.
     */
    public function parseMinutes(string $value): int
    {
        $value = trim($value);

        if ($value === '' || $value === '00:00') {
            return 0;
        }

        $value = ltrim($value, '-');

        if (preg_match('/^(\d{1,3}):(\d{2})$/', $value, $matches)) {
            return (int) $matches[1] * 60 + (int) $matches[2];
        }

        return 0;
    }

    /**
     * Parseia horário "HH:MM" ou "HH:MM (I)" para formato "HH:MM:SS".
     * Retorna null se o campo não for um horário válido.
     */
    public function parseTime(string $value): ?string
    {
        $value = trim($value);
        $value = preg_replace('/\s*\(I\)\s*$/', '', $value);
        $value = trim($value);

        if (preg_match('/^(\d{1,2}):(\d{2})$/', $value, $matches)) {
            return sprintf('%02d:%02d:00', (int) $matches[1], (int) $matches[2]);
        }

        return null;
    }

    /**
     * Verifica se o valor é um horário real (não um tipo especial de dia).
     */
    public function isTimeValue(string $value): bool
    {
        $value = trim($value);
        $value = (string) preg_replace('/\s*\(I\)\s*$/', '', $value);

        return (bool) preg_match('/^\d{1,2}:\d{2}$/', trim($value));
    }

    /**
     * Processa o arquivo CSV e popula os time_records.
     *
     * @return array{records_count: int, errors: array<string>}
     */
    public function process(TimeImport $timeImport, string $filePath): array
    {
        $errors       = [];
        $recordsCount = 0;

        $handle = fopen($filePath, 'r');
        if (! $handle) {
            return ['records_count' => 0, 'errors' => ['Não foi possível abrir o arquivo.']];
        }

        $headerLine = fgets($handle);
        if (! $headerLine) {
            fclose($handle);

            return ['records_count' => 0, 'errors' => ['Arquivo vazio.']];
        }

        if (str_starts_with($headerLine, "\xEF\xBB\xBF")) {
            $headerLine = substr($headerLine, 3);
        }

        $headers           = str_getcsv(trim($headerLine), ';');
        $hasOvertimeColumns = $this->detectFormat($headers);
        $colMap            = array_flip(array_map('trim', $headers));

        rewind($handle);
        fgets($handle);

        $employeeCache = [];

        while (($line = fgets($handle)) !== false) {
            if (trim($line) === '') {
                continue;
            }

            $row = str_getcsv(trim($line), ';');

            $get = function (string $col) use ($row, $colMap): string {
                $idx = $colMap[$col] ?? $colMap[trim($col)] ?? null;

                return ($idx !== null && isset($row[$idx])) ? trim($row[$idx]) : '';
            };

            $cpfRaw = $get('CPF do funcionário');
            $cpf    = $this->normalizeCpf($cpfRaw);

            if (empty($cpf)) {
                continue;
            }

            if (! isset($employeeCache[$cpf])) {
                $employeeCache[$cpf] = Employee::query()
                    ->where('cpf', $cpfRaw)
                    ->orWhere('cpf', $cpf)
                    ->orWhereRaw(
                        "REPLACE(REPLACE(REPLACE(cpf, '.', ''), '-', ''), ' ', '') = ?",
                        [$cpf]
                    )
                    ->first();
            }
            $employee = $employeeCache[$cpf];

            if (! $employee) {
                $nome     = $get('Nome do funcionário');
                $errors[] = "Funcionário não encontrado: CPF {$cpfRaw} ({$nome})";
                continue;
            }

            $dateRaw = $get('Dia');
            $date    = $this->parseDate($dateRaw);
            if (! $date) {
                $errors[] = "Data inválida na linha: {$dateRaw}";
                continue;
            }

            $rawEntry1 = $get('Entrada 1');
            $rawExit1  = $get('Saída 1');
            $rawEntry2 = $get('Entrada 2');
            $rawExit2  = $get('Saída 2');

            ['day_type' => $dayType, 'holiday_name' => $holidayName] = $this->parseDayType($rawEntry1, $rawExit1);

            $entry1 = $this->isTimeValue($rawEntry1) ? $this->parseTime($rawEntry1) : null;
            $exit1  = $this->isTimeValue($rawExit1)  ? $this->parseTime($rawExit1)  : null;
            $entry2 = $this->isTimeValue($rawEntry2) ? $this->parseTime($rawEntry2) : null;
            $exit2  = $this->isTimeValue($rawExit2)  ? $this->parseTime($rawExit2)  : null;

            $totalNormalHours  = $this->parseMinutes($get('Total Normais'));
            $totalNightHours   = $this->parseMinutes($get('Total Noturno'));
            $isAbsenceDay      = ! empty(trim($get('Dia Falta')));
            $isWorkedDay       = ! empty(trim($get('Dias Trabalhados')));
            $absenceHours      = $this->parseMinutes($get('Falta e Atraso'));
            $bonusHours        = $this->parseMinutes($get('Abono'));
            $negativeBankHours = $this->parseMinutes($get('Banco Negativo'));
            $justification     = $get('Justificativas');

            $overtime50  = 0;
            $overtime100 = 0;

            if ($hasOvertimeColumns) {
                $ot50Key  = $this->findColumnKey($colMap, 'Extra 50%');
                $ot100Key = $this->findColumnKey($colMap, 'Extra 100%');
                $overtime50  = ($ot50Key !== null && isset($row[$ot50Key]))  ? $this->parseMinutes($row[$ot50Key])  : 0;
                $overtime100 = ($ot100Key !== null && isset($row[$ot100Key])) ? $this->parseMinutes($row[$ot100Key]) : 0;
            }

            try {
                TimeRecord::updateOrCreate(
                    ['employee_id' => $employee->id, 'date' => $date->format('Y-m-d')],
                    [
                        'time_import_id'      => $timeImport->id,
                        'entry_1'             => $entry1,
                        'exit_1'              => $exit1,
                        'entry_2'             => $entry2,
                        'exit_2'              => $exit2,
                        'total_normal_hours'  => $totalNormalHours,
                        'total_night_hours'   => $totalNightHours,
                        'day_type'            => $dayType->value,
                        'is_absence_day'      => $isAbsenceDay,
                        'is_worked_day'       => $isWorkedDay,
                        'absence_hours'       => $absenceHours,
                        'bonus_hours'         => $bonusHours,
                        'overtime_50'         => $overtime50,
                        'overtime_100'        => $overtime100,
                        'negative_bank_hours' => $negativeBankHours,
                        'justification'       => $justification ?: null,
                        'holiday_name'        => $holidayName,
                        'raw_entry_1'         => $rawEntry1 ?: null,
                        'raw_exit_1'          => $rawExit1 ?: null,
                        'raw_entry_2'         => $rawEntry2 ?: null,
                        'raw_exit_2'          => $rawExit2 ?: null,
                    ]
                );
                $recordsCount++;
            } catch (\Exception $e) {
                $errors[] = "Erro ao salvar registro {$cpf} / {$date->format('d/m/Y')}: " . $e->getMessage();
            }
        }

        fclose($handle);

        return ['records_count' => $recordsCount, 'errors' => $errors];
    }

    /**
     * Encontra índice da coluna no mapa com busca normalizada (trata espaços extras).
     */
    private function findColumnKey(array $colMap, string $search): ?int
    {
        $normalizedSearch = preg_replace('/\s+/', ' ', trim($search));

        foreach ($colMap as $header => $idx) {
            $normalizedHeader = preg_replace('/\s+/', ' ', trim($header));

            if ($normalizedHeader === $normalizedSearch) {
                return $idx;
            }
        }

        return null;
    }
}
