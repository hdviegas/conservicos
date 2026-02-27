<?php

namespace App\Models;

use App\Enums\AccountType;
use App\Enums\PixKeyType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'department_id',
        'position_id',
        'work_schedule_id',
        'transport_voucher_type_id',
        'name',
        'social_name',
        'cpf',
        'pis',
        'rg',
        'birth_date',
        'gender',
        'marital_status',
        'nationality',
        'naturality',
        'father_name',
        'mother_name',
        'blood_type',
        'phone',
        'emergency_phone',
        'email',
        'address',
        'neighborhood',
        'city',
        'state',
        'zip_code',
        'matricula',
        'folha',
        'ctps',
        'admission_date',
        'termination_date',
        'termination_reason',
        'cnh',
        'cnh_category',
        'cnh_expiration',
        'bank_code',
        'bank_name',
        'agency',
        'agency_digit',
        'account_number',
        'account_digit',
        'account_type',
        'pix_key_type',
        'pix_key',
        'hours_bank_start_date',
        'external_id',
        'active',
        'observations',
        'salary_override',
        'gratificacao',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'admission_date' => 'date',
        'termination_date' => 'date',
        'cnh_expiration' => 'date',
        'hours_bank_start_date' => 'date',
        'active' => 'boolean',
        'account_type' => AccountType::class,
        'pix_key_type' => PixKeyType::class,
        'salary_override' => 'decimal:2',
        'gratificacao' => 'decimal:2',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    public function workSchedule(): BelongsTo
    {
        return $this->belongsTo(WorkSchedule::class);
    }

    public function transportVoucherType(): BelongsTo
    {
        return $this->belongsTo(TransportVoucherType::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('active', true)->whereNull('termination_date');
    }

    public function scopeTerminated(Builder $query): Builder
    {
        return $query->whereNotNull('termination_date');
    }

    public function scopeByCompany(Builder $query, int $companyId): Builder
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeByDepartment(Builder $query, int $departmentId): Builder
    {
        return $query->where('department_id', $departmentId);
    }

    public function timeRecords(): HasMany
    {
        return $this->hasMany(TimeRecord::class);
    }

    public function transportVouchers(): HasMany
    {
        return $this->hasMany(TransportVoucher::class);
    }

    public function vacations(): HasMany
    {
        return $this->hasMany(Vacation::class);
    }

    public function employeeExams(): HasMany
    {
        return $this->hasMany(EmployeeExam::class);
    }

    public function employeeTrainings(): HasMany
    {
        return $this->hasMany(EmployeeTraining::class);
    }

    public function paymentBatchItems(): HasMany
    {
        return $this->hasMany(PaymentBatchItem::class);
    }

    public function employeeDocuments(): HasMany
    {
        return $this->hasMany(EmployeeDocument::class);
    }
}
