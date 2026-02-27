<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('position_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('work_schedule_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name', 255);
            $table->string('social_name', 255)->nullable();
            $table->string('cpf', 14)->unique();
            $table->string('pis', 15)->nullable();
            $table->string('rg', 20)->nullable();
            $table->date('birth_date')->nullable();
            $table->string('gender', 20)->nullable();
            $table->string('marital_status', 30)->nullable();
            $table->string('nationality', 50)->nullable();
            $table->string('naturality', 100)->nullable();
            $table->string('father_name', 255)->nullable();
            $table->string('mother_name', 255)->nullable();
            $table->string('blood_type', 5)->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('emergency_phone', 20)->nullable();
            $table->string('email', 255)->nullable();
            $table->string('address', 500)->nullable();
            $table->string('neighborhood', 100)->nullable();
            $table->string('city', 100)->nullable();
            $table->char('state', 2)->nullable();
            $table->string('zip_code', 10)->nullable();
            $table->string('matricula', 20)->nullable();
            $table->string('folha', 20)->nullable();
            $table->string('ctps', 30)->nullable();
            $table->date('admission_date')->nullable();
            $table->date('termination_date')->nullable();
            $table->text('termination_reason')->nullable();
            $table->string('cnh', 20)->nullable();
            $table->string('cnh_category', 5)->nullable();
            $table->date('cnh_expiration')->nullable();
            $table->string('bank_code', 5)->nullable();
            $table->string('bank_name', 100)->nullable();
            $table->string('agency', 10)->nullable();
            $table->string('agency_digit', 2)->nullable();
            $table->string('account_number', 15)->nullable();
            $table->string('account_digit', 2)->nullable();
            $table->string('account_type', 10)->nullable();
            $table->string('pix_key_type', 10)->nullable();
            $table->string('pix_key', 255)->nullable();
            $table->date('hours_bank_start_date')->nullable();
            $table->string('external_id', 20)->nullable()->index();
            $table->boolean('active')->default(true);
            $table->text('observations')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index('company_id');
            $table->index('department_id');
            $table->index('position_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
