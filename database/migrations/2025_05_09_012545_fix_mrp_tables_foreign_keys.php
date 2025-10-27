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
        // Fix mrp_demand_forecasts foreign keys
        if (Schema::hasTable('mrp_demand_forecasts')) {
            Schema::table('mrp_demand_forecasts', function (Blueprint $table) {
                // Drop the existing foreign key constraint if it exists
                $this->dropForeignKeyIfExists('mrp_demand_forecasts', 'mrp_demand_forecasts_product_id_foreign');
                
                // Add the corrected foreign key
                $table->foreign('product_id')
                      ->references('id')
                      ->on('sc_products')
                      ->onDelete('cascade');
            });
        }

        // Fix mrp_bom_headers foreign keys
        if (Schema::hasTable('mrp_bom_headers')) {
            Schema::table('mrp_bom_headers', function (Blueprint $table) {
                $this->dropForeignKeyIfExists('mrp_bom_headers', 'mrp_bom_headers_product_id_foreign');
                
                $table->foreign('product_id')
                      ->references('id')
                      ->on('sc_products')
                      ->onDelete('cascade');
            });
        }

        // Fix mrp_bom_details foreign keys
        if (Schema::hasTable('mrp_bom_details')) {
            Schema::table('mrp_bom_details', function (Blueprint $table) {
                $this->dropForeignKeyIfExists('mrp_bom_details', 'mrp_bom_details_component_id_foreign');
                
                $table->foreign('component_id')
                      ->references('id')
                      ->on('sc_products')
                      ->onDelete('cascade');
            });
        }

        // Fix mrp_inventory_levels foreign keys
        if (Schema::hasTable('mrp_inventory_levels')) {
            Schema::table('mrp_inventory_levels', function (Blueprint $table) {
                $this->dropForeignKeyIfExists('mrp_inventory_levels', 'mrp_inventory_levels_product_id_foreign');
                $this->dropForeignKeyIfExists('mrp_inventory_levels', 'mrp_inventory_levels_location_id_foreign');
                
                $table->foreign('product_id')
                      ->references('id')
                      ->on('sc_products')
                      ->onDelete('cascade');
                
                $table->foreign('location_id')
                      ->references('id')
                      ->on('sc_inventory_locations')
                      ->onDelete('cascade');
            });
        }

        // Fix mrp_production_schedules foreign keys
        if (Schema::hasTable('mrp_production_schedules')) {
            Schema::table('mrp_production_schedules', function (Blueprint $table) {
                $this->dropForeignKeyIfExists('mrp_production_schedules', 'mrp_production_schedules_product_id_foreign');
                $this->dropForeignKeyIfExists('mrp_production_schedules', 'mrp_production_schedules_location_id_foreign');
                
                $table->foreign('product_id')
                      ->references('id')
                      ->on('sc_products')
                      ->onDelete('cascade');
                
                if (Schema::hasColumn('mrp_production_schedules', 'location_id')) {
                    $table->foreign('location_id')
                          ->references('id')
                          ->on('sc_inventory_locations')
                          ->onDelete('cascade');
                }
            });
        }

        // Fix mrp_production_orders foreign keys
        if (Schema::hasTable('mrp_production_orders')) {
            Schema::table('mrp_production_orders', function (Blueprint $table) {
                $this->dropForeignKeyIfExists('mrp_production_orders', 'mrp_production_orders_product_id_foreign');
                $this->dropForeignKeyIfExists('mrp_production_orders', 'mrp_production_orders_location_id_foreign');
                
                $table->foreign('product_id')
                      ->references('id')
                      ->on('sc_products')
                      ->onDelete('cascade');
                
                if (Schema::hasColumn('mrp_production_orders', 'location_id')) {
                    $table->foreign('location_id')
                          ->references('id')
                          ->on('sc_inventory_locations')
                          ->onDelete('cascade');
                }
            });
        }

        // Fix mrp_purchase_plans foreign keys
        if (Schema::hasTable('mrp_purchase_plans')) {
            Schema::table('mrp_purchase_plans', function (Blueprint $table) {
                $this->dropForeignKeyIfExists('mrp_purchase_plans', 'mrp_purchase_plans_product_id_foreign');
                $this->dropForeignKeyIfExists('mrp_purchase_plans', 'mrp_purchase_plans_supplier_id_foreign');
                
                $table->foreign('product_id')
                      ->references('id')
                      ->on('sc_products')
                      ->onDelete('cascade');
                
                $table->foreign('supplier_id')
                      ->references('id')
                      ->on('sc_suppliers')
                      ->onDelete('cascade');
            });
        }

        // Fix mrp_capacity_plans foreign keys
        if (Schema::hasTable('mrp_capacity_plans')) {
            Schema::table('mrp_capacity_plans', function (Blueprint $table) {
                $this->dropForeignKeyIfExists('mrp_capacity_plans', 'mrp_capacity_plans_work_center_id_foreign');
                
                if (Schema::hasColumn('mrp_capacity_plans', 'work_center_id')) {
                    $table->foreign('work_center_id')
                          ->references('id')
                          ->on('sc_work_centers')
                          ->onDelete('cascade');
                }
            });
        }

        // Fix mrp_financial_reports foreign keys for users
        if (Schema::hasTable('mrp_financial_reports')) {
            Schema::table('mrp_financial_reports', function (Blueprint $table) {
                $this->dropForeignKeyIfExists('mrp_financial_reports', 'mrp_financial_reports_created_by_foreign');
                $this->dropForeignKeyIfExists('mrp_financial_reports', 'mrp_financial_reports_approved_by_foreign');
                
                if (Schema::hasColumn('mrp_financial_reports', 'created_by')) {
                    $table->foreign('created_by')
                          ->references('id')
                          ->on('users')
                          ->onDelete('set null');
                }
                
                if (Schema::hasColumn('mrp_financial_reports', 'approved_by')) {
                    $table->foreign('approved_by')
                          ->references('id')
                          ->on('users')
                          ->onDelete('set null');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No need to restore the original foreign keys as they were incorrect
    }
    
    /**
     * Helper method to drop a foreign key if it exists
     */
    private function dropForeignKeyIfExists($table, $foreignKey)
    {
        // Check if the foreign key exists before trying to drop it
        $sm = Schema::getConnection()->getDoctrineSchemaManager();
        $foreignKeys = $sm->listTableForeignKeys($table);
        
        foreach ($foreignKeys as $key) {
            if ($key->getName() === $foreignKey) {
                Schema::table($table, function (Blueprint $table) use ($foreignKey) {
                    $table->dropForeign($foreignKey);
                });
                break;
            }
        }
    }
};
