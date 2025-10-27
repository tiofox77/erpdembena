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
        Schema::table('mrp_inventory_levels', function (Blueprint $table) {
            // Verificar e adicionar as colunas faltantes se ainda não existirem
            if (!Schema::hasColumn('mrp_inventory_levels', 'maximum_stock')) {
                $table->decimal('maximum_stock', 15, 4)->nullable()->after('reorder_point');
            }
            
            if (!Schema::hasColumn('mrp_inventory_levels', 'economic_order_quantity')) {
                $table->decimal('economic_order_quantity', 15, 4)->nullable()->after('maximum_stock');
            }
            
            if (!Schema::hasColumn('mrp_inventory_levels', 'daily_usage_rate')) {
                $table->decimal('daily_usage_rate', 15, 4)->nullable()->after('lead_time_days');
            }
            
            if (!Schema::hasColumn('mrp_inventory_levels', 'abc_classification')) {
                $table->string('abc_classification', 1)->nullable()->after('daily_usage_rate');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mrp_inventory_levels', function (Blueprint $table) {
            // Remover as colunas adicionadas se existirem
            $columns = ['maximum_stock', 'economic_order_quantity', 'daily_usage_rate', 'abc_classification'];
            
            foreach ($columns as $column) {
                if (Schema::hasColumn('mrp_inventory_levels', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
