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
        Schema::create('mrp_purchase_plans', function (Blueprint $table) {
            $table->id();
            $table->string('plan_number')->unique();
            $table->foreignId('product_id')->constrained('sc_products')->onDelete('cascade');
            $table->foreignId('supplier_id')->nullable()->constrained('sc_suppliers');
            $table->date('planned_date');
            $table->date('required_date');
            $table->decimal('quantity', 10, 3);
            $table->decimal('unit_price', 12, 2)->nullable();
            $table->enum('status', ['planned', 'requested', 'ordered', 'received', 'cancelled'])->default('planned');
            $table->foreignId('purchase_order_id')->nullable()->comment('Link to actual purchase order when created');
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mrp_purchase_plans');
    }
};
