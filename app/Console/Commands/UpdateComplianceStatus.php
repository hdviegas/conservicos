<?php

namespace App\Console\Commands;

use App\Enums\ComplianceStatus;
use App\Models\EmployeeExam;
use App\Models\EmployeeTraining;
use Carbon\Carbon;
use Illuminate\Console\Command;

class UpdateComplianceStatus extends Command
{
    protected $signature = 'app:update-compliance-status';

    protected $description = 'Recalcula o status de todos os exames e treinamentos de funcionários';

    public function handle(): int
    {
        $updatedCount = 0;

        $updatedCount += $this->updateExams();
        $updatedCount += $this->updateTrainings();

        $this->info("Status de compliance atualizado. Total de registros atualizados: {$updatedCount}");

        return self::SUCCESS;
    }

    private function resolveStatus(?Carbon $expirationDate, int $validityMonths): ComplianceStatus
    {
        if ($expirationDate === null || $validityMonths === 0) {
            return ComplianceStatus::Valid;
        }

        $daysUntilExpiration = Carbon::now()->diffInDays($expirationDate, false);

        if ($daysUntilExpiration < 0) {
            return ComplianceStatus::Expired;
        }

        if ($daysUntilExpiration <= 15) {
            return ComplianceStatus::Expiring15d;
        }

        if ($daysUntilExpiration <= 30) {
            return ComplianceStatus::Expiring30d;
        }

        return ComplianceStatus::Valid;
    }

    private function updateExams(): int
    {
        $updated = 0;

        EmployeeExam::with('exam')->each(function (EmployeeExam $employeeExam) use (&$updated) {
            $validityMonths = $employeeExam->exam?->validity_months ?? 0;
            $newStatus = $this->resolveStatus($employeeExam->expiration_date, $validityMonths);

            if ($employeeExam->status !== $newStatus) {
                $employeeExam->status = $newStatus;
                $employeeExam->save();
                $updated++;
            }
        });

        $this->line("Exames verificados. Atualizados: {$updated}");

        return $updated;
    }

    private function updateTrainings(): int
    {
        $updated = 0;

        EmployeeTraining::with('training')->each(function (EmployeeTraining $employeeTraining) use (&$updated) {
            $validityMonths = $employeeTraining->training?->validity_months ?? 0;
            $newStatus = $this->resolveStatus($employeeTraining->expiration_date, $validityMonths);

            if ($employeeTraining->status !== $newStatus) {
                $employeeTraining->status = $newStatus;
                $employeeTraining->save();
                $updated++;
            }
        });

        $this->line("Treinamentos verificados. Atualizados: {$updated}");

        return $updated;
    }
}
