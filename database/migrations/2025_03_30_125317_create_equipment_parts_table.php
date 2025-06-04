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
        Schema::create('equipment_parts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('part_number')->nullable();
            $table->text('description')->nullable();
            $table->integer('stock_quantity')->default(0);
            $table->decimal('unit_cost', 10, 2)->nullable();
            $table->date('last_restock_date')->nullable();
            $table->integer('minimum_stock_level')->default(1);
            $table->foreignId('maintenance_equipment_id')
                ->constrained('maintenance_equipment')
                ->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('equipment_parts');
    }
};
