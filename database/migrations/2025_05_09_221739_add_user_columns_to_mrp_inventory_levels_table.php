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
        Schema::table('mrp_inventory_levels', function (Blueprint $table) {
            // Adicionar colunas para rastreamento de usuários
            if (!Schema::hasColumn('mrp_inventory_levels', 'created_by')) {
                $table->foreignId('created_by')->nullable()->constrained('users');
            }
            
            if (!Schema::hasColumn('mrp_inventory_levels', 'updated_by')) {
                $table->foreignId('updated_by')->nullable()->constrained('users');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mrp_inventory_levels', function (Blueprint $table) {
            // Remover chaves estrangeiras primeiro
            if (Schema::hasColumn('mrp_inventory_levels', 'created_by')) {
                $table->dropForeign(['created_by']);
                $table->dropColumn('created_by');
            }
            
            if (Schema::hasColumn('mrp_inventory_levels', 'updated_by')) {
                $table->dropForeign(['updated_by']);
                $table->dropColumn('updated_by');
            }
        });
    }
};
