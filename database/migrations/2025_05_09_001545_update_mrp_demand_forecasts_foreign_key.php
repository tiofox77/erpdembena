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
        // Verifica se a tabela existe antes de tentar alterá-la
        if (Schema::hasTable('mrp_demand_forecasts')) {
            Schema::table('mrp_demand_forecasts', function (Blueprint $table) {
                // Remover a chave estrangeira existente, se existir
                try {
                    $table->dropForeign(['product_id']);
                } catch (\Exception $e) {
                    // Ignorar se a chave não existir
                }
                
                // Adicionar nova chave estrangeira referenciando a tabela correta
                // Verificar se a tabela de referência existe
                if (Schema::hasTable('sc_products')) {
                    $table->foreign('product_id')
                          ->references('id')
                          ->on('sc_products')
                          ->onDelete('cascade');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mrp_demand_forecasts', function (Blueprint $table) {
            try {
                // Remover a chave estrangeira adicionada
                $table->dropForeign(['product_id']);
                
                // Recriar a chave original se necessário
                $table->foreign('product_id')
                      ->references('id')
                      ->on('products')
                      ->onDelete('cascade');
            } catch (\Exception $e) {
                // Ignorar erros ao desfazer
            }
        });
    }
};
