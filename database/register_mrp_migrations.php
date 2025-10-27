<?php

// This script inserts MRP migration entries into the migrations table
// Run with: php database/register_mrp_migrations.php

$basePath = __DIR__ . '/..';
require $basePath . '/vendor/autoload.php';

$app = require_once $basePath . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

// List of MRP migrations to register
$migrations = [
    '2025_05_08_000001_create_mrp_demand_forecasts_table',
    '2025_05_08_000002_create_mrp_bom_headers_table',
    '2025_05_08_000003_create_mrp_bom_details_table',
    '2025_05_08_000004_create_mrp_inventory_levels_table',
    '2025_05_08_000005_create_mrp_production_schedules_table',
    '2025_05_08_000006_create_mrp_production_orders_table',
    '2025_05_08_000007_create_mrp_purchase_plans_table',
    '2025_05_08_000008_create_mrp_capacity_plans_table',
    '2025_05_08_000009_create_mrp_financial_reports_table'
];

// Record each migration as completed
foreach ($migrations as $migration) {
    // Check if this migration is already in the database
    $exists = DB::table('migrations')->where('migration', $migration)->exists();
    
    if (!$exists) {
        // Insert the migration as already completed
        DB::table('migrations')->insert([
            'migration' => $migration,
            'batch' => DB::table('migrations')->max('batch') + 1
        ]);
        echo "Registered migration: $migration\n";
    } else {
        echo "Migration already registered: $migration\n";
    }
}

echo "All MRP migrations have been registered. You can now run the fix migration.\n";
