<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('payroll_batch_items', function (Blueprint $table) {
            $table->decimal('night_shift_allowance', 15, 2)->default(0)->after('overtime_amount');
            $table->integer('night_shift_days')->default(0)->after('night_shift_allowance');
            $table->decimal('position_subsidy', 15, 2)->default(0)->after('family_allowance');
            $table->decimal('performance_subsidy', 15, 2)->default(0)->after('position_subsidy');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payroll_batch_items', function (Blueprint $table) {
            $table->dropColumn(['night_shift_allowance', 'night_shift_days', 'position_subsidy', 'performance_subsidy']);
        });
    }
};
