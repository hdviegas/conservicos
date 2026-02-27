<?php

namespace App\Enums;

enum PaymentBatchStatus: string
{
    case Draft = 'draft';
    case Generated = 'generated';
    case SentToBank = 'sent_to_bank';
    case Processed = 'processed';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match($this) {
            self::Draft => 'Rascunho',
            self::Generated => 'Gerado',
            self::SentToBank => 'Enviado ao Banco',
            self::Processed => 'Processado',
            self::Rejected => 'Rejeitado',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::Draft => 'gray',
            self::Generated => 'warning',
            self::SentToBank => 'info',
            self::Processed => 'success',
            self::Rejected => 'danger',
        };
    }
}
