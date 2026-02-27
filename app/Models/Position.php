<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Position extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'weekly_hours',
        'base_salary',
        'description',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
        'weekly_hours' => 'decimal:2',
        'base_salary' => 'decimal:2',
    ];

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }

    public function complianceRequirements(): HasMany
    {
        return $this->hasMany(CompliancePositionRequirement::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('active', true);
    }
}
