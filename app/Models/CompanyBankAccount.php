<?php

namespace App\Models;

use App\Enums\AccountType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompanyBankAccount extends Model
{
    protected $fillable = [
        'company_id',
        'bank_code',
        'bank_name',
        'agency',
        'agency_digit',
        'account_number',
        'account_digit',
        'account_type',
        'covenant_code',
        'is_default',
        'active',
    ];

    protected $casts = [
        'account_type' => AccountType::class,
        'is_default'   => 'boolean',
        'active'       => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
