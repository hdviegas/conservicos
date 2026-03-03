<?php

namespace Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Seeder;

class CompanySeeder extends Seeder
{
    public function run(): void
    {
        Company::firstOrCreate(
            ['cnpj' => '03225148000112'],
            [
                'name' => 'SERVICON SERVICOS LTDA',
                'inscricao_estadual' => 'ISENTO',
                'active' => true,
            ]
        );

        Company::firstOrCreate(
            ['cnpj' => '44053313000183'],
            [
                'name' => 'LARA P R ROCHA PSICOLOGIA E SERVICOS LTDA',
                'inscricao_estadual' => 'ISENTO',
                'active' => true,
            ]
        );
    }
}
