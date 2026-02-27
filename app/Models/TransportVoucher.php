<?php

namespace App\Models;

use App\Enums\TransportVoucherStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransportVoucher extends Model
{
    protected $fillable = [
        'employee_id',
        'transport_voucher_type_id',
        'period_start',
        'period_end',
        'worked_days',
        'daily_value',
        'total_value',
        'generated_at',
        'status',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'worked_days' => 'integer',
        'daily_value' => 'decimal:2',
        'total_value' => 'decimal:2',
        'generated_at' => 'datetime',
        'status' => TransportVoucherStatus::class,
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function transportVoucherType(): BelongsTo
    {
        return $this->belongsTo(TransportVoucherType::class);
    }
}
