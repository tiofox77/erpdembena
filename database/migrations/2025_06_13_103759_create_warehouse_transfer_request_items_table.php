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
        Schema::create('sc_warehouse_transfer_request_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('transfer_request_id');
            $table->unsignedBigInteger('product_id');
            $table->decimal('quantity_requested', 15, 5);
            $table->decimal('quantity_approved', 15, 5)->nullable();
            $table->decimal('quantity_transferred', 15, 5)->default(0);
            $table->text('notes')->nullable();
            $table->decimal('unit_cost', 15, 5)->nullable();
            $table->string('status')->default('pending');
            $table->timestamps();
            $table->softDeletes();
            
            // Foreign keys
            $table->foreign('transfer_request_id')
                  ->references('id')
                  ->on('sc_warehouse_transfer_requests')
                  ->onDelete('cascade');
                  
            $table->foreign('product_id')
                  ->references('id')
                  ->on('sc_products')
                  ->onDelete('restrict');
            
            // Indexes with custom short names
            $table->index('status', 'wtri_status_idx');
            $table->index(['transfer_request_id', 'product_id'], 'wtri_request_product_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sc_warehouse_transfer_request_items');
    }
};
