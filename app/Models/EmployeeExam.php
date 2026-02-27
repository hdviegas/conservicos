<?php

namespace App\Models;

use App\Enums\ComplianceStatus;
use App\Enums\ExamResult;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeExam extends Model
{
    protected $fillable = [
        'employee_id',
        'exam_id',
        'performed_date',
        'expiration_date',
        'provider',
        'doctor_name',
        'crm',
        'status',
        'result',
        'restrictions',
        'attachment_path',
        'notes',
    ];

    protected $casts = [
        'performed_date' => 'date',
        'expiration_date' => 'date',
        'status' => ComplianceStatus::class,
        'result' => ExamResult::class,
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }
}
