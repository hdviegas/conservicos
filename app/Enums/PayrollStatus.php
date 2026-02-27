<?php

namespace App\Enums;

enum PayrollStatus: string
{
    case Draft = 'draft';
    case Calculating = 'calculating';
    case Calculated = 'calculated';
    case Reviewed = 'reviewed';
    case Closed = 'closed';

    public function label(): string
    {
        return match($this) {
            self::Draft => 'Rascunho',
            self::Calculating => 'Calculando',
            self::Calculated => 'Calculado',
            self::Reviewed => 'Revisado',
            self::Closed => 'Fechado',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::Draft => 'gray',
            self::Calculating => 'warning',
            self::Calculated => 'info',
            self::Reviewed => 'primary',
            self::Closed => 'success',
        };
    }
}
