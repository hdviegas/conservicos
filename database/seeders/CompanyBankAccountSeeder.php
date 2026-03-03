<?php

namespace Database\Seeders;

use App\Enums\AccountType;
use App\Models\Company;
use App\Models\CompanyBankAccount;
use Illuminate\Database\Seeder;

class CompanyBankAccountSeeder extends Seeder
{
    public function run(): void
    {
        $servicon = Company::where('cnpj', '03225148000112')->firstOrFail();
        $lara = Company::where('cnpj', '44053313000183')->firstOrFail();

        CompanyBankAccount::updateOrCreate(
            ['company_id' => $servicon->id, 'bank_code' => '001'],
            [
                'bank_name'      => 'BANCO DO BRASIL',
                'agency'         => '12345',
                'agency_digit'   => '5',
                'account_number' => '123456',
                'account_digit'  => '6',
                'account_type'   => AccountType::Checking,
                'covenant_code'  => '1234567',
                'is_default'     => true,
                'active'         => true,
            ]
        );

        CompanyBankAccount::updateOrCreate(
            ['company_id' => $servicon->id, 'bank_code' => '104'],
            [
                'bank_name'      => 'CAIXA ECONOMICA FEDERAL',
                'agency'         => '1234',
                'agency_digit'   => '5',
                'account_number' => '12345',
                'account_digit'  => '6',
                'account_type'   => AccountType::Checking,
                'covenant_code'  => '1234567',
                'is_default'     => false,
                'active'         => true,
            ]
        );

        CompanyBankAccount::updateOrCreate(
            ['company_id' => $lara->id, 'bank_code' => '001'],
            [
                'bank_name'      => 'BANCO DO BRASIL',
                'agency'         => '12345',
                'agency_digit'   => '5',
                'account_number' => '234567',
                'account_digit'  => '8',
                'account_type'   => AccountType::Checking,
                'covenant_code'  => '7654321',
                'is_default'     => true,
                'active'         => true,
            ]
        );

        CompanyBankAccount::updateOrCreate(
            ['company_id' => $lara->id, 'bank_code' => '104'],
            [
                'bank_name'      => 'CAIXA ECONOMICA FEDERAL',
                'agency'         => '1234',
                'agency_digit'   => '5',
                'account_number' => '23456',
                'account_digit'  => '7',
                'account_type'   => AccountType::Checking,
                'covenant_code'  => '7654321',
                'is_default'     => false,
                'active'         => true,
            ]
        );
    }
}
