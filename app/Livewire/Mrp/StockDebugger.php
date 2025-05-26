<?php

namespace App\Livewire\Mrp;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

/**
 * Classe de depuração para operações de estoque
 */
class StockDebugger
{
    /**
     * Registra o início de um processo de estoque
     *
     * @param string $process Nome do processo
     * @param array $data Dados relevantes para o processo
     */
    public static function startProcess($process, array $data = [])
    {
        Log::channel('daily')->info("=== INÍCIO DO PROCESSO: {$process} ===", $data);
    }
    
    /**
     * Registra o fim de um processo de estoque
     *
     * @param string $process Nome do processo
     * @param array $data Dados relevantes para o processo
     */
    public static function endProcess($process, array $data = [])
    {
        Log::channel('daily')->info("=== FIM DO PROCESSO: {$process} ===", $data);
    }
    
    /**
     * Registra informação detalhada sobre um passo do processo
     *
     * @param string $step Nome do passo
     * @param array $data Dados relevantes para o passo
     */
    public static function step($step, array $data = [])
    {
        Log::channel('daily')->info("[PASSO] {$step}", $data);
    }
    
    /**
     * Registra informação sobre uma consulta SQL
     *
     * @param string $query Descrição da consulta
     * @param \Illuminate\Database\Eloquent\Builder $builder Query builder
     */
    public static function query($query, $builder)
    {
        Log::channel('daily')->debug("[SQL] {$query}", [
            'sql' => $builder->toSql(),
            'bindings' => $builder->getBindings()
        ]);
    }
    
    /**
     * Registra informação sobre um objeto do banco de dados
     *
     * @param string $description Descrição do objeto
     * @param mixed $object O objeto a ser registrado
     */
    public static function object($description, $object)
    {
        $data = $object ? (is_object($object) && method_exists($object, 'toArray') ? $object->toArray() : (array) $object) : null;
        Log::channel('daily')->debug("[OBJETO] {$description}", [
            'data' => $data,
            'type' => is_object($object) ? get_class($object) : gettype($object),
            'is_null' => is_null($object)
        ]);
    }
    
    /**
     * Registra um erro
     *
     * @param string $message Mensagem de erro
     * @param \Exception $exception Exceção opcional
     */
    public static function error($message, \Exception $exception = null)
    {
        $data = ['message' => $message];
        
        if ($exception) {
            $data['exception'] = [
                'class' => get_class($exception),
                'message' => $exception->getMessage(),
                'code' => $exception->getCode(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTraceAsString()
            ];
        }
        
        Log::channel('daily')->error("[ERRO] {$message}", $data);
    }
    
    /**
     * Verificar configuração de armazéns
     */
    public static function checkWarehouses()
    {
        try {
            // Obter todos os armazéns
            $allWarehouses = DB::table('sc_inventory_locations')->get();
            
            // Contar armazéns de matéria-prima
            $rawMaterialWarehouses = DB::table('sc_inventory_locations')
                ->where('is_raw_material_warehouse', 1)
                ->where('is_active', 1)
                ->get();
                
            // Contar armazéns de produto acabado
            $finishedGoodsWarehouses = DB::table('sc_inventory_locations')
                ->where('is_finished_goods_warehouse', 1)
                ->where('is_active', 1)
                ->get();
                
            Log::channel('daily')->info("[VERIFICAÇÃO DE ARMAZÉNS]", [
                'total_warehouses' => $allWarehouses->count(),
                'raw_material_warehouses' => $rawMaterialWarehouses->count(),
                'raw_material_warehouse_ids' => $rawMaterialWarehouses->pluck('id')->toArray(),
                'finished_goods_warehouses' => $finishedGoodsWarehouses->count(),
                'finished_goods_warehouse_ids' => $finishedGoodsWarehouses->pluck('id')->toArray(),
            ]);
            
            // Verificar a estrutura da tabela
            $columns = DB::getSchemaBuilder()->getColumnListing('sc_inventory_locations');
            Log::channel('daily')->debug("[ESTRUTURA DE TABELA] sc_inventory_locations", [
                'columns' => $columns
            ]);
            
            return [
                'success' => true,
                'raw_material_count' => $rawMaterialWarehouses->count(),
                'finished_goods_count' => $finishedGoodsWarehouses->count()
            ];
        } catch (\Exception $e) {
            self::error("Erro ao verificar armazéns", $e);
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Verificar transações de inventário
     */
    public static function checkInventoryTransactions($reference_type = null, $reference_id = null)
    {
        try {
            $query = DB::table('sc_inventory_transactions');
            
            if ($reference_type) {
                $query->where('reference_type', $reference_type);
            }
            
            if ($reference_id) {
                $query->where('reference_id', $reference_id);
            }
            
            $transactions = $query->orderBy('id', 'desc')->limit(10)->get();
            
            Log::channel('daily')->info("[VERIFICAÇÃO DE TRANSAÇÕES]", [
                'count' => $transactions->count(),
                'reference_type' => $reference_type,
                'reference_id' => $reference_id,
                'transactions' => $transactions->toArray()
            ]);
            
            // Verificar a estrutura da tabela
            $columns = DB::getSchemaBuilder()->getColumnListing('sc_inventory_transactions');
            Log::channel('daily')->debug("[ESTRUTURA DE TABELA] sc_inventory_transactions", [
                'columns' => $columns
            ]);
            
            return [
                'success' => true,
                'count' => $transactions->count()
            ];
        } catch (\Exception $e) {
            self::error("Erro ao verificar transações", $e);
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}
