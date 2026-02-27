<?php

namespace App\Enums;

enum ExamResult: string
{
    case Fit = 'fit';
    case Unfit = 'unfit';
    case FitWithRestrictions = 'fit_with_restrictions';

    public function label(): string
    {
        return match($this) {
            self::Fit => 'Apto',
            self::Unfit => 'Inapto',
            self::FitWithRestrictions => 'Apto com Restrições',
        };
    }
}
