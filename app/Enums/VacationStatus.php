<?php

namespace App\Enums;

enum VacationStatus: string
{
    case Pending = 'pending';
    case Scheduled = 'scheduled';
    case InProgress = 'in_progress';
    case Completed = 'completed';
    case Expired = 'expired';

    public function label(): string
    {
        return match($this) {
            self::Pending => 'Pendente',
            self::Scheduled => 'Agendado',
            self::InProgress => 'Em Curso',
            self::Completed => 'Concluído',
            self::Expired => 'Vencido',
        };
    }
}
