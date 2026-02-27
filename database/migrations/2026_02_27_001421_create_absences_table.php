<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('absences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->string('type', 30);
            $table->boolean('justified')->default(false);
            $table->text('justification_text')->nullable();
            $table->string('attachment_path', 500)->nullable();
            $table->string('cid_code', 10)->nullable();
            $table->integer('days_count')->default(1);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('employee_id');
            $table->index('date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('absences');
    }
};
