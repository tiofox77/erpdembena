<?php

namespace App\Livewire\Mrp;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Mrp\DemandForecast;
use App\Models\SupplyChain\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class DemandForecasting extends Component
{
    use WithPagination;
    
    // Propriedades do componente
    public $search = '';
    public $sortField = 'forecast_date';
    public $sortDirection = 'asc';
    public $perPage = 10;
    
    // Propriedades para modal
    public $showModal = false;
    public $showDeleteModal = false;
    public $editMode = false;
    public $forecastId = null;
    
    // Propriedades do formulário
    public $forecast = [
        'product_id' => '',
        'forecast_date' => '',
        'forecast_quantity' => '',
        'confidence_level' => '',
        'forecast_type' => 'manual',
        'notes' => ''
    ];
    
    // Propriedades de filtro
    public $dateFilter = null;
    public $productFilter = null;
    public $typeFilter = null;
    
    /**
     * Regras de validação
     */
    protected function rules()
    {
        return [
            'forecast.product_id' => 'required|exists:sc_products,id',
            'forecast.forecast_date' => 'required|date',
            'forecast.forecast_quantity' => 'required|numeric|min:0',
            'forecast.confidence_level' => 'nullable|numeric|min:0|max:100',
            'forecast.forecast_type' => ['required', Rule::in(['manual', 'automatic', 'adjusted'])],
            'forecast.notes' => 'nullable|string|max:1000',
        ];
    }
    
    /**
     * Messages de validação customizadas
     */
    protected function messages()
    {
        return [
            'forecast.product_id.required' => 'O produto é obrigatório.',
            'forecast.forecast_date.required' => 'A data de previsão é obrigatória.',
            'forecast.forecast_quantity.required' => 'A quantidade prevista é obrigatória.',
            'forecast.forecast_quantity.min' => 'A quantidade prevista deve ser maior ou igual a zero.',
            'forecast.confidence_level.min' => 'O nível de confiança deve ser entre 0 e 100.',
            'forecast.confidence_level.max' => 'O nível de confiança deve ser entre 0 e 100.',
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
        $this->dateFilter = null;
        $this->productFilter = null;
        $this->typeFilter = null;
        $this->resetPage();
    }
    
    /**
     * Abrir modal para criar nova previsão
     */
    public function create()
    {
        $this->resetValidation();
        $this->reset('forecast');
        $this->forecast['forecast_date'] = date('Y-m-d');
        $this->forecast['forecast_type'] = 'manual';
        $this->editMode = false;
        $this->showModal = true;
    }
    
    /**
     * Carregar e abrir modal para editar previsão existente
     */
    public function edit($id)
    {
        $this->resetValidation();
        $this->forecastId = $id;
        $forecast = DemandForecast::findOrFail($id);
        
        $this->forecast = [
            'product_id' => $forecast->product_id,
            'forecast_date' => $forecast->forecast_date->format('Y-m-d'),
            'forecast_quantity' => $forecast->forecast_quantity,
            'confidence_level' => $forecast->confidence_level,
            'forecast_type' => $forecast->forecast_type,
            'notes' => $forecast->notes
        ];
        
        $this->editMode = true;
        $this->showModal = true;
    }
    
    /**
     * Confirmar exclusão de previsão
     */
    public function confirmDelete($id)
    {
        $this->forecastId = $id;
        $this->showDeleteModal = true;
    }
    
    /**
     * Excluir previsão de demanda
     */
    public function delete()
    {
        $forecast = DemandForecast::findOrFail($this->forecastId);
        $forecast->delete();
        
        $this->showDeleteModal = false;
        $this->forecastId = null;
        
        $this->dispatch('notify', 
            type: 'success',
            title: 'Exclusão realizada!',
            message: 'A previsão de demanda foi excluída com sucesso.'
        );
    }
    
    /**
     * Fechar modal
     */
    public function closeModal()
    {
        $this->showModal = false;
        $this->showDeleteModal = false;
    }
    
    /**
     * Salvar previsão de demanda (criar ou atualizar)
     */
    public function save()
    {
        $this->validate();
        
        // Process data before saving - convert empty values to null
        if (isset($this->forecast['confidence_level']) && $this->forecast['confidence_level'] === '') {
            $this->forecast['confidence_level'] = null;
        }
        
        if ($this->editMode) {
            $forecast = DemandForecast::findOrFail($this->forecastId);
            $forecast->fill($this->forecast);
            $forecast->updated_by = Auth::id();
            $forecast->save();
            
            $this->dispatch('notify', 
                type: 'success',
                title: 'Previsão atualizada!',
                message: 'A previsão de demanda foi atualizada com sucesso.'
            );
        } else {
            $forecast = new DemandForecast($this->forecast);
            $forecast->created_by = Auth::id();
            $forecast->updated_by = Auth::id();
            $forecast->save();
            
            $this->dispatch('notify', 
                type: 'success',
                title: 'Previsão criada!',
                message: 'A previsão de demanda foi criada com sucesso.'
            );
        }
        
        $this->closeModal();
    }
    
    /**
     * Carregar dados para a view
     */
    public function render()
    {
        $query = DemandForecast::with(['product'])
            ->when($this->search, function ($query, $search) {
                $query->whereHas('product', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('sku', 'like', "%{$search}%");
                });
            })
            ->when($this->dateFilter, function ($query, $date) {
                $query->where('forecast_date', $date);
            })
            ->when($this->productFilter, function ($query, $productId) {
                $query->where('product_id', $productId);
            })
            ->when($this->typeFilter, function ($query, $type) {
                $query->where('forecast_type', $type);
            })
            ->orderBy($this->sortField, $this->sortDirection);
        
        $forecasts = $query->paginate($this->perPage);
        $products = Product::orderBy('name')->get();
        
        $forecastTypes = [
            'manual' => 'Manual',
            'automatic' => 'Automático',
            'adjusted' => 'Ajustado'
        ];
        
        return view('livewire.mrp.demand-forecasting', [
            'forecasts' => $forecasts,
            'products' => $products,
            'forecastTypes' => $forecastTypes
        ])->layout('layouts.livewire', [
            'title' => 'Planejamento de Demanda'
        ]);
    }
}
