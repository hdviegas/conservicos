<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transport_vouchers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('transport_voucher_type_id')->constrained()->cascadeOnDelete();
            $table->date('period_start');
            $table->date('period_end');
            $table->integer('worked_days')->default(0);
            $table->decimal('daily_value', 10, 2);
            $table->decimal('total_value', 10, 2);
            $table->timestamp('generated_at')->useCurrent();
            $table->string('status', 20)->default('pending');
            $table->timestamps();

            $table->index('employee_id');
            $table->index('transport_voucher_type_id');
            $table->index('status');
            $table->unique(['employee_id', 'period_start', 'period_end']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transport_vouchers');
    }
};
