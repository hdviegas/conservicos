<?php

namespace Database\Seeders;

use App\Models\Position;
use Illuminate\Database\Seeder;

class PositionSeeder extends Seeder
{
    public function run(): void
    {
        $positions = [
            ['name' => 'AJUDANTE DE CARGAS', 'weekly_hours' => 44.00],
            ['name' => 'OPERADOR EMPILHADEIRA', 'weekly_hours' => 44.00],
            ['name' => 'ANALISTA DE LOGISTICA', 'weekly_hours' => 44.00],
            ['name' => 'CONFERENTE', 'weekly_hours' => 44.00],
            ['name' => 'AUXILIAR DE ESCRITORIO', 'weekly_hours' => 44.00],
            ['name' => 'AUXILIAR DE PATIO', 'weekly_hours' => 44.00],
        ];

        foreach ($positions as $position) {
            Position::firstOrCreate(
                ['name' => $position['name']],
                ['weekly_hours' => $position['weekly_hours'], 'active' => true]
            );
        }
    }
}
