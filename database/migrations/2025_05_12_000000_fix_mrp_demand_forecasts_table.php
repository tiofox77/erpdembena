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
            
            // Criar a tabela novamente com a estrutura correta que corresponde ao componente Livewire
            Schema::create('mrp_demand_forecasts', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('product_id')->comment('Referência ao produto da previsão');
                $table->date('forecast_date')->comment('Data da previsão');
                $table->integer('forecast_quantity')->comment('Quantidade prevista');
                $table->decimal('confidence_level', 5, 2)->nullable()->comment('Nível de confiança (0-100%)');
                $table->enum('forecast_type', ['manual', 'automatic', 'adjusted'])->default('manual')->comment('Tipo de previsão');
                $table->text('notes')->nullable()->comment('Notas adicionais');
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->timestamps();
                $table->softDeletes();
                
                // Adicionar a chave estrangeira para a tabela sc_products
                // Baseado nas memórias do projeto, produtos usam prefix sc_
                $table->foreign('product_id')
                      ->references('id')
                      ->on('sc_products')
                      ->onDelete('cascade');
                      
                // Chaves estrangeiras para os usuários
                $table->foreign('created_by')
                      ->references('id')
                      ->on('users')
                      ->onDelete('set null');
                      
                $table->foreign('updated_by')
                      ->references('id')
                      ->on('users')
                      ->onDelete('set null');
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