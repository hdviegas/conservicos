<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trainings', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->text('description')->nullable();
            $table->string('category', 20);
            $table->string('nr_reference', 10)->nullable();
            $table->integer('validity_months')->default(0);
            $table->integer('required_hours')->nullable();
            $table->boolean('is_mandatory')->default(false);
            $table->boolean('requires_certificate')->default(false);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trainings');
    }
};
