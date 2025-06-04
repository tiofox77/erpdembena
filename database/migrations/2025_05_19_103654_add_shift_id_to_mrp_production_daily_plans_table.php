<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Executa as migrações.
     *
     * Adiciona a coluna shift_id à tabela de planos diários de produção
     * seguindo o padrão de nomenclatura do módulo MRP
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('mrp_production_daily_plans', function (Blueprint $table) {
            // Adiciona a coluna shift_id após a coluna schedule_id
            $table->foreignId('shift_id')
                ->after('schedule_id')
                ->nullable()
                ->constrained('mrp_shifts')
                ->onDelete('set null');
            
            // Adiciona índice para melhorar o desempenho nas consultas
            $table->index('shift_id', 'mrp_daily_plan_shift_idx');
        });
    }

    /**
     * Reverte as migrações.
     *
     * Remove a coluna shift_id da tabela de planos diários de produção
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('mrp_production_daily_plans', function (Blueprint $table) {
            // Remove a chave estrangeira primeiro
            $table->dropForeign(['shift_id']);
            
            // Remove o índice
            $table->dropIndex('mrp_daily_plan_shift_idx');
            
            // Remove a coluna
            $table->dropColumn('shift_id');
        });
    }
};
