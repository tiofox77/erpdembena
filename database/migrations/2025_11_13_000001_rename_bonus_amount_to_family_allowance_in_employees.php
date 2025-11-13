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
        Schema::table('employees', function (Blueprint $table) {
            $table->renameColumn('bonus_amount', 'family_allowance');
        });
        
        // Atualizar o comentário
        DB::statement("ALTER TABLE employees MODIFY COLUMN family_allowance DECIMAL(12,2) NULL DEFAULT 0 COMMENT 'Ajuda Familiar - Subsídio mensal'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->renameColumn('family_allowance', 'bonus_amount');
        });
        
        // Restaurar o comentário original
        DB::statement("ALTER TABLE employees MODIFY COLUMN bonus_amount DECIMAL(12,2) NULL DEFAULT 0 COMMENT 'Bônus opcional mensal'");
    }
};
