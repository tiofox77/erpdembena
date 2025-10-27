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
        Schema::create('sc_goods_receipt_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('goods_receipt_id');
            $table->foreign('goods_receipt_id')->references('id')->on('sc_goods_receipts')->onDelete('cascade');
            $table->unsignedBigInteger('purchase_order_item_id')->nullable();
            $table->foreign('purchase_order_item_id')->references('id')->on('sc_purchase_order_items')->onDelete('set null');
            $table->unsignedBigInteger('product_id');
            $table->foreign('product_id')->references('id')->on('sc_products')->onDelete('cascade');
            $table->decimal('expected_quantity', 12, 2)->nullable();
            $table->decimal('received_quantity', 12, 2);
            $table->decimal('accepted_quantity', 12, 2);
            $table->decimal('rejected_quantity', 12, 2)->default(0);
            $table->string('rejection_reason')->nullable();
            $table->string('batch_number')->nullable();
            $table->date('expiry_date')->nullable();
            $table->string('serial_numbers')->nullable();
            $table->enum('status', ['accepted', 'partially_accepted', 'rejected', 'pending_qa'])->default('accepted');
            $table->decimal('unit_cost', 12, 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sc_goods_receipt_items');
    }
};
