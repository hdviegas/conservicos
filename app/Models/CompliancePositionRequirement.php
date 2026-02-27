<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class CompliancePositionRequirement extends Model
{
    protected $fillable = [
        'position_id',
        'requireable_type',
        'requireable_id',
        'is_mandatory',
    ];

    protected $casts = [
        'is_mandatory' => 'boolean',
    ];

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    public function requireable(): MorphTo
    {
        return $this->morphTo();
    }
}
