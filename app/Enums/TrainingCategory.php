<?php

namespace App\Enums;

enum TrainingCategory: string
{
    case Operational = 'operational';
    case Safety = 'safety';
    case Regulatory = 'regulatory';
    case Onboarding = 'onboarding';

    public function label(): string
    {
        return match($this) {
            self::Operational => 'Operacional',
            self::Safety => 'Segurança',
            self::Regulatory => 'Regulatório',
            self::Onboarding => 'Integração',
        };
    }
}
