<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_trainings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('training_id')->constrained()->cascadeOnDelete();
            $table->date('performed_date');
            $table->date('expiration_date')->nullable();
            $table->string('instructor_name', 255)->nullable();
            $table->string('institution', 255)->nullable();
            $table->integer('hours_completed')->nullable();
            $table->string('status', 20)->default('valid');
            $table->string('grade', 10)->nullable();
            $table->string('attachment_path', 500)->nullable();
            $table->string('certificate_path', 500)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('employee_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_trainings');
    }
};
