<?php

namespace App\Livewire\Mrp;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Mrp\ProductionSchedule;
use App\Models\SupplyChain\Inventory;
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
            
            // Registramos a movimentação de estoque
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
                    preg_match('/^(\d{2}:\d{2})/', $endTime, $matches);
                    $endTime = $matches[1] ?? '00:00';
                }
                
                // Combinar data e hora para criar o DateTime
                $endDateTime = Carbon::parse($endDate . ' ' . $endTime);
                $now = Carbon::now();
            } catch (\Exception $ex) {
                Log::error('Erro ao processar data/hora: ' . $ex->getMessage(), [
                    'end_date' => $schedule->end_date,
                    'end_time' => $schedule->end_time
                ]);
                // Fallback para evitar que o erro interrompa o processo
                $endDateTime = Carbon::now()->subMinute();
                $now = Carbon::now();
            }
            
            if ($now > $endDateTime) {
                $schedule->is_delayed = true;
                $schedule->delay_reason = $this->schedule['delay_reason'] ?? 'Produção concluída após a data/hora final prevista';
            }
            
            $schedule->save();
            
            // Atualizar todos os planos diários para 'completed'
            $schedule->dailyPlans()->update([
                'status' => 'completed',
                'actual_quantity' => DB::raw('planned_quantity'), // Por padrão, vamos considerar que foi produzido conforme planejado
            ]);
            
            // Movimentar estoque - adicionando o produto produzido ao estoque
            try {
                Log::info('Iniciando movimentação de estoque');
                
                if (class_exists('\\App\\Models\\SupplyChain\\Inventory') && isset($schedule->product_id) && isset($schedule->location_id)) {
                    // Atualizando o estoque - aumentando a quantidade do produto produzido
                    $inventory = Inventory::firstOrNew([
                        'product_id' => $schedule->product_id,
                        'location_id' => $schedule->location_id
                    ]);
                    
                    // Registramos a quantidade atual antes da atualização
                    $currentStock = $inventory->quantity ?? 0;
                    
                    // Atualizamos o estoque com a quantidade produzida
                    $inventory->quantity = ($currentStock + $actualQuantity);
                    $inventory->save();
                    
                    // Registramos a movimentação de estoque
                    $movement = new InventoryMovement();
                    $movement->product_id = $schedule->product_id;
                    $movement->location_id = $schedule->location_id;
                    $movement->document_type = 'production';
                    $movement->document_id = $schedule->id;
                    $movement->previous_quantity = $currentStock;
                    $movement->quantity_change = $actualQuantity;
                    $movement->current_quantity = $inventory->quantity;
                    $movement->movement_type = 'in';
                    $movement->notes = 'Produção completada: ' . $schedule->schedule_number;
                    $movement->created_by = auth()->id();
                    $movement->save();
                    
                    Log::info('Estoque atualizado com sucesso', [
                        'produto_id' => $schedule->product_id,
                        'produto' => $schedule->product->name ?? 'N/A',
                        'localização' => $schedule->location_id,
                        'estoque_anterior' => $currentStock,
                        'quantidade_adicionada' => $actualQuantity,
                        'estoque_atual' => $inventory->quantity
                    ]);
                } else {
                    Log::warning('Não foi possível movimentar o estoque', [
                        'motivo' => 'Classes de estoque não encontradas ou produto/localização não definidos',
                        'produto_id' => $schedule->product_id ?? null,
                        'localização_id' => $schedule->location_id ?? null
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('Erro ao movimentar estoque: ' . $e->getMessage(), [
                    'exception' => $e,
                    'produto_id' => $schedule->product_id ?? null,
                    'localização_id' => $schedule->location_id ?? null
                ]);
                
                // Não interrompemos o processo principal se a movimentação de estoque falhar
                // Apenas registramos o erro e notificamos o usuário
                $this->dispatch('notify',
                    type: 'warning',
                    title: __('messages.warning'),
                    message: __('messages.stock_update_failed', ['error' => $e->getMessage()])
                );
            }
            
            // Carregar dados atualizados
            $this->view($this->scheduleId);
            
            // Notificar usuário
            $this->dispatch('notify',
                type: 'success',
                title: __('messages.success'),
                message: __('messages.production_completed')
            );
            
            Log::info('=== FIM DO MÉTODO COMPLETE PRODUCTION ===');
        } catch (\Exception $e) {
            Log::error('Erro ao concluir produção: ' . $e->getMessage());
            
            $this->dispatch('notify',
                type: 'error',
                title: __('messages.error'),
                message: __('messages.production_complete_error', ['error' => $e->getMessage()])
            );
        }
    }
}
