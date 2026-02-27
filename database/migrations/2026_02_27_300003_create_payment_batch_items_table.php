<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_batch_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_batch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 10, 2);
            $table->string('payment_method', 15);
            $table->string('bank_code', 5)->nullable();
            $table->string('agency', 10)->nullable();
            $table->string('agency_digit', 2)->nullable();
            $table->string('account_number', 15)->nullable();
            $table->string('account_digit', 2)->nullable();
            $table->string('account_type', 10)->nullable();
            $table->string('pix_key', 255)->nullable();
            $table->string('status', 15)->default('pending');
            $table->string('rejection_reason', 255)->nullable();
            $table->unsignedBigInteger('reference_id')->nullable()->index();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('payment_batch_id');
            $table->index('employee_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_batch_items');
    }
};
