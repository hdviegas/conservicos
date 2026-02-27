<?php

namespace App\Models;

use App\Enums\ComplianceStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeTraining extends Model
{
    protected $fillable = [
        'employee_id',
        'training_id',
        'performed_date',
        'expiration_date',
        'instructor_name',
        'institution',
        'hours_completed',
        'status',
        'grade',
        'attachment_path',
        'certificate_path',
        'notes',
    ];

    protected $casts = [
        'performed_date' => 'date',
        'expiration_date' => 'date',
        'status' => ComplianceStatus::class,
        'hours_completed' => 'integer',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function training(): BelongsTo
    {
        return $this->belongsTo(Training::class);
    }
}
