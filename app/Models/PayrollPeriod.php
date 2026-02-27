<?php

namespace App\Models;

use App\Enums\PayrollStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PayrollPeriod extends Model
{
    protected $fillable = [
        'company_id',
        'month',
        'year',
        'status',
        'calculated_at',
        'closed_at',
        'closed_by',
        'notes',
    ];

    protected $casts = [
        'status'        => PayrollStatus::class,
        'calculated_at' => 'datetime',
        'closed_at'     => 'datetime',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function closedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public function entries(): HasMany
    {
        return $this->hasMany(PayrollEntry::class);
    }

    public function getPeriodLabelAttribute(): string
    {
        return str_pad((string) $this->month, 2, '0', STR_PAD_LEFT) . '/' . $this->year;
    }
}
