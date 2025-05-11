<?php

namespace App\Livewire\Mrp;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Mrp\InventoryLevel;
use App\Models\SupplyChain\Product;
use App\Models\SupplyChain\InventoryLocation as Location;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class InventoryLevels extends Component
{
    use WithPagination;
    
    // Propriedades do componente
    public $search = '';
    public $sortField = 'created_at';
    public $sortDirection = 'desc';
    public $perPage = 10;
    
    // Propriedades para modal
    public $showModal = false;
    public $showDeleteModal = false;
    public $showViewModal = false; // Modal de visualização
    public $editMode = false;
    public $inventoryLevelId = null;
    public $uom = 'un'; // Unidade de medida padrão
    public $selectedLevel = null; // Nível de inventário selecionado para exclusão
    public $viewingLevel = null; // Nível de inventário sendo visualizado
    public $unitTypes = []; // Tipos de unidade disponíveis
    
    // Propriedades do formulário
    public $inventoryLevel = [
        'product_id' => '',
        'location_id' => '',
        'safety_stock' => 0,
        'reorder_point' => 0,
        'maximum_stock' => null,
        'economic_order_quantity' => null,
        'lead_time_days' => 0,
        'daily_usage_rate' => null,
        'abc_classification' => null,
        'notes' => ''
    ];
    
    // Propriedades de filtro
    public $classificationFilter = null;
    public $locationFilter = null;
    public $productFilter = null;
    
    /**
     * Inicializar o componente
     */
    public function mount()
    {
        // Inicializar tipos de unidade a partir do modelo UnitType
        $unitTypes = \App\Models\UnitType::getActive();
        
        // Converter para array associativo [valor => rótulo]
        $this->unitTypes = [];
        foreach ($unitTypes as $unitType) {
            $this->unitTypes[$unitType->symbol] = $unitType->name;
        }
        
        // Se não houver tipos de unidade, usar valores padrão
        if (empty($this->unitTypes)) {
            $this->unitTypes = [
                'un' => __('messages.unit'),
                'kg' => __('messages.kilogram'),
                'g' => __('messages.gram'),
                'l' => __('messages.liter'),
                'ml' => __('messages.milliliter'),
                'pcs' => __('messages.pieces')
            ];
        }
    }
    
    /**
     * Regras de validação
     */
    protected function rules()
    {
        return [
            'inventoryLevel.product_id' => 'required|exists:sc_products,id',
            'inventoryLevel.location_id' => [
                'required',
                'exists:sc_inventory_locations,id',
                function ($attribute, $value, $fail) {
                    // Verificar se já existe uma configuração para este produto e localização
                    $query = InventoryLevel::where('product_id', $this->inventoryLevel['product_id'])
                        ->where('location_id', $value);
                    
                    if ($this->editMode) {
                        $query->where('id', '!=', $this->inventoryLevelId);
                    }
                    
                    if ($query->exists()) {
                        $fail('Já existe uma configuração para este produto e localização.');
                    }
                },
            ],
            'inventoryLevel.safety_stock' => 'required|numeric|min:0',
            'inventoryLevel.reorder_point' => 'required|numeric|min:0',
            'inventoryLevel.maximum_stock' => 'nullable|numeric|min:0|gte:inventoryLevel.reorder_point',
            'inventoryLevel.economic_order_quantity' => 'nullable|numeric|min:0',
            'inventoryLevel.lead_time_days' => 'required|integer|min:0',
            'inventoryLevel.daily_usage_rate' => 'nullable|numeric|min:0',
            'inventoryLevel.abc_classification' => ['nullable', Rule::in(['A', 'B', 'C'])],
            'inventoryLevel.notes' => 'nullable|string|max:1000',
        ];
    }
    
    /**
     * Messages de validação customizadas
     */
    protected function messages()
    {
        return [
            'inventoryLevel.product_id.required' => 'O produto é obrigatório.',
            'inventoryLevel.location_id.required' => 'A localização é obrigatória.',
            'inventoryLevel.safety_stock.required' => 'O estoque de segurança é obrigatório.',
            'inventoryLevel.safety_stock.min' => 'O estoque de segurança deve ser maior ou igual a zero.',
            'inventoryLevel.reorder_point.required' => 'O ponto de reposição é obrigatório.',
            'inventoryLevel.reorder_point.min' => 'O ponto de reposição deve ser maior ou igual a zero.',
            'inventoryLevel.maximum_stock.min' => 'O estoque máximo deve ser maior ou igual a zero.',
            'inventoryLevel.maximum_stock.gte' => 'O estoque máximo deve ser maior ou igual ao ponto de reposição.',
            'inventoryLevel.economic_order_quantity.min' => 'A quantidade econômica de pedido deve ser maior ou igual a zero.',
            'inventoryLevel.lead_time_days.required' => 'O tempo de reposição é obrigatório.',
            'inventoryLevel.lead_time_days.min' => 'O tempo de reposição deve ser maior ou igual a zero.',
            'inventoryLevel.daily_usage_rate.min' => 'A taxa de uso diário deve ser maior ou igual a zero.',
        ];
    }
    
    /**
     * Resetar paginação quando a busca mudar
     */
    public function updatingSearch()
    {
        $this->resetPage();
    }
    
    /**
     * Atualizar unidade de medida quando o produto é alterado
     */
    public function updatedInventoryLevelProductId($value)
    {
        if (!empty($value)) {
            $product = Product::find($value);
            if ($product) {
                $this->uom = $product->uom ?? 'un';
            }
        } else {
            $this->uom = 'un';
        }
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
        $this->search = '';
        $this->classificationFilter = null;
        $this->locationFilter = null;
        $this->productFilter = null;
        $this->resetPage();
    }
    
    /**
     * Calcular o ponto de reposição
     */
    public function calculateReorderPoint()
    {
        // Se tivermos taxa de uso diário e tempo de reposição, podemos calcular o ponto de reposição
        if (is_numeric($this->inventoryLevel['daily_usage_rate']) && is_numeric($this->inventoryLevel['lead_time_days']) && is_numeric($this->inventoryLevel['safety_stock'])) {
            $this->inventoryLevel['reorder_point'] = ($this->inventoryLevel['daily_usage_rate'] * $this->inventoryLevel['lead_time_days']) + $this->inventoryLevel['safety_stock'];
        }
    }
    
    /**
     * Abrir modal para criar nova configuração de nível de estoque
     */
    public function create()
    {
        $this->resetValidation();
        $this->reset('inventoryLevel');
        $this->inventoryLevel['safety_stock'] = 0;
        $this->inventoryLevel['reorder_point'] = 0;
        $this->inventoryLevel['lead_time_days'] = 0;
        $this->uom = 'un'; // Reiniciar a unidade de medida para o valor padrão
        $this->editMode = false;
        $this->showModal = true;
    }
    
    /**
     * Carregar e abrir modal para editar configuração de nível de estoque
     */
    public function edit($id)
    {
        $this->resetValidation();
        $this->inventoryLevelId = $id;
        $inventoryLevel = InventoryLevel::findOrFail($id);
        
        $this->inventoryLevel = [
            'product_id' => $inventoryLevel->product_id,
            'location_id' => $inventoryLevel->location_id,
            'safety_stock' => $inventoryLevel->safety_stock,
            'reorder_point' => $inventoryLevel->reorder_point,
            'maximum_stock' => $inventoryLevel->maximum_stock,
            'economic_order_quantity' => $inventoryLevel->economic_order_quantity,
            'lead_time_days' => $inventoryLevel->lead_time_days,
            'daily_usage_rate' => $inventoryLevel->daily_usage_rate,
            'abc_classification' => $inventoryLevel->abc_classification,
            'notes' => $inventoryLevel->notes
        ];
        
        // Carregar a unidade de medida do produto
        if ($inventoryLevel->product_id) {
            $product = Product::find($inventoryLevel->product_id);
            if ($product) {
                $this->uom = $product->uom ?? 'un';
            }
        }
        
        $this->editMode = true;
        $this->showModal = true;
    }
    
    /**
     * Confirmar exclusão de configuração
     */
    public function confirmDelete($id)
    {
        $this->inventoryLevelId = $id;
        $this->selectedLevel = InventoryLevel::with('product')->findOrFail($id);
        $this->showDeleteModal = true;
    }
    
    /**
     * Excluir configuração de nível de estoque
     */
    public function delete()
    {
        $inventoryLevel = InventoryLevel::findOrFail($this->inventoryLevelId);
        $inventoryLevel->delete();
        
        $this->showDeleteModal = false;
        $this->inventoryLevelId = null;
        
        $this->dispatch('notify', 
            type: 'success',
            title: 'Exclusão realizada!',
            message: 'A configuração de nível de estoque foi excluída com sucesso.'
        );
    }
    
    /**
     * Abrir modal de visualização
     */
    public function view($id)
    {
        $this->viewingLevel = InventoryLevel::with(['product.category', 'location'])->findOrFail($id);
        $this->showViewModal = true;
    }

    /**
     * Fechar modal de visualização
     */
    public function closeViewModal()
    {
        $this->showViewModal = false;
        $this->viewingLevel = null;
    }

    /**
     * Fechar modal
     */
    public function closeModal()
    {
        $this->showModal = false;
        $this->showDeleteModal = false;
        $this->selectedLevel = null; // Limpar o nível selecionado para exclusão
    }
    
    /**
     * Salvar configuração de nível de estoque (criar ou atualizar)
     */
    public function save()
    {
        $this->validate();
        
        if ($this->editMode) {
            $inventoryLevel = InventoryLevel::findOrFail($this->inventoryLevelId);
            $inventoryLevel->fill($this->inventoryLevel);
            $inventoryLevel->updated_by = Auth::id();
            $inventoryLevel->save();
            
            $this->dispatch('notify', 
                type: 'success',
                title: 'Configuração atualizada!',
                message: 'A configuração de nível de estoque foi atualizada com sucesso.'
            );
        } else {
            $inventoryLevel = new InventoryLevel($this->inventoryLevel);
            $inventoryLevel->created_by = Auth::id();
            $inventoryLevel->updated_by = Auth::id();
            $inventoryLevel->save();
            
            $this->dispatch('notify', 
                type: 'success',
                title: 'Configuração criada!',
                message: 'A configuração de nível de estoque foi criada com sucesso.'
            );
        }
        
        $this->closeModal();
    }
    
    /**
     * Carregar dados para a view
     */
    public function render()
    {
        $query = InventoryLevel::with(['product', 'location'])
            ->when($this->search, function ($query, $search) {
                $query->whereHas('product', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('sku', 'like', "%{$search}%");
                });
            })
            ->when($this->classificationFilter, function ($query, $classification) {
                $query->where('abc_classification', $classification);
            })
            ->when($this->locationFilter, function ($query, $locationId) {
                $query->where('location_id', $locationId);
            })
            ->when($this->productFilter, function ($query, $productId) {
                $query->where('product_id', $productId);
            })
            ->orderBy($this->sortField, $this->sortDirection);
        
        $inventoryLevels = $query->paginate($this->perPage);
        
        $products = Product::orderBy('name')->get();
        $locations = Location::orderBy('name')->get();
        
        $classifications = [
            'A' => 'Classe A (Alto valor)',
            'B' => 'Classe B (Médio valor)',
            'C' => 'Classe C (Baixo valor)'
        ];
        
        return view('livewire.mrp.inventory-levels', [
            'inventoryLevels' => $inventoryLevels,
            'products' => $products,
            'locations' => $locations,
            'classifications' => $classifications
        ])->layout('layouts.livewire', [
            'title' => 'Gestão de Níveis de Estoque'
        ]);
    }
}
