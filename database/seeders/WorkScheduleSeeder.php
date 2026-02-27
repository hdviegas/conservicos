<?php

namespace Database\Seeders;

use App\Enums\WorkScheduleType;
use App\Models\WorkSchedule;
use Illuminate\Database\Seeder;

class WorkScheduleSeeder extends Seeder
{
    public function run(): void
    {
        $schedules = [
            [
                'name' => '6H AS 14:20H SEG A SAB (MOINHO)',
                'type' => WorkScheduleType::Regular,
                'is_night_shift' => false,
                'start_time' => '06:00',
                'end_time' => '14:20',
            ],
            [
                'name' => '13:40H AS 22H SEG A SAB (MOINHO)',
                'type' => WorkScheduleType::Regular,
                'is_night_shift' => true,
                'start_time' => '13:40',
                'end_time' => '22:00',
            ],
            [
                'name' => '21:45 AS 06:33H (CD M DIAS)',
                'type' => WorkScheduleType::Regular,
                'is_night_shift' => true,
                'start_time' => '21:45',
                'end_time' => '06:33',
            ],
            [
                'name' => 'NOITE - ESCALA 12X36 - IMPAR (MOINHO)',
                'type' => WorkScheduleType::Scale12x36,
                'is_night_shift' => true,
                'daily_hours' => 12.00,
            ],
            [
                'name' => 'NOITE - ESCALA 12X36 - PAR (MOINHO)',
                'type' => WorkScheduleType::Scale12x36,
                'is_night_shift' => true,
                'daily_hours' => 12.00,
            ],
            [
                'name' => 'ESCALA 6X2 13H AS 21:15',
                'type' => WorkScheduleType::Scale6x2,
                'is_night_shift' => false,
                'start_time' => '13:00',
                'end_time' => '21:15',
            ],
            [
                'name' => '08H AS 17H SEG A SEXT E SAB 08H AS 12H (CD M DIAS)',
                'type' => WorkScheduleType::Regular,
                'is_night_shift' => false,
                'start_time' => '08:00',
                'end_time' => '17:00',
                'weekly_hours' => 44.00,
            ],
            [
                'name' => '20H (RIOGRANDENSE)',
                'type' => WorkScheduleType::Regular,
                'is_night_shift' => true,
                'start_time' => '20:00',
            ],
            [
                'name' => '7H (RIOGRANDENSE)',
                'type' => WorkScheduleType::Regular,
                'is_night_shift' => false,
                'start_time' => '07:00',
            ],
            [
                'name' => '8H (RIOGRANDENSE)',
                'type' => WorkScheduleType::Regular,
                'is_night_shift' => false,
                'start_time' => '08:00',
            ],
            [
                'name' => '07H ADM (RIOGRANDENSE)',
                'type' => WorkScheduleType::Regular,
                'is_night_shift' => false,
                'start_time' => '07:00',
            ],
            [
                'name' => '6H QUALIDADE (RIOGRANDENSE)',
                'type' => WorkScheduleType::Regular,
                'is_night_shift' => false,
                'start_time' => '06:00',
            ],
            [
                'name' => '08H AS 18H SEG A QUI E 08H AS 17H SEXT (CD M DIAS)',
                'type' => WorkScheduleType::Regular,
                'is_night_shift' => false,
                'start_time' => '08:00',
                'end_time' => '18:00',
                'weekly_hours' => 44.00,
            ],
            [
                'name' => '13:45H AS 22H SEG A SAB (MOINHO)',
                'type' => WorkScheduleType::Regular,
                'is_night_shift' => true,
                'start_time' => '13:45',
                'end_time' => '22:00',
            ],
            [
                'name' => '21:45H AS 06:33H SEG A SEXT (MOINHO)',
                'type' => WorkScheduleType::Regular,
                'is_night_shift' => true,
                'start_time' => '21:45',
                'end_time' => '06:33',
            ],
        ];

        foreach ($schedules as $schedule) {
            WorkSchedule::firstOrCreate(
                ['name' => $schedule['name']],
                array_merge(['active' => true], $schedule)
            );
        }
    }
}
