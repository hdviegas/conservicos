<?php

namespace Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Seeder;

class CompanySeeder extends Seeder
{
    public function run(): void
    {
        Company::firstOrCreate(
            ['cnpj' => '03.225.148/0001-12'],
            [
                'name' => 'SERVICON SERVICOS LTDA',
                'inscricao_estadual' => 'ISENTO',
                'active' => true,
            ]
        );

        Company::firstOrCreate(
            ['cnpj' => '44.053.313/0001-83'],
            [
                'name' => 'LARA P R ROCHA PSICOLOGIA E SERVICOS LTDA',
                'inscricao_estadual' => 'ISENTO',
                'active' => true,
            ]
        );
    }
}
