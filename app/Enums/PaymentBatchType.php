<?php

namespace App\Enums;

enum PaymentBatchType: string
{
    case Salary = 'salary';
    case Advance = 'advance';
    case Vacation = 'vacation';
    case Termination = 'termination';
    case TransportVoucher = 'transport_voucher';
    case MealVoucher = 'meal_voucher';

    public function label(): string
    {
        return match($this) {
            self::Salary => 'Salário',
            self::Advance => 'Adiantamento',
            self::Vacation => 'Férias',
            self::Termination => 'Rescisão',
            self::TransportVoucher => 'Vale Transporte',
            self::MealVoucher => 'Vale Refeição',
        };
    }
}
