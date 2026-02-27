<?php

namespace App\Enums;

enum TransportVoucherStatus: string
{
    case Pending = 'pending';
    case Paid = 'paid';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match($this) {
            self::Pending => 'Pendente',
            self::Paid => 'Pago',
            self::Cancelled => 'Cancelado',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::Pending => 'warning',
            self::Paid => 'success',
            self::Cancelled => 'danger',
        };
    }
}
