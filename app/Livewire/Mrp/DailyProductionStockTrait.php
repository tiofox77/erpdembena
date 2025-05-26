<?php

namespace App\Livewire\Mrp;

use Illuminate\Support\Facades\Log;
use App\Livewire\Mrp\StockDebugger;
use App\Models\Mrp\BomHeader;
use App\Models\Mrp\BomDetail;
use App\Models\Mrp\ProductionDailyPlan;
use App\Models\SupplyChain\InventoryItem;
use App\Models\SupplyChain\InventoryTransaction;
use App\Models\SupplyChain\InventoryLocation;
use App\Models\SupplyChain\Product;
use Illuminate\Support\Facades\DB;

/**
 * Trait para gerenciar o estoque relacionado aos planos diários de produção
 * Responsável por consumir matérias-primas e adicionar produtos acabados ao estoque
 */
trait DailyProductionStockTrait
{
    /**
     * Processa o consumo de matérias-primas para um plano diário de produção
     * Consome materiais apenas para a diferença de quantidade produzida
     * Só processa quando o status é 'completed'
     * 
     * @param ProductionDailyPlan $plan Plano diário atualizado
     * @param float $previousQuantity Quantidade anterior produzida
     * @param string $previousStatus Status anterior do plano
     * @return array Resultado do processamento
     */
    private function processMaterialConsumption(ProductionDailyPlan $plan, float $previousQuantity = 0, string $previousStatus = '')
    {
        try {
            // Usar o StockDebugger para registrar o início do processo
            StockDebugger::startProcess('PROCESS_MATERIAL_CONSUMPTION', [
                'plan_id' => $plan->id,
                'schedule_id' => $plan->schedule_id,
                'previous_quantity' => $previousQuantity,
                'new_quantity' => $plan->actual_quantity,
                'status' => $plan->status,
                'previous_status' => $previousStatus,
                'date_time' => date('Y-m-d H:i:s')
            ]);
            
            // Verificar configuração de armazéns no sistema
            StockDebugger::step('Verificando configuração de armazéns');
            $warehouseCheck = StockDebugger::checkWarehouses();
            
            // Verificar transações existentes para este plano
            StockDebugger::step('Verificando transações existentes para este plano');
            $transactionCheck = StockDebugger::checkInventoryTransactions('production_daily_plan', $plan->id);
            
            Log::info('=== INÍCIO DO MÉTODO PROCESS MATERIAL CONSUMPTION (DAILY PLAN) ===', [
                'plan_id' => $plan->id,
                'schedule_id' => $plan->schedule_id,
                'previous_quantity' => $previousQuantity,
                'new_quantity' => $plan->actual_quantity,
                'status' => $plan->status,
                'previous_status' => $previousStatus
            ]);
            
            // Verificar se o status atual é 'completed' - só processa neste caso
            if ($plan->status !== 'completed') {
                Log::info('Consumo de material ignorado: status não é "completed"', [
                    'plan_id' => $plan->id,
                    'current_status' => $plan->status
                ]);
                return [
                    'success' => true,
                    'message' => 'Plano não está com status "completed"',
                    'components_processed' => 0
                ];
            }
            
            Log::info('Processando consumo de materiais para plano com status "completed"', [
                'plan_id' => $plan->id,
                'current_status' => $plan->status
            ]);
            
            // Calcular a quantidade total para consumo de materiais (quantidade produzida + quantidade com defeitos)
            $totalQuantity = $plan->actual_quantity + ($plan->defect_quantity ?? 0);
            $previousTotal = $previousQuantity; // Assumimos que o previousQuantity já inclui defeitos anteriores
            
            // Calcular a diferença de quantidade (apenas processar se houver aumento)
            $quantityDifference = $totalQuantity - $previousTotal;
            
            Log::info('Cálculo de quantidade para consumo de materiais', [
                'plan_id' => $plan->id,
                'actual_quantity' => $plan->actual_quantity,
                'defect_quantity' => $plan->defect_quantity ?? 0,
                'total_quantity' => $totalQuantity,
                'previous_total' => $previousTotal,
                'quantity_difference' => $quantityDifference
            ]);
            
            if ($quantityDifference <= 0) {
                Log::info('Nenhum consumo de material necessário (sem aumento de quantidade total)', [
                    'plan_id' => $plan->id,
                    'quantity_difference' => $quantityDifference
                ]);
                return [
                    'success' => true,
                    'message' => 'Nenhum consumo de material necessário',
                    'components_processed' => 0
                ];
            }
            
            // Buscar o agendamento relacionado ao plano
            $schedule = $plan->schedule;
            
            if (!$schedule) {
                throw new \Exception('Agendamento não encontrado para o plano diário');
            }
            
            // Buscar o BOM (Lista de Materiais) para o produto
            $bomHeader = BomHeader::where('product_id', $schedule->product_id)
                ->where('status', 'active')
                ->orderBy('version', 'desc')
                ->first();
            
            if (!$bomHeader) {
                Log::warning('Nenhuma BOM ativa encontrada para o produto', [
                    'product_id' => $schedule->product_id,
                    'plan_id' => $plan->id
                ]);
                return [
                    'success' => false,
                    'message' => 'BOM não encontrada ou não ativa',
                    'components_processed' => 0
                ];
            }
            
            // Buscar todos os componentes da BOM
            $components = BomDetail::where('bom_header_id', $bomHeader->id)->with('component')->get();
            
            if ($components->isEmpty()) {
                Log::warning('BOM não possui componentes', [
                    'bom_header_id' => $bomHeader->id,
                    'product_id' => $schedule->product_id
                ]);
                return [
                    'success' => false,
                    'message' => 'BOM não possui componentes',
                    'components_processed' => 0
                ];
            }

            // Buscar o armazém configurado para matéria-prima no schedule
            // Se não estiver configurado, usar armazéns ativos
            $rawMaterialWarehouses = collect();
            
            // Se temos um armazém de matéria-prima configurado no schedule, usar este
            if ($schedule->raw_material_warehouse_id) {
                // Verificar se o armazém configurado é ativo e é de matéria-prima
                $configuredWarehouse = InventoryLocation::where('id', $schedule->raw_material_warehouse_id)
                    ->where('is_active', 1)
                    ->where('is_raw_material_warehouse', 1)
                    ->first();
                    
                if ($configuredWarehouse) {
                    $rawMaterialWarehouses->push($configuredWarehouse);
                    
                    Log::debug('Usando armazém configurado no production schedule:', [
                        'warehouse_id' => $configuredWarehouse->id,
                        'warehouse_name' => $configuredWarehouse->name
                    ]);
                }
            }
            
            // Verificar se temos algum armazém para usar
            if ($rawMaterialWarehouses->isEmpty()) {
                // Como alternativa, usar armazéns ativos de matéria-prima
                Log::warning('Nenhum armazém de matéria-prima configurado no schedule, buscando armazéns ativos marcados como matéria-prima', [
                    'plan_id' => $plan->id,
                    'schedule_id' => $schedule->id,
                    'warehouse_id_from_schedule' => $schedule->raw_material_warehouse_id,
                    'warehouse_exists' => $schedule->raw_material_warehouse_id ? InventoryLocation::where('id', $schedule->raw_material_warehouse_id)->exists() : false,
                    'warehouse_is_active' => $schedule->raw_material_warehouse_id ? InventoryLocation::where('id', $schedule->raw_material_warehouse_id)->where('is_active', 1)->exists() : false,
                    'warehouse_is_raw_material' => $schedule->raw_material_warehouse_id ? InventoryLocation::where('id', $schedule->raw_material_warehouse_id)->where('is_raw_material_warehouse', 1)->exists() : false
                ]);
                
                // Buscar apenas armazéns ativos marcados como armazéns de matéria-prima
                $activeWarehouses = InventoryLocation::where('is_active', 1)
                    ->where('is_raw_material_warehouse', 1)
                    ->get();
                
                if ($activeWarehouses->isEmpty()) {
                    Log::error('Nenhum armazém de matéria-prima ativo encontrado no sistema', [
                        'plan_id' => $plan->id,
                        'total_locations' => InventoryLocation::count(),
                        'total_raw_material_warehouses' => InventoryLocation::where('is_raw_material_warehouse', 1)->count()
                    ]);
                    
                    return [
                        'success' => false,
                        'message' => 'Não existem armazéns de matéria-prima ativos configurados no sistema',
                        'components_processed' => 0,
                        'components_with_issues' => []
                    ];
                }
                
                // Usar o primeiro armazém ativo como fonte para matéria-prima
                $defaultSourceWarehouse = $activeWarehouses->first();
                $rawMaterialWarehouses = $activeWarehouses;
                
                Log::warning('Usando armazém ativo como fonte para matéria-prima (fallback)', [
                    'warehouse_id' => $defaultSourceWarehouse->id,
                    'warehouse_name' => $defaultSourceWarehouse->name
                ]);
            } else {
                // Selecionar o primeiro armazém configurado
                $defaultSourceWarehouse = $rawMaterialWarehouses->first();
                
                Log::debug('Usando armazém configurado:', [
                    'warehouse_id' => $defaultSourceWarehouse->id,
                    'warehouse_name' => $defaultSourceWarehouse->name
                ]);
            }

            // Exibir os armazéns encontrados
            Log::debug('Armazéns de matéria-prima encontrados:', [
                'count' => $rawMaterialWarehouses->count(),
                'warehouses' => $rawMaterialWarehouses->toArray()
            ]);
            
            Log::info('Processando componentes da BOM para plano diário', [
                'bom_header_id' => $bomHeader->id,
                'total_components' => $components->count(),
                'plan_id' => $plan->id,
                'quantity_difference' => $quantityDifference,
                'raw_material_warehouses' => $rawMaterialWarehouses->pluck('name', 'id')->toArray()
            ]);
            
            $componentsWithIssues = [];
            $componentsProcessed = 0;
            
            // Para cada componente da BOM
            foreach ($components as $component) {
                // Calcular a quantidade necessária baseada na produção real
                $requiredQuantity = $component->quantity * $quantityDifference;
                $remainingQuantity = $requiredQuantity;
                $componentName = $component->component ? $component->component->name : "Componente ID: {$component->component_id}";
                
                // Registrar informações iniciais
                Log::info("Iniciando processamento de componente para plano diário", [
                    'component_id' => $component->component_id,
                    'component_name' => $componentName,
                    'required_quantity' => $requiredQuantity,
                    'plan_id' => $plan->id
                ]);
                
                // Verificar estoque em cada armazém de matéria-prima
                foreach ($rawMaterialWarehouses as $warehouse) {
                    // Se já consumimos toda a quantidade necessária, podemos sair do loop
                    if ($remainingQuantity <= 0) {
                        break;
                    }
                    
                    // Buscar o item de estoque do componente neste armazém
                    $inventoryItem = InventoryItem::firstOrNew([
                        'product_id' => $component->component_id,
                        'location_id' => $warehouse->id
                    ]);
                    
                    // Registrar a quantidade atual antes da atualização
                    $currentStock = $inventoryItem->quantity_on_hand ?? 0;
                    
                    // Determinar quanto vamos consumir deste armazém
                    $quantityToDeduct = min($currentStock, $remainingQuantity);
                    
                    // Se não houver estoque neste armazém, continuar para o próximo
                    if ($currentStock <= 0) {
                        Log::info("Armazém sem estoque deste componente", [
                            'warehouse_id' => $warehouse->id,
                            'warehouse_name' => $warehouse->name,
                            'component_id' => $component->component_id,
                            'plan_id' => $plan->id
                        ]);
                        continue;
                    }
                    
                    Log::info("Processando consumo de componente do armazém de matéria-prima", [
                        'warehouse_id' => $warehouse->id,
                        'warehouse_name' => $warehouse->name,
                        'component_id' => $component->component_id,
                        'current_stock' => $currentStock,
                        'quantity_to_deduct' => $quantityToDeduct,
                        'remaining_quantity' => $remainingQuantity,
                        'plan_id' => $plan->id
                    ]);
                    
                    // Descontar a quantidade do estoque
                    $inventoryItem->quantity_on_hand = $currentStock - $quantityToDeduct;
                    $inventoryItem->quantity_available = $inventoryItem->quantity_on_hand - ($inventoryItem->quantity_allocated ?? 0);
                    $inventoryItem->save();
                    
                    // Atualizar a quantidade restante
                    $remainingQuantity -= $quantityToDeduct;
                    
                    // Registrar a movimentação de estoque para o componente
                    try {
                        $transaction = new InventoryTransaction();
                        
                        // Gerar um número de transação único
                        $prefix = 'TRX-DAILY-' . date('Ymd');
                        $lastTransaction = InventoryTransaction::where('transaction_number', 'LIKE', $prefix . '-%')
                            ->orderBy('id', 'desc')
                            ->first();
                        
                        // Depuração - verificar a consulta de busca da última transação
                        Log::debug('SQL para buscar última transação:', [
                            'sql' => InventoryTransaction::where('transaction_number', 'LIKE', $prefix . '-%')->orderBy('id', 'desc')->toSql(),
                            'bindings' => InventoryTransaction::where('transaction_number', 'LIKE', $prefix . '-%')->orderBy('id', 'desc')->getBindings(),
                            'table' => (new InventoryTransaction)->getTable(),
                            'prefix' => $prefix
                        ]);
                            
                        if ($lastTransaction) {
                            $lastNumber = intval(substr($lastTransaction->transaction_number, strlen($prefix) + 1));
                            $newNumber = $lastNumber + 1;
                            Log::debug('Encontrou transação anterior:', [
                                'last_transaction_id' => $lastTransaction->id,
                                'last_transaction_number' => $lastTransaction->transaction_number,
                                'last_number' => $lastNumber,
                                'new_number' => $newNumber
                            ]);
                        } else {
                            $newNumber = 1;
                            Log::debug('Nenhuma transação anterior encontrada. Começando com número 1.');
                        }
                        $transaction->transaction_number = $prefix . '-' . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
                        
                        // Configurar a transação para o componente com o tipo DAILY_PRODUCTION
                        $transaction->product_id = $component->component_id;
                        $transaction->source_location_id = $warehouse->id;
                        $transaction->destination_location_id = null; // Componente consumido, não tem destino
                        $transaction->reference_type = 'production_daily_plan';
                        $transaction->reference_id = $plan->id;
                        $transaction->quantity = $quantityToDeduct;
                        $transaction->transaction_type = 'daily_production'; // Tipo específico para consumo em plano diário
                        $transaction->notes = "Matéria-prima consumida para plano diário: {$plan->id}";
                        $transaction->created_by = auth()->id() ?? 1; // Garante que sempre tenha um ID de usuário
                        
                        // Definir campos que podem ser obrigatórios
                        // Buscar custo unitário do componente ou usar valor padrão
                        $componentProduct = Product::find($component->component_id);
                        $transaction->unit_cost = $componentProduct ? ($componentProduct->cost_price ?: 0) : 0;
                        $transaction->total_cost = $transaction->quantity * $transaction->unit_cost;
                        
                        // Verificar se está faltando algum campo obrigatório
                        $requiredFields = ['product_id', 'quantity', 'transaction_number', 'transaction_type'];
                        $missingFields = [];
                        foreach ($requiredFields as $field) {
                            if (empty($transaction->$field)) {
                                $missingFields[] = $field;
                            }
                        }
                        if (!empty($missingFields)) {
                            Log::warning('Campos obrigatórios não preenchidos na transação de matéria-prima:', [
                                'missing_fields' => $missingFields,
                                'transaction_data' => $transaction->toArray()
                            ]);
                        }
                        
                        // Depuração - Mostrar dados da transação antes de salvar
                        Log::debug('Tentativa de salvar transação de estoque:', [
                            'transaction_data' => $transaction->toArray()
                        ]);
                        
                        $saveResult = $transaction->save();
                        
                        // Verificar se a transação foi realmente salva no banco
                        $savedTransaction = InventoryTransaction::where('transaction_number', $transaction->transaction_number)->first();
                        Log::warning('Verificação de persistência da transação de matéria-prima:', [
                            'transaction_number' => $transaction->transaction_number,
                            'save_result' => $saveResult,
                            'encontrada' => $savedTransaction ? 'Sim' : 'Não',
                            'id_se_encontrada' => $savedTransaction ? $savedTransaction->id : null,
                            'exists_flag' => $transaction->exists
                        ]);
                        
                        Log::debug('Transação de estoque salva com sucesso:', [
                            'transaction_id' => $transaction->id,
                            'transaction_number' => $transaction->transaction_number
                        ]);
                    } catch (\Exception $e) {
                        Log::error('ERRO ao salvar transação de estoque para componente:', [
                            'component_id' => $component->component_id,
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString()
                        ]);
                    }
                    
                    Log::info("Componente deduzido do armazém para plano diário", [
                        'warehouse_id' => $warehouse->id,
                        'warehouse_name' => $warehouse->name,
                        'component_id' => $component->component_id,
                        'component_name' => $componentName,
                        'quantity_deducted' => $quantityToDeduct,
                        'prev_stock' => $currentStock,
                        'new_stock' => $inventoryItem->quantity_on_hand,
                        'plan_id' => $plan->id
                    ]);
                    
                    $componentsProcessed++;
                }
                
                // Se ainda temos quantidade restante, precisamos criar um registro negativo
                if ($remainingQuantity > 0) {
                    Log::warning("Estoque insuficiente de matéria-prima nos armazéns. Criando estoque negativo.", [
                        'component_id' => $component->component_id,
                        'component_name' => $componentName,
                        'remaining_quantity' => $remainingQuantity,
                        'plan_id' => $plan->id
                    ]);
                    
                    // Selecionar o primeiro armazém de matéria-prima para criar o estoque negativo
                    $defaultWarehouse = $rawMaterialWarehouses->first();
                    
                    // Buscar o item de estoque do componente neste armazém
                    $inventoryItem = InventoryItem::firstOrNew([
                        'product_id' => $component->component_id,
                        'location_id' => $defaultWarehouse->id
                    ]);
                    
                    // Registrar a quantidade atual antes da atualização
                    $currentStock = $inventoryItem->quantity_on_hand ?? 0;
                    
                    // Descontar a quantidade restante do estoque (vai ficar negativo)
                    $inventoryItem->quantity_on_hand = $currentStock - $remainingQuantity;
                    $inventoryItem->quantity_available = $inventoryItem->quantity_on_hand - ($inventoryItem->quantity_allocated ?? 0);
                    $inventoryItem->save();
                    
                    // Registrar a movimentação de estoque para o componente
                    $transaction = new InventoryTransaction();
                    
                    // Gerar um número de transação único
                    $prefix = 'TRX-DAILY-NEG-' . date('Ymd');
                    $lastTransaction = InventoryTransaction::where('transaction_number', 'LIKE', $prefix . '-%')
                        ->orderBy('id', 'desc')
                        ->first();
                        
                    if ($lastTransaction) {
                        $lastNumber = intval(substr($lastTransaction->transaction_number, strlen($prefix) + 1));
                        $newNumber = $lastNumber + 1;
                    } else {
                        $newNumber = 1;
                    }
                    $transaction->transaction_number = $prefix . '-' . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
                    
                    // Configurar a transação para o componente
                    $transaction->product_id = $component->component_id;
                    $transaction->source_location_id = $defaultWarehouse->id;
                    $transaction->destination_location_id = null;
                    $transaction->reference_type = 'production_daily_plan';
                    $transaction->reference_id = $plan->id;
                    $transaction->quantity = $remainingQuantity;
                    $transaction->transaction_type = 'daily_production';
                    $transaction->notes = "Matéria-prima consumida para plano diário (estoque negativo): {$plan->id}";
                    $transaction->created_by = auth()->id();
                    $transaction->save();
                    
                    // Registrar componentes que ficaram com estoque negativo
                    $componentsWithIssues[] = [
                        'component_id' => $component->component_id,
                        'component_name' => $componentName,
                        'required' => $requiredQuantity,
                        'consumed' => $requiredQuantity - $remainingQuantity,
                        'shortage' => $remainingQuantity,
                        'warehouse_id' => $defaultWarehouse->id,
                        'warehouse_name' => $defaultWarehouse->name,
                        'plan_id' => $plan->id
                    ];
                    
                    $componentsProcessed++;
                }
            }
            
            // Registrar os componentes com problemas de estoque no log
            if (!empty($componentsWithIssues)) {
                Log::warning('Componentes com estoque negativo após dedução para plano diário', [
                    'plan_id' => $plan->id,
                    'components_with_issues' => $componentsWithIssues
                ]);
            }
            
            // Verificar se as transações foram realmente criadas
            StockDebugger::step('Verificando se as transações foram criadas');
            $finalTransactionCheck = StockDebugger::checkInventoryTransactions('production_daily_plan', $plan->id);
            
            // Realiza verificação direta no banco de dados
            try {
                $query = DB::table('sc_inventory_transactions');
                $query->where('reference_type', 'production_daily_plan');
                $query->where('reference_id', $plan->id);
                $query->where('transaction_type', 'daily_production');
                $rawCount = $query->count();
                    
                StockDebugger::step('Verificação direta de transações de matéria-prima', [
                    'count' => $rawCount,
                    'plan_id' => $plan->id
                ]);
            } catch (\Exception $e) {
                StockDebugger::error('Erro ao verificar transações de matéria-prima diretamente', $e);
            }
            
            Log::info('=== FIM DO MÉTODO PROCESS MATERIAL CONSUMPTION (DAILY PLAN) ===', [
                'plan_id' => $plan->id,
                'components_processed' => $componentsProcessed
            ]);
            
            // Usar o StockDebugger para registrar o fim do processo
            StockDebugger::endProcess('PROCESS_MATERIAL_CONSUMPTION', [
                'plan_id' => $plan->id,
                'components_processed' => $componentsProcessed,
                'components_with_issues' => !empty($componentsWithIssues) ? count($componentsWithIssues) : 0,
                'success' => true,
                'date_time' => date('Y-m-d H:i:s')
            ]);
            
            return [
                'success' => true,
                'message' => 'Consumo de materiais processado com sucesso',
                'components_processed' => $componentsProcessed,
                'components_with_issues' => $componentsWithIssues
            ];
            
        } catch (\Exception $e) {
            Log::error('Erro ao processar consumo de materiais para plano diário: ' . $e->getMessage(), [
                'plan_id' => $plan->id ?? null,
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);
            
            return [
                'success' => false,
                'message' => 'Erro ao processar consumo de materiais: ' . $e->getMessage(),
                'components_processed' => 0
            ];
        }
    }
    
    /**
     * Adiciona o produto final ao estoque baseado na produção do plano diário
     * Adiciona apenas a diferença de quantidade produzida com qualidade (actual_quantity)
     * Os produtos com defeito (defect_quantity) não são adicionados ao estoque
     * Só processa quando o status é 'completed'
     * 
     * @param ProductionDailyPlan $plan Plano diário atualizado
     * @param float $previousQuantity Quantidade anterior produzida
     * @param string $previousStatus Status anterior do plano
     * @return array Resultado do processamento
     */
    private function addFinishedProductToStock(ProductionDailyPlan $plan, float $previousQuantity = 0, string $previousStatus = '')
    {
        try {
            // Usar o StockDebugger para registrar o início do processo
            StockDebugger::startProcess('ADD_FINISHED_PRODUCT_TO_STOCK', [
                'plan_id' => $plan->id,
                'schedule_id' => $plan->schedule_id,
                'previous_quantity' => $previousQuantity,
                'new_quantity' => $plan->actual_quantity,
                'status' => $plan->status,
                'previous_status' => $previousStatus,
                'date_time' => date('Y-m-d H:i:s')
            ]);
            
            // Verificar configuração de armazéns no sistema
            StockDebugger::step('Verificando configuração de armazéns de produto acabado');
            $warehouseCheck = StockDebugger::checkWarehouses();
            
            // Verificar transações existentes para este plano
            StockDebugger::step('Verificando transações existentes de produto acabado para este plano');
            $transactionCheck = StockDebugger::checkInventoryTransactions('production_daily_plan', $plan->id);
            
            // Verificar se o produto existe
            if ($plan->schedule) {
                StockDebugger::step('Verificando existência do produto final');
                $productExists = Product::where('id', $plan->schedule->product_id)->exists();
                StockDebugger::object('Produto final', [
                    'product_id' => $plan->schedule->product_id,
                    'exists' => $productExists ? 'Sim' : 'Não'
                ]);
            }
            
            Log::info('=== INÍCIO DO MÉTODO ADD FINISHED PRODUCT TO STOCK (DAILY PLAN) ===', [
                'plan_id' => $plan->id,
                'schedule_id' => $plan->schedule_id,
                'previous_quantity' => $previousQuantity,
                'new_quantity' => $plan->actual_quantity,
                'defect_quantity' => $plan->defect_quantity ?? 0, // Apenas para fins de log
                'status' => $plan->status,
                'previous_status' => $previousStatus
            ]);
            
            // Informar que apenas produtos de qualidade são adicionados ao estoque
            Log::info('NOTA: Apenas produtos com qualidade (actual_quantity) são adicionados ao estoque de produtos acabados. Produtos com defeito (defect_quantity) são contabilizados apenas para consumo de matérias-primas.', [
                'plan_id' => $plan->id,
                'actual_quantity' => $plan->actual_quantity,
                'defect_quantity' => $plan->defect_quantity ?? 0,
            ]);
            
            // Verificar se o status atual é 'completed' - só processa neste caso
            if ($plan->status !== 'completed') {
                Log::info('Adição de produto ao estoque ignorada: status não é "completed"', [
                    'plan_id' => $plan->id,
                    'current_status' => $plan->status
                ]);
                return [
                    'success' => true,
                    'message' => 'Plano não está com status "completed"',
                    'quantity_added' => 0
                ];
            }
            
            Log::info('Processando adição de produto ao estoque para plano com status "completed"', [
                'plan_id' => $plan->id,
                'current_status' => $plan->status
            ]);
            
            // Calcular a diferença de quantidade (apenas processar se houver aumento)
            $quantityDifference = $plan->actual_quantity - $previousQuantity;
            
            if ($quantityDifference <= 0) {
                Log::info('Nenhuma adição de produto ao estoque necessária (sem aumento de quantidade)', [
                    'plan_id' => $plan->id,
                    'quantity_difference' => $quantityDifference
                ]);
                return [
                    'success' => true,
                    'message' => 'Nenhuma adição de produto ao estoque necessária',
                    'quantity_added' => 0
                ];
            }
            
            // Buscar o agendamento relacionado ao plano
            $schedule = $plan->schedule;
            
            if (!$schedule) {
                throw new \Exception('Agendamento não encontrado para o plano diário');
            }
            
            // Obter o armazém configurado no production schedule
            $schedule = $plan->schedule;
            if (!$schedule || !$schedule->location_id) {
                // Caso não haja armazém configurado, pegar o primeiro armazém ativo que não seja de matéria-prima
                $finishedGoodsWarehouses = InventoryLocation::where('is_active', 1)
                    ->where(function($query) {
                        $query->where('is_raw_material_warehouse', 0)
                              ->orWhereNull('is_raw_material_warehouse');
                    })
                    ->get();
                
                // Depuração - exibir a consulta SQL
                Log::debug('SQL para buscar armazéns não matéria-prima:', [
                    'sql' => InventoryLocation::where('is_active', 1)->where('is_raw_material_warehouse', 0)->toSql(),
                    'table' => (new InventoryLocation)->getTable(),
                    'connection' => config('database.default')
                ]);
                
                if ($finishedGoodsWarehouses->isEmpty()) {
                    // Consulta alternativa para verificar se existem quaisquer armazéns
                    $allWarehouses = Location::count();
                    $activeWarehouses = Location::where('is_active', 1)->count();
                    
                    Log::warning('Não foi possível encontrar armazéns de produto acabado', [
                        'plan_id' => $plan->id,
                        'schedule_id' => $plan->schedule_id,
                        'total_warehouses' => $allWarehouses,
                        'active_warehouses' => $activeWarehouses,
                        'table_exists' => \Illuminate\Support\Facades\Schema::hasTable((new Location)->getTable()) ? 'Sim' : 'Não'
                    ]);
                    
                    return [
                        'success' => false,
                        'message' => 'Não existem armazéns disponíveis para produto acabado',
                        'quantity_added' => 0
                    ];
                }
                
                // Selecionar o primeiro armazém disponível
                $defaultWarehouse = $finishedGoodsWarehouses->first();
                
                Log::debug('Selecionado primeiro armazém disponível para produto acabado:', [
                    'warehouse_id' => $defaultWarehouse->id,
                    'warehouse_name' => $defaultWarehouse->name
                ]);
            } else {
                // Usar o armazém configurado no production schedule (para produtos acabados)
                $defaultWarehouse = InventoryLocation::where('id', $schedule->location_id)
                    ->where('is_active', 1)
                    ->first();
                
                if (!$defaultWarehouse) {
                    Log::warning('Armazém configurado no production schedule não está ativo ou não existe', [
                        'plan_id' => $plan->id,
                        'schedule_id' => $schedule->id,
                        'location_id' => $schedule->location_id,
                        'warehouse_exists' => $schedule->location_id ? InventoryLocation::where('id', $schedule->location_id)->exists() : false,
                        'warehouse_is_active' => $schedule->location_id ? InventoryLocation::where('id', $schedule->location_id)->where('is_active', 1)->exists() : false
                    ]);
                    
                    // Tentar encontrar qualquer armazém ativo como fallback
                    $defaultWarehouse = InventoryLocation::where('is_active', 1)->first();
                    
                    if (!$defaultWarehouse) {
                        return [
                            'success' => false,
                            'message' => 'Não foi possível encontrar um armazém ativo',
                            'quantity_added' => 0
                        ];
                    }
                }
                
                Log::debug('Usando armazém configurado no production schedule:', [
                    'warehouse_id' => $defaultWarehouse->id,
                    'warehouse_name' => $defaultWarehouse->name,
                    'schedule_id' => $schedule->id
                ]);
            }
            
            // Buscar o item de estoque do produto final neste armazém
            $inventoryItem = InventoryItem::firstOrNew([
                'product_id' => $schedule->product_id,
                'location_id' => $defaultWarehouse->id
            ]);
            
            // Registrar a quantidade atual antes da atualização
            $currentStock = $inventoryItem->quantity_on_hand ?? 0;
            
            // Adicionar a quantidade produzida ao estoque
            $inventoryItem->quantity_on_hand = $currentStock + $quantityDifference;
            $inventoryItem->quantity_available = $inventoryItem->quantity_on_hand - ($inventoryItem->quantity_allocated ?? 0);
            $inventoryItem->save();
            
            // Registrar a movimentação de estoque para o produto final
            try {
                $transaction = new InventoryTransaction();
                
                // Gerar um número de transação único
                $prefix = 'TRX-FG-DAILY-' . date('Ymd');
                $lastTransaction = InventoryTransaction::where('transaction_number', 'LIKE', $prefix . '-%')
                    ->orderBy('id', 'desc')
                    ->first();
                
                // Depuração - verificar a consulta de busca da última transação
                Log::debug('SQL para buscar última transação de produto acabado:', [
                    'sql' => InventoryTransaction::where('transaction_number', 'LIKE', $prefix . '-%')->orderBy('id', 'desc')->toSql(),
                    'bindings' => InventoryTransaction::where('transaction_number', 'LIKE', $prefix . '-%')->orderBy('id', 'desc')->getBindings(),
                    'table' => (new InventoryTransaction)->getTable(),
                    'prefix' => $prefix
                ]);
                    
                if ($lastTransaction) {
                    $lastNumber = intval(substr($lastTransaction->transaction_number, strlen($prefix) + 1));
                    $newNumber = $lastNumber + 1;
                    Log::debug('Encontrou transação anterior de produto acabado:', [
                        'last_transaction_id' => $lastTransaction->id,
                        'last_transaction_number' => $lastTransaction->transaction_number,
                        'last_number' => $lastNumber,
                        'new_number' => $newNumber
                    ]);
                } else {
                    $newNumber = 1;
                    Log::debug('Nenhuma transação anterior de produto acabado encontrada. Começando com número 1.');
                }
                $transaction->transaction_number = $prefix . '-' . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
                
                // Verificar se o produto existe
                $productExists = \App\Models\SupplyChain\Product::where('id', $schedule->product_id)->exists();
                Log::debug('Verificação do produto para transação de produto acabado:', [
                    'product_id' => $schedule->product_id,
                    'product_exists' => $productExists ? 'Sim' : 'Não'
                ]);
                
                // Configurar a transação para o produto final
                $transaction->product_id = $schedule->product_id;
                $transaction->source_location_id = null; // Produção, não tem origem
                $transaction->destination_location_id = $defaultWarehouse->id;
                $transaction->reference_type = 'production_daily_plan';
                $transaction->reference_id = $plan->id;
                $transaction->quantity = $quantityDifference;
                $transaction->transaction_type = 'daily_production_fg'; // Tipo específico para produção em plano diário
                $transaction->notes = "Produto acabado adicionado ao estoque do plano diário: {$plan->id}";
                $transaction->created_by = auth()->id() ?? 1; // Garante que sempre tenha um ID de usuário
                
                // Definir campos que podem ser obrigatórios
                // Buscar custo unitário do produto ou usar valor padrão
                $product = Product::find($schedule->product_id);
                $transaction->unit_cost = $product ? ($product->cost_price ?: 0) : 0;
                $transaction->total_cost = $transaction->quantity * $transaction->unit_cost;
                
                // Verificar se está faltando algum campo obrigatório
                $requiredFields = ['product_id', 'quantity', 'transaction_number', 'transaction_type'];
                $missingFields = [];
                foreach ($requiredFields as $field) {
                    if (empty($transaction->$field)) {
                        $missingFields[] = $field;
                    }
                }
                if (!empty($missingFields)) {
                    Log::warning('Campos obrigatórios não preenchidos na transação:', [
                        'missing_fields' => $missingFields,
                        'transaction_data' => $transaction->toArray()
                    ]);
                }
                
                // Depuração - Mostrar dados da transação antes de salvar
                Log::debug('Tentativa de salvar transação de estoque de produto acabado:', [
                    'transaction_data' => $transaction->toArray()
                ]);
                
                $saveResult = $transaction->save();
                
                // Verificar se a transação foi realmente salva no banco
                $savedTransaction = InventoryTransaction::where('transaction_number', $transaction->transaction_number)->first();
                Log::warning('Verificação de persistência da transação:', [
                    'transaction_number' => $transaction->transaction_number,
                    'save_result' => $saveResult,
                    'encontrada' => $savedTransaction ? 'Sim' : 'Não',
                    'id_se_encontrada' => $savedTransaction ? $savedTransaction->id : null,
                    'exists_flag' => $transaction->exists
                ]);
                
                Log::debug('Transação de estoque de produto acabado salva com sucesso:', [
                    'transaction_id' => $transaction->id,
                    'transaction_number' => $transaction->transaction_number
                ]);
            } catch (\Exception $e) {
                Log::error('ERRO ao salvar transação de estoque para produto acabado:', [
                    'product_id' => $schedule->product_id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
            
            // Verificar se as transações foram realmente criadas
            StockDebugger::step('Verificando se as transações de produto acabado foram criadas');
            $finalTransactionCheck = StockDebugger::checkInventoryTransactions('production_daily_plan', $plan->id);
            
            // Realiza verificação direta no banco de dados
            try {
                $query = DB::table('sc_inventory_transactions');
                $query->where('reference_type', 'production_daily_plan');
                $query->where('reference_id', $plan->id);
                $query->where('transaction_type', 'daily_production_fg');
                $fgCount = $query->count();
                    
                StockDebugger::step('Verificação direta de transações de produto acabado', [
                    'count' => $fgCount,
                    'plan_id' => $plan->id
                ]);
                
                // Se não houver transações, verificar os tipos de transação disponíveis
                if ($fgCount === 0) {
                    // Verificar os tipos de transação existentes no sistema
                    $transactionTypes = DB::table('sc_inventory_transactions')
                        ->select('transaction_type')
                        ->distinct()
                        ->get()
                        ->pluck('transaction_type')
                        ->toArray();
                        
                    StockDebugger::step('Tipos de transação disponíveis no sistema', [
                        'types' => $transactionTypes
                    ]);
                    
                    // Verificar se o tipo 'daily_production_fg' existe na classe InventoryTransaction
                    $reflection = new \ReflectionClass(InventoryTransaction::class);
                    $constants = $reflection->getConstants();
                    
                    StockDebugger::step('Constantes definidas na classe InventoryTransaction', [
                        'constants' => $constants
                    ]);
                }
            } catch (\Exception $e) {
                StockDebugger::error('Erro ao verificar transações de produto acabado diretamente', $e);
            }
            
            Log::info('Produto final adicionado ao estoque', [
                'plan_id' => $plan->id,
                'product_id' => $schedule->product_id,
                'warehouse_id' => $defaultWarehouse->id,
                'warehouse_name' => $defaultWarehouse->name,
                'quantity_added' => $quantityDifference,
                'prev_stock' => $currentStock,
                'new_stock' => $inventoryItem->quantity_on_hand
            ]);
            
            Log::info('=== FIM DO MÉTODO ADD FINISHED PRODUCT TO STOCK (DAILY PLAN) ===', [
                'plan_id' => $plan->id,
                'quantity_added' => $quantityDifference
            ]);
            
            // Usar o StockDebugger para registrar o fim do processo
            StockDebugger::endProcess('ADD_FINISHED_PRODUCT_TO_STOCK', [
                'plan_id' => $plan->id,
                'product_id' => $schedule->product_id,
                'warehouse_id' => $defaultWarehouse->id,
                'quantity' => $quantityDifference,
                'success' => true,
                'date_time' => date('Y-m-d H:i:s')
            ]);
            
            return [
                'success' => true,
                'message' => 'Produto final adicionado ao estoque com sucesso',
                'quantity_added' => $quantityDifference,
                'warehouse_id' => $defaultWarehouse->id,
                'warehouse_name' => $defaultWarehouse->name
            ];
            
        } catch (\Exception $e) {
            StockDebugger::error('Erro ao adicionar produto final ao estoque', $e);
            
            Log::error('Erro ao adicionar produto final ao estoque: ' . $e->getMessage(), [
                'plan_id' => $plan->id ?? null,
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);
            
            // Usar o StockDebugger para registrar o fim do processo com erro
            StockDebugger::endProcess('ADD_FINISHED_PRODUCT_TO_STOCK', [
                'plan_id' => $plan->id,
                'success' => false,
                'error' => $e->getMessage(),
                'date_time' => date('Y-m-d H:i:s')
            ]);
            
            return [
                'success' => false,
                'message' => 'Erro ao adicionar produto final ao estoque: ' . $e->getMessage(),
                'quantity_added' => 0
            ];
        }
    }
}
