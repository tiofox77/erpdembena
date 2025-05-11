<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migration.
     *
     * @return void
     */
    public function up()
    {
        // Desativar verificação de chaves estrangeiras temporariamente
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        try {
            // Verificar se a tabela existe, se existir mas estiver com problemas, dropamos
            if (Schema::hasTable('mrp_demand_forecasts')) {
                Schema::drop('mrp_demand_forecasts');
            }
            
            // Criar a tabela novamente com a estrutura correta
            Schema::create('mrp_demand_forecasts', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('product_id')->comment('Referência ao produto da previsão');
                $table->integer('year')->comment('Ano da previsão');
                $table->integer('month')->comment('Mês da previsão (1-12)');
                $table->integer('quantity')->comment('Quantidade prevista');
                $table->text('notes')->nullable()->comment('Notas adicionais');
                $table->timestamps();
                $table->softDeletes();
                
                // Adicionar a chave estrangeira para a tabela sc_products
                // Baseado nas memórias do projeto, produtos usam prefix sc_
                $table->foreign('product_id')
                      ->references('id')
                      ->on('sc_products')
                      ->onDelete('cascade');
            });
            
            // Garantir que a migration original seja marcada como executada
            $migrations = [
                '2025_05_08_000001_create_mrp_demand_forecasts_table',
                '2025_05_09_001545_update_mrp_demand_forecasts_foreign_key'
            ];
            
            $batch = DB::table('migrations')->max('batch');
            
            foreach ($migrations as $migration) {
                if (!DB::table('migrations')->where('migration', $migration)->exists()) {
                    DB::table('migrations')->insert([
                        'migration' => $migration,
                        'batch' => $batch
                    ]);
                }
            }
            
        } finally {
            // Reativar verificação de chaves estrangeiras
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Nada a fazer, pois esta é uma migração de correção
    }
};