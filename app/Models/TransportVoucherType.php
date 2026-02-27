<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TransportVoucherType extends Model
{
    protected $fillable = [
        'name',
        'description',
        'daily_value',
        'active',
    ];

    protected $casts = [
        'daily_value' => 'decimal:2',
        'active' => 'boolean',
    ];

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }

    public function transportVouchers(): HasMany
    {
        return $this->hasMany(TransportVoucher::class);
    }
}
