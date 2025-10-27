# Sistema de Filtros e Busca

## 1. Implementação de Filtros e Busca

O ERP DEMBENA utiliza um sistema padronizado de filtros e busca em todas as listagens, facilitando a navegação e localização de informações pelos usuários.

### 1.1 Estrutura de Filtros em Componentes Livewire

```php
// Propriedades para filtros e pesquisa
#[Url(history: true)]
public $search = '';

#[Url(history: true)]
public $status = '';

#[Url(history: true)]
public $dateFrom = '';

#[Url(history: true)]
public $dateTo = '';

public $perPage = 10;
```

### 1.2 Template Padrão de Filtros

```blade
<div class="p-4 sm:p-6 bg-white border-b border-gray-200">
    <div class="grid grid-cols-1 sm:grid-cols-3 md:grid-cols-4 gap-4">
        <!-- Search -->
        <div>
            <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Busca</label>
            <input
                type="text"
                id="search"
                wire:model.live.debounce.300ms="search"
                placeholder="Buscar por referência, nome..."
                class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"
            >
        </div>

        <!-- Status Filter -->
        <div>
            <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
            <select
                id="status"
                wire:model.live="status"
                class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"
            >
                <option value="">Todos os Status</option>
                @foreach($statusOptions as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <!-- Date From -->
        <div>
            <label for="dateFrom" class="block text-sm font-medium text-gray-700 mb-1">Data Inicial</label>
            <input
                type="date"
                id="dateFrom"
                wire:model.live="dateFrom"
                class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"
            >
        </div>

        <!-- Date To -->
        <div>
            <label for="dateTo" class="block text-sm font-medium text-gray-700 mb-1">Data Final</label>
            <input
                type="date"
                id="dateTo"
                wire:model.live="dateTo"
                class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"
            >
        </div>
    </div>

    <!-- Second row with Per Page and Clear Filters -->
    <div class="mt-4 flex flex-col sm:flex-row justify-between items-center">
        <!-- Items Per Page -->
        <div class="w-full sm:w-auto mb-3 sm:mb-0">
            <label for="perPage" class="block text-sm font-medium text-gray-700 mb-1">Itens por Página</label>
            <select
                id="perPage"
                wire:model.live="perPage"
                class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:w-auto sm:text-sm border-gray-300 rounded-md"
            >
                <option value="10">10 por página</option>
                <option value="25">25 por página</option>
                <option value="50">50 por página</option>
                <option value="100">100 por página</option>
            </select>
        </div>

        <!-- Clear Filters Button -->
        <button 
            wire:click="clearFilters" 
            class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
        >
            <i class="fas fa-times-circle mr-2"></i> Limpar Filtros
        </button>
    </div>
</div>
```

### 1.3 Métodos para Gerenciar Filtros

```php
// Resetar paginação quando algum filtro é alterado
public function updatedSearch()
{
    $this->resetPage();
}

public function updatedStatus()
{
    $this->resetPage();
}

public function updatedDateFrom()
{
    $this->resetPage();
}

public function updatedDateTo()
{
    $this->resetPage();
}

public function updatedPerPage()
{
    $this->resetPage();
}

// Limpar todos os filtros
public function clearFilters()
{
    $this->search = '';
    $this->status = '';
    $this->dateFrom = '';
    $this->dateTo = '';
    $this->resetPage();
}
```

### 1.4 Aplicação de Filtros na Consulta

```php
public function render()
{
    $query = Model::query()
        ->when($this->search, function ($query, $search) {
            return $query->where(function ($query) use ($search) {
                $query->where('reference_number', 'like', '%'.$search.'%')
                    ->orWhere('description', 'like', '%'.$search.'%')
                    ->orWhereHas('relatedModel', function ($query) use ($search) {
                        $query->where('name', 'like', '%'.$search.'%');
                    });
            });
        })
        ->when($this->status, function ($query, $status) {
            return $query->where('status', $status);
        })
        ->when($this->dateFrom, function ($query, $dateFrom) {
            return $query->whereDate('created_at', '>=', $dateFrom);
        })
        ->when($this->dateTo, function ($query, $dateTo) {
            return $query->whereDate('created_at', '<=', $dateTo);
        })
        ->orderBy($this->sortField, $this->sortDirection);
    
    $records = $query->paginate($this->perPage);
    
    return view('livewire.module-name', [
        'records' => $records,
        'statusOptions' => [
            'pending' => 'Pendente',
            'approved' => 'Aprovado',
            'rejected' => 'Rejeitado',
            // ...
        ]
    ]);
}
```
