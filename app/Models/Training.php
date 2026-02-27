<?php

namespace App\Models;

use App\Enums\TrainingCategory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Training extends Model
{
    protected $fillable = [
        'name',
        'description',
        'category',
        'nr_reference',
        'validity_months',
        'required_hours',
        'is_mandatory',
        'requires_certificate',
        'active',
    ];

    protected $casts = [
        'category' => TrainingCategory::class,
        'is_mandatory' => 'boolean',
        'requires_certificate' => 'boolean',
        'active' => 'boolean',
        'validity_months' => 'integer',
        'required_hours' => 'integer',
    ];

    public function employeeTrainings(): HasMany
    {
        return $this->hasMany(EmployeeTraining::class);
    }

    public function complianceRequirements(): MorphMany
    {
        return $this->morphMany(CompliancePositionRequirement::class, 'requireable');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('active', true);
    }
}
