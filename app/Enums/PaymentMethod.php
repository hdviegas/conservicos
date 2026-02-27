<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case BankTransfer = 'bank_transfer';
    case Pix = 'pix';

    public function label(): string
    {
        return match($this) {
            self::BankTransfer => 'Transferência Bancária',
            self::Pix => 'PIX',
        };
    }
}
