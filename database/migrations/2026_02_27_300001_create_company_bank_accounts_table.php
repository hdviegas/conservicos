<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_bank_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('bank_code', 5);
            $table->string('bank_name', 100);
            $table->string('agency', 10);
            $table->string('agency_digit', 2)->nullable();
            $table->string('account_number', 15);
            $table->string('account_digit', 2)->nullable();
            $table->string('account_type', 10);
            $table->string('covenant_code', 20)->nullable();
            $table->boolean('is_default')->default(false);
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->index('company_id');
            $table->index('bank_code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_bank_accounts');
    }
};
