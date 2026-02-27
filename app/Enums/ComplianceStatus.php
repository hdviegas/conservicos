<?php

namespace App\Enums;

enum ComplianceStatus: string
{
    case Valid = 'valid';
    case Expiring30d = 'expiring_30d';
    case Expiring15d = 'expiring_15d';
    case Expired = 'expired';
    case NotApplicable = 'not_applicable';

    public function label(): string
    {
        return match($this) {
            self::Valid => 'Válido',
            self::Expiring30d => 'Vencendo em 30d',
            self::Expiring15d => 'Vencendo em 15d',
            self::Expired => 'Vencido',
            self::NotApplicable => 'Não Aplicável',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::Valid => 'success',
            self::Expiring30d => 'warning',
            self::Expiring15d => 'danger',
            self::Expired => 'danger',
            self::NotApplicable => 'gray',
        };
    }
}
