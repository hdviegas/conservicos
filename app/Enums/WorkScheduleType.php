<?php

namespace App\Enums;

enum WorkScheduleType: string
{
    case Regular = 'regular';
    case Scale12x36 = 'scale_12x36';
    case Scale6x2 = 'scale_6x2';
    case Custom = 'custom';

    public function label(): string
    {
        return match($this) {
            self::Regular => 'Regular',
            self::Scale12x36 => 'Escala 12x36',
            self::Scale6x2 => 'Escala 6x2',
            self::Custom => 'Personalizado',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::Regular => 'success',
            self::Scale12x36 => 'warning',
            self::Scale6x2 => 'info',
            self::Custom => 'gray',
        };
    }
}
