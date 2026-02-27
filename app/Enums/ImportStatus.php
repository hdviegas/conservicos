<?php

namespace App\Enums;

enum ImportStatus: string
{
    case Pending = 'pending';
    case Processing = 'processing';
    case Completed = 'completed';
    case Failed = 'failed';

    public function label(): string
    {
        return match($this) {
            self::Pending => 'Pendente',
            self::Processing => 'Processando',
            self::Completed => 'Concluído',
            self::Failed => 'Falhou',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::Pending => 'warning',
            self::Processing => 'info',
            self::Completed => 'success',
            self::Failed => 'danger',
        };
    }
}
