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
        Schema::create('mrp_inventory_levels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('sc_products')->onDelete('cascade');
            $table->foreignId('location_id')->nullable()->constrained('sc_inventory_locations');
            $table->decimal('safety_stock', 10, 3)->default(0)->comment('Nível mínimo de segurança');
            $table->decimal('reorder_point', 10, 3)->default(0)->comment('Ponto de reabastecimento');
            $table->decimal('maximum_stock', 10, 3)->nullable()->comment('Nível máximo de estoque');
            $table->decimal('economic_order_quantity', 10, 3)->nullable()->comment('Quantidade econômica de pedido');
            $table->integer('lead_time_days')->default(0)->comment('Tempo de reposição em dias');
            $table->decimal('daily_usage_rate', 10, 3)->nullable()->comment('Taxa de uso diário médio');
            $table->enum('abc_classification', ['A', 'B', 'C'])->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->timestamps();
            $table->softDeletes();
            
            // Índice composto para garantir apenas um registro por produto e localização
            $table->unique(['product_id', 'location_id']);
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
