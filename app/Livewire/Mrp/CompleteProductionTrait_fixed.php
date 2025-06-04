<?php

namespace App\Livewire\Mrp;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Mrp\ProductionSchedule;
use App\Models\Mrp\BomHeader;
use App\Models\Mrp\BomDetail;
use App\Models\SupplyChain\Inventory;
use App\Models\SupplyChain\InventoryItem;
use App\Models\SupplyChain\InventoryTransaction;
use App\Models\SupplyChain\InventoryMovement;
use Carbon\Carbon;

trait CompleteProductionTrait
{
    /**
     * Flag para controlar se houve movimentação de estoque
     */
    public $stockMoved = false;
    /**
     * Método para movimentar manualmente o estoque após produção
     */
    public function moveProductionToStock()
    {
        try {
            Log::info('=== INÍCIO DO MÉTODO MOVE PRODUCTION TO STOCK ===');
            
            // Recuperamos o agendamento e o produto relacionado
            $schedule = ProductionSchedule::with(['product'])->findOrFail($this->scheduleId);
            
            // Verificar se o produto e a localização estão definidos
            if (empty($schedule->product_id)) {
                throw new \Exception('O produto não está definido para este agendamento');
            }
            
            if (empty($schedule->location_id)) {
                throw new \Exception('A localização não está definida para este agendamento');
            }
            
            // Movimentar estoque - adicionando o produto produzido ao estoque
            $actualQuantity = $schedule->actual_quantity;
            
            // Atualizando o estoque - aumentando a quantidade do produto produzido
            $inventory = \App\Models\SupplyChain\InventoryItem::firstOrNew([
                'product_id' => $schedule->product_id,
                'location_id' => $schedule->location_id
            ]);
            
            // Registramos a quantidade atual antes da atualização
            $currentStock = $inventory->quantity_on_hand ?? 0;
            
            // Atualizamos o estoque com a quantidade produzida
            $inventory->quantity_on_hand = ($currentStock + $actualQuantity);
            $inventory->quantity_available = ($inventory->quantity_on_hand - ($inventory->quantity_allocated ?? 0));
            $inventory->save();
            
            // Registramos a movimentação de estoque para o produto acabado
            $transaction = new \App\Models\SupplyChain\InventoryTransaction();
            
            // Geramos um número de transação único
            $prefix = 'TRX-' . date('Ymd');
            $lastTransaction = \App\Models\SupplyChain\InventoryTransaction::where('transaction_number', 'LIKE', $prefix . '-%')
                ->orderBy('id', 'desc')
                ->first();
                
            if ($lastTransaction) {
                $lastNumber = intval(substr($lastTransaction->transaction_number, strlen($prefix) + 1));
                $newNumber = $lastNumber + 1;
            } else {
                $newNumber = 1;
            }
            $transaction->transaction_number = $prefix . '-' . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
            
            $transaction->product_id = $schedule->product_id;
            $transaction->destination_location_id = $schedule->location_id; // Destino para produção
            $transaction->source_location_id = $schedule->location_id; // Mesma localização para produção
            $transaction->reference_type = 'production';
            $transaction->reference_id = $schedule->id;
            $transaction->quantity = $actualQuantity;
            $transaction->transaction_type = 'production_receipt'; // Valor válido para o ENUM
            $transaction->notes = 'Produção completada (movimentação manual): ' . $schedule->schedule_number;
            $transaction->created_by = auth()->id();
            $transaction->save();
            
            // -----------------------------------------
            // NOVO: Descontar componentes do estoque
            // -----------------------------------------
            $this->deductComponentsFromStock($schedule);
            
            // Marcar que o estoque foi movimentado
            $this->stockMoved = true;
            
            // Atualizar o agendamento
            $schedule->stock_moved = true;
            $schedule->stock_moved_at = now();
            $schedule->stock_moved_by = auth()->id();
            $schedule->save();
            
            Log::info('Estoque atualizado com sucesso', [
                'produto_id' => $schedule->product_id,
                'produto' => $schedule->product->name ?? 'N/A',
                'localização' => $schedule->location_id,
                'estoque_anterior' => $currentStock,
                'quantidade_adicionada' => $actualQuantity,
                'estoque_atual' => $inventory->quantity_on_hand
            ]);
            
            // Disparamos o toastr com uma mensagem de sucesso
            $this->dispatch('toast', [
                'type' => 'success',
                'title' => __('messages.success'),
                'message' => __('messages.stock_updated_success', ['quantity' => $actualQuantity, 'product' => $schedule->product->name ?? 'N/A'])
            ]);
            
            // Recarregar dados atualizados
            $this->view($this->scheduleId);
            
            Log::info('=== FIM DO MÉTODO MOVE PRODUCTION TO STOCK ===');
        } catch (\Exception $e) {
            Log::error('Erro ao movimentar estoque: ' . $e->getMessage(), [
                'scheduleId' => $this->scheduleId ?? null
            ]);
            
            // Disparamos o toastr com uma mensagem de erro
            $this->dispatch('toast', [
                'type' => 'error',
                'title' => __('messages.error'),
                'message' => __('messages.stock_update_failed', ['error' => $e->getMessage()])
            ]);
        }
    }
    
