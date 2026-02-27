<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transport_voucher_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->string('description', 500)->nullable();
            $table->decimal('daily_value', 10, 2);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transport_voucher_types');
    }
};
