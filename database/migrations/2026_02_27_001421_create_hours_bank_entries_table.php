<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hours_bank_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->string('type', 10);
            $table->integer('minutes');
            $table->string('source', 25);
            $table->text('description')->nullable();
            $table->foreignId('reference_time_record_id')
                ->nullable()
                ->constrained('time_records')
                ->nullOnDelete();
            $table->timestamps();

            $table->index('employee_id');
            $table->index('date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hours_bank_entries');
    }
};
