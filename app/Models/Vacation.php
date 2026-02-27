<?php

namespace App\Models;

use App\Enums\VacationStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Vacation extends Model
{
    protected $fillable = [
        'employee_id',
        'acquisition_period_start',
        'acquisition_period_end',
        'scheduled_start',
        'scheduled_end',
        'actual_start',
        'actual_end',
        'days_enjoyed',
        'days_sold',
        'status',
        'notes',
    ];

    protected $casts = [
        'acquisition_period_start' => 'date',
        'acquisition_period_end' => 'date',
        'scheduled_start' => 'date',
        'scheduled_end' => 'date',
        'actual_start' => 'date',
        'actual_end' => 'date',
        'days_enjoyed' => 'integer',
        'days_sold' => 'integer',
        'status' => VacationStatus::class,
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
