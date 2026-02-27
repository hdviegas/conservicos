<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->decimal('salary_override', 10, 2)->nullable()->after('position_id')
                ->comment('Salário individual; se nulo, herda o base_salary do cargo (position)');
            $table->decimal('gratificacao', 10, 2)->nullable()->default(0)->after('salary_override')
                ->comment('Valor fixo de gratificação mensal do funcionário');
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn(['salary_override', 'gratificacao']);
        });
    }
};
