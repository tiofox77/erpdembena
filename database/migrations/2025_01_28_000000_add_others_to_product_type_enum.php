<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddOthersToProductTypeEnum extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add 'others' to the product_type enum
        DB::statement("ALTER TABLE sc_products MODIFY COLUMN product_type ENUM('finished_product', 'raw_material', 'others') NOT NULL DEFAULT 'finished_product'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove 'others' from the product_type enum
        // First, update any records with 'others' to 'finished_product' to avoid constraint violation
        DB::statement("UPDATE sc_products SET product_type = 'finished_product' WHERE product_type = 'others'");
        
        // Then modify the column back to the original enum values  
        DB::statement("ALTER TABLE sc_products MODIFY COLUMN product_type ENUM('finished_product', 'raw_material') NOT NULL DEFAULT 'finished_product'");
    }
}
