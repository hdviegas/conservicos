<?php

namespace App\Enums;

enum HoursBankType: string
{
    case Credit = 'credit';
    case Debit = 'debit';

    public function label(): string
    {
        return match($this) {
            self::Credit => 'Crédito',
            self::Debit => 'Débito',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::Credit => 'success',
            self::Debit => 'danger',
        };
    }
}
