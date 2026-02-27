<?php

namespace App\Enums;

enum DayType: string
{
    case Worked = 'worked';
    case Sunday = 'sunday';
    case Holiday = 'holiday';
    case DayOff = 'folga';
    case BankHoursOff = 'folga_bh';
    case Vacation = 'vacation';
    case MedicalLeave = 'medical_leave';
    case WeddingLeave = 'wedding_leave';
    case Absence = 'absence';
    case OtherJustified = 'other_justified';

    public function label(): string
    {
        return match($this) {
            self::Worked => 'Trabalhado',
            self::Sunday => 'Domingo',
            self::Holiday => 'Feriado',
            self::DayOff => 'Folga',
            self::BankHoursOff => 'Folga BH',
            self::Vacation => 'Férias',
            self::MedicalLeave => 'Atestado Médico',
            self::WeddingLeave => 'Licença Casamento',
            self::Absence => 'Falta',
            self::OtherJustified => 'Justificado',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::Worked => 'success',
            self::Sunday => 'gray',
            self::Holiday => 'info',
            self::DayOff => 'warning',
            self::BankHoursOff => 'warning',
            self::Vacation => 'primary',
            self::MedicalLeave => 'danger',
            self::WeddingLeave => 'primary',
            self::Absence => 'danger',
            self::OtherJustified => 'warning',
        };
    }
}
