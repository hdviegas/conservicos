<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('compliance_position_requirements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('position_id')->constrained()->cascadeOnDelete();
            $table->string('requireable_type', 255);
            $table->unsignedBigInteger('requireable_id');
            $table->boolean('is_mandatory')->default(true);
            $table->timestamps();

            $table->index(['requireable_type', 'requireable_id'], 'cpr_requireable_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('compliance_position_requirements');
    }
};
