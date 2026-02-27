<?php

namespace App\Enums;

enum AccountType: string
{
    case Checking = 'checking';
    case Savings = 'savings';

    public function label(): string
    {
        return match($this) {
            self::Checking => 'Conta Corrente',
            self::Savings => 'Conta Poupança',
        };
    }
}
