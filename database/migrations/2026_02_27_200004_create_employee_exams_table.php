<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_exams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('exam_id')->constrained()->cascadeOnDelete();
            $table->date('performed_date');
            $table->date('expiration_date')->nullable();
            $table->string('provider', 255)->nullable();
            $table->string('doctor_name', 255)->nullable();
            $table->string('crm', 20)->nullable();
            $table->string('status', 20)->default('valid');
            $table->string('result', 25)->nullable();
            $table->text('restrictions')->nullable();
            $table->string('attachment_path', 500)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('employee_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_exams');
    }
};
