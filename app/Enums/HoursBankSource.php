<?php

namespace App\Enums;

enum HoursBankSource: string
{
    case Import = 'import';
    case Manual = 'manual';
    case BankHoursOff = 'bank_hours_off';
    case OvertimeConversion = 'overtime_conversion';

    public function label(): string
    {
        return match($this) {
            self::Import => 'Importação',
            self::Manual => 'Manual',
            self::BankHoursOff => 'Folga BH',
            self::OvertimeConversion => 'Conversão Hora Extra',
        };
    }
}