    /**
     * Método para registrar a conclusão da produção e movimentar o estoque
     */
    public function completeProduction()
    {
        try {
            Log::info('=== INÍCIO DO MÉTODO COMPLETE PRODUCTION ===');
            
            // Recuperamos o agendamento e o produto relacionado
            $schedule = ProductionSchedule::with(['product', 'dailyPlans'])->findOrFail($this->scheduleId);
            
            // Calcular a quantidade real a partir dos planos diários, se não fornecida pelo usuário
            if (empty($this->schedule['actual_quantity'])) {
                $actualQuantity = $schedule->dailyPlans()->sum('actual_quantity');
                
                // Se ainda for zero, use a quantidade planejada como fallback
                if ($actualQuantity <= 0) {
                    $actualQuantity = $schedule->dailyPlans()->sum('planned_quantity');
                }
                
                // Atualizar o modelo para que a interface fique sincronizada
                $this->schedule['actual_quantity'] = $actualQuantity;
                
                Log::info('Quantidade real calculada automaticamente dos planos diários', [
                    'actual_quantity' => $actualQuantity
                ]);
            } else {
                // Validar se o usuário forneceu uma quantidade válida
                $this->validate([
                    'schedule.actual_quantity' => 'numeric|min:0.01',  // Requer pelo menos uma quantidade mínima
                ]);
                
                // Definimos a quantidade produzida conforme informado pelo usuário
                $actualQuantity = $this->schedule['actual_quantity'];
            }
            
            // Atualizar o status e registrar a data/hora de conclusão
            $schedule->status = 'completed';
            $schedule->actual_end_time = now();
            $schedule->actual_quantity = $actualQuantity;
            
            // Calcular o percentual de conclusão para log
            $completionPercentage = 0;
            if ($schedule->planned_quantity > 0) {
                $completionPercentage = round(($actualQuantity / $schedule->planned_quantity) * 100, 1);
            }
            
            Log::info('Detalhes da produção concluída', [
                'id' => $schedule->id,
                'produto' => $schedule->product->name ?? 'N/A',
                'quantidade_planejada' => $schedule->planned_quantity,
                'quantidade_produzida' => $actualQuantity,
                'percentual_conclusao' => $completionPercentage
            ]);
            
            // Verificar se houve atraso
            try {
                // Processando a data final e a hora separadamente para evitar problemas de formatação
                $endDate = $schedule->end_date;
                if ($endDate instanceof \DateTime || $endDate instanceof Carbon) {
                    $endDate = $endDate->format('Y-m-d');
                }
                
                // Certifique-se de que a hora está no formato correto (H:i)
                $endTime = $schedule->end_time;
                if (strlen($endTime) > 5) {
                    // Se a hora contiver segundos ou outros dados, extrair apenas HH:MM
                    $endTime = substr($endTime, 0, 5);
                }
                
                // Combinar data e hora finais planejadas
                $plannedEndDateTime = Carbon::parse($endDate . ' ' . $endTime);
                
                // Calcular o atraso em minutos
                $delay = now()->diffInMinutes($plannedEndDateTime, false);
                
                // Se a diferença for negativa, significa que a produção foi concluída com atraso
                if ($delay < 0) {
                    $delayInMinutes = abs($delay);
                    $delayInHours = round($delayInMinutes / 60, 1);
                    
                    Log::info('Produção concluída com atraso', [
                        'atraso_minutos' => $delayInMinutes,
                        'atraso_horas' => $delayInHours
                    ]);
                    
                    // Registrar o atraso no agendamento
                    $schedule->delay_minutes = $delayInMinutes;
                } else {
                    Log::info('Produção concluída dentro do prazo');
                    $schedule->delay_minutes = 0;
                }
            } catch (\Exception $e) {
                // Se houver erro no cálculo do atraso, não interromper o processo
                Log::warning('Erro ao calcular atraso: ' . $e->getMessage());
            }
            
            // Salvar o agendamento com as atualizações
            $schedule->save();
            
            // Se movimentação automática de estoque estiver ativada
            if (setting('production.auto_stock_movement', false)) {
                Log::info('Movimentação automática de estoque ativada. Processando...');
                
                // Movimentar o estoque automaticamente
                $this->deductComponentsFromStock($schedule);
                
                // Atualizar o estoque do produto acabado
                $inventory = \App\Models\SupplyChain\InventoryItem::firstOrNew([
                    'product_id' => $schedule->product_id,
                    'location_id' => $schedule->location_id
                ]);
                
                // Registramos a quantidade atual antes da atualização
                $currentStock = $inventory->quantity_on_hand ?? 0;
                
                // Atualizamos o estoque com a quantidade produzida
                $inventory->quantity_on_hand = ($currentStock + $actualQuantity);
                $inventory->quantity_available = ($inventory->quantity_on_hand - ($inventory->quantity_allocated ?? 0));
                $inventory->save();
                
                // Registrar movimentação de estoque
                $transaction = new \App\Models\SupplyChain\InventoryTransaction();
                
                // Gerar um número de transação único
                $prefix = 'TRX-AUTO-' . date('Ymd');
                $lastTransaction = \App\Models\SupplyChain\InventoryTransaction::where('transaction_number', 'LIKE', $prefix . '-%')
                    ->orderBy('id', 'desc')
                    ->first();
                    
                if ($lastTransaction) {
                    $lastNumber = intval(substr($lastTransaction->transaction_number, strlen($prefix) + 1));
                    $newNumber = $lastNumber + 1;
                } else {
                    $newNumber = 1;
                }
                $transaction->transaction_number = $prefix . '-' . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
                
                $transaction->product_id = $schedule->product_id;
                $transaction->destination_location_id = $schedule->location_id;
                $transaction->source_location_id = $schedule->location_id;
                $transaction->reference_type = 'production';
                $transaction->reference_id = $schedule->id;
                $transaction->quantity = $actualQuantity;
                $transaction->transaction_type = 'production_receipt';
                $transaction->notes = 'Produção completada (automático): ' . $schedule->schedule_number;
                $transaction->created_by = auth()->id();
                $transaction->save();
                
                // Marcar que o estoque foi movimentado
                $schedule->stock_moved = true;
                $schedule->stock_moved_at = now();
                $schedule->stock_moved_by = auth()->id();
                $schedule->save();
                
                $this->stockMoved = true;
            }
            
            // Disparamos o toastr com uma mensagem de sucesso
            $this->dispatch('toast', [
                'type' => 'success',
                'title' => __('messages.success'),
                'message' => __('messages.production_completed', ['schedule' => $schedule->schedule_number])
            ]);
            
            // Fechar o modal
            $this->modalOpen = false;
            
            // Recarregar a listagem
            $this->loadSchedules();
            
            Log::info('=== FIM DO MÉTODO COMPLETE PRODUCTION ===');
        } catch (\Exception $e) {
            Log::error('Erro ao completar produção: ' . $e->getMessage(), [
                'scheduleId' => $this->scheduleId ?? null,
                'exception' => $e
            ]);
            
            // Disparamos o toastr com uma mensagem de erro
            $this->dispatch('toast', [
                'type' => 'error',
                'title' => __('messages.error'),
                'message' => __('messages.production_complete_error', ['error' => $e->getMessage()])
            ]);
        }
    }
    
