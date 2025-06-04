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
        Schema::table('equipment_part_requests', function (Blueprint $table) {
            // Eliminar colunas que agora estão na tabela de itens
            $table->dropForeign(['equipment_part_id']);
            $table->dropColumn([
                'equipment_part_id',
                'quantity_required',
                'unit',
                'supplier_reference'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('equipment_part_requests', function (Blueprint $table) {
            $table->foreignId('equipment_part_id')->nullable()->constrained('equipment_parts');
            $table->integer('quantity_required')->default(1);
            $table->string('unit', 50)->default('pcs');
            $table->string('supplier_reference')->nullable();
        });
    }
};
