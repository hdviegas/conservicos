<?php

namespace App\Enums;

enum CnabFormat: string
{
    case Cnab240 = 'cnab_240';
    case Cnab400 = 'cnab_400';

    public function label(): string
    {
        return match($this) {
            self::Cnab240 => 'CNAB 240',
            self::Cnab400 => 'CNAB 400',
        };
    }
}
