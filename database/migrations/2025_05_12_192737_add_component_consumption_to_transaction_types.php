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
        // MySQL doesn't support adding a value to an enum directly with Blueprint
        // We need to use a raw SQL statement to modify the column
        DB::statement("ALTER TABLE sc_inventory_transactions MODIFY COLUMN transaction_type ENUM(
            'purchase_receipt', 
            'sales_issue',
            'transfer',
            'adjustment',
            'return_to_supplier',
            'return_from_customer',
            'production_issue',
            'production_receipt',
            'scrap',
            'maintenance_issue',
            'component_consumption'
        ) NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert the enum back to original values, removing 'component_consumption'
        DB::statement("ALTER TABLE sc_inventory_transactions MODIFY COLUMN transaction_type ENUM(
            'purchase_receipt', 
            'sales_issue',
            'transfer',
            'adjustment',
            'return_to_supplier',
            'return_from_customer',
            'production_issue',
            'production_receipt',
            'scrap',
            'maintenance_issue'
        ) NOT NULL");
    }
};
