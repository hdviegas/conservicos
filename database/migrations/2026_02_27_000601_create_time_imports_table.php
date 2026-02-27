<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('time_imports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('filename', 255);
            $table->string('original_filename', 255);
            $table->tinyInteger('period_month');
            $table->smallInteger('period_year');
            $table->string('type', 20);
            $table->integer('records_count')->default(0);
            $table->string('status', 20)->default('pending');
            $table->json('errors')->nullable();
            $table->timestamp('imported_at')->nullable();
            $table->timestamps();

            $table->index('company_id');
            $table->index('status');
            $table->index(['period_year', 'period_month']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('time_imports');
    }
};
