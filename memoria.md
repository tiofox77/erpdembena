# ERP DEMBENA - Documentação de Referência

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

## Componentes do Sistema

### 1. Sistema de Modais

O ERP DEMBENA utiliza um sistema de modais reutilizáveis para operações CRUD e visualizações detalhadas. Os modais são implementados através de um componente específico e Alpine.js.

#### 1.1 Componente ModalComponent

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

#### 1.2 Template do Modal (Blade)

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

#### 1.3 Como Utilizar o Modal

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

### 2. Estrutura Básica de Componente Livewire (CRUD)

Cada módulo do sistema segue uma estrutura padrão de CRUD, implementada através de componentes Livewire.

#### Exemplo de Classe Livewire para CRUD

```php
namespace App\\Livewire;

use Livewire\\Component;
use Livewire\\WithPagination;
use Livewire\\WithFileUploads;
use Livewire\\Attributes\\Url;
use App\\Models\\ModelName;
use Illuminate\\Support\\Facades\\Auth;
use Illuminate\\Support\\Str;
use Barryvdh\\DomPDF\\Facade\\Pdf;

class ModuleName extends Component
{
    use WithPagination, WithFileUploads;

    // Propriedades para filtros e pesquisa
    #[Url(history: true)]
    public $search = '';
    
    #[Url(history: true)]
    public $status = '';
    
    #[Url(history: true)]
    public $sortField = 'created_at';
    
    #[Url(history: true)]
    public $sortDirection = 'desc';
    
    public $perPage = 10;
    
    // Propriedades para formulário e modal
    public $showModal = false;
    public $isEditing = false;
    public $recordId = null;
    public $showDeleteModal = false;
    
    // Dados do formulário
    public $record = [
        'field1' => '',
        'field2' => '',
        // ...
    ];
    
    // Regras de validação
    protected function rules()
    {
        return [
            'record.field1' => 'required|string|max:255',
            'record.field2' => 'nullable|string',
            // ...
        ];
    }
    
    // Mensagens de erro personalizadas
    protected function messages()
    {
        return [
            'record.field1.required' => 'O campo X é obrigatório',
            // ...
        ];
    }
    
    // Inicialização
    public function mount()
    {
        $this->resetForm();
    }
    
    // Validação em tempo real
    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
    }
    
    // Métodos para CRUD
    public function openModal()
    {
        $this->resetForm();
        $this->showModal = true;
    }
    
    public function closeModal()
    {
        $this->showModal = false;
        $this->reset('isEditing', 'recordId');
    }
    
    public function edit($id)
    {
        $this->resetForm();
        $this->isEditing = true;
        $this->recordId = $id;
        
        $record = ModelName::findOrFail($id);
        $this->record = $record->toArray();
        
        $this->showModal = true;
    }
    
    public function save()
    {
        $this->validate();
        
        try {
            if ($this->isEditing) {
                $record = ModelName::findOrFail($this->recordId);
                $record->update($this->record);
                $message = 'Registro atualizado com sucesso!';
            } else {
                ModelName::create(array_merge(
                    $this->record,
                    ['created_by' => Auth::id()]
                ));
                $message = 'Registro criado com sucesso!';
            }
            
            $this->dispatch('notify', type: 'success', message: $message);
            $this->closeModal();
            
        } catch (\Exception $e) {
            $this->dispatch('notify', type: 'error', message: 'Erro: ' . $e->getMessage());
        }
    }
    
    public function confirmDelete($id)
    {
        $this->recordId = $id;
        $this->showDeleteModal = true;
    }
    
    public function delete()
    {
        try {
            ModelName::findOrFail($this->recordId)->delete();
            $this->dispatch('notify', type: 'success', message: 'Registro excluído com sucesso!');
            $this->showDeleteModal = false;
        } catch (\Exception $e) {
            $this->dispatch('notify', type: 'error', message: 'Erro ao excluir: ' . $e->getMessage());
        }
    }
    
    // Métodos para filtros
    public function updatedSearch()
    {
        $this->resetPage();
    }
    
    public function updatedStatus()
    {
        $this->resetPage();
    }
    
    public function updatedPerPage()
    {
        $this->resetPage();
    }
    
    public function clearFilters()
    {
        $this->search = '';
        $this->status = '';
        $this->resetPage();
    }
    
    // Método para gerar PDF
    public function generatePdf($id = null)
    {
        try {
            if ($id) {
                // PDF individual
                $record = ModelName::findOrFail($id);
                $pdf = Pdf::loadView('livewire.module.single-pdf', [
                    'record' => $record
                ]);
                return $pdf->download('record_' . $id . '.pdf');
            } else {
                // PDF de lista/relatório
                $query = ModelName::query()
                    ->when($this->search, function ($query, $search) {
                        return $query->where('field1', 'like', '%'.$search.'%')
                            ->orWhere('field2', 'like', '%'.$search.'%');
                    })
                    ->when($this->status, function ($query, $status) {
                        return $query->where('status', $status);
                    })
                    ->orderBy($this->sortField, $this->sortDirection);
                
                $records = $query->get();
                
                $pdf = Pdf::loadView('livewire.module.list-pdf', [
                    'records' => $records,
                    'filters' => [
                        'search' => $this->search,
                        'status' => $this->status
                    ],
                    'reportTitle' => 'Relatório de Registros'
                ]);
                return $pdf->download('records_report_' . date('Y-m-d') . '.pdf');
            }
        } catch (\Exception $e) {
            $this->dispatch('notify', type: 'error', message: 'Erro ao gerar PDF: ' . $e->getMessage());
        }
    }
    
    // Render principal
    public function render()
    {
        $records = ModelName::query()
            ->when($this->search, function ($query, $search) {
                return $query->where('field1', 'like', '%'.$search.'%')
                    ->orWhere('field2', 'like', '%'.$search.'%');
            })
            ->when($this->status, function ($query, $status) {
                return $query->where('status', $status);
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);
            
        return view('livewire.module-name', [
            'records' => $records,
            'statusOptions' => [
                'active' => 'Ativo',
                'inactive' => 'Inativo',
                // ...
            ]
        ]);
    }
}
```
