<?php

namespace App\Models;

use App\Enums\ImportStatus;
use App\Enums\ImportType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TimeImport extends Model
{
    protected $fillable = [
        'company_id',
        'user_id',
        'filename',
        'original_filename',
        'period_month',
        'period_year',
        'type',
        'records_count',
        'status',
        'errors',
        'imported_at',
    ];

    protected $casts = [
        'type' => ImportType::class,
        'status' => ImportStatus::class,
        'errors' => 'array',
        'imported_at' => 'datetime',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function timeRecords(): HasMany
    {
        return $this->hasMany(TimeRecord::class);
    }
}
