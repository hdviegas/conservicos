<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transport_vouchers', function (Blueprint $table) {
            // Remove old columns
            $table->dropColumn(['month', 'year', 'total_days', 'notes']);
        });

        Schema::table('transport_vouchers', function (Blueprint $table) {
            // Add new columns
            $table->foreignId('transport_voucher_type_id')
                ->after('employee_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->date('period_start')->after('transport_voucher_type_id');
            $table->date('period_end')->after('period_start');
            $table->integer('worked_days')->default(0)->after('period_end');
            $table->decimal('daily_value', 10, 2)->change();
            $table->timestamp('generated_at')->useCurrent()->after('total_value');
            $table->string('status', 20)->default('pending')->after('generated_at');

            $table->index('transport_voucher_type_id');
            $table->index('status');
            $table->unique(['employee_id', 'period_start', 'period_end']);
        });
    }

    public function down(): void
    {
        Schema::table('transport_vouchers', function (Blueprint $table) {
            $table->dropUnique(['employee_id', 'period_start', 'period_end']);
            $table->dropIndex(['status']);
            $table->dropForeignIdFor(\App\Models\TransportVoucherType::class);
            $table->dropIndex(['transport_voucher_type_id']);
            $table->dropColumn([
                'transport_voucher_type_id',
                'period_start',
                'period_end',
                'worked_days',
                'generated_at',
                'status',
            ]);
        });

        Schema::table('transport_vouchers', function (Blueprint $table) {
            $table->tinyInteger('month')->unsigned()->after('employee_id');
            $table->smallInteger('year')->unsigned()->after('month');
            $table->integer('total_days')->default(0)->after('daily_value');
            $table->text('notes')->nullable()->after('total_value');
        });
    }
};
