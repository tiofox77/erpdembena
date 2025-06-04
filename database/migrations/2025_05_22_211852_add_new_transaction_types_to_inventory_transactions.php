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
        // Verificação da estrutura atual e dos dados existentes
        $existingTypes = DB::select("SHOW COLUMNS FROM sc_inventory_transactions WHERE Field = 'transaction_type'");
        if (!empty($existingTypes)) {
            $typeInfo = $existingTypes[0]->Type;
            
            // Verificar a estrutura do ENUM atual
            if (preg_match("/^enum\((.*)\)$/i", $typeInfo, $matches)) {
                $values = str_getcsv($matches[1], ',', "'");
                
                // Verificar se os novos valores já estão no ENUM
                $needsRawProduction = !in_array('raw_production', $values);
                $needsProductionOrder = !in_array('production_order', $values);
                
                // Se precisamos adicionar novos valores
                if ($needsRawProduction || $needsProductionOrder) {
                    // Adicionar novos valores ao ENUM existente
                    $newValues = $values;
                    if ($needsRawProduction) $newValues[] = 'raw_production';
                    if ($needsProductionOrder) $newValues[] = 'production_order';
                    
                    // Construir a nova definição do ENUM
                    $enumDefinition = "'" . implode("','",$newValues) . "'";
                    
                    // Alterar a coluna com a nova definição do ENUM
                    DB::statement("ALTER TABLE sc_inventory_transactions MODIFY COLUMN transaction_type ENUM($enumDefinition);");
                    
                    DB::table('sc_inventory_transactions')
                        ->where('transaction_type', 'component_consumption')
                        ->update(['transaction_type' => 'production_issue']);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Verificar se existem registros com os novos tipos
        $hasRawProduction = DB::table('sc_inventory_transactions')
            ->where('transaction_type', 'raw_production')
            ->exists();
            
        $hasProductionOrder = DB::table('sc_inventory_transactions')
            ->where('transaction_type', 'production_order')
            ->exists();
            
        // Se existem registros, convertê-los para um tipo compatível antes de remover do ENUM
        if ($hasRawProduction) {
            DB::table('sc_inventory_transactions')
                ->where('transaction_type', 'raw_production')
                ->update(['transaction_type' => 'production_issue']);
        }
        
        if ($hasProductionOrder) {
            DB::table('sc_inventory_transactions')
                ->where('transaction_type', 'production_order')
                ->update(['transaction_type' => 'production_receipt']);
        }
        
        // Obter a estrutura atual do ENUM
        $existingTypes = DB::select("SHOW COLUMNS FROM sc_inventory_transactions WHERE Field = 'transaction_type'");
        if (!empty($existingTypes)) {
            $typeInfo = $existingTypes[0]->Type;
            
            if (preg_match("/^enum\((.*)\)$/i", $typeInfo, $matches)) {
                $values = str_getcsv($matches[1], ',', "'");
                
                // Remover os novos valores
                $values = array_diff($values, ['raw_production', 'production_order']);
                
                // Construir a nova definição do ENUM
                $enumDefinition = "'" . implode("','",$values) . "'";
                
                // Alterar a coluna com a nova definição do ENUM
                DB::statement("ALTER TABLE sc_inventory_transactions MODIFY COLUMN transaction_type ENUM($enumDefinition);");
            }
        }
    }
};
