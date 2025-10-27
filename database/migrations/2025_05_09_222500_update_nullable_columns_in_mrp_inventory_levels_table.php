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
        // Primeiro vamos verificar se as colunas existem e modificar apenas se existirem
        if (Schema::hasColumn('mrp_inventory_levels', 'economic_order_quantity')) {
            DB::statement('ALTER TABLE `mrp_inventory_levels` MODIFY `economic_order_quantity` DECIMAL(15, 4) NULL');
        }
        
        if (Schema::hasColumn('mrp_inventory_levels', 'maximum_stock')) {
            DB::statement('ALTER TABLE `mrp_inventory_levels` MODIFY `maximum_stock` DECIMAL(15, 4) NULL');
        }
        
        if (Schema::hasColumn('mrp_inventory_levels', 'daily_usage_rate')) {
            DB::statement('ALTER TABLE `mrp_inventory_levels` MODIFY `daily_usage_rate` DECIMAL(15, 4) NULL');
        }
        
        if (Schema::hasColumn('mrp_inventory_levels', 'abc_classification')) {
            DB::statement('ALTER TABLE `mrp_inventory_levels` MODIFY `abc_classification` VARCHAR(1) NULL');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverter as modificações para NOT NULL se as colunas existirem
        if (Schema::hasColumn('mrp_inventory_levels', 'economic_order_quantity')) {
            DB::statement('ALTER TABLE `mrp_inventory_levels` MODIFY `economic_order_quantity` DECIMAL(15, 4) NOT NULL');
        }
        
        if (Schema::hasColumn('mrp_inventory_levels', 'maximum_stock')) {
            DB::statement('ALTER TABLE `mrp_inventory_levels` MODIFY `maximum_stock` DECIMAL(15, 4) NOT NULL');
        }
        
        if (Schema::hasColumn('mrp_inventory_levels', 'daily_usage_rate')) {
            DB::statement('ALTER TABLE `mrp_inventory_levels` MODIFY `daily_usage_rate` DECIMAL(15, 4) NOT NULL');
        }
        
        if (Schema::hasColumn('mrp_inventory_levels', 'abc_classification')) {
            DB::statement('ALTER TABLE `mrp_inventory_levels` MODIFY `abc_classification` VARCHAR(1) NOT NULL');
        }
    }
};
