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
        // First, fix any incorrect raw_material values if they exist
        DB::statement("UPDATE sc_products SET product_type = 'raw_material' WHERE product_type = 'raw_material'");
        
        // Then, modify the column to ensure it accepts only the correct enum values
        DB::statement("ALTER TABLE sc_products MODIFY COLUMN product_type ENUM('finished_product', 'raw_material') NOT NULL DEFAULT 'finished_product'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No need to revert as this is fixing the existing schema
    }
};
