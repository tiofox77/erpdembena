# Guia para Implementação de CRUD com Laravel Livewire

Este guia descreve o padrão e a estrutura para implementação de funcionalidades CRUD (Create, Read, Update, Delete) usando Laravel Livewire em sistemas de gerenciamento. Use este documento como referência para entender o fluxo e a arquitetura do projeto.

## Estrutura do Projeto

### Diretórios Principais

-   **app/Models/**: Contém todos os modelos Eloquent do sistema

    -   Modelos principais: `MaintenanceEquipment`, `MaintenanceArea`, `MaintenanceLine`, `EquipmentPart`, etc.
    -   Cada modelo define suas relações com outros modelos usando métodos como `hasMany`, `belongsTo`, etc.

-   **app/Livewire/**: Contém os componentes Livewire que gerenciam a interface do usuário e a lógica CRUD

    -   Componentes principais: `EquipmentParts`, `MaintenanceDashboard`, `UserManagement`, etc.
    -   Subdiretórios organizados por funcionalidade: `Components/`, `Reports/`, `History/`, etc.

-   **resources/views/livewire/**: Contém os templates Blade associados a cada componente Livewire

    -   Exemplos: `equipment-parts.blade.php`, `maintenance-dashboard.blade.php`, etc.

-   **resources/views/layouts/**: Contém os layouts principais do sistema
    -   `livewire.blade.php`: Layout principal usado por todos os componentes Livewire

### Hierarquia dos Modelos e Relacionamentos

O sistema segue uma estrutura hierárquica para os equipamentos de manutenção:

1. **Áreas (MaintenanceArea)**

    - Uma área pode ter múltiplas linhas

    ```php
    public function lines()
    {
        return $this->hasMany(MaintenanceLine::class, 'maintenance_area_id');
    }
    ```

2. **Linhas (MaintenanceLine)**

    - Uma linha pertence a uma área
    - Uma linha pode ter múltiplos equipamentos

    ```php
    public function area()
    {
        return $this->belongsTo(MaintenanceArea::class, 'maintenance_area_id');
    }

    public function equipment()
    {
        return $this->hasMany(MaintenanceEquipment::class, 'maintenance_line_id');
    }
    ```

3. **Equipamentos (MaintenanceEquipment)**

    - Um equipamento pertence a uma linha e a uma área
    - Um equipamento pode ter múltiplas tarefas, planos de manutenção, manutenções corretivas e peças

    ```php
    public function line()
    {
        return $this->belongsTo(MaintenanceLine::class, 'line_id');
    }

    public function area()
    {
        return $this->belongsTo(MaintenanceArea::class, 'area_id');
    }

    public function tasks()
    {
        return $this->hasMany(MaintenanceTask::class, 'equipment_id');
    }

    public function maintenancePlans()
    {
        return $this->hasMany(MaintenancePlan::class, 'equipment_id');
    }

    public function correctives()
    {
        return $this->hasMany(MaintenanceCorrective::class, 'equipment_id');
    }

    public function parts()
    {
        return $this->hasMany(EquipmentPart::class, 'maintenance_equipment_id');
    }
    ```

4. **Peças de Equipamento (EquipmentPart)**
    - Uma peça pertence a um equipamento
    ```php
    public function equipment()
    {
        return $this->belongsTo(MaintenanceEquipment::class, 'maintenance_equipment_id');
    }
    ```

## Estrutura Básica do CRUD

### 1. Componente Livewire

O componente Livewire (`MaintenanceLineArea.php` neste exemplo) segue esta estrutura:

```php
namespace App\Livewire\Components;

use App\Models\MaintenanceLine as Line;
use App\Models\MaintenanceArea as Area;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Attributes\On;

class MaintenanceLineArea extends Component
{
    use WithPagination;

    // Propriedades com suporte a URL
    #[Url]
    public $activeTab = 'areas';

    #[Url(history: true)]
    public $search = '';

    // Propriedades de estado
    public $perPage = 10;
    public $showAreaModal = false;
    public $showLineModal = false;
    public $showDeleteModal = false;
    public $isEditing = false;
    public $deleteType = '';
    public $deleteId = null;

    // Dados do formulário
    public $area = [
        'name' => '',
        'description' => ''
    ];

    public $line = [
        'name' => '',
        'description' => ''
    ];

    // Regras de validação
    protected function rules()
    {
        return [
            'area.name' => 'required|string|max:255',
            'area.description' => 'nullable|string',
            'line.name' => 'required|string|max:255',
            'line.description' => 'nullable|string',
        ];
    }

    // Mensagens de validação personalizadas
    protected function messages()
    {
        return [
            'area.name.required' => 'The area name is required.',
            'area.name.max' => 'The area name must not exceed 255 characters.',
            'line.name.required' => 'The line name is required.',
            'line.name.max' => 'The line name must not exceed 255 characters.',
        ];
    }

    // Propriedades computadas para dados
    public function getAreasProperty()
    {
        return Area::when($this->search, function ($query) {
                return $query->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('description', 'like', '%' . $this->search . '%');
            })
            ->orderBy('name')
            ->paginate($this->perPage);
    }

    // Métodos CRUD para Áreas
    public function openCreateAreaModal() { /* ... */ }
    public function editArea($id) { /* ... */ }
    public function saveArea() { /* ... */ }
    public function confirmDeleteArea($id) { /* ... */ }

    // Métodos CRUD para Linhas
    public function openCreateLineModal() { /* ... */ }
    public function editLine($id) { /* ... */ }
    public function saveLine() { /* ... */ }
    public function confirmDeleteLine($id) { /* ... */ }

    // Métodos comuns
    public function closeModal() { /* ... */ }
    public function deleteConfirmed() { /* ... */ }
    public function updated($propertyName) {
        // Validação em tempo real
        $this->validateOnly($propertyName);
    }

    // Renderização do componente
    public function render()
    {
        return view('livewire.components.maintenance-line-area');
    }
}
```

### 2. Implementação de CRUD

#### Create & Update

```php
public function saveArea()
{
    // Validar os dados do formulário
    $validatedData = $this->validate([
        'area.name' => 'required|string|max:255',
        'area.description' => 'nullable|string'
    ]);

    try {
        if ($this->isEditing) {
            // Atualizar registro existente
            $area = Area::findOrFail($this->area['id']);
            $area->update([
                'name' => $this->area['name'],
                'description' => $this->area['description']
            ]);
            $message = 'Area updated successfully';
        } else {
            // Criar novo registro
            Area::create([
                'name' => $this->area['name'],
                'description' => $this->area['description']
            ]);
            $message = 'Area created successfully';
        }

        // Enviar notificação com tipo apropriado
        $notificationType = $this->isEditing ? 'info' : 'success';
        $this->dispatch('notify', type: $notificationType, message: $message);

        // Limpar e fechar o modal
        $this->showAreaModal = false;
        $this->reset(['area']);
    } catch (\Exception $e) {
        // Registrar e notificar erros
        Log::error('Error saving area: ' . $e->getMessage());
        $this->dispatch('notify', type: 'error', message: 'Error saving area: ' . $e->getMessage());
    }
}
```

#### Delete

```php
public function deleteConfirmed()
{
    try {
        if ($this->deleteType === 'area') {
            $area = Area::findOrFail($this->deleteId);
            $area->delete();
            $message = 'Area deleted successfully';
        } else if ($this->deleteType === 'line') {
            $line = Line::findOrFail($this->deleteId);
            $line->delete();
            $message = 'Line deleted successfully';
        }

        // Usar warning para notificações de exclusão
        $this->dispatch('notify', type: 'warning', message: $message);

        // Fechar modal e limpar dados
        $this->showDeleteModal = false;
        $this->reset(['deleteId', 'deleteType']);
    } catch (\Exception $e) {
        Log::error('Error deleting ' . $this->deleteType . ': ' . $e->getMessage());
        $this->dispatch('notify', type: 'error', message: 'Error deleting ' . $this->deleteType . ': ' . $e->getMessage());
    }
}
```

### 3. Blade Template com Interatividade

O arquivo `maintenance-line-area.blade.php` implementa:

```html
<div>
    <!-- Estrutura básica -->
    <div class="container mx-auto py-6">
        <!-- Cabeçalho e botões de ação -->
        <div class="mb-6 flex justify-between items-center">
            <h1 class="text-2xl font-bold">Maintenance Management</h1>
            <div class="space-x-2">
                <button wire:click="openCreateAreaModal">
                    <i class="fas fa-plus-circle mr-1"></i> Add Area
                </button>
                <button wire:click="openCreateLineModal">
                    <i class="fas fa-plus-circle mr-1"></i> Add Line
                </button>
            </div>
        </div>

        <!-- Navegação por abas -->
        <div class="mb-4 border-b border-gray-200">
            <ul class="flex flex-wrap -mb-px text-sm font-medium text-center">
                <li class="mr-2">
                    <a
                        href="#"
                        wire:click.prevent="$set('activeTab', 'areas')"
                        class="{{ $activeTab === 'areas' ? 'border-b-2 border-blue-600 text-blue-600' : 'text-gray-500' }}"
                    >
                        <i class="fas fa-map-marker-alt mr-2"></i> Areas
                    </a>
                </li>
                <!-- ... -->
            </ul>
        </div>

        <!-- Tabelas com dados -->
        <div class="{{ $activeTab === 'areas' ? '' : 'hidden' }}">
            <table>
                <!-- Cabeçalho da tabela -->
                <thead>
                    <!-- ... -->
                </thead>
                <tbody>
                    @forelse ($this->areas as $area)
                    <tr>
                        <!-- Dados -->
                        <td>{{ $area->id }}</td>
                        <td>{{ $area->name }}</td>
                        <td>{{ $area->description }}</td>
                        <td>
                            <!-- Botões de ação -->
                            <button wire:click="editArea({{ $area->id }})">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button
                                wire:click="confirmDeleteArea({{ $area->id }})"
                            >
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <!-- Estado vazio -->
                    <tr>
                        <td colspan="4">
                            <div
                                class="flex flex-col items-center justify-center py-4"
                            >
                                <i
                                    class="fas fa-folder-open text-gray-400 text-4xl mb-3"
                                ></i>
                                <p>No areas found</p>
                                <button wire:click="openCreateAreaModal">
                                    Add your first area
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            <!-- Paginação -->
            <div class="mt-4">{{ $this->areas->links() }}</div>
        </div>

        <!-- Modais CRUD -->
        @if($showAreaModal)
        <div
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
        >
            <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6">
                <!-- Cabeçalho do modal -->
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium">
                        <i
                            class="fas {{ $isEditing ? 'fa-edit' : 'fa-plus-circle' }} mr-2"
                        ></i>
                        {{ $isEditing ? 'Edit' : 'Create' }} Area
                    </h3>
                    <button wire:click="closeModal">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <!-- Sumário de erros -->
                @if($errors->any())
                <div
                    class="mb-4 p-4 bg-red-50 border-l-4 border-red-500 text-red-700"
                >
                    <p class="font-bold">
                        <i class="fas fa-exclamation-circle mr-2"></i>Please
                        correct the following errors:
                    </p>
                    <ul class="mt-2 list-disc list-inside text-sm">
                        @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <!-- Formulário -->
                <form wire:submit.prevent="saveArea">
                    <div class="mb-4">
                        <label for="area-name">Name</label>
                        <div class="mt-1 relative rounded-md shadow-sm">
                            <input
                                type="text"
                                id="area-name"
                                class="@error('area.name') border-red-300 text-red-900 @enderror"
                                wire:model.live="area.name"
                            />
                            @error('area.name')
                            <div
                                class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none"
                            >
                                <i
                                    class="fas fa-exclamation-circle text-red-500"
                                ></i>
                            </div>
                            @enderror
                        </div>
                        @error('area.name')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <!-- Outros campos ... -->

                    <!-- Botões de ação -->
                    <div class="flex justify-end space-x-2">
                        <button type="button" wire:click="closeModal">
                            <i class="fas fa-times mr-1"></i> Cancel
                        </button>
                        <button type="submit">
                            <i
                                class="fas {{ $isEditing ? 'fa-save' : 'fa-plus' }} mr-1"
                            ></i>
                            {{ $isEditing ? 'Update' : 'Create' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
        @endif

        <!-- Modal de confirmação de exclusão -->
        @if($showDeleteModal)
        <div
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
        >
            <!-- Conteúdo do modal de exclusão -->
        </div>
        @endif
    </div>

    <!-- JavaScript para Notificações -->
    <script>
        document.addEventListener("livewire:initialized", () => {
            Livewire.on("notify", (params) => {
                if (window.toastr) {
                    toastr.options = {
                        closeButton: true,
                        progressBar: true,
                        positionClass: "toast-top-right",
                        timeOut: 5000,
                    };

                    // Exibir notificação baseada no tipo
                    if (params.type === "success") {
                        toastr.success(
                            params.message,
                            params.title || "Success"
                        );
                    } else if (params.type === "error") {
                        toastr.error(params.message, params.title || "Error");
                    } else if (params.type === "warning") {
                        toastr.warning(
                            params.message,
                            params.title || "Warning"
                        );
                    } else {
                        toastr.info(
                            params.message,
                            params.title || "Information"
                        );
                    }
                } else {
                    alert(params.message);
                }
            });
        });
    </script>
</div>
```

## Padrões de Design e Interatividade

### 1. Notificações

-   Use `$this->dispatch('notify', type: 'tipo', message: 'mensagem')` para disparar notificações.
-   Tipos de notificação:
    -   `success`: Para criação bem-sucedida (cor verde)
    -   `info`: Para atualizações bem-sucedidas (cor azul)
    -   `warning`: Para exclusões bem-sucedidas (cor amarela)
    -   `error`: Para erros (cor vermelha)

### 2. Validação e Feedback de Erros

-   Três níveis de feedback de erro:
    -   Sumário de todos os erros no topo do formulário
    -   Destaque visual nos campos com erro (bordas vermelhas + ícone)
    -   Mensagens específicas abaixo de cada campo com erro
-   Validação em tempo real com `wire:model.live`
-   Método `updated($propertyName)` para validação durante digitação

### 3. Modais

-   Três tipos de modais:
    -   Modal de criação/edição
    -   Modal de confirmação de exclusão
-   Use `$this->showXModal = true/false` para abrir/fechar
-   Use `$this->closeModal()` para lógica de fechamento comum

### 4. Estados Vazios

-   Forneça feedback visual quando não houver dados
-   Inclua botões de ação dentro dos estados vazios
-   Use ícones para comunicar visualmente o estado

### 5. Ícones e Feedback Visual

-   Use ícones para melhorar a usabilidade (Font Awesome já incluído)
-   Botões de ação usam ícones em vez de apenas texto
-   Cores diferentes para diferentes tipos de ações

## Sistema de Layout

### Layout Principal (livewire.blade.php)

O sistema utiliza um layout principal definido em `resources/views/layouts/livewire.blade.php` que estrutura a aplicação com:

1. **Sidebar**

    - Menu de navegação principal com subitens hierárquicos
    - Menu de manutenção com submenus para diferentes funcionalidades
    - Submenu de configurações de manutenção corretiva
    - Sistema de toggle para expandir/colapsar submenus

2. **Header**

    - Barra de título com o nome da página atual
    - Campo de busca global
    - Notificações
    - Menu de usuário

3. **Conteúdo Principal**

    - Slot `{{ $slot }}` onde o conteúdo específico de cada componente Livewire é renderizado

4. **Scripts e Estilos**
    - Biblioteca Tailwind CSS para estilos
    - Font Awesome para ícones
    - Toastr para notificações
    - Alpine.js para interatividade no lado do cliente
    - FullCalendar para componentes de calendário
    - Chart.js para gráficos

### Como Utilizar o Layout

Cada componente Livewire é renderizado dentro do layout principal:

```php
// Exemplo de renderização em um componente Livewire
public function render()
{
    return view('livewire.equipment-parts')
        ->layout('layouts.livewire', ['title' => 'Equipment Parts Management']);
}
```

O parâmetro `title` é passado para o layout para atualizar o título da página no header.

### Integração com Notificações

O layout principal inclui o JavaScript para processar eventos de notificação:

```javascript
document.addEventListener("livewire:initialized", () => {
    Livewire.on("notify", (params) => {
        if (window.toastr) {
            toastr.options = {
                closeButton: true,
                progressBar: true,
                positionClass: "toast-top-right",
                timeOut: 5000,
            };

            // Exibir notificação baseada no tipo
            if (params.type === "success") {
                toastr.success(params.message, params.title || "Success");
            } else if (params.type === "error") {
                toastr.error(params.message, params.title || "Error");
            } else if (params.type === "warning") {
                toastr.warning(params.message, params.title || "Warning");
            } else {
                toastr.info(params.message, params.title || "Information");
            }
        }
    });
});
```

## Implementação de Relações

O sistema possui uma estrutura hierárquica de modelos com relações definidas entre eles. Para implementar novas relações:

1. Adicione a coluna de chave estrangeira na migração:

    ```php
    $table->foreignId('maintenance_area_id')
        ->nullable()
        ->constrained('maintenance_areas')
        ->nullOnDelete();
    ```

2. Adicione o campo no modelo:

    ```php
    protected $fillable = [
        'name',
        'description',
        'maintenance_area_id',
    ];
    ```

3. Configure as relações nos modelos:

    ```php
    // Em MaintenanceLine.php
    public function area()
    {
        return $this->belongsTo(MaintenanceArea::class, 'maintenance_area_id');
    }

    // Em MaintenanceArea.php
    public function lines()
    {
        return $this->hasMany(MaintenanceLine::class, 'maintenance_area_id');
    }
    ```

4. Adicione campos de seleção no formulário:

    ```html
    <div class="mb-4">
        <label for="line-area">Area</label>
        <select id="line-area" wire:model="line.maintenance_area_id">
            <option value="">Select Area</option>
            @foreach($this->allAreas as $area)
            <option value="{{ $area->id }}">{{ $area->name }}</option>
            @endforeach
        </select>
        @error('line.maintenance_area_id')
        <p class="text-red-600">{{ $message }}</p>
        @enderror
    </div>
    ```

5. Exemplo de carregamento de dados relacionados:
    ```php
    #[Computed]
    public function getPartsProperty()
    {
        return EquipmentPart::when($this->search, function ($query) {
                // Filtros de busca
            })
            ->when($this->equipmentId, function ($query) {
                // Filtro por equipamento
                return $query->where('maintenance_equipment_id', $this->equipmentId);
            })
            ->with('equipment') // Carrega o equipamento relacionado
            ->orderBy('name')
            ->paginate($this->perPage);
    }
    ```

## Conclusão

Este padrão de CRUD com Laravel Livewire oferece:

1. Operações CRUD completas e reativas
2. Validação em tempo real com feedback visual claro
3. Sistema de notificações consistente
4. Interface intuitiva com feedback visual e estados vazios tratados
5. Código modular e reutilizável
6. Estrutura hierárquica de modelos com relacionamentos bem definidos
7. Layout principal consistente com navegação intuitiva

Siga esta estrutura para manter a consistência em todo o sistema, facilitando a manutenção e extensão do código.
