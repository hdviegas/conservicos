<?php

namespace App\Enums;

enum PaymentItemStatus: string
{
    case Pending = 'pending';
    case Paid = 'paid';
    case Rejected = 'rejected';
    case Returned = 'returned';

    public function label(): string
    {
        return match($this) {
            self::Pending => 'Pendente',
            self::Paid => 'Pago',
            self::Rejected => 'Rejeitado',
            self::Returned => 'Devolvido',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::Pending => 'warning',
            self::Paid => 'success',
            self::Rejected => 'danger',
            self::Returned => 'gray',
        };
    }
}
