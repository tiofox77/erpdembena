<?php

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../bootstrap/app.php';

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// List of MRP tables to drop
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

echo "Starting to drop MRP tables...\n";

foreach ($mrpTables as $table) {
    if (Schema::hasTable($table)) {
        // Disable foreign key checks to avoid constraint issues
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        
        // Drop the table
        Schema::dropIfExists($table);
        
        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
        
        echo "Dropped table: $table\n";
    } else {
        echo "Table $table does not exist, skipping...\n";
    }
}

// Now remove any migration records related to MRP tables
DB::table('migrations')
    ->where('migration', 'like', '%create_mrp_%')
    ->orWhere('migration', 'like', '%update_mrp_%')
    ->delete();

echo "Removed migration records for MRP tables\n";
echo "Completed. You can now run 'php artisan migrate' to recreate the tables.\n";
