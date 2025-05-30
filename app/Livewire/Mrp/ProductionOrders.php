<?php

namespace App\Livewire\Mrp;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Mrp\ProductionOrder;
use App\Models\Mrp\ProductionSchedule;
use App\Models\Mrp\BomHeader;
use App\Models\Mrp\BomDetail;
use App\Models\SupplyChain\Product;
use App\Models\SupplyChain\InventoryLocation as Location;
use App\Models\SupplyChain\InventoryItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ProductionOrders extends Component
{
    use WithPagination;
    
    // Propriedades do componente
    public $search = '';
    public $sortField = 'planned_start_date';
    public $sortDirection = 'asc';
    public $perPage = 10;
    
    // Propriedades para modal
    public $showModal = false;
    public $showDeleteModal = false;
    public $showDetailsModal = false;
    public $showMaterialsModal = false;
    public $showAvailabilityModal = false; // Modal para mostrar logs de disponibilidade
    public $editMode = false;
    public $orderId = null;
    
    // Propriedades do formulário
    public $order = [
        'order_number' => '',
        'product_id' => '',
        'bom_header_id' => '',
        'schedule_id' => '',
        'planned_start_date' => '',
        'planned_end_date' => '',
        'actual_start_date' => '',
        'actual_end_date' => '',
        'planned_quantity' => '',
        'produced_quantity' => 0,
        'rejected_quantity' => 0,
        'status' => 'draft',
        'priority' => 'medium',
        'location_id' => '',
        'notes' => ''
    ];
    
    // Propriedades para seleções dependentes
    public $availableBoms = [];
    public $availableSchedules = [];
    public $bomComponents = [];
    
    // Propriedades para verificação de disponibilidade
    public $availabilityLog = [];
    public $availabilityComponents = [];
    public $verificationInProgress = false;
    
    // Propriedades de filtro
    public $statusFilter = null;
    public $priorityFilter = null;
    public $dateFilter = null;
    public $productFilter = null;
    public $locationFilter = null;
    
    /**
     * Mount component
     */
    public function mount()
    {
        $this->order['planned_start_date'] = date('Y-m-d');
        $this->order['planned_end_date'] = date('Y-m-d', strtotime('+7 days'));
    }
    
    /**
     * Regras de validação
     */
    protected function rules()
    {
        return [
            'order.order_number' => [
                'required',
                'string',
                'max:50',
                $this->editMode
                    ? Rule::unique('mrp_production_orders', 'order_number')->ignore($this->orderId)
                    : Rule::unique('mrp_production_orders', 'order_number'),
            ],
            'order.product_id' => 'required|exists:sc_products,id',
            'order.bom_header_id' => 'nullable|exists:mrp_bom_headers,id',
            'order.schedule_id' => 'nullable|exists:mrp_production_schedules,id',
            'order.planned_start_date' => 'required|date',
            'order.planned_end_date' => 'required|date|after_or_equal:order.planned_start_date',
            'order.actual_start_date' => 'nullable|date',
            'order.actual_end_date' => 'nullable|date|after_or_equal:order.actual_start_date',
            'order.planned_quantity' => 'required|numeric|min:0.001',
            'order.produced_quantity' => 'nullable|numeric|min:0',
            'order.rejected_quantity' => 'nullable|numeric|min:0',
            'order.status' => ['required', Rule::in(['draft', 'released', 'in_progress', 'completed', 'cancelled'])],
            'order.priority' => ['required', Rule::in(['low', 'medium', 'high', 'urgent'])],
            'order.location_id' => 'nullable|exists:locations,id',
            'order.notes' => 'nullable|string|max:1000',
        ];
    }
    
    /**
     * Resetar paginação quando qualquer filtro mudar
     */
    public function updatingSearch()
    {
        $this->resetPage();
    }
    
    public function updatingStatusFilter()
    {
        $this->resetPage();
    }
    
    public function updatingPriorityFilter()
    {
        $this->resetPage();
    }
    
    public function updatingProductFilter()
    {
        $this->resetPage();
    }
    
    public function updatingLocationFilter()
    {
        $this->resetPage();
    }
    
    public function updatingPerPage()
    {
        $this->resetPage();
    }
    
    /**
     * Atualização quando o produto é selecionado
     */
    public function updatedOrderProductId()
    {
        $this->loadAvailableBoms();
        $this->loadAvailableSchedules();
        $this->generateOrderNumber();
    }
    
    /**
     * Carregar BOMs disponíveis para o produto selecionado
     */
    private function loadAvailableBoms()
    {
        \Illuminate\Support\Facades\Log::info('=== INÍCIO DO MÉTODO loadAvailableBoms ===', [
            'product_id' => $this->order['product_id']
        ]);
        
        $this->availableBoms = [];
        $this->order['bom_header_id'] = '';
        
        if (empty($this->order['product_id'])) {
            \Illuminate\Support\Facades\Log::info('product_id vazio, retornando sem carregar BOMs');
            return;
        }
        
        try {
            // Verificar schema da tabela BOM headers
            $bomHeadersColumns = \Illuminate\Support\Facades\Schema::getColumnListing('mrp_bom_headers');
            \Illuminate\Support\Facades\Log::info('Colunas na tabela mrp_bom_headers', [
                'columns' => $bomHeadersColumns
            ]);
            
            // Verificar se existem BOMs para o produto
            $existingBoms = BomHeader::where('product_id', $this->order['product_id'])->count();
            \Illuminate\Support\Facades\Log::info('Quantidade de BOMs para o produto', [
                'product_id' => $this->order['product_id'],
                'count' => $existingBoms
            ]);
            
            // Verificar a FK explicitamente
            $product = \App\Models\SupplyChain\Product::find($this->order['product_id']);
            \Illuminate\Support\Facades\Log::info('Produto consultado', [
                'product' => $product ? $product->toArray() : 'Produto não encontrado'
            ]);
            
            // Buscar BOMs com query mais clara e log detalhado
            $query = BomHeader::where('product_id', $this->order['product_id'])
                ->where(function($q) {
                    $q->where('status', 'active')
                      ->orWhereNull('status');
                })
                ->orderBy('version', 'desc');
                
            \Illuminate\Support\Facades\Log::info('Consulta SQL para BOMs: ' . $query->toSql(), [
                'bindings' => $query->getBindings()
            ]);
            
            $this->availableBoms = $query->get()->toArray();
            
            \Illuminate\Support\Facades\Log::info('BOMs carregados', [
                'count' => count($this->availableBoms),
                'boms' => $this->availableBoms
            ]);
            
            // Se existir algum BOM, selecionar o primeiro como padrão
            if (count($this->availableBoms) > 0) {
                $this->order['bom_header_id'] = $this->availableBoms[0]['id'];
                $this->loadBomComponents();
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Erro ao carregar BOMs: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);
        }
        
        \Illuminate\Support\Facades\Log::info('=== FIM DO MÉTODO loadAvailableBoms ===');
    }
    
    /**
     * Carregar programações disponíveis para o produto selecionado
     */
    private function loadAvailableSchedules()
    {
        \Illuminate\Support\Facades\Log::info('=== INÍCIO DO MÉTODO loadAvailableSchedules ===', [
            'product_id' => $this->order['product_id']
        ]);
        
        $this->availableSchedules = [];
        $this->order['schedule_id'] = '';
        
        if (empty($this->order['product_id'])) {
            \Illuminate\Support\Facades\Log::info('product_id vazio, retornando sem carregar Programações');
            return;
        }
        
        try {
            // Verificar schema da tabela
            $schedulesColumns = \Illuminate\Support\Facades\Schema::getColumnListing('mrp_production_schedules');
            \Illuminate\Support\Facades\Log::info('Colunas na tabela mrp_production_schedules', [
                'columns' => $schedulesColumns
            ]);
            
            // Verificar se existem programações para o produto
            $existingSchedules = ProductionSchedule::where('product_id', $this->order['product_id'])->count();
            \Illuminate\Support\Facades\Log::info('Quantidade de Programações para o produto', [
                'product_id' => $this->order['product_id'],
                'count' => $existingSchedules
            ]);
            
            // Buscar programações com query mais flexível
            $query = ProductionSchedule::where('product_id', $this->order['product_id'])
                ->where(function($q) {
                    $q->whereIn('status', ['confirmed', 'in_progress', 'scheduled'])
                      ->orWhereNull('status');
                })
                ->orderBy('start_date');
                
            \Illuminate\Support\Facades\Log::info('Consulta SQL para Programações: ' . $query->toSql(), [
                'bindings' => $query->getBindings()
            ]);
            
            $this->availableSchedules = $query->get()->toArray();
            
            \Illuminate\Support\Facades\Log::info('Programações carregadas', [
                'count' => count($this->availableSchedules),
                'programacoes' => $this->availableSchedules
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Erro ao carregar Programações: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);
        }
        
        \Illuminate\Support\Facades\Log::info('=== FIM DO MÉTODO loadAvailableSchedules ===');
    }
    
    /**
     * Quando uma BOM é selecionada
     */
    public function updatedOrderBomHeaderId()
    {
        $this->loadBomComponents();
    }
    
    /**
     * Carregar componentes da BOM selecionada
     */
    private function loadBomComponents()
    {
        $this->bomComponents = [];
        
        if (empty($this->order['bom_header_id'])) {
            return;
        }
        
        $this->bomComponents = BomDetail::where('bom_header_id', $this->order['bom_header_id'])
            ->with(['component'])
            ->orderBy('level')
            ->orderBy('position')
            ->get()
            ->toArray();
    }
    
    /**
     * Quando uma programação é selecionada
     */
    public function updatedOrderScheduleId()
    {
        if (empty($this->order['schedule_id'])) {
            return;
        }
        
        $schedule = ProductionSchedule::find($this->order['schedule_id']);
        if ($schedule) {
            $this->order['planned_start_date'] = $schedule->start_date->format('Y-m-d');
            $this->order['planned_end_date'] = $schedule->end_date->format('Y-m-d');
            
            if (empty($this->order['planned_quantity'])) {
                $this->order['planned_quantity'] = $schedule->planned_quantity;
            }
            
            if (empty($this->order['location_id']) && $schedule->location_id) {
                $this->order['location_id'] = $schedule->location_id;
            }
        }
    }
    
    /**
     * Gerar número da ordem de produção
     */
    public function generateOrderNumber()
    {
        if (empty($this->order['product_id'])) {
            return;
        }
        
        $product = Product::find($this->order['product_id']);
        if (!$product) {
            return;
        }
        
        // Gerar número sequencial
        $lastOrder = ProductionOrder::orderBy('id', 'desc')->first();
        $lastId = $lastOrder ? $lastOrder->id + 1 : 1;
        
        // Formato: OP-AAAAMMDD-XXXX (XXXX = número sequencial)
        $this->order['order_number'] = 'OP-' . date('Ymd') . '-' . str_pad($lastId, 4, '0', STR_PAD_LEFT);
    }
    
    /**
     * Ordenar por coluna
     */
    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortDirection = 'asc';
        }
        
        $this->sortField = $field;
    }
    
    /**
     * Resetar filtros
     */
    public function resetFilters()
    {
        $this->reset(['search', 'statusFilter', 'priorityFilter', 'dateFilter', 'productFilter', 'locationFilter']);
        $this->resetPage();
    }
    
    /**
     * Abrir modal para criar nova ordem de produção
     */
    public function create()
    {
        $this->resetValidation();
        $this->reset('order');
        $this->order['status'] = 'draft';
        $this->order['priority'] = 'medium';
        $this->order['planned_start_date'] = date('Y-m-d');
        $this->order['planned_end_date'] = date('Y-m-d', strtotime('+7 days'));
        $this->order['produced_quantity'] = 0;
        $this->order['rejected_quantity'] = 0;
        $this->availableBoms = [];
        $this->availableSchedules = [];
        $this->bomComponents = [];
        $this->editMode = false;
        $this->showModal = true;
    }
    
    /**
     * Carregar e abrir modal para editar ordem de produção
     */
    public function edit($id)
    {
        $this->resetValidation();
        $this->orderId = $id;
        $order = ProductionOrder::findOrFail($id);
        
        $this->order = [
            'order_number' => $order->order_number,
            'product_id' => $order->product_id,
            'bom_header_id' => $order->bom_header_id,
            'schedule_id' => $order->schedule_id,
            'planned_start_date' => $order->planned_start_date->format('Y-m-d'),
            'planned_end_date' => $order->planned_end_date->format('Y-m-d'),
            'actual_start_date' => $order->actual_start_date ? $order->actual_start_date->format('Y-m-d') : null,
            'actual_end_date' => $order->actual_end_date ? $order->actual_end_date->format('Y-m-d') : null,
            'planned_quantity' => $order->planned_quantity,
            'produced_quantity' => $order->produced_quantity,
            'rejected_quantity' => $order->rejected_quantity,
            'status' => $order->status,
            'priority' => $order->priority,
            'location_id' => $order->location_id,
            'notes' => $order->notes
        ];
        
        $this->loadAvailableBoms();
        $this->loadAvailableSchedules();
        $this->loadBomComponents();
        
        $this->editMode = true;
        $this->showModal = true;
    }
    
    /**
     * Visualizar detalhes da ordem de produção
     */
    public function viewDetails($id)
    {
        $this->orderId = $id;
        $this->showDetailsModal = true;
    }
    
    /**
     * Visualizar materiais necessários
     */
    public function viewMaterials($id)
    {
        $this->orderId = $id;
        $this->showMaterialsModal = true;
    }
    
    /**
     * Confirmar exclusão de ordem de produção
     */
    public function confirmDelete($id)
    {
        $this->orderId = $id;
        $this->showDeleteModal = true;
    }
    
    /**
     * Excluir ordem de produção
     */
    public function delete()
    {
        try {
            $order = ProductionOrder::findOrFail($this->orderId);
            
            // Só permitir excluir se estiver em rascunho ou cancelada
            if (!in_array($order->status, ['draft', 'cancelled'])) {
                $this->dispatch('notify', [
                    'type' => 'error',
                    'title' => 'Não é possível excluir',
                    'message' => 'Apenas ordens em rascunho ou canceladas podem ser excluídas.'
                ]);
                
                $this->showDeleteModal = false;
                return;
            }
            
            $order->delete();
            
            $this->dispatch('notify', [
                'type' => 'success',
                'title' => 'Exclusão realizada!',
                'message' => 'A ordem de produção foi excluída com sucesso.'
            ]);
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'title' => 'Erro ao excluir',
                'message' => 'Ocorreu um erro ao excluir a ordem: ' . $e->getMessage()
            ]);
        }
        
        $this->showDeleteModal = false;
        $this->orderId = null;
    }
    
    /**
     * Fechar modais
     */
    public function closeModal()
    {
        $this->showModal = false;
        $this->showDeleteModal = false;
        $this->showDetailsModal = false;
        $this->showMaterialsModal = false;
        $this->showAvailabilityModal = false;
        
        // Limpar logs de disponibilidade quando fechar o modal
        $this->availabilityLog = [];
        $this->availabilityComponents = [];
        $this->verificationInProgress = false;
    }
    
    /**
     * Verificar disponibilidade dos componentes para uma ordem de produção
     * 
     * @param ProductionOrder $order A ordem de produção a ser verificada
     * @param bool $logOnly Se true, apenas registra logs sem bloquear a operação
     * @return array Resultado da verificação com status e mensagem
     */
    public function verifyComponentsAvailability(ProductionOrder $order, $logOnly = false)
    {
        // Verificar se há uma BOM associada à ordem
        if (!$order->bomHeader) {
            Log::warning("Verificação de disponibilidade falhou: Ordem #{$order->id} não tem BOM associada");
            return [
                'success' => false,
                'message' => 'Esta ordem não possui uma lista de materiais (BOM) associada.'
            ];
        }
        
        // Para evitar loop infinito, marcamos que a verificação está em andamento
        if ($this->verificationInProgress) {
            Log::warning("Evitando loop infinito na verificação de disponibilidade para ordem #{$order->id}");
            return [
                'success' => $logOnly, // Se estamos apenas logando, permitimos continuar
                'message' => 'Verificação já está em andamento. Evitando loop infinito.'
            ];
        }
        
        $this->verificationInProgress = true;
        $this->availabilityLog = []; // Limpar logs anteriores
        
        try {
            Log::info("Iniciando verificação de disponibilidade para ordem #{$order->id}", [
                'product_id' => $order->product_id,
                'bom_id' => $order->bom_header_id,
                'quantity' => $order->planned_quantity
            ]);
            
            $this->addAvailabilityLog("Iniciando verificação de componentes para produção de {$order->planned_quantity} unidades do produto ID: {$order->product_id}");
            
            // Buscar todos os componentes da BOM com seus produtos associados
            $components = BomDetail::where('bom_header_id', $order->bom_header_id)
                ->with(['component' => function($query) {
                    $query->withSum('inventoryItems', 'quantity_on_hand');
                }])
                ->get();
            
            $this->addAvailabilityLog("Encontrados " . $components->count() . " componentes na BOM");
            
            // Verificar a disponibilidade de cada componente
            $unavailableComponents = [];
            $this->availabilityComponents = [];
            
            foreach ($components as $index => $component) {
                // Evitar loop infinito verificando tempo de execução
                if ($index > 0 && $index % 10 === 0) {
                    $this->addAvailabilityLog("Verificação em andamento... processados {$index} de " . $components->count() . " componentes");
                }
                
                // Calcular quantidade necessária para a produção
                $requiredQuantity = $component->quantity * $order->planned_quantity;
                
                // Buscar estoque disponível
                $availableQuantity = $component->component->inventory_items_sum_quantity_on_hand ?? 0;
                
                if (!$availableQuantity) {
                    // Tenta buscar diretamente do inventário
                    $inventoryItems = InventoryItem::where('product_id', $component->component_id)->get();
                    $availableQuantity = $inventoryItems->sum('quantity_on_hand');
                }
                
                $isAvailable = $availableQuantity >= $requiredQuantity;
                $shortage = $isAvailable ? 0 : ($requiredQuantity - $availableQuantity);
                
                // Registrar resultado da verificação para este componente
                $this->availabilityComponents[] = [
                    'component_id' => $component->component_id,
                    'name' => $component->component ? ($component->component->name ?? "Sem nome") : "Componente ID: {$component->component_id}",
                    'required_quantity' => $requiredQuantity,
                    'available_quantity' => $availableQuantity,
                    'is_available' => $isAvailable,
                    'shortage' => $shortage,
                    'is_critical' => $component->is_critical
                ];
                
                // Fix: Can't use null coalescing operator inside string interpolation
                $componentName = $component->component ? ($component->component->name ?? "Sem nome") : "Componente ID: {$component->component_id}";
                $logMessage = "Componente: {$componentName} - ";
                $logMessage .= "Necessário: {$requiredQuantity} - Disponível: {$availableQuantity} - ";
                $logMessage .= $isAvailable ? "OK" : "FALTA {$shortage} unidades";
                
                $this->addAvailabilityLog($logMessage);
                
                // Se o componente não está disponível e é crítico, adicionar à lista de indisponíveis
                if (!$isAvailable && $component->is_critical) {
                    $unavailableComponents[] = [
                        'name' => $component->component->name ?? "Componente ID: {$component->component_id}",
                        'shortage' => $shortage
                    ];
                }
            }
            
            // Verificar se há componentes críticos indisponíveis
            if (!empty($unavailableComponents) && !$logOnly) {
                $componentsText = implode(', ', array_map(function($item) {
                    return "{$item['name']} (faltam {$item['shortage']} unidades)";
                }, $unavailableComponents));
                
                $message = "Componentes críticos indisponíveis: {$componentsText}";
                $this->addAvailabilityLog("ERRO: " . $message);
                
                Log::warning("Verificação de disponibilidade falhou para ordem #{$order->id}: {$message}", [
                    'unavailable_components' => $unavailableComponents
                ]);
                
                $this->verificationInProgress = false;
                return [
                    'success' => false,
                    'message' => $message
                ];
            }
            
            $this->addAvailabilityLog("Verificação concluída com sucesso! Todos os componentes críticos estão disponíveis.");
            
            Log::info("Verificação de disponibilidade concluída com sucesso para ordem #{$order->id}", [
                'components' => $this->availabilityComponents
            ]);
            
            $this->verificationInProgress = false;
            return [
                'success' => true,
                'message' => 'Todos os componentes necessários estão disponíveis.'
            ];
            
        } catch (\Exception $e) {
            $errorMessage = "Erro ao verificar disponibilidade: {$e->getMessage()}";
            $this->addAvailabilityLog("ERRO: " . $errorMessage);
            
            Log::error("Exceção durante verificação de disponibilidade para ordem #{$order->id}", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            $this->verificationInProgress = false;
            return [
                'success' => $logOnly, // Se estamos apenas logando, permitimos continuar
                'message' => $errorMessage
            ];
        }
    }
    
    /**
     * Adiciona uma entrada ao log de verificação de disponibilidade
     */
    private function addAvailabilityLog($message)
    {
        $timestamp = now()->format('H:i:s');
        $this->availabilityLog[] = "[{$timestamp}] {$message}";
        
        // Limitar o tamanho do log para evitar problemas de memória
        if (count($this->availabilityLog) > 100) {
            array_shift($this->availabilityLog);
        }
    }
    
    /**
     * Mostra o modal com detalhes da verificação de disponibilidade
     */
    public function showAvailabilityDetails($id)
    {
        $this->orderId = $id;
        $order = ProductionOrder::findOrFail($id);
        
        // Executar verificação de disponibilidade apenas para logs
        $this->verifyComponentsAvailability($order, true);
        
        $this->showAvailabilityModal = true;
    }
    
    /**
     * Atualizar status da ordem de produção
     */
    public function updateStatus($id, $status)
    {
        $order = ProductionOrder::findOrFail($id);
        
        // Verificar transições válidas de status
        $validTransitions = [
            'draft' => ['released', 'cancelled'],
            'released' => ['in_progress', 'cancelled'],
            'in_progress' => ['completed', 'cancelled'],
            'completed' => [],
            'cancelled' => ['draft']
        ];
        
        if (!in_array($status, $validTransitions[$order->status] ?? [])) {
            $this->dispatch('notify', [
                'type' => 'error',
                'title' => 'Transição inválida',
                'message' => "Não é possível alterar o status de '{$order->status}' para '{$status}'."
            ]);
            return;
        }
        
        // Verificar disponibilidade de componentes antes de mudar para 'released' ou 'in_progress'
        if (($status === 'released' || $status === 'in_progress') && $order->bomHeader) {
            Log::info("Iniciando verificação de disponibilidade para mudança de status da ordem #{$order->id}");
            
            $availabilityCheck = $this->verifyComponentsAvailability($order);
            
            if (!$availabilityCheck['success']) {
                $this->dispatch('notify', [
                    'type' => 'error',
                    'title' => 'Componentes Insuficientes',
                    'message' => $availabilityCheck['message']
                ]);
                return;
            }
            
            Log::info("Verificação de disponibilidade concluída com sucesso para mudança de status da ordem #{$order->id}");
        }
        
        // Atualizar campos adicionais baseado na transição de status
        if ($status === 'in_progress' && empty($order->actual_start_date)) {
            $order->actual_start_date = now();
        } else if ($status === 'completed' && empty($order->actual_end_date)) {
            $order->actual_end_date = now();
        }
        
        $order->status = $status;
        $order->updated_by = Auth::id();
        $order->save();
        
        $this->dispatch('notify', [
            'type' => 'success',
            'title' => 'Status Atualizado!',
            'message' => 'O status da ordem foi atualizado com sucesso.'
        ]);
    }
    
    /**
     * Atualizar quantidade produzida
     */
    public function updateQuantity($id, $field, $value)
    {
        if (!in_array($field, ['produced_quantity', 'rejected_quantity'])) {
            return;
        }
        
        $order = ProductionOrder::findOrFail($id);
        
        if (!in_array($order->status, ['in_progress', 'completed'])) {
            $this->dispatch('notify', [
                'type' => 'error',
                'title' => 'Operação inválida',
                'message' => 'Só é possível atualizar quantidades para ordens em andamento ou concluídas.'
            ]);
            return;
        }
        
        if ($value < 0) {
            $this->dispatch('notify', [
                'type' => 'error',
                'title' => 'Valor inválido',
                'message' => 'A quantidade não pode ser negativa.'
            ]);
            return;
        }
        
        $order->$field = $value;
        $order->updated_by = Auth::id();
        $order->save();
        
        $this->dispatch('notify', [
            'type' => 'success',
            'title' => 'Quantidade Atualizada!',
            'message' => 'A quantidade foi atualizada com sucesso.'
        ]);
    }
    
    /**
     * Salvar ordem de produção (criar ou atualizar)
     */
    public function save()
    {
        $this->validate();
        
        try {
            DB::beginTransaction();
            
            if ($this->editMode) {
                $order = ProductionOrder::findOrFail($this->orderId);
                $order->fill($this->order);
                $order->updated_by = Auth::id();
            } else {
                $order = new ProductionOrder($this->order);
                $order->created_by = Auth::id();
                $order->updated_by = Auth::id();
            }
            
            $order->save();
            
            // Verificar disponibilidade de componentes após salvar se o status não for 'draft' ou 'cancelled'
            if ($order->status !== 'draft' && $order->status !== 'cancelled' && $order->bomHeader) {
                Log::info("Verificando disponibilidade de componentes após salvar ordem #{$order->id}");
                $this->verifyComponentsAvailability($order, true); // Apenas para registro, não bloqueia o salvamento
            }
            
            DB::commit();
            
            $this->closeModal();
            
            $this->dispatch('notify', [
                'type' => 'success',
                'title' => 'Salvo com Sucesso!',
                'message' => 'Ordem de produção ' . ($this->editMode ? 'atualizada' : 'criada') . ' com sucesso.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Erro ao salvar ordem de produção: " . $e->getMessage(), [
                'order_data' => $this->order,
                'exception' => $e
            ]);
            $this->dispatch('notify', [
                'type' => 'error',
                'title' => 'Erro ao Salvar',
                'message' => 'Ocorreu um erro ao salvar a ordem de produção: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Carregar dados para a view
     */
    public function render()
    {
        // Construir a consulta para a listagem
        $query = ProductionOrder::with(['product', 'location', 'bomHeader', 'schedule'])
            ->when($this->search, function ($query, $search) {
                $query->where(function($q) use ($search) {
                    $q->where('order_number', 'like', "%{$search}%")
                      ->orWhereHas('product', function ($q) use ($search) {
                          $q->where('name', 'like', "%{$search}%")
                            ->orWhere('sku', 'like', "%{$search}%");
                      });
                });
            })
            ->when($this->statusFilter, function ($query, $status) {
                $query->where('status', $status);
            })
            ->when($this->priorityFilter, function ($query, $priority) {
                $query->where('priority', $priority);
            })
            ->when($this->dateFilter, function ($query, $date) {
                $query->whereDate('planned_start_date', '<=', $date)
                      ->whereDate('planned_end_date', '>=', $date);
            })
            ->when($this->productFilter, function ($query, $productId) {
                $query->where('product_id', $productId);
            })
            ->when($this->locationFilter, function ($query, $locationId) {
                $query->where('location_id', $locationId);
            })
            ->orderBy($this->sortField, $this->sortDirection);
        
        $productionOrders = $query->paginate($this->perPage);
        
        // Carregar dados para selects
        $products = Product::where('type', 'finished')->orderBy('name')->get();
        $locations = Location::orderBy('name')->get();
        
        // Selecionar ordem para visualização detalhada
        $selectedOrder = null;
        $orderMaterials = [];
        
        if ($this->orderId) {
            if ($this->showDetailsModal) {
                $selectedOrder = ProductionOrder::with(['product', 'location', 'bomHeader', 'schedule'])->find($this->orderId);
            }
            
            if ($this->showMaterialsModal || $this->showAvailabilityModal) {
                $selectedOrder = ProductionOrder::with(['bomHeader.details.component', 'product'])->find($this->orderId);
                
                if ($selectedOrder && $selectedOrder->bomHeader) {
                    $orderMaterials = $selectedOrder->bomHeader->details->map(function ($detail) use ($selectedOrder) {
                        $requiredQuantity = $detail->quantity * $selectedOrder->planned_quantity;
                        
                        // Buscar estoque disponível
                        $availableQuantity = 0;
                        if ($detail->component) {
                            // Tentar obter através do relacionamento inventoryItems
                            if (method_exists($detail->component, 'inventoryItems')) {
                                $availableQuantity = $detail->component->inventoryItems->sum('quantity_on_hand');
                            } else {
                                // Ou diretamente do atributo stock_quantity se disponível
                                $availableQuantity = $detail->component->stock_quantity ?? 0;
                            }
                            
                            // Se ainda for zero, tentar buscar diretamente
                            if ($availableQuantity == 0) {
                                $items = InventoryItem::where('product_id', $detail->component_id)->get();
                                $availableQuantity = $items->sum('quantity_on_hand');
                            }
                        }
                        
                        return [
                            'component' => $detail->component,
                            'quantity_per_unit' => $detail->quantity,
                            'uom' => $detail->uom,
                            'required_quantity' => $requiredQuantity,
                            'available_quantity' => $availableQuantity,
                            'shortage' => max(0, $requiredQuantity - $availableQuantity),
                            'is_critical' => $detail->is_critical,
                        ];
                    })->toArray();
                }
            }
        }
        
        // Definições para os selects
        $statuses = [
            'draft' => 'Rascunho',
            'released' => 'Liberada',
            'in_progress' => 'Em Andamento',
            'completed' => 'Concluída',
            'cancelled' => 'Cancelada'
        ];
        
        $priorities = [
            'low' => 'Baixa',
            'medium' => 'Média',
            'high' => 'Alta',
            'urgent' => 'Urgente'
        ];
        
        return view('livewire.mrp.production-orders', [
            'productionOrders' => $productionOrders,
            'products' => $products,
            'locations' => $locations,
            'statuses' => $statuses,
            'priorities' => $priorities,
            'selectedOrder' => $selectedOrder,
            'orderMaterials' => $orderMaterials
        ])->layout('layouts.livewire', [
            'title' => 'Ordens de Produção'
        ]);
    }
}
