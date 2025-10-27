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
        Schema::create('stock_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('equipment_part_id')->constrained('equipment_parts')->onDelete('cascade');
            $table->integer('quantity');
            $table->enum('type', ['stock_in', 'stock_out']);
            $table->decimal('unit_cost', 15, 2)->nullable();
            $table->string('supplier')->nullable();
            $table->string('invoice_number')->nullable();
            $table->timestamp('transaction_date');
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('work_order_id')->nullable();
            $table->unsignedBigInteger('maintenance_request_id')->nullable();
            $table->timestamps();
            
            // Índices para melhorar a performance
            $table->index('type');
            $table->index('transaction_date');
            
            // Adicionar chave estrangeira apenas para usuários
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_transactions');
    }
};
