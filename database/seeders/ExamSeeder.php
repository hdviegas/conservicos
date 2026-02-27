<?php

namespace Database\Seeders;

use App\Enums\ExamCategory;
use App\Models\Exam;
use Illuminate\Database\Seeder;

class ExamSeeder extends Seeder
{
    public function run(): void
    {
        $exams = [
            [
                'name' => 'Exame Admissional',
                'category' => ExamCategory::OccupationalHealth,
                'validity_months' => 0,
                'is_mandatory' => false,
                'requires_attachment' => false,
                'active' => true,
            ],
            [
                'name' => 'Exame Periódico (ASO)',
                'category' => ExamCategory::OccupationalHealth,
                'validity_months' => 12,
                'is_mandatory' => true,
                'requires_attachment' => true,
                'active' => true,
            ],
            [
                'name' => 'Exame Demissional',
                'category' => ExamCategory::OccupationalHealth,
                'validity_months' => 0,
                'is_mandatory' => false,
                'requires_attachment' => false,
                'active' => true,
            ],
            [
                'name' => 'Exame de Retorno ao Trabalho',
                'category' => ExamCategory::OccupationalHealth,
                'validity_months' => 0,
                'is_mandatory' => false,
                'requires_attachment' => false,
                'active' => true,
            ],
            [
                'name' => 'Exame de Mudança de Função',
                'category' => ExamCategory::OccupationalHealth,
                'validity_months' => 0,
                'is_mandatory' => false,
                'requires_attachment' => false,
                'active' => true,
            ],
            [
                'name' => 'NR-11 Empilhadeira/Transporte',
                'category' => ExamCategory::RegulatoryNorm,
                'nr_reference' => 'NR-11',
                'validity_months' => 12,
                'is_mandatory' => true,
                'requires_attachment' => false,
                'active' => true,
            ],
            [
                'name' => 'NR-12 Segurança em Máquinas',
                'category' => ExamCategory::RegulatoryNorm,
                'nr_reference' => 'NR-12',
                'validity_months' => 12,
                'is_mandatory' => true,
                'requires_attachment' => false,
                'active' => true,
            ],
            [
                'name' => 'NR-35 Trabalho em Altura',
                'category' => ExamCategory::RegulatoryNorm,
                'nr_reference' => 'NR-35',
                'validity_months' => 24,
                'is_mandatory' => true,
                'requires_attachment' => false,
                'active' => true,
            ],
            [
                'name' => 'NR-06 EPIs',
                'category' => ExamCategory::RegulatoryNorm,
                'nr_reference' => 'NR-06',
                'validity_months' => 12,
                'is_mandatory' => true,
                'requires_attachment' => false,
                'active' => true,
            ],
        ];

        foreach ($exams as $examData) {
            Exam::firstOrCreate(
                ['name' => $examData['name']],
                $examData
            );
        }
    }
}
