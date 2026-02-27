<?php

namespace Database\Seeders;

use App\Models\Holiday;
use Illuminate\Database\Seeder;

class HolidaySeeder extends Seeder
{
    public function run(): void
    {
        $holidays = [
            ['name' => 'Confraternização Universal', 'date' => '2026-01-01', 'recurring' => true],
            ['name' => 'Carnaval', 'date' => '2026-02-16', 'recurring' => false],
            ['name' => 'Carnaval', 'date' => '2026-02-17', 'recurring' => false],
            ['name' => 'Sexta-Feira Santa', 'date' => '2026-04-03', 'recurring' => false],
            ['name' => 'Tiradentes', 'date' => '2026-04-21', 'recurring' => true],
            ['name' => 'Dia do Trabalho', 'date' => '2026-05-01', 'recurring' => true],
            ['name' => 'Corpus Christi', 'date' => '2026-06-04', 'recurring' => false],
            ['name' => 'Independência do Brasil', 'date' => '2026-09-07', 'recurring' => true],
            ['name' => 'Nossa Senhora Aparecida', 'date' => '2026-10-12', 'recurring' => true],
            ['name' => 'Finados', 'date' => '2026-11-02', 'recurring' => true],
            ['name' => 'Proclamação da República', 'date' => '2026-11-15', 'recurring' => true],
            ['name' => 'Consciência Negra', 'date' => '2026-11-20', 'recurring' => true],
            ['name' => 'Natal', 'date' => '2026-12-25', 'recurring' => true],
        ];

        foreach ($holidays as $holiday) {
            Holiday::firstOrCreate(
                ['name' => $holiday['name'], 'date' => $holiday['date']],
                [
                    'city_id' => null,
                    'recurring' => $holiday['recurring'],
                    'active' => true,
                ]
            );
        }
    }
}