    /**
     * Método para descontar os componentes do estoque após produção
     * Permite que o estoque fique negativo
     * 
     * @param ProductionSchedule $schedule Agendamento de produção
     * @return void
     */
    private function deductComponentsFromStock(ProductionSchedule $schedule)
    {
        try {
            Log::info('=== INÍCIO DO MÉTODO DEDUCT COMPONENTS FROM STOCK ===');
            
            // Buscar o BOM (Lista de Materiais) para o produto
            $bomHeader = \App\Models\Mrp\BomHeader::where('product_id', $schedule->product_id)
                ->where('status', 'active') // Usando 'status' ao invés de 'is_active'
                ->orderBy('version', 'desc') // Prioriza a versão mais recente da BOM ao invés de is_default
                ->first();
            
            if (!$bomHeader) {
                Log::warning('Nenhuma BOM encontrada para o produto', [
                    'product_id' => $schedule->product_id,
                    'schedule_id' => $schedule->id
                ]);
                return;
            }
            
            // Buscar todos os componentes da BOM
            $components = \App\Models\Mrp\BomDetail::where('bom_header_id', $bomHeader->id)->with('component')->get();
            
            if ($components->isEmpty()) {
                Log::warning('BOM não possui componentes', [
                    'bom_header_id' => $bomHeader->id,
                    'product_id' => $schedule->product_id
                ]);
                return;
            }
            
            Log::info('Processando componentes da BOM', [
                'bom_header_id' => $bomHeader->id,
                'total_components' => $components->count(),
                'schedule_id' => $schedule->id,
                'product_quantity' => $schedule->actual_quantity
            ]);
            
            $componentsWithIssues = [];
            
            // Para cada componente da BOM
            foreach ($components as $component) {
                // Calcular a quantidade necessária baseada na produção real
                $requiredQuantity = $component->quantity * $schedule->actual_quantity;
                
                // Buscar o item de estoque do componente
                $inventoryItem = \App\Models\SupplyChain\InventoryItem::firstOrNew([
                    'product_id' => $component->component_id,
                    'location_id' => $schedule->location_id
                ]);
                
                // Registrar a quantidade atual antes da atualização
                $currentStock = $inventoryItem->quantity_on_hand ?? 0;
                
                // Descontar a quantidade necessária do estoque (pode ficar negativo)
                $inventoryItem->quantity_on_hand = $currentStock - $requiredQuantity;
                $inventoryItem->quantity_available = $inventoryItem->quantity_on_hand - ($inventoryItem->quantity_allocated ?? 0);
                $inventoryItem->save();
                
                // Registrar a movimentação de estoque para o componente
                $transaction = new \App\Models\SupplyChain\InventoryTransaction();
                
                // Gerar um número de transação único
                $prefix = 'TRX-COMP-' . date('Ymd');
                $lastTransaction = \App\Models\SupplyChain\InventoryTransaction::where('transaction_number', 'LIKE', $prefix . '-%')
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
                $transaction->source_location_id = $schedule->location_id;
                $transaction->destination_location_id = null; // Componente consumido, não tem destino
                $transaction->reference_type = 'production_component';
                $transaction->reference_id = $schedule->id;
                $transaction->quantity = $requiredQuantity;
                $transaction->transaction_type = 'component_consumption'; // Consumo de componente
                $transaction->notes = "Componente consumido para produção: {$schedule->schedule_number}";
                $transaction->created_by = auth()->id();
                $transaction->save();
                
                // Registrar informações úteis para debugging
                $componentName = $component->component ? $component->component->name : "Componente ID: {$component->component_id}";
                
                Log::info("Componente deduzido do estoque", [
                    'component_id' => $component->component_id,
                    'component_name' => $componentName,
                    'required_quantity' => $requiredQuantity,
                    'prev_stock' => $currentStock,
                    'new_stock' => $inventoryItem->quantity_on_hand,
                    'is_negative' => $inventoryItem->quantity_on_hand < 0
                ]);
                
                // Registrar componentes que ficaram com estoque negativo
                if ($inventoryItem->quantity_on_hand < 0) {
                    $componentsWithIssues[] = [
                        'component_id' => $component->component_id,
                        'component_name' => $componentName,
                        'required' => $requiredQuantity,
                        'available' => $currentStock,
                        'shortage' => abs($inventoryItem->quantity_on_hand)
                    ];
                }
            }
            
            // Registrar os componentes com problemas de estoque no log
            if (!empty($componentsWithIssues)) {
                Log::warning('Componentes com estoque negativo após dedução', [
                    'schedule_id' => $schedule->id,
                    'schedule_number' => $schedule->schedule_number,
                    'components_with_issues' => $componentsWithIssues
                ]);
            }
            
            Log::info('=== FIM DO MÉTODO DEDUCT COMPONENTS FROM STOCK ===');
        } catch (\Exception $e) {
            Log::error('Erro ao descontar componentes do estoque: ' . $e->getMessage(), [
                'schedule_id' => $schedule->id ?? null,
                'exception' => $e
            ]);
            
            // Não lançamos a exceção para cima para evitar que o processo principal seja interrompido
            // Apenas registramos o erro no log
        }
    }
}
