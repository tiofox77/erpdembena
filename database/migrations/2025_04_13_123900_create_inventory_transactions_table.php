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
        Schema::create('sc_inventory_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_number')->unique();
            $table->enum('transaction_type', [
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
            ]);
            $table->unsignedBigInteger('product_id');
            $table->foreign('product_id')->references('id')->on('sc_products')->onDelete('cascade');
            $table->unsignedBigInteger('source_location_id')->nullable();
            $table->foreign('source_location_id')->references('id')->on('sc_inventory_locations')->onDelete('set null');
            $table->unsignedBigInteger('destination_location_id')->nullable();
            $table->foreign('destination_location_id')->references('id')->on('sc_inventory_locations')->onDelete('set null');
            $table->decimal('quantity', 12, 2);
            $table->decimal('unit_cost', 12, 2)->nullable();
            $table->decimal('total_cost', 12, 2)->nullable();
            $table->string('batch_number')->nullable();
            $table->date('expiry_date')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable(); // Could be PO ID, SO ID, etc.
            $table->string('reference_type')->nullable(); // Model name for polymorphic relation
            $table->unsignedBigInteger('created_by');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
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
        Schema::dropIfExists('sc_inventory_transactions');
    }
};
