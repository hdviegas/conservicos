<?php

namespace App\Models;

use App\Enums\AbsenceType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Absence extends Model
{
    protected $fillable = [
        'employee_id',
        'date',
        'type',
        'justified',
        'justification_text',
        'attachment_path',
        'cid_code',
        'days_count',
        'notes',
    ];

    protected $casts = [
        'date'      => 'date',
        'type'      => AbsenceType::class,
        'justified' => 'boolean',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
