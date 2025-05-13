<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // We need to use DB facade for direct SQL because Schema builder might have
        // limitations when changing column types
        DB::statement('ALTER TABLE mrp_production_daily_plans MODIFY planned_quantity DECIMAL(10,2) NOT NULL DEFAULT 0.00');
        DB::statement('ALTER TABLE mrp_production_daily_plans MODIFY actual_quantity DECIMAL(10,2) NOT NULL DEFAULT 0.00');
        DB::statement('ALTER TABLE mrp_production_daily_plans MODIFY defect_quantity DECIMAL(10,2) NOT NULL DEFAULT 0.00');
        
        // Also update the ProductionSchedule precision to ensure consistency
        DB::statement('ALTER TABLE mrp_production_schedules MODIFY planned_quantity DECIMAL(10,2) NOT NULL DEFAULT 0.00');
        DB::statement('ALTER TABLE mrp_production_schedules MODIFY actual_quantity DECIMAL(10,2) NOT NULL DEFAULT 0.00');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // We're not providing a rollback for this migration since it's a data fix
        // Going back to 3 decimal places would cause data loss/truncation
    }
};
