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
        Schema::create('mrp_purchase_plan_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_plan_id')->constrained('mrp_purchase_plan_headers')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('sc_products')->onDelete('cascade');
            $table->decimal('quantity', 10, 3);
            $table->string('unit_of_measure')->nullable();
            $table->decimal('unit_price', 12, 2)->nullable();
            $table->decimal('total_price', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mrp_purchase_plan_items');
    }
};
