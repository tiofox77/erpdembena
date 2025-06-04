<?php

namespace App\Livewire\Maintenance;

use App\Models\EquipmentPart;
use App\Models\Maintenance\EquipmentType;
use App\Models\Maintenance\MaintenanceEquipment;
use App\Models\StockTransaction;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class EquipmentParts extends Component
{
    use WithPagination;
    
    protected $paginationTheme = 'tailwind';
    
    // Propriedades gerais
    public $search = '';
    public $perPage = 10;
    public $sortField = 'name';
    public $sortDirection = 'asc';
    public $filters = [
        'equipment_id' => '',
        'equipment_type_id' => '',
        'status' => ''
    ];
    
    // Propriedades para modais
    public $showModal = false;
    public $showDeleteModal = false;
    public $showViewModal = false;
    public $editMode = false;
    public $part = [];
    public $equipments = [];
    public $equipmentTypes = [];
    public $partToDelete = null;
    public $partToView = null;
    
    // Propriedades para modal de estoque
    public $showStockModal = false;
    public $stockTransaction = [
        'type' => 'stock_in',
        'quantity' => 1,
        'notes' => '',
    ];
    public $currentPart = null;
    
    protected function rules()
    {
        return [
            'part.name' => 'required|string|max:100',
            'part.part_number' => 'nullable|string|max:50',
            'part.bar_code' => 'nullable|string|max:50',
            'part.description' => 'nullable|string',
            'part.stock_quantity' => 'required|integer|min:0',
            'part.unit_cost' => 'nullable|numeric|min:0',
            'part.last_restock_date' => 'nullable|date',
            'part.minimum_stock_level' => 'required|integer|min:0',
            'part.maintenance_equipment_id' => 'nullable|exists:maintenance_equipment,id',
            'part.equipment_type_id' => 'nullable|exists:equipment_types,id'
        ];
    }
    
    protected $messages = [
        'part.name.required' => 'O nome da peça é obrigatório',
        'part.stock_quantity.required' => 'A quantidade em estoque é obrigatória',
        'part.stock_quantity.min' => 'A quantidade em estoque não pode ser negativa',
        'part.minimum_stock_level.required' => 'O nível mínimo de estoque é obrigatório',
        'part.equipment_type_id.exists' => 'O tipo de equipamento selecionado não existe'
    ];
    
    public function mount()
    {
        $this->resetPart();
        $this->loadFormData();
    }
    
    public function loadFormData()
    {
        $this->equipments = MaintenanceEquipment::orderBy('name')->get();
        $this->equipmentTypes = EquipmentType::where('is_active', true)->orderBy('name')->get();
    }
    
    public function render()
    {
        $partsQuery = EquipmentPart::query()
            ->with(['equipment', 'equipmentType']);
            
        // Aplicar pesquisa
        if ($this->search) {
            $partsQuery->where(function($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('part_number', 'like', '%' . $this->search . '%')
                      ->orWhere('bar_code', 'like', '%' . $this->search . '%')
                      ->orWhere('description', 'like', '%' . $this->search . '%');
            });
        }
        
        // Aplicar filtros
        if (!empty($this->filters['equipment_id'])) {
            $partsQuery->where('maintenance_equipment_id', $this->filters['equipment_id']);
        }
        
        if (!empty($this->filters['equipment_type_id'])) {
            $partsQuery->where('equipment_type_id', $this->filters['equipment_type_id']);
        }
        
        if ($this->filters['status'] === 'low_stock') {
            $partsQuery->whereRaw('stock_quantity <= minimum_stock_level');
        } elseif ($this->filters['status'] === 'out_of_stock') {
            $partsQuery->where('stock_quantity', 0);
        }
        
        // Aplicar ordenação
        $parts = $partsQuery->orderBy($this->sortField, $this->sortDirection)
                         ->paginate($this->perPage);
        
        return view('livewire.maintenance.equipment-parts', [
            'parts' => $parts,
            'equipments' => $this->equipments,
            'equipmentTypes' => $this->equipmentTypes
        ]);
    }
    
    public function resetPart()
    {
        $this->part = [
            'name' => '',
            'part_number' => '',
            'bar_code' => '',
            'description' => '',
            'stock_quantity' => 0,
            'unit_cost' => null,
            'last_restock_date' => null,
            'minimum_stock_level' => 1,
            'maintenance_equipment_id' => null,
            'equipment_type_id' => null
        ];
        $this->resetValidation();
    }
    
    public function create()
    {
        $this->resetPart();
        
        // Carregar lista de tipos de equipamento
        $this->equipmentTypes = EquipmentType::where('is_active', true)->orderBy('name')->get();
        
        // Log para depuração
        Log::info('Abrindo modal de criação com tipos de equipamento:', [
            'tipos_count' => $this->equipmentTypes->count(),
            'primeiro_tipo' => $this->equipmentTypes->first() ? $this->equipmentTypes->first()->name : 'Nenhum'
        ]);
        
        $this->editMode = false;
        $this->showModal = true;
    }
    
    public function edit($id)
    {
        $this->resetValidation();
        $part = EquipmentPart::findOrFail($id);
        $this->part = $part->toArray();
        
        // Carregar lista de tipos de equipamento
        $this->equipmentTypes = EquipmentType::where('is_active', true)->orderBy('name')->get();
        
        // Log para depuração
        Log::info('Abrindo modal de edição para peça:', [
            'part_id' => $id,
            'part_name' => $part->name,
            'equipment_type_id' => $part->equipment_type_id,
            'tipos_count' => $this->equipmentTypes->count()
        ]);
        
        $this->editMode = true;
        $this->showModal = true;
    }
    
    public function save()
    {
        $this->validate();
        
        try {
            DB::beginTransaction();
            
            if ($this->editMode) {
                $part = EquipmentPart::findOrFail($this->part['id']);
                $oldQuantity = $part->stock_quantity;
                $newQuantity = $this->part['stock_quantity'];
                
                $part->update($this->part);
                
                // Criar um registro de transação de estoque se a quantidade foi alterada
                if ($oldQuantity != $newQuantity) {
                    $transactionType = ($newQuantity > $oldQuantity) ? 'stock_in' : 'stock_out';
                    $quantity = abs($newQuantity - $oldQuantity);
                    
                    if ($quantity > 0) {
                        StockTransaction::create([
                            'equipment_part_id' => $part->id,
                            'type' => $transactionType,
                            'quantity' => $quantity,
                            'date' => now(),
                            'notes' => 'Ajuste manual de estoque',
                            'created_by' => auth()->id() ?? 1
                        ]);
                    }
                }
                
                $this->dispatch('notify', 
                    type: 'warning', 
                    message: __('messages.part_updated', ['name' => $part->name])
                );
                
                Log::info('Equipment part updated', ['id' => $part->id, 'name' => $part->name]);
            } else {
                $part = EquipmentPart::create($this->part);
                
                // Criar um registro de transação de estoque para o estoque inicial
                if ($this->part['stock_quantity'] > 0) {
                    StockTransaction::create([
                        'equipment_part_id' => $part->id,
                        'type' => 'stock_in',
                        'quantity' => $this->part['stock_quantity'],
                        'date' => now(),
                        'notes' => 'Estoque inicial',
                        'created_by' => auth()->id() ?? 1
                    ]);
                }
                
                $this->dispatch('notify', 
                    type: 'success', 
                    message: __('messages.part_created', ['name' => $part->name])
                );
                
                Log::info('Equipment part created', ['id' => $part->id, 'name' => $part->name]);
            }
            
            DB::commit();
            $this->showModal = false;
            $this->resetPart();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error saving equipment part', [
                'error' => $e->getMessage(),
                'part' => $this->part
            ]);
            
            $this->dispatch('notify', 
                type: 'error', 
                message: __('messages.error_saving_part', ['error' => $e->getMessage()])
            );
        }
    }
    
    public function confirmDelete($id)
    {
        Log::info('Confirmando exclusão de peça', ['id' => $id]);
        
        try {
            $this->partToDelete = EquipmentPart::with('equipmentType')->findOrFail($id);
            $this->showDeleteModal = true;
            
            Log::info('Modal de confirmação de exclusão aberto');
        } catch (\Exception $e) {
            Log::error('Erro ao confirmar exclusão de peça', [
                'id' => $id,
                'erro' => $e->getMessage()
            ]);
            
            $this->dispatch('notify', 
                type: 'error', 
                message: __('messages.part_not_found')
            );
        }
    }
    
    public function delete()
    {
        Log::info('Excluindo peça de equipamento', ['id' => $this->partToDelete->id]);
        
        try {
            DB::beginTransaction();
            
            // Capturar o nome para usar na mensagem
            $partName = $this->partToDelete->name;

            // Excluir as transações de estoque relacionadas
            StockTransaction::where('equipment_part_id', $this->partToDelete->id)->delete();
            
            // Excluir a peça
            $this->partToDelete->delete();
            
            DB::commit();
            
            // Fechar o modal e resetar a propriedade
            $this->showDeleteModal = false;
            $this->partToDelete = null;
            
            // Notificar o usuário
            $this->dispatch('notify', 
                type: 'success', 
                message: __('messages.part_deleted_successfully', ['name' => $partName])
            );
            
            Log::info('Peça excluída com sucesso', ['nome' => $partName]);
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Erro ao excluir peça', ['erro' => $e->getMessage()]);
            
            $this->dispatch('notify', 
                type: 'error', 
                message: __('messages.error_deleting_part', ['error' => $e->getMessage()])
            );
        }
    }
    
    public function closeDeleteModal()
    {
        $this->showDeleteModal = false;
        $this->partToDelete = null;
    }
    
    public function openStockModal($partId)
    {
        $this->currentPart = EquipmentPart::findOrFail($partId);
        $this->stockTransaction = [
            'type' => 'stock_in',
            'quantity' => 1,
            'notes' => '',
        ];
        $this->showStockModal = true;
    }
    
    public function saveStockTransaction()
    {
        $this->validate([
            'stockTransaction.type' => 'required|in:stock_in,stock_out',
            'stockTransaction.quantity' => 'required|integer|min:1',
            'stockTransaction.notes' => 'nullable|string',
        ], [
            'stockTransaction.quantity.required' => 'A quantidade é obrigatória',
            'stockTransaction.quantity.min' => 'A quantidade deve ser pelo menos 1',
        ]);
        
        try {
            DB::beginTransaction();
            
            // Verificar se tem estoque suficiente para saída
            if ($this->stockTransaction['type'] === 'stock_out' && 
                $this->currentPart->stock_quantity < $this->stockTransaction['quantity']) {
                
                $this->dispatch('notify', 
                    type: 'error', 
                    message: __('messages.insufficient_stock')
                );
                return;
            }
            
            // Criar a transação de estoque
            StockTransaction::create([
                'equipment_part_id' => $this->currentPart->id,
                'type' => $this->stockTransaction['type'],
                'quantity' => $this->stockTransaction['quantity'],
                'date' => now(),
                'notes' => $this->stockTransaction['notes'],
                'created_by' => auth()->id() ?? 1
            ]);
            
            // Atualizar o estoque da peça
            $newQuantity = $this->currentPart->stock_quantity;
            if ($this->stockTransaction['type'] === 'stock_in') {
                $newQuantity += $this->stockTransaction['quantity'];
                $message = __('messages.stock_added', [
                    'quantity' => $this->stockTransaction['quantity'], 
                    'part' => $this->currentPart->name
                ]);
            } else {
                $newQuantity -= $this->stockTransaction['quantity'];
                $message = __('messages.stock_removed', [
                    'quantity' => $this->stockTransaction['quantity'], 
                    'part' => $this->currentPart->name
                ]);
            }
            
            // Atualizar o estoque e a data de última reposição se for uma entrada
            $this->currentPart->stock_quantity = $newQuantity;
            if ($this->stockTransaction['type'] === 'stock_in') {
                $this->currentPart->last_restock_date = now();
            }
            $this->currentPart->save();
            
            DB::commit();
            
            $this->dispatch('notify', 
                type: 'success', 
                message: $message
            );
            
            Log::info('Stock transaction created', [
                'part_id' => $this->currentPart->id,
                'type' => $this->stockTransaction['type'],
                'quantity' => $this->stockTransaction['quantity']
            ]);
            
            $this->showStockModal = false;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating stock transaction', [
                'error' => $e->getMessage(),
                'part_id' => $this->currentPart ? $this->currentPart->id : null,
                'stockTransaction' => $this->stockTransaction
            ]);
            
            $this->dispatch('notify', 
                type: 'error', 
                message: __('messages.error_creating_stock_transaction')
            );
        }
    }
    
    public function closeModal()
    {
        $this->showModal = false;
        $this->showStockModal = false;
        $this->resetPart();
        $this->currentPart = null;
    }
    
    /**
     * Abre o modal para visualizar detalhes da peça
     */
    public function view($id)
    {
        Log::info('Visualizando detalhes da peça', ['id' => $id]);
        
        try {
            $this->partToView = EquipmentPart::with(['equipment', 'equipmentType'])->findOrFail($id);
            $this->showViewModal = true;
            
            Log::info('Detalhes da peça carregados com sucesso');
        } catch (\Exception $e) {
            Log::error('Erro ao visualizar peça', [
                'id' => $id,
                'erro' => $e->getMessage()
            ]);
            
            $this->dispatch('notify', 
                type: 'error', 
                message: __('messages.part_not_found')
            );
        }
    }
    
    /**
     * Fecha o modal de visualização
     */
    public function closeViewModal()
    {
        $this->showViewModal = false;
        $this->partToView = null;
    }
    
    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }
    
    public function updatingSearch()
    {
        $this->resetPage();
    }
    
    public function updatingFilters()
    {
        $this->resetPage();
    }
    
    public function resetFilters()
    {
        $this->reset('filters');
        $this->filters = [
            'equipment_id' => '',
            'equipment_type_id' => '',
            'status' => ''
        ];
        $this->resetPage();
    }
}
