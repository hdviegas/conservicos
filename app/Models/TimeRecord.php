<?php

namespace App\Models;

use App\Enums\DayType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TimeRecord extends Model
{
    protected $fillable = [
        'time_import_id',
        'employee_id',
        'date',
        'entry_1',
        'exit_1',
        'entry_2',
        'exit_2',
        'total_normal_hours',
        'total_night_hours',
        'day_type',
        'is_absence_day',
        'is_worked_day',
        'absence_hours',
        'bonus_hours',
        'overtime_50',
        'overtime_100',
        'negative_bank_hours',
        'justification',
        'holiday_name',
        'raw_entry_1',
        'raw_exit_1',
        'raw_entry_2',
        'raw_exit_2',
    ];

    protected $casts = [
        'date' => 'date',
        'day_type' => DayType::class,
        'is_absence_day' => 'boolean',
        'is_worked_day' => 'boolean',
    ];

    public function timeImport(): BelongsTo
    {
        return $this->belongsTo(TimeImport::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
