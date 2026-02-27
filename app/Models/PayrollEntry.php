<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayrollEntry extends Model
{
    protected $fillable = [
        'payroll_period_id',
        'employee_id',
        'total_worked_days',
        'total_absence_days',
        'total_justified_absence_days',
        'total_sundays',
        'total_holidays',
        'total_folgas',
        'total_normal_hours',
        'total_night_hours',
        'overtime_50_hours',
        'overtime_50_value',
        'overtime_100_hours',
        'overtime_100_value',
        'night_differential_hours',
        'night_differential_value',
        'dsr_base_value',
        'dsr_discount_value',
        'dsr_final_value',
        'transport_voucher_days',
        'transport_voucher_daily_value',
        'transport_voucher_total',
        'hours_bank_balance',
        'hours_bank_credit',
        'hours_bank_debit',
        'base_salary',
        'gross_additions',
        'gross_deductions',
        'vacation_notes',
        'inss_notes',
        'termination_notes',
        'observations',
    ];

    protected $casts = [
        'base_salary'                  => 'decimal:2',
        'overtime_50_value'            => 'decimal:2',
        'overtime_100_value'           => 'decimal:2',
        'night_differential_value'     => 'decimal:2',
        'dsr_base_value'               => 'decimal:2',
        'dsr_discount_value'           => 'decimal:2',
        'dsr_final_value'              => 'decimal:2',
        'transport_voucher_daily_value' => 'decimal:2',
        'transport_voucher_total'      => 'decimal:2',
        'gross_additions'              => 'decimal:2',
        'gross_deductions'             => 'decimal:2',
    ];

    public function payrollPeriod(): BelongsTo
    {
        return $this->belongsTo(PayrollPeriod::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function getGrossTotalAttribute(): float
    {
        return (float) $this->base_salary + (float) $this->gross_additions;
    }

    public static function minutesToHours(int $minutes): string
    {
        return sprintf('%02d:%02d', intdiv($minutes, 60), $minutes % 60);
    }
}
