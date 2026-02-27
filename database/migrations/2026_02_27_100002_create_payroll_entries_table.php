<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payroll_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_period_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();

            // Dias
            $table->integer('total_worked_days')->default(0);
            $table->integer('total_absence_days')->default(0);
            $table->integer('total_justified_absence_days')->default(0);
            $table->integer('total_sundays')->default(0);
            $table->integer('total_holidays')->default(0);
            $table->integer('total_folgas')->default(0);

            // Horas (em minutos)
            $table->integer('total_normal_hours')->default(0);
            $table->integer('total_night_hours')->default(0);

            // Horas extras
            $table->integer('overtime_50_hours')->default(0);
            $table->decimal('overtime_50_value', 10, 2)->default(0);
            $table->integer('overtime_100_hours')->default(0);
            $table->decimal('overtime_100_value', 10, 2)->default(0);

            // Adicional noturno
            $table->integer('night_differential_hours')->default(0);
            $table->decimal('night_differential_value', 10, 2)->default(0);

            // DSR
            $table->decimal('dsr_base_value', 10, 2)->default(0);
            $table->decimal('dsr_discount_value', 10, 2)->default(0);
            $table->decimal('dsr_final_value', 10, 2)->default(0);

            // Vale Transporte
            $table->integer('transport_voucher_days')->default(0);
            $table->decimal('transport_voucher_daily_value', 8, 2)->default(0);
            $table->decimal('transport_voucher_total', 10, 2)->default(0);

            // Banco de Horas
            $table->integer('hours_bank_balance')->default(0);
            $table->integer('hours_bank_credit')->default(0);
            $table->integer('hours_bank_debit')->default(0);

            // Totais financeiros
            $table->decimal('base_salary', 10, 2)->default(0);
            $table->decimal('gross_additions', 10, 2)->default(0);
            $table->decimal('gross_deductions', 10, 2)->default(0);

            // Observações
            $table->text('vacation_notes')->nullable();
            $table->text('inss_notes')->nullable();
            $table->text('termination_notes')->nullable();
            $table->text('observations')->nullable();

            $table->timestamps();

            $table->unique(['payroll_period_id', 'employee_id']);
            $table->index('employee_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_entries');
    }
};
