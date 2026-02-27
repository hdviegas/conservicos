<?php

namespace App\Models;

use App\Enums\HoursBankSource;
use App\Enums\HoursBankType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HoursBankEntry extends Model
{
    protected $fillable = [
        'employee_id',
        'date',
        'type',
        'minutes',
        'source',
        'description',
        'reference_time_record_id',
    ];

    protected $casts = [
        'date'   => 'date',
        'type'   => HoursBankType::class,
        'source' => HoursBankSource::class,
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function referenceTimeRecord(): BelongsTo
    {
        return $this->belongsTo(TimeRecord::class, 'reference_time_record_id');
    }
}
