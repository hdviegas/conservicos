<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            CompanySeeder::class,
            DepartmentSeeder::class,
            PositionSeeder::class,
            WorkScheduleSeeder::class,
            HolidaySeeder::class,
            ExamSeeder::class,
            TrainingSeeder::class,
            ComplianceRequirementSeeder::class,
            CompanyBankAccountSeeder::class,
        ]);
    }
}
