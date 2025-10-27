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
        // Drop tables if they exist
        $mrpTables = [
            'mrp_demand_forecasts',
            'mrp_bom_headers',
            'mrp_bom_details',
            'mrp_inventory_levels',
            'mrp_production_schedules',
            'mrp_production_orders',
            'mrp_purchase_plans',
            'mrp_capacity_plans',
            'mrp_financial_reports'
        ];

        foreach ($mrpTables as $table) {
            // Check if table exists before attempting to drop
            $tableExists = DB::select("SHOW TABLES LIKE '$table'");
            if (count($tableExists) > 0) {
                Schema::dropIfExists($table);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No need to recreate tables in down method
        // The subsequent migrations will create them again
    }
};
