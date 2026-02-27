<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $servicon = Company::where('cnpj', '03.225.148/0001-12')->firstOrFail();
        $lara = Company::where('cnpj', '44.053.313/0001-83')->firstOrFail();

        $departments = [
            ['company_id' => $servicon->id, 'name' => 'RIOGRANDENSE'],
            ['company_id' => $servicon->id, 'name' => 'CD MDIAS'],
            ['company_id' => $lara->id, 'name' => 'MOINHO RIBEIRA'],
        ];

        foreach ($departments as $department) {
            Department::firstOrCreate(
                ['company_id' => $department['company_id'], 'name' => $department['name']],
                ['active' => true]
            );
        }
    }
}
