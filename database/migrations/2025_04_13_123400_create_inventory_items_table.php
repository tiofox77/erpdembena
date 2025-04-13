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
        Schema::create('sc_inventory_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->foreign('product_id')->references('id')->on('sc_products')->onDelete('cascade');
            $table->unsignedBigInteger('location_id');
            $table->foreign('location_id')->references('id')->on('sc_inventory_locations')->onDelete('cascade');
            $table->decimal('quantity_on_hand', 12, 2)->default(0);
            $table->decimal('quantity_allocated', 12, 2)->default(0);
            $table->decimal('quantity_available', 12, 2)->default(0);
            $table->string('bin_location')->nullable();
            $table->string('batch_number')->nullable();
            $table->date('expiry_date')->nullable();
            $table->string('serial_number')->nullable();
            $table->string('status')->default('available');
            $table->decimal('unit_cost', 12, 2)->nullable();
            $table->timestamps();
            
            // Composite unique index to prevent duplicate product-location combinations
            $table->unique(['product_id', 'location_id', 'batch_number', 'serial_number'], 'sc_inventory_item_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sc_inventory_items');
    }
};
