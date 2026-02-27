<?php

namespace App\Models;

use App\Enums\WorkScheduleType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'weekly_work_days',
        'daily_hours',
        'weekly_hours',
        'start_time',
        'end_time',
        'is_night_shift',
        'description',
        'active',
    ];

    protected $casts = [
        'type' => WorkScheduleType::class,
        'weekly_work_days' => 'integer',
        'active' => 'boolean',
        'is_night_shift' => 'boolean',
        'daily_hours' => 'decimal:2',
        'weekly_hours' => 'decimal:2',
    ];

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('active', true);
    }
}
