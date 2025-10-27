# Documentação Técnica: ERP DEMBENA

## Índice

1. [Estrutura e Fundamentos do Sistema](#estrutura-e-fundamentos-do-sistema)
2. [Sistema de Modais](#sistema-de-modais)
3. [Sistema de Notificações](#sistema-de-notificações)
4. [Sistema de Geração de PDFs](#sistema-de-geração-de-pdfs)
5. [Sistema de Filtros e Busca](#sistema-de-filtros-e-busca)
6. [Layout de Tabelas e Componentes Visuais](#layout-de-tabelas-e-componentes-visuais)
7. [CRUD Operations](#crud-operations)

## Estrutura e Fundamentos do Sistema

### 1. Arquitetura Geral

O ERP DEMBENA é um sistema de gestão empresarial desenvolvido com Laravel e Livewire, utilizando um design moderno e responsivo com TailwindCSS. A aplicação segue o padrão MVC (Model-View-Controller) adaptado para o Livewire, onde cada módulo funcional é implementado como um componente Livewire independente.

#### Tecnologias Principais:
- **Backend**: Laravel 10+
- **Frontend**: Livewire 3, Alpine.js, TailwindCSS
- **Banco de Dados**: MySQL
- **Geração de PDFs**: Laravel DomPDF
- **Ícones**: Font Awesome
- **Notificações**: Toastr.js

### 2. Estrutura de Diretórios

```
- app/
  |- Http/
  |- Livewire/    # Componentes Livewire para cada módulo do sistema
  |- Models/       # Modelos Eloquent
  |- Providers/
- config/
- database/
  |- migrations/   # Migrações para criar e modificar tabelas
  |- seeders/      # Seeds para dados iniciais
- public/
  |- css/
  |- js/
  |- storage/      # Arquivos enviados pelos usuários (imagens, etc.)
- resources/
  |- views/
     |- livewire/  # Templates Blade para componentes Livewire
     |- layouts/   # Layouts compartilhados
- routes/
  |- web.php      # Definição de rotas
```

## Sistema de Modais

O ERP DEMBENA utiliza um sistema de modais reutilizáveis para operações CRUD e visualizações detalhadas. Os modais são implementados através de um componente específico e Alpine.js.

### 1. Componente ModalComponent

```php
namespace App\Livewire\Components;

use Livewire\Component;

class ModalComponent extends Component
{
    public $showModal = false;
    public $modalTitle = '';
    public $modalSize = 'md'; // sm, md, lg, xl, full
    public $modalContent = '';
    
    public function openModal($title = '', $size = 'md', $params = [])
    {
        $this->showModal = true;
        $this->modalTitle = $title;
        $this->modalSize = $size;
        
        // Opcionalmente passa parâmetros adicionais
        foreach ($params as $key => $value) {
            $this->{$key} = $value;
        }
    }
    
    public function closeModal()
    {
        $this->showModal = false;
        $this->reset('modalTitle', 'modalSize');
    }
    
    public function render()
    {
        return view('livewire.components.modal-component');
    }
}
```

### 2. Template do Modal (Blade)

```html
<!-- resources/views/livewire/components/modal-component.blade.php -->
@if($showModal)
    <div class="fixed z-10 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <!-- Background overlay -->
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>

            <!-- Modal panel -->
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle {{ $modalSize === 'sm' ? 'sm:max-w-sm' : ($modalSize === 'md' ? 'sm:max-w-lg' : ($modalSize === 'lg' ? 'sm:max-w-2xl' : ($modalSize === 'xl' ? 'sm:max-w-4xl' : 'sm:max-w-full'))) }} sm:w-full">
                <!-- Modal header -->
                <div class="bg-gray-50 px-4 py-3 border-b border-gray-200 sm:px-6 flex justify-between items-center">
                    <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                        {{ $modalTitle }}
                    </h3>
                    <button 
                        type="button" 
                        wire:click="closeModal" 
                        class="bg-white rounded-md text-gray-400 hover:text-gray-500 focus:outline-none"
                    >
                        <span class="sr-only">Close</span>
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <!-- Modal content -->
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    {{ $slot }}
                </div>
                
                <!-- Modal footer (slots opcionais) -->
                @isset($footer)
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        {{ $footer }}
                    </div>
                @endisset
            </div>
        </div>
    </div>
@endif
```

### 3. Como Utilizar o Modal

```php
// Em qualquer componente Livewire
public function openModal()
{
    $this->showModal = true;
    $this->isEditing = false;
    $this->resetForm();
}

public function closeModal()
{
    $this->showModal = false;
    $this->reset('isEditing', 'recordId');
}
```

## Sistema de Notificações

### 1. Estrutura de Notificações Toast

O ERP DEMBENA utiliza um sistema de notificações toast para fornecer feedback ao usuário sobre ações realizadas. Essas notificações são implementadas usando o Livewire para disparar eventos e JavaScript/CSS para exibi-las.

### 1.1 Dispatcher de Notificações

```php
// Em qualquer componente Livewire
public function save()
{
    try {
        // Lógica de salvamento
        $this->dispatch('notify', type: 'success', message: 'Registro salvo com sucesso!');
    } catch (\Exception $e) {
        $this->dispatch('notify', type: 'error', message: 'Erro ao salvar: ' . $e->getMessage());
    }
}
```

### 1.2 Listener JavaScript (app.js)

```javascript
// Em resources/js/app.js
document.addEventListener('livewire:initialized', () => {
    Livewire.on('notify', (params) => {
        toastr[params.type](params.message);
    });
});
```

### 1.3 Configuração do Toastr

```javascript
// Em resources/js/app.js ou em um arquivo separado
toastr.options = {
    closeButton: true,
    progressBar: true,
    positionClass: "toast-top-right",
    timeOut: 5000,
    extendedTimeOut: 1000
};
```

## Sistema de Geração de PDFs

### 1. Estrutura do Método de Geração de PDFs

O sistema usa o pacote Laravel DomPDF para gerar relatórios em PDF tanto para registros individuais quanto para listas de registros com filtros aplicados.

#### 1.1 Método Padrão de Geração de PDF

```php
public function generatePdf($id = null)
{
    try {
        // Para PDFs individuais (com ID específico)
        if ($id) {
            $transaction = StockTransaction::with(['part', 'createdBy'])
                ->where('type', 'stock_in') // ou 'stock_out' dependendo do componente
                ->findOrFail($id);
            $pdf = Pdf::loadView('livewire.stocks.stock-in-pdf', [
                'transaction' => $transaction
            ]);
            return $pdf->download('stock_in_' . $id . '.pdf');
        } 
        // Para relatórios de múltiplos registros (filtrados)
        else {
            // Aplicar filtros na consulta
            $query = StockTransaction::with(['part', 'part.equipment', 'createdBy'])
                ->when($this->search, function ($query, $search) {
                    return $query->where('reference_number', 'like', '%'.$search.'%')
                        ->orWhereHas('part', function ($query) use ($search) {
                            $query->where('name', 'like', '%'.$search.'%');
                        });
                })
                ->when($this->status, function ($query, $status) {
                    return $query->where('status', $status);
                })
                ->orderBy($this->sortField, $this->sortDirection);
            
            $transactions = $query->get();
            
            // Dados adicionais para o relatório
            $data = [
                'transactions' => $transactions,
                'filters' => [
                    'dateFrom' => $this->dateFrom,
                    'dateTo' => $this->dateTo,
                    'search' => $this->search,
                    'status' => $this->status
                ],
                'reportTitle' => 'Relatório de Estoque',
                'companyName' => Setting::get('company_name', 'ERP DEMBENA'),
                'companyAddress' => Setting::get('company_address', ''),
                'companyPhone' => Setting::get('company_phone', ''),
                'companyEmail' => Setting::get('company_email', ''),
                'generatedBy' => auth()->user()->name,
                'generatedAt' => now()->format('d/m/Y H:i:s')
            ];
            
            $pdf = Pdf::loadView('livewire.stocks.stock-in-list-pdf', $data);
            return $pdf->download('stock_in_report_' . date('Y-m-d') . '.pdf');
        }
    } catch (\Exception $e) {
        $this->dispatch('notify', type: 'error', message: 'Erro ao gerar PDF: ' . $e->getMessage());
    }
}
```

### 2. Templates PDF

#### 2.1 Estrutura do Template PDF

```blade
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $reportTitle ?? 'Relatório' }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.5;
        }
        .logo {
            max-height: 70px;
            max-width: 220px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
        }
        .footer {
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
            margin-top: 30px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
            text-align: left;
            padding: 8px;
        }
        td {
            padding: 8px;
        }
        .info-section {
            margin-bottom: 20px;
        }
        .info-label {
            font-weight: bold;
        }
        .page-number {
            position: absolute;
            bottom: 10px;
            right: 10px;
            font-size: 10px;
        }
    </style>
</head>
<body>
    <!-- Cabeçalho com logo da empresa -->
    <div class="header">
        @php
            $logoPath = \App\Models\Setting::get('company_logo');
            $logoFullPath = $logoPath ? public_path('storage/' . $logoPath) : public_path('img/logo.png');
            $companyName = \App\Models\Setting::get('company_name', 'ERP DEMBENA');
        @endphp
        <img src="{{ $logoFullPath }}" alt="{{ $companyName }} Logo" class="logo">
        <h1>{{ $reportTitle ?? 'Detalhes do Registro' }}</h1>
    </div>

    <!-- Informações do documento -->
    <div class="info-section">
        <p><span class="info-label">Data de emissão:</span> {{ now()->format('d/m/Y H:i') }}</p>
        <p><span class="info-label">Gerado por:</span> {{ auth()->user()->name ?? 'Sistema' }}</p>
        @if(isset($filters))
        <p><span class="info-label">Filtros aplicados:</span> 
            @if($filters['search'])
            Busca: {{ $filters['search'] }}, 
            @endif
            @if($filters['status'])
            Status: {{ $statusOptions[$filters['status']] ?? $filters['status'] }}, 
            @endif
            @if(isset($filters['dateFrom']) && $filters['dateFrom'])
            De: {{ $filters['dateFrom'] }}, 
            @endif
            @if(isset($filters['dateTo']) && $filters['dateTo'])
            Até: {{ $filters['dateTo'] }}
            @endif
        </p>
        @endif
    </div>

    <!-- Conteúdo principal - varia conforme o tipo de relatório -->
    @yield('content')

    <!-- Rodapé com informações de contato -->
    <div class="footer">
        <p>{{ $companyName }} | {{ \App\Models\Setting::get('company_address', '') }}</p>
        <p>Tel: {{ \App\Models\Setting::get('company_phone', '') }} | Email: {{ \App\Models\Setting::get('company_email', '') }}</p>
        <p>Documento gerado em {{ now()->format('d/m/Y H:i:s') }}</p>
    </div>

    <div class="page-number">Página 1</div>
</body>
</html>
```

## Sistema de Filtros e Busca

### 1. Implementação de Filtros e Busca

O ERP DEMBENA utiliza um sistema padronizado de filtros e busca em todas as listagens, facilitando a navegação e localização de informações pelos usuários.

#### 1.1 Estrutura de Filtros em Componentes Livewire

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

#### 1.2 Template Padrão de Filtros

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

#### 1.3 Métodos para Gerenciar Filtros

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

#### 1.4 Aplicação de Filtros na Consulta

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

## Layout de Tabelas e Componentes Visuais

### 1. Estrutura Padronizada de Tabelas

O ERP DEMBENA utiliza um layout consistente para tabelas de dados em todos os módulos, garantindo uma experiência de usuário coesa.

#### 1.1 Template Padrão para Tabelas

```blade
<!-- Table -->
<div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <!-- Exemplo de coluna com ordenação -->
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    <div class="flex items-center cursor-pointer" wire:click="sortBy('reference_number')">
                        ID/Referência
                        @if($sortField === 'reference_number')
                            @if($sortDirection === 'asc')
                                <i class="fas fa-sort-up ml-1"></i>
                            @else
                                <i class="fas fa-sort-down ml-1"></i>
                            @endif
                        @else
                            <i class="fas fa-sort ml-1 text-gray-300"></i>
                        @endif
                    </div>
                </th>
                
                <!-- Coluna normal -->
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Descrição
                </th>
                
                <!-- Outras colunas... -->
                
                <!-- Coluna de status com cores -->
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Status
                </th>
                
                <!-- Coluna de ações -->
                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Ações
                </th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @forelse($records as $record)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                        {{ $record->reference_number }}
                    </td>
                    
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $record->description }}
                    </td>
                    
                    <!-- Outras colunas... -->
                    
                    <!-- Status com badges coloridos -->
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                            {{ $record->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 
                               ($record->status === 'approved' ? 'bg-green-100 text-green-800' : 
                               ($record->status === 'rejected' ? 'bg-red-100 text-red-800' : 
                               ($record->status === 'processing' ? 'bg-blue-100 text-blue-800' : 
                                'bg-purple-100 text-purple-800'))) }}">
                            {{ $statusOptions[$record->status] ?? 'Unknown' }}
                        </span>
                    </td>
                    
                    <!-- Ações -->
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <div class="flex justify-end space-x-2">
                            <!-- Botão de visualização -->
                            <button wire:click="viewDetails({{ $record->id }})" class="text-blue-600 hover:text-blue-900" title="Visualizar">
                                <i class="fas fa-eye"></i>
                            </button>
                            
                            <!-- Botão de PDF -->
                            <button wire:click="generatePDF({{ $record->id }})" class="text-red-600 hover:text-red-900" title="Gerar PDF">
                                <i class="fas fa-file-pdf"></i>
                            </button>
                            
                            <!-- Botão de edição -->
                            <button wire:click="edit({{ $record->id }})" class="text-indigo-600 hover:text-indigo-900" title="Editar">
                                <i class="fas fa-edit"></i>
                            </button>
                            
                            <!-- Botões de aprovação/rejeição condicionais -->
                            @if($record->status === 'pending')
                                <button wire:click="changeStatus({{ $record->id }}, 'approved')" class="text-green-600 hover:text-green-900" title="Aprovar">
                                    <i class="fas fa-check"></i>
                                </button>
                                <button wire:click="changeStatus({{ $record->id }}, 'rejected')" class="text-red-600 hover:text-red-900" title="Rejeitar">
                                    <i class="fas fa-times"></i>
                                </button>
                            @endif
                            
                            <!-- Botão de exclusão -->
                            <button wire:click="confirmDelete({{ $record->id }})" class="text-red-600 hover:text-red-900" title="Excluir">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                        Nenhum registro encontrado.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- Pagination -->
<div class="px-4 py-3 bg-white border-t border-gray-200 sm:px-6">
    {{ $records->links() }}
</div>
```

### 2. Cabeçalhos de Listagem

Os cabeçalhos das listagens seguem um padrão consistente em todo o sistema:

```blade
<div class="p-4 sm:px-6 flex flex-col sm:flex-row justify-between sm:items-center border-b border-gray-200">
    <h1 class="text-lg font-medium text-gray-900 flex items-center">
        <i class="fas fa-clipboard-list mr-3 text-gray-500"></i> Título do Módulo
    </h1>
    <div class="mt-3 sm:mt-0 flex space-x-2">
        <!-- Botão de exportação PDF -->
        <button 
            type="button" 
            wire:click="generateListPDF"
            class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
        >
            <i class="fas fa-file-pdf mr-2 text-red-500"></i> Exportar Lista
        </button>
        
        <!-- Botão de novo registro -->
        <button 
            type="button" 
            wire:click="openModal"
            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
        >
            <i class="fas fa-plus-circle mr-2"></i> Novo Registro
        </button>
    </div>
</div>
```

### 3. Indicadores Visuais e Cores de Status

O sistema utiliza um esquema de cores consistente para indicar diferentes status:

| Status | Classe CSS | Cor |
|--------|------------|-----|
| Pendente | `bg-yellow-100 text-yellow-800` | Amarelo |
| Aprovado | `bg-green-100 text-green-800` | Verde |
| Rejeitado | `bg-red-100 text-red-800` | Vermelho |
| Em Processamento | `bg-blue-100 text-blue-800` | Azul |
| Finalizado | `bg-purple-100 text-purple-800` | Roxo |

## CRUD Operations

### 1. Save - Criação e Edição de Registros

A operação de save é responsável por criar novos registros ou atualizar registros existentes. Abaixo está o padrão implementado nos componentes do ERP DEMBENA:

```php
public function save()
{
    try {
        // Validação dos dados do formulário
        $this->validate([
            'data.name' => 'required|string|max:255',
            'data.description' => 'nullable|string|max:500',
            'data.equipment_id' => 'required|exists:equipment,id',
            'data.quantity' => 'required|numeric|min:0',
            'data.unit' => 'required|string',
            'data.requested_by' => 'required|string|max:255',
            // Validação condicional para imagem
            'image' => $this->isEditing ? 'nullable|image|max:1024' : 'required|image|max:1024',
        ], [
            'data.name.required' => 'O nome da peça é obrigatório.',
            'data.equipment_id.required' => 'O equipamento é obrigatório.',
            'data.quantity.required' => 'A quantidade é obrigatória.',
            'data.quantity.numeric' => 'A quantidade deve ser um número.',
            'data.quantity.min' => 'A quantidade deve ser maior ou igual a zero.',
            'data.unit.required' => 'A unidade é obrigatória.',
            'data.requested_by.required' => 'O solicitante é obrigatório.',
            'image.required' => 'A imagem é obrigatória para novos pedidos.',
            'image.image' => 'O arquivo deve ser uma imagem.',
            'image.max' => 'A imagem não pode ter mais de 1MB.',
        ]);

        // Iniciar transação DB para garantir integridade
        DB::beginTransaction();

        // Determinar se é uma criação ou atualização
        if ($this->isEditing) {
            // Atualização de um registro existente
            $partRequest = EquipmentPartRequest::findOrFail($this->recordId);
            $partRequest->update([
                'name' => $this->data['name'],
                'description' => $this->data['description'],
                'equipment_id' => $this->data['equipment_id'],
                'quantity' => $this->data['quantity'],
                'unit' => $this->data['unit'],
                'requested_by' => $this->data['requested_by'],
                'updated_by' => auth()->id(),
            ]);
            
            // Processar upload de imagem se uma nova imagem foi fornecida
            if ($this->image) {
                // Excluir imagem antiga se existir
                if ($partRequest->image_path) {
                    Storage::disk('public')->delete($partRequest->image_path);
                }
                
                // Salvar nova imagem
                $imagePath = $this->image->store('part-requests', 'public');
                $partRequest->update(['image_path' => $imagePath]);
            }
            
            $message = 'Pedido de peça atualizado com sucesso.';
        } else {
            // Criação de um novo registro
            // Processar upload de imagem
            $imagePath = $this->image->store('part-requests', 'public');
            
            // Criar o registro principal
            $partRequest = EquipmentPartRequest::create([
                'reference_number' => $this->generateReferenceNumber(),
                'name' => $this->data['name'],
                'description' => $this->data['description'],
                'equipment_id' => $this->data['equipment_id'],
                'quantity' => $this->data['quantity'],
                'unit' => $this->data['unit'],
                'requested_by' => $this->data['requested_by'],
                'status' => 'pending',
                'image_path' => $imagePath,
                'created_by' => auth()->id(),
                'updated_by' => auth()->id(),
            ]);
            
            $message = 'Pedido de peça criado com sucesso.';
        }

        // Confirmar a transação
        DB::commit();
        
        // Notificação e limpeza do formulário
        $this->dispatch('notify', type: 'success', message: $message);
        $this->closeModal();
        $this->resetForm();
    } catch (ValidationException $e) {
        // Erros de validação são capturados pelo Livewire automaticamente
        throw $e;
    } catch (\Exception $e) {
        // Reverter a transação em caso de erro
        DB::rollBack();
        $this->dispatch('notify', type: 'error', message: 'Erro ao salvar o pedido: ' . $e->getMessage());
        
        // Log do erro para debugging
        Log::error('Erro ao salvar pedido de peça: ' . $e->getMessage(), [
            'exception' => $e,
            'data' => $this->data,
            'user_id' => auth()->id()
        ]);
    }
}
```

### 2. Delete - Exclusão de Registros

A exclusão de registros segue um padrão de confirmação antes da exclusão efetiva:

```php
// Método para iniciar o processo de exclusão (exibir modal de confirmação)
public function confirmDelete($id)
{
    $this->recordIdToDelete = $id;
    $this->confirmingDeletion = true;
}

// Método para cancelar a exclusão
public function cancelDelete()
{
    $this->reset(['recordIdToDelete', 'confirmingDeletion']);
}

// Método para executar a exclusão após confirmação
public function delete()
{
    try {
        // Encontra o registro a ser excluído
        $record = Model::findOrFail($this->recordIdToDelete);
        
        // Verificação de permissão ou regras de negócio
        if ($record->status !== 'pending' && !auth()->user()->hasRole('admin')) {
            throw new \Exception('Apenas registros pendentes podem ser excluídos.');
        }
        
        // Exclusão de arquivos relacionados, se houver
        if ($record->image_path) {
            Storage::disk('public')->delete($record->image_path);
        }
        
        // Executa a exclusão
        $record->delete();
        
        // Notificação e limpeza de estado
        $this->dispatch('notify', type: 'success', message: 'Registro excluído com sucesso!');
        $this->reset(['recordIdToDelete', 'confirmingDeletion']);
    } catch (\Exception $e) {
        $this->dispatch('notify', type: 'error', message: 'Erro ao excluir: ' . $e->getMessage());
    }
}
```

### 3. Modal de Confirmação para Exclusão

```blade
<!-- Confirmation Modal -->
@if($confirmingDeletion)
    <div class="fixed z-10 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>

            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                            <i class="fas fa-exclamation-triangle text-red-600"></i>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                Confirmar Exclusão
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500">
                                    Tem certeza que deseja excluir este registro? Esta ação não pode ser desfeita.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button 
                        type="button"
                        wire:click="delete"
                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm"
                    >
                        Excluir
                    </button>
                    <button 
                        type="button"
                        wire:click="cancelDelete"
                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
                    >
                        Cancelar
                    </button>
                </div>
            </div>
        </div>
    </div>
@endif
```

### 4. Alteração de Status

```php
public function changeStatus($id, $status)
{
    try {
        // Validação do status
        if (!in_array($status, ['pending', 'approved', 'rejected', 'processing', 'completed'])) {
            throw new \Exception('Status inválido.');
        }
        
        // Verificação de permissão
        if (($status === 'approved' || $status === 'rejected') && !auth()->user()->can('approve-records')) {
            throw new \Exception('Você não tem permissão para aprovar ou rejeitar registros.');
        }
        
        // Atualização do status
        $record = Model::findOrFail($id);
        $record->update([
            'status' => $status,
            'status_changed_at' => now(),
            'status_changed_by' => auth()->id(),
        ]);
        
        // Notificação de sucesso
        $statusName = $this->statusOptions[$status] ?? ucfirst($status);
        $this->dispatch('notify', type: 'success', message: "Status alterado para {$statusName} com sucesso!");
        
    } catch (\Exception $e) {
        $this->dispatch('notify', type: 'error', message: 'Erro ao alterar status: ' . $e->getMessage());
    }
}
```

## Validação em Tempo Real e Feedback Visual

### 1. Validação em Tempo Real com Livewire

O sistema utiliza validação em tempo real através de Livewire, proporcionando feedback imediato ao usuário:

```blade
<div>
    <label for="username" class="block text-sm font-medium text-gray-700">Nome de Usuário</label>
    <input 
        type="text" 
        id="username" 
        wire:model.live="data.username" 
        class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md {{ $errors->has('data.username') ? 'border-red-300' : '' }}"
    >
    @error('data.username')
        <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
    @enderror
</div>
```

### 2. Indicadores de Carregamento em Botões

Nos botões de salvar, o sistema implementa indicadores de carregamento para proporcionar feedback visual durante operações:

```blade
<button 
    type="button" 
    wire:click="save" 
    wire:loading.attr="disabled"
    class="inline-flex justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
>
    <span wire:loading.remove wire:target="save">
        <i class="fas fa-save mr-2"></i> Salvar
    </span>
    <span wire:loading wire:target="save" class="inline-flex items-center">
        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg> Processando...
    </span>
</button>
```

Para botões de outras ações:

```blade
<button 
    type="button" 
    wire:click="generatePDF({{ $record->id }})"
    wire:loading.attr="disabled"
    wire:target="generatePDF({{ $record->id }})"
    class="text-red-600 hover:text-red-900"
    title="Gerar PDF"
>
    <span wire:loading.remove wire:target="generatePDF({{ $record->id }})">
        <i class="fas fa-file-pdf"></i>
    </span>
    <span wire:loading wire:target="generatePDF({{ $record->id }})">
        <i class="fas fa-spinner fa-spin"></i>
    </span>
</button>
```

## Padronização de Ícones

O sistema ERP DEMBENA utiliza um padrão consistente de ícones do Font Awesome para melhorar a experiência do usuário e a legibilidade da interface.

### 1. Ícones em Campos de Formulário

```blade
<!-- Campo de usuário -->
<div class="relative">
    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
        <i class="fas fa-user text-gray-400"></i>
    </div>
    <input 
        type="text" 
        wire:model.live="data.requested_by"
        class="pl-10 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
        placeholder="Nome do solicitante"
    >
</div>

<!-- Campo de data -->
<div class="relative">
    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
        <i class="fas fa-calendar text-gray-400"></i>
    </div>
    <input 
        type="date" 
        wire:model.live="data.request_date"
        class="pl-10 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
    >
</div>

<!-- Campo de equipamento (select) -->
<div class="relative">
    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
        <i class="fas fa-tools text-gray-400"></i>
    </div>
    <select 
        wire:model.live="data.equipment_id"
        class="pl-10 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
    >
        <option value="">Selecione um equipamento</option>
        @foreach($equipments as $equipment)
            <option value="{{ $equipment->id }}">{{ $equipment->name }}</option>
        @endforeach
    </select>
</div>
```

### 2. Tabela de Ícones Padrão

| Entidade | Ícone | Classe CSS |
|----------|-------|------------|
| Usuário | <i class="fas fa-user"></i> | `fas fa-user` |
| Data/Calendário | <i class="fas fa-calendar"></i> | `fas fa-calendar` |
| Hora | <i class="fas fa-clock"></i> | `fas fa-clock` |
| Equipamento | <i class="fas fa-tools"></i> | `fas fa-tools` |
| Peça/Item | <i class="fas fa-cog"></i> | `fas fa-cog` |
| Descrição | <i class="fas fa-align-left"></i> | `fas fa-align-left` |
| Status | <i class="fas fa-info-circle"></i> | `fas fa-info-circle` |
| Email | <i class="fas fa-envelope"></i> | `fas fa-envelope` |
| Telefone | <i class="fas fa-phone"></i> | `fas fa-phone` |
| Localização | <i class="fas fa-map-marker-alt"></i> | `fas fa-map-marker-alt` |
| Departamento | <i class="fas fa-building"></i> | `fas fa-building` |
| PDF | <i class="fas fa-file-pdf"></i> | `fas fa-file-pdf` |
| Editar | <i class="fas fa-edit"></i> | `fas fa-edit` |
| Excluir | <i class="fas fa-trash"></i> | `fas fa-trash` |
| Visualizar | <i class="fas fa-eye"></i> | `fas fa-eye` |
| Aprovar | <i class="fas fa-check"></i> | `fas fa-check` |
| Rejeitar | <i class="fas fa-times"></i> | `fas fa-times` |
| Adicionar | <i class="fas fa-plus-circle"></i> | `fas fa-plus-circle` |
| Filtrar | <i class="fas fa-filter"></i> | `fas fa-filter` |
| Pesquisar | <i class="fas fa-search"></i> | `fas fa-search` |
| Carregando | <i class="fas fa-spinner fa-spin"></i> | `fas fa-spinner fa-spin` |
