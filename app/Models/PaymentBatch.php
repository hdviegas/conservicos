<?php

namespace App\Models;

use App\Enums\CnabFormat;
use App\Enums\PaymentBatchStatus;
use App\Enums\PaymentBatchType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaymentBatch extends Model
{
    protected $fillable = [
        'company_id',
        'type',
        'reference_month',
        'reference_year',
        'payment_date',
        'bank_code',
        'total_amount',
        'total_records',
        'status',
        'cnab_format',
        'file_path',
        'return_file_path',
        'notes',
        'generated_by',
        'generated_at',
    ];

    protected $casts = [
        'type'           => PaymentBatchType::class,
        'status'         => PaymentBatchStatus::class,
        'cnab_format'    => CnabFormat::class,
        'payment_date'   => 'date',
        'generated_at'   => 'datetime',
        'total_amount'   => 'decimal:2',
        'total_records'  => 'integer',
        'reference_month'=> 'integer',
        'reference_year' => 'integer',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function generatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PaymentBatchItem::class);
    }

    public function getPeriodLabelAttribute(): string
    {
        return str_pad((string) $this->reference_month, 2, '0', STR_PAD_LEFT) . '/' . $this->reference_year;
    }
}
