<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->string('type', 50);
            $table->string('name', 255);
            $table->string('path', 500);
            $table->string('mime_type', 100)->nullable();
            $table->unsignedBigInteger('size')->nullable()->comment('File size in bytes');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('employee_id');
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_documents');
    }
};
