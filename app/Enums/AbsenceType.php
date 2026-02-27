<?php

namespace App\Enums;

enum AbsenceType: string
{
    case Unjustified = 'unjustified';
    case MedicalCertificate = 'medical_certificate';
    case WeddingLeave = 'wedding_leave';
    case PaternityLeave = 'paternity_leave';
    case BereavementLeave = 'bereavement_leave';
    case OtherJustified = 'other_justified';

    public function label(): string
    {
        return match($this) {
            self::Unjustified => 'Falta Injustificada',
            self::MedicalCertificate => 'Atestado Médico',
            self::WeddingLeave => 'Licença Casamento',
            self::PaternityLeave => 'Licença Paternidade',
            self::BereavementLeave => 'Luto',
            self::OtherJustified => 'Outro Justificado',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::Unjustified => 'danger',
            self::MedicalCertificate => 'warning',
            self::WeddingLeave => 'primary',
            self::PaternityLeave => 'primary',
            self::BereavementLeave => 'gray',
            self::OtherJustified => 'info',
        };
    }

    public function isJustified(): bool
    {
        return $this !== self::Unjustified;
    }
}
