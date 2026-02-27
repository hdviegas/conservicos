<?php

namespace Database\Seeders;

use App\Enums\TrainingCategory;
use App\Models\Training;
use Illuminate\Database\Seeder;

class TrainingSeeder extends Seeder
{
    public function run(): void
    {
        $trainings = [
            [
                'name' => 'Operação de Empilhadeira',
                'category' => TrainingCategory::Operational,
                'nr_reference' => 'NR-11',
                'validity_months' => 12,
                'required_hours' => 8,
                'is_mandatory' => true,
                'requires_certificate' => true,
                'active' => true,
            ],
            [
                'name' => 'Movimentação Manual de Cargas',
                'category' => TrainingCategory::Operational,
                'nr_reference' => 'NR-11',
                'validity_months' => 12,
                'required_hours' => 4,
                'is_mandatory' => true,
                'requires_certificate' => false,
                'active' => true,
            ],
            [
                'name' => 'Segurança do Trabalho Geral',
                'category' => TrainingCategory::Safety,
                'nr_reference' => null,
                'validity_months' => 12,
                'required_hours' => 4,
                'is_mandatory' => true,
                'requires_certificate' => false,
                'active' => true,
            ],
            [
                'name' => 'Brigada de Incêndio / CIPA',
                'category' => TrainingCategory::Safety,
                'nr_reference' => 'NR-23',
                'validity_months' => 12,
                'required_hours' => 8,
                'is_mandatory' => true,
                'requires_certificate' => true,
                'active' => true,
            ],
            [
                'name' => 'Primeiros Socorros',
                'category' => TrainingCategory::Safety,
                'nr_reference' => null,
                'validity_months' => 24,
                'required_hours' => 8,
                'is_mandatory' => false,
                'requires_certificate' => false,
                'active' => true,
            ],
            [
                'name' => 'Integração de Novos Funcionários',
                'category' => TrainingCategory::Onboarding,
                'nr_reference' => null,
                'validity_months' => 0,
                'required_hours' => 4,
                'is_mandatory' => true,
                'requires_certificate' => false,
                'active' => true,
            ],
        ];

        foreach ($trainings as $trainingData) {
            Training::firstOrCreate(
                ['name' => $trainingData['name']],
                $trainingData
            );
        }
    }
}
