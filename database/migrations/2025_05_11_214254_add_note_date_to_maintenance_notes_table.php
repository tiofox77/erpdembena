<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('maintenance_notes', function (Blueprint $table) {
            // Adicionar campo para data específica da nota
            $table->date('note_date')->nullable()->after('maintenance_plan_id');
            
            // Criar índice para melhorar performance em buscas por data
            $table->index(['maintenance_plan_id', 'note_date'], 'plan_note_date_idx');
        });
        
        // Atualizar registros existentes para usar a data de criação como note_date
        DB::statement('UPDATE maintenance_notes SET note_date = DATE(created_at) WHERE note_date IS NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('maintenance_notes', function (Blueprint $table) {
            // Remover índice
            $table->dropIndex('plan_note_date_idx');
            
            // Remover campo
            $table->dropColumn('note_date');
        });
    }
};
