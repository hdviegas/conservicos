<?php

namespace App\Enums;

enum ImportType: string
{
    case Employees = 'employees';
    case TimeReport = 'time_report';

    public function label(): string
    {
        return match($this) {
            self::Employees => 'Funcionários',
            self::TimeReport => 'Relatório de Ponto',
        };
    }
}
