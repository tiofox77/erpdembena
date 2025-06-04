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
        Schema::create('sc_products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('sku')->unique();
            $table->unsignedBigInteger('category_id')->nullable();
            $table->foreign('category_id')->references('id')->on('sc_product_categories')->onDelete('set null');
            $table->text('description')->nullable();
            $table->decimal('unit_price', 12, 2)->default(0);
            $table->decimal('cost_price', 12, 2)->default(0);
            $table->string('unit_of_measure')->default('unit');
            $table->string('barcode')->nullable();
            $table->string('image')->nullable();
            $table->integer('min_stock_level')->default(0);
            $table->integer('reorder_point')->default(0);
            $table->integer('lead_time_days')->default(0);
            $table->boolean('is_stockable')->default(true);
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('primary_supplier_id')->nullable();
            $table->foreign('primary_supplier_id')->references('id')->on('sc_suppliers')->onDelete('set null');
            $table->enum('tax_type', ['standard', 'reduced', 'exempt'])->default('standard');
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->string('location')->nullable(); // Storage location
            $table->decimal('weight', 8, 3)->nullable();
            $table->decimal('width', 8, 3)->nullable();
            $table->decimal('height', 8, 3)->nullable();
            $table->decimal('depth', 8, 3)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sc_products');
    }
};
