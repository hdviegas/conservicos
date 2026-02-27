<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Holiday extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'date',
        'city_id',
        'recurring',
        'active',
    ];

    protected $casts = [
        'date' => 'date',
        'recurring' => 'boolean',
        'active' => 'boolean',
    ];

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function isNational(): bool
    {
        return is_null($this->city_id);
    }
}
