<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('type', 25);
            $table->tinyInteger('reference_month');
            $table->smallInteger('reference_year');
            $table->date('payment_date');
            $table->string('bank_code', 5);
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->integer('total_records')->default(0);
            $table->string('status', 20)->default('draft');
            $table->string('cnab_format', 10);
            $table->string('file_path', 500)->nullable();
            $table->string('return_file_path', 500)->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('generated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('generated_at')->nullable();
            $table->timestamps();

            $table->index('company_id');
            $table->index(['company_id', 'reference_month', 'reference_year']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_batches');
    }
};
