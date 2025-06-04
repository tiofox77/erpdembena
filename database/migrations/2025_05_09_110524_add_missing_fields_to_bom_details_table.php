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
        Schema::table('mrp_bom_details', function (Blueprint $table) {
            // Verificar e adicionar campo level se não existir
            if (!Schema::hasColumn('mrp_bom_details', 'level')) {
                $table->integer('level')->default(1)->comment('Nível do componente na estrutura da BOM')->after('uom');
            }
            
            // Verificar e adicionar campo position se não existir
            if (!Schema::hasColumn('mrp_bom_details', 'position')) {
                $table->integer('position')->nullable()->comment('Posição do componente na estrutura da BOM')->after('level');
            }
            
            // Verificar e adicionar campo scrap_percentage se não existir
            if (!Schema::hasColumn('mrp_bom_details', 'scrap_percentage')) {
                $table->decimal('scrap_percentage', 5, 2)->default(0)->comment('Percentual de perda do componente')->after('position');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mrp_bom_details', function (Blueprint $table) {
            // As colunas só serão removidas se existirem
            if (Schema::hasColumn('mrp_bom_details', 'level')) {
                $table->dropColumn('level');
            }
            
            if (Schema::hasColumn('mrp_bom_details', 'position')) {
                $table->dropColumn('position');
            }
            
            if (Schema::hasColumn('mrp_bom_details', 'scrap_percentage')) {
                $table->dropColumn('scrap_percentage');
            }
        });
    }
};
