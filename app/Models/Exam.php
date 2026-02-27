<?php

namespace App\Models;

use App\Enums\ExamCategory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Exam extends Model
{
    protected $fillable = [
        'name',
        'description',
        'category',
        'nr_reference',
        'validity_months',
        'is_mandatory',
        'requires_attachment',
        'active',
    ];

    protected $casts = [
        'category' => ExamCategory::class,
        'is_mandatory' => 'boolean',
        'requires_attachment' => 'boolean',
        'active' => 'boolean',
        'validity_months' => 'integer',
    ];

    public function employeeExams(): HasMany
    {
        return $this->hasMany(EmployeeExam::class);
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
