<?php

namespace App\Models;

use App\Enums\PaymentItemStatus;
use App\Enums\PaymentMethod;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentBatchItem extends Model
{
    protected $fillable = [
        'payment_batch_id',
        'employee_id',
        'amount',
        'payment_method',
        'bank_code',
        'agency',
        'agency_digit',
        'account_number',
        'account_digit',
        'account_type',
        'pix_key',
        'status',
        'rejection_reason',
        'reference_id',
        'notes',
    ];

    protected $casts = [
        'payment_method' => PaymentMethod::class,
        'status'         => PaymentItemStatus::class,
        'amount'         => 'decimal:2',
    ];

    public function paymentBatch(): BelongsTo
    {
        return $this->belongsTo(PaymentBatch::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
