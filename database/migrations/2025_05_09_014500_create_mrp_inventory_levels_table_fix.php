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
        // Drop the existing table if it exists
        Schema::dropIfExists('mrp_inventory_levels');
        
        // Create mrp_inventory_levels table
        Schema::create('mrp_inventory_levels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('sc_products')->onDelete('cascade');
            $table->foreignId('location_id')->constrained('sc_inventory_locations')->onDelete('cascade');
            $table->integer('reorder_point')->default(0);
            $table->integer('safety_stock')->default(0);
            $table->integer('max_stock_level')->default(0);
            $table->integer('economic_order_quantity')->default(0)->comment('EOQ');
            $table->integer('lead_time_days')->default(0)->comment('Average lead time in days');
            $table->enum('abc_classification', ['A', 'B', 'C'])->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mrp_inventory_levels');
    }
};
