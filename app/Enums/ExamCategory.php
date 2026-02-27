<?php

namespace App\Enums;

enum ExamCategory: string
{
    case OccupationalHealth = 'occupational_health';
    case RegulatoryNorm = 'regulatory_norm';

    public function label(): string
    {
        return match($this) {
            self::OccupationalHealth => 'Saúde Ocupacional',
            self::RegulatoryNorm => 'Norma Regulamentadora',
        };
    }
}
