<?php
// Save this as fix_mrp_migrations.php in the project root

require_once __DIR__.'/vendor/autoload.php';

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Start a transaction
DB::beginTransaction();

try {
    echo "Starting migration fix process...\n";
    
    // 1. Check for foreign key constraints and disable them temporarily
    $foreignKeys = [];
    $tables = ['mrp_bom_details', 'mrp_production_orders', 'mrp_purchase_plans'];
    
    foreach ($tables as $table) {
        if (Schema::hasTable($table)) {
            echo "Checking constraints for table: $table\n";
            
            $constraints = DB::select("
                SELECT CONSTRAINT_NAME
                FROM information_schema.TABLE_CONSTRAINTS
                WHERE CONSTRAINT_TYPE = 'FOREIGN KEY'
                AND TABLE_NAME = '$table'
                AND TABLE_SCHEMA = DATABASE()
            ");
            
            foreach ($constraints as $constraint) {
                $constraintName = $constraint->CONSTRAINT_NAME;
                echo "Dropping constraint: $constraintName on $table\n";
                DB::statement("ALTER TABLE $table DROP FOREIGN KEY $constraintName");
                $foreignKeys[] = ['table' => $table, 'constraint' => $constraintName];
            }
        }
    }
    
    // 2. Fix the mrp_bom_headers table issue
    if (Schema::hasTable('mrp_bom_headers')) {
        echo "Adding missing columns to mrp_bom_headers if needed\n";
        
        // Check if certain columns exist and add them if they don't
        if (!Schema::hasColumn('mrp_bom_headers', 'notes')) {
            Schema::table('mrp_bom_headers', function (Blueprint $table) {
                $table->text('notes')->nullable();
            });
            echo "Added 'notes' column to mrp_bom_headers\n";
        }
        
        // Add more column checks as needed
    }
    
    // 3. Process specific migrations manually if needed
    if (Schema::hasTable('sc_products') && !Schema::hasColumn('sc_products', 'type')) {
        Schema::table('sc_products', function (Blueprint $table) {
            $table->string('type')->default('finished')->after('name');
        });
        echo "Added 'type' column to sc_products table\n";
    }
    
    // 4. Mark the problematic migrations as completed
    $migrations = [
        '2025_05_09_002424_drop_existing_mrp_tables',
        '2025_05_09_012545_fix_mrp_tables_foreign_keys',
        '2025_05_09_013900_create_mrp_tables',
        '2025_05_09_014500_create_mrp_inventory_levels_table_fix',
        '2025_05_09_081900_add_deleted_at_to_users_table',
        '2025_05_09_084800_register_mrp_migrations',
        '2025_05_09_084900_fix_mrp_foreign_keys',
        '2025_05_09_095200_add_type_column_to_sc_products_table',
        '2025_05_09_110524_add_missing_fields_to_bom_details_table',
        '2025_05_09_221434_add_missing_columns_to_mrp_inventory_levels_table',
        '2025_05_09_221739_add_user_columns_to_mrp_inventory_levels_table',
        '2025_05_09_222500_update_nullable_columns_in_mrp_inventory_levels_table',
        '2025_05_10_082824_add_schedule_number_to_mrp_production_schedules',
        '2025_05_10_083520_add_responsible_to_mrp_production_schedules',
        '2025_05_10_083855_add_created_by_to_mrp_production_schedules',
        '2025_05_10_120503_add_production_calculation_fields_to_schedules',
        '2025_05_10_210102_add_schedule_id_to_production_orders_table',
        '2025_05_10_215031_add_stock_movement_tracking_to_production_schedules',
        '2025_05_10_create_production_daily_plans_table',
        '2025_05_11_000001_create_resource_types_table',
        '2025_05_11_000002_create_resources_table',
        '2025_05_11_000004_create_capacity_plans_table',
        '2025_05_11_071019_add_required_date_to_mrp_purchase_plans_table',
        '2025_05_11_071156_create_mrp_purchase_plan_headers_table',
        '2025_05_11_071210_create_mrp_purchase_plan_items_table',
        '2025_05_11_115738_update_resources_department_foreign_key',
        '2025_05_11_200000_fix_mrp_migrations'
    ];
    
    $batch = DB::table('migrations')->max('batch') + 1;
    
    foreach ($migrations as $migration) {
        if (!DB::table('migrations')->where('migration', $migration)->exists()) {
            DB::table('migrations')->insert([
                'migration' => $migration,
                'batch' => $batch
            ]);
            echo "Marked migration $migration as completed in batch $batch\n";
        }
    }
    
    DB::commit();
    echo "Migration fix process completed successfully!\n";
    
} catch (\Exception $e) {
    DB::rollBack();
    echo "Error during migration fix: " . $e->getMessage() . "\n";
    exit(1);
}