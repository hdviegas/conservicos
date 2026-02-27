<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('time_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('time_import_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->time('entry_1')->nullable();
            $table->time('exit_1')->nullable();
            $table->time('entry_2')->nullable();
            $table->time('exit_2')->nullable();
            $table->integer('total_normal_hours')->default(0);
            $table->integer('total_night_hours')->default(0);
            $table->string('day_type', 20);
            $table->boolean('is_absence_day')->default(false);
            $table->boolean('is_worked_day')->default(false);
            $table->integer('absence_hours')->default(0);
            $table->integer('bonus_hours')->default(0);
            $table->integer('overtime_50')->default(0);
            $table->integer('overtime_100')->default(0);
            $table->integer('negative_bank_hours')->default(0);
            $table->text('justification')->nullable();
            $table->string('holiday_name', 100)->nullable();
            $table->string('raw_entry_1', 100)->nullable();
            $table->string('raw_exit_1', 100)->nullable();
            $table->string('raw_entry_2', 100)->nullable();
            $table->string('raw_exit_2', 100)->nullable();
            $table->timestamps();

            $table->unique(['employee_id', 'date']);
            $table->index('employee_id');
            $table->index('date');
            $table->index('time_import_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('time_records');
    }
};
