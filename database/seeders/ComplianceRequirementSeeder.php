<?php

namespace Database\Seeders;

use App\Models\CompliancePositionRequirement;
use App\Models\Exam;
use App\Models\Position;
use App\Models\Training;
use Illuminate\Database\Seeder;

class ComplianceRequirementSeeder extends Seeder
{
    public function run(): void
    {
        $requirements = [
            'OPERADOR EMPILHADEIRA' => [
                'exams' => ['Exame Periódico (ASO)', 'NR-11 Empilhadeira/Transporte', 'NR-12 Segurança em Máquinas'],
                'trainings' => ['Operação de Empilhadeira'],
            ],
            'AJUDANTE DE CARGAS' => [
                'exams' => ['Exame Periódico (ASO)', 'NR-11 Empilhadeira/Transporte', 'NR-35 Trabalho em Altura', 'NR-06 EPIs'],
                'trainings' => ['Movimentação Manual de Cargas', 'Segurança do Trabalho Geral'],
            ],
            'ANALISTA DE LOGISTICA' => [
                'exams' => ['Exame Periódico (ASO)', 'NR-06 EPIs'],
                'trainings' => ['Integração de Novos Funcionários'],
            ],
            'CONFERENTE' => [
                'exams' => ['Exame Periódico (ASO)', 'NR-06 EPIs', 'NR-11 Empilhadeira/Transporte'],
                'trainings' => ['Segurança do Trabalho Geral'],
            ],
        ];

        foreach ($requirements as $positionName => $items) {
            $position = Position::where('name', $positionName)->first();

            if (! $position) {
                $this->command->warn("Cargo não encontrado: {$positionName}");
                continue;
            }

            foreach ($items['exams'] as $examName) {
                $exam = Exam::where('name', $examName)->first();

                if (! $exam) {
                    $this->command->warn("Exame não encontrado: {$examName}");
                    continue;
                }

                CompliancePositionRequirement::firstOrCreate([
                    'position_id' => $position->id,
                    'requireable_type' => Exam::class,
                    'requireable_id' => $exam->id,
                ], [
                    'is_mandatory' => true,
                ]);
            }

            foreach ($items['trainings'] as $trainingName) {
                $training = Training::where('name', $trainingName)->first();

                if (! $training) {
                    $this->command->warn("Treinamento não encontrado: {$trainingName}");
                    continue;
                }

                CompliancePositionRequirement::firstOrCreate([
                    'position_id' => $position->id,
                    'requireable_type' => Training::class,
                    'requireable_id' => $training->id,
                ], [
                    'is_mandatory' => true,
                ]);
            }
        }
    }
}
