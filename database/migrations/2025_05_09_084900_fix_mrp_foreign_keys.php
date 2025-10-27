<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Este migration corrige as referências de chave estrangeira nas tabelas do MRP,
     * apenas se as colunas e tabelas existirem, de forma não destrutiva.
     */
    public function up(): void
    {
        // Array de mapeamento de correções de chave estrangeira
        $foreignKeyFixes = [
            // Formato: 'tabela' => [['coluna', 'tabela_referencia'], ...] 
            'mrp_demand_forecasts' => [
                ['product_id', 'sc_products']
            ],
            'mrp_bom_headers' => [
                ['product_id', 'sc_products']
            ],
            'mrp_bom_details' => [
                ['component_id', 'sc_products']
            ],
            'mrp_inventory_levels' => [
                ['product_id', 'sc_products'],
                ['location_id', 'sc_inventory_locations']
            ],
            'mrp_production_schedules' => [
                ['product_id', 'sc_products'],
                ['location_id', 'sc_inventory_locations']
            ],
            'mrp_production_orders' => [
                ['product_id', 'sc_products'],
                ['location_id', 'sc_inventory_locations']
            ],
            'mrp_purchase_plans' => [
                ['product_id', 'sc_products'],
                ['supplier_id', 'sc_suppliers']
            ],
            'mrp_capacity_plans' => [
                ['work_center_id', 'sc_work_centers']
            ],
            'mrp_financial_reports' => [
                ['created_by', 'users'],
                ['approved_by', 'users']
            ]
        ];

        // Para cada tabela que precisa ser corrigida
        foreach ($foreignKeyFixes as $table => $columns) {
            // Verifica se a tabela existe antes de tentar corrigi-la
            if (Schema::hasTable($table)) {
                foreach ($columns as $columnInfo) {
                    $column = $columnInfo[0];
                    $referenceTable = $columnInfo[1];
                    
                    // Verifica se a coluna existe na tabela
                    if (Schema::hasColumn($table, $column)) {
                        // Nome padrão da chave estrangeira
                        $fkName = "{$table}_{$column}_foreign";
                        
                        // Verifica se a tabela de referência existe
                        if (Schema::hasTable($referenceTable)) {
                            Schema::table($table, function (Blueprint $table) use ($column, $referenceTable, $fkName) {
                                // Tenta remover a chave estrangeira existente, se houver
                                try {
                                    $sm = Schema::getConnection()->getDoctrineSchemaManager();
                                    $foreignKeys = $sm->listTableForeignKeys($table->getTable());
                                    
                                    foreach ($foreignKeys as $key) {
                                        if ($key->getName() === $fkName) {
                                            $table->dropForeign($fkName);
                                            break;
                                        }
                                    }
                                } catch (\Exception $e) {
                                    // Ignora erros se a chave não existir
                                }
                                
                                // Adiciona a chave estrangeira correta
                                try {
                                    $table->foreign($column)
                                          ->references('id')
                                          ->on($referenceTable)
                                          ->onDelete('cascade');
                                } catch (\Exception $e) {
                                    // Log ou registra o erro, mas não falha a migration
                                    DB::table('migration_errors')->insert([
                                        'migration' => '2025_05_09_084900_fix_mrp_foreign_keys',
                                        'table' => $table->getTable(),
                                        'column' => $column,
                                        'error' => $e->getMessage(),
                                        'created_at' => now()
                                    ]);
                                }
                            });
                        }
                    }
                }
            }
        }
        
        // Cria uma tabela para registrar erros de migração, se não existir
        if (!Schema::hasTable('migration_errors')) {
            Schema::create('migration_errors', function (Blueprint $table) {
                $table->id();
                $table->string('migration');
                $table->string('table');
                $table->string('column');
                $table->text('error');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     * Não fazemos nada no down para garantir segurança da base de dados
     */
    public function down(): void
    {
        // Não desfazemos as correções, pois é uma operação potencialmente destrutiva
    }
};
