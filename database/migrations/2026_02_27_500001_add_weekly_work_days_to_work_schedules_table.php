<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('work_schedules', function (Blueprint $table) {
            // How many days per week the employee works.
            // Regular: 5 (Mon–Fri) or 6 (Mon–Sat).
            // Null for 12x36 and 6x2 (handled by type-specific logic).
            $table->tinyInteger('weekly_work_days')->unsigned()->nullable()->after('type');
        });

        // Default regular schedules to 6 days/week (Mon–Sat) — most common pattern
        // for the logistics operations at SERVICON/LARA. Admins can override per schedule.
        DB::table('work_schedules')
            ->where('type', 'regular')
            ->update(['weekly_work_days' => 6]);
    }

    public function down(): void
    {
        Schema::table('work_schedules', function (Blueprint $table) {
            $table->dropColumn('weekly_work_days');
        });
    }
};
