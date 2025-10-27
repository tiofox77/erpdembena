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

# Laravel Livewire CRUD Guide - User Management

This guide describes the User Management component implementation using Laravel and Livewire. The component provides a complete CRUD interface with real-time validation, sorting, filtering, and permission management.

## Table of Contents

1. [Overview](#overview)
2. [Component Structure](#component-structure)
3. [CRUD Operations](#crud-operations)
4. [Interactivity Features](#interactivity-features)
5. [Notification System](#notification-system)
6. [Form Inputs & Validation](#form-inputs--validation)
7. [Layout & UI Components](#layout--ui-components)
8. [Icons](#icons)
9. [Sorting & Filtering](#sorting--filtering)
10. [Permissions](#permissions)
11. [Code Examples](#code-examples)

## Overview

The User Management component is a Livewire-based interface that allows administrators to manage users in the system. It includes functionalities for creating, viewing, updating, and deleting users, as well as filtering, sorting, and real-time form validation.

## Component Structure

The component consists of:

-   **UserManagement.php**: A Livewire component class that handles the logic
-   **user-management.blade.php**: The template file with UI elements
-   **Modals**: For creating, editing, and confirming deletion
-   **SearchBar and Filters**: For searching and filtering users

## CRUD Operations

### Create

-   New user button at the top-right of the interface
-   Modal form with validation for all required fields
-   Role assignment via Spatie Permission package
-   Save button that creates the user and displays a success notification

### Read

-   Table view of all users with sortable columns
-   Paginated results (10 users per page)
-   Visual indicators for active/inactive status

### Update

-   Edit button for each user row
-   Pre-filled modal form with existing user information
-   Password field is optional during editing
-   Save button that updates user information

### Delete

-   Delete button for each user row
-   Confirmation modal to prevent accidental deletion
-   Notification after successful deletion

## Interactivity Features

### Real-time Features:

-   **Live Validation**: Form validation occurs as users type
-   **Instant Filtering**: Results update as filters are applied
-   **Dynamic Sorting**: Table can be sorted by clicking column headers
-   **Status Toggle**: Toggle user status (active/inactive) instantly

### Livewire Properties:

-   **URL Parameters**: Filters and sort state are stored in URL parameters
-   **Pagination**: Integrated with Livewire pagination
-   **Modal States**: Control for opening/closing modals

## Notification System

The component uses a notification system via Livewire dispatch events:

```php
$this->dispatch('notify', type: 'success', message: 'User created successfully');
```

Types of notifications:

-   **Success**: For successful operations (green)
-   **Error**: For failures or errors (red)
-   **Info**: For general information (blue)
-   **Warning**: For cautionary messages (yellow)

## Form Inputs & Validation

The form includes:

-   First name (required)
-   Last name (required)
-   Email (required, unique)
-   Phone (optional)
-   Role (required)
-   Department (required)
-   Password (required for new users, optional for edits)
-   Password confirmation
-   Active status toggle

Validation rules are defined in the `rules()` method and custom messages in `messages()`.

## Layout & UI Components

### UI Components:

-   **Header**: With title and "New User" button
-   **Filter Bar**: For searching and filtering users
-   **Table**: With sortable columns and pagination
-   **Status Indicator**: Visual representation of user status
-   **Action Buttons**: For edit and delete operations
-   **Modals**: For create, edit, and delete operations
-   **Pagination**: For navigating through results

### Responsive Design:

-   Grid system for form inputs to display side-by-side on larger screens and stacked on smaller screens
-   Responsive table with overflow handling
-   Mobile-friendly modals and form elements

## Icons

The component uses FontAwesome icons throughout the interface:

-   **User Icon**: For the component header
-   **Plus Icon**: For the "New User" button
-   **Edit Icon**: For edit buttons
-   **Trash Icon**: For delete buttons
-   **Sort Icons**: For sortable columns
-   **Check/X Icons**: For form validation
-   **Status Indicators**: For user status

## Sorting & Filtering

### Sorting:

-   Any column can be sorted by clicking the header
-   Sort direction toggles between ascending and descending
-   Current sort field is visually indicated

### Filtering:

-   Search box for filtering by name or email
-   Dropdown filters for:
    -   Role
    -   Department
    -   Status (Active/Inactive)
-   Clear all filters button when no results match

## Permissions

The component checks for user permissions before allowing access:

```php
if (!auth()->user()->can('users.manage')) {
    return redirect()->route('maintenance.dashboard')
            ->with('error', 'You do not have permission to access this page.');
}
```

This ensures only authorized users can access the User Management interface.

## Code Examples

### Opening the Create User Modal

```php
public function openModal()
{
    $this->resetValidation();
    $this->isEditing = false;
    $this->user = [
        'first_name' => '',
        'last_name' => '',
        'email' => '',
        'phone' => '',
        'role' => '',
        'department' => '',
        'password' => '',
        'password_confirmation' => '',
        'is_active' => true
    ];
    $this->showModal = true;
}
```

### Saving a User

```php
public function saveUser()
{
    $validatedData = $this->validate();

    try {
        if ($this->isEditing) {
            // Update existing user
            $user = User::findOrFail($this->user['id']);

            // Set user fields
            $userData = [
                'first_name' => $this->user['first_name'],
                'last_name' => $this->user['last_name'],
                'email' => $this->user['email'],
                'phone' => $this->user['phone'],
                'department' => $this->user['department'],
                'is_active' => $this->user['is_active']
            ];

            // Only update password if provided
            if (!empty($this->user['password'])) {
                $userData['password'] = Hash::make($this->user['password']);
            }

            $user->update($userData);

            // Update user role
            if (!empty($this->user['role'])) {
                $user->syncRoles([$this->user['role']]);
            }

            $message = 'User updated successfully';
            $notificationType = 'info';

        } else {
            // Create new user
            $user = User::create([
                'first_name' => $this->user['first_name'],
                'last_name' => $this->user['last_name'],
                'email' => $this->user['email'],
                'phone' => $this->user['phone'],
                'department' => $this->user['department'],
                'password' => Hash::make($this->user['password']),
                'is_active' => $this->user['is_active']
            ]);

            // Assign role
            if (!empty($this->user['role'])) {
                $user->assignRole($this->user['role']);
            }

            $message = 'User created successfully';
            $notificationType = 'success';
        }

        // Send notification
        $this->dispatch('notify', type: $notificationType, message: $message);

        // Close modal and reset form
        $this->showModal = false;
        $this->reset('user');

    } catch (\Exception $e) {
        Log::error('Error saving user: ' . $e->getMessage());
        $this->dispatch('notify', type: 'error', message: 'Error saving user: ' . $e->getMessage());
    }
}
```

### Filtering Users

```php
public function getUsersProperty()
{
    return User::with('roles')
        ->when($this->search, function ($query) {
            return $query->where(function ($q) {
                $q->where('first_name', 'like', '%' . $this->search . '%')
                  ->orWhere('last_name', 'like', '%' . $this->search . '%')
                  ->orWhere('email', 'like', '%' . $this->search . '%')
                  ->orWhereRaw("CONCAT(first_name, ' ', last_name) like ?", ['%' . $this->search . '%']);
            });
        })
        ->when($this->filterRole, function ($query) {
            // Filter by role using Spatie relationship
            return $query->role($this->filterRole);
        })
        ->when($this->filterDepartment, function ($query) {
            return $query->where('department', $this->filterDepartment);
        })
        ->when($this->filterStatus !== '', function ($query) {
            return $query->where('is_active', $this->filterStatus);
        })
        ->orderBy($this->sortField, $this->sortDirection)
        ->paginate(10);
}
```

# Form Input Styling Best Practices

When creating form inputs in the application, follow these responsive styling guidelines to ensure consistency and improve usability across all device sizes:

## Input Classes

For all form inputs (text, number, email, select, etc.), use these Tailwind CSS classes:

```html
<input
    type="text"
    id="example_field"
    wire:model.live="model.example_field"
    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 text-xs sm:text-sm py-1.5 px-2"
/>
```

### Key Input Classes:

-   `text-xs sm:text-sm`: Responsive text size that's smaller on mobile, slightly larger on desktop
-   `py-1.5 px-2`: Compact padding that works well across device sizes
-   `border border-gray-300`: Explicit border styling with light gray color
-   `rounded-md`: Consistent border radius
-   `shadow-sm`: Subtle shadow for depth
-   `focus:border-blue-500 focus:ring-blue-500`: Blue highlight when input is focused

## Select Inputs

Apply the same classes to select inputs for consistency:

```html
<select
    id="example_select"
    wire:model.live="model.example_select"
    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 text-xs sm:text-sm py-1.5 px-2"
>
    <option value="">Select option</option>
    @foreach($options as $option)
    <option value="{{ $option->id }}">{{ $option->name }}</option>
    @endforeach
</select>
```

## Textarea Inputs

For textarea elements, use the same base classes but specify rows instead of fixed height:

```html
<textarea
    id="example_textarea"
    wire:model.live="model.example_textarea"
    rows="4"
    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 text-xs sm:text-sm py-1.5 px-2"
    placeholder="Enter your detailed description here..."
></textarea>
```

## Labels with Icons

Use responsive text sizes for labels with proper spacing and add appropriate icons:

```html
<label
    for="example_field"
    class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 flex items-center"
>
    <i class="fas fa-user mr-1 text-gray-500"></i> Field Label
</label>
```

## Error Handling

When showing validation errors, add these conditional classes and elements:

```html
<div class="relative rounded-md shadow-sm">
    <input
        type="text"
        id="example_field"
        wire:model.live="model.example_field"
        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 text-xs sm:text-sm py-1.5 px-2
        @error('model.example_field') border-red-300 text-red-900 @enderror"
    />
    @error('model.example_field')
    <div
        class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none"
    >
        <i class="fas fa-exclamation-circle text-red-500"></i>
    </div>
    @enderror
</div>
@error('model.example_field')
<p class="mt-1 text-xs text-red-600">{{ $message }}</p>
@enderror
```

## Grid Layout Structure

Organize forms with responsive grid layouts:

```html
<div class="grid grid-cols-1 md:grid-cols-3 gap-3 sm:gap-4 mb-3 sm:mb-4">
    <!-- Input fields here -->
</div>
```

Key grid classes:

-   `grid-cols-1 md:grid-cols-3`: Single column on mobile, three columns on medium screens and up
-   `gap-3 sm:gap-4`: Smaller gaps on mobile, slightly larger on desktop
-   `mb-3 sm:mb-4`: Responsive margin bottom

## Section Grouping

For logical grouping of related fields, use this pattern:

```html
<div class="md:col-span-3">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 sm:gap-4 mt-2">
        <!-- Related input fields here -->
    </div>
</div>
```

## Buttons

For form buttons, use responsive padding and text size with appropriate icons:

```html
<button
    type="button"
    class="px-3 py-1.5 sm:px-4 sm:py-2 border border-gray-300 rounded-md shadow-sm text-xs sm:text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 flex items-center"
>
    <i class="fas fa-times mr-1"></i> Cancel
</button>

<button
    type="submit"
    class="px-3 py-1.5 sm:px-4 sm:py-2 border border-transparent rounded-md shadow-sm text-xs sm:text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 flex items-center"
>
    <i class="fas {{ $isEditing ? 'fa-save' : 'fa-plus' }} mr-1"></i>
    {{ $isEditing ? 'Update' : 'Save' }} Record
</button>
```

## Error Summary

Display a summary of all errors at the top of the form:

```html
@if($errors->any())
<div class="mb-4 p-4 bg-red-50 border-l-4 border-red-500 text-red-700">
    <p class="font-bold flex items-center">
        <i class="fas fa-exclamation-circle mr-2"></i>
        Please correct the following errors:
    </p>
    <ul class="mt-2 list-disc list-inside text-sm">
        @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif
```

# Form Organization and Visual Hierarchy

To create more intuitive and user-friendly forms, organize them into logical sections with clear visual hierarchy using background colors, icons, and appropriate spacing.

## Section Headers

Each logical group of form fields should have a clear header with an appropriate icon:

```html
<div class="bg-gray-50 p-3 rounded-md mb-4">
    <h4 class="text-sm font-medium text-gray-700 mb-2 flex items-center">
        <i class="fas fa-info-circle mr-2 text-blue-500"></i> Section Title
    </h4>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-3 sm:gap-4">
        <!-- Form fields for this section -->
    </div>
</div>
```

## Section Icon Guide

Use consistent, meaningful icons for different form sections:

| Section Type        | Recommended Icon          | Color      |
| ------------------- | ------------------------- | ---------- |
| Basic Information   | `fa-info-circle`          | blue-500   |
| Equipment/Technical | `fa-cogs`                 | blue-500   |
| Time/Dates          | `fa-clock`                | blue-500   |
| Failure/Problems    | `fa-exclamation-triangle` | orange-500 |
| Personnel           | `fa-users`                | blue-500   |
| Description/Details | `fa-file-alt`             | blue-500   |
| Location            | `fa-map-marker-alt`       | blue-500   |

## Field Label Icons

Add appropriate icons to field labels based on their purpose:

| Field Type      | Recommended Icon      |
| --------------- | --------------------- |
| Date/Year       | `fa-calendar-alt`     |
| Month           | `fa-calendar-day`     |
| Week            | `fa-calendar-week`    |
| Equipment       | `fa-wrench`           |
| Status          | `fa-toggle-on`        |
| System/Process  | `fa-sitemap`          |
| Start Time      | `fa-play-circle`      |
| End Time        | `fa-stop-circle`      |
| Duration        | `fa-hourglass-half`   |
| Description     | `fa-align-left`       |
| Actions         | `fa-tasks`            |
| User (Reporter) | `fa-user-edit`        |
| User (Resolver) | `fa-user-check`       |
| Category        | `fa-tags` or `fa-tag` |
| Failure Mode    | `fa-times-circle`     |
| Failure Cause   | `fa-search`           |

## Helper Text with Icons

Add icons to helper text for better visual cues:

```html
<p class="mt-1 text-xs text-gray-500 flex items-center">
    <i class="fas fa-calculator mr-1"></i> Calculated automatically when start
    and end times are set
</p>
```

## Input Placeholders

Add helpful placeholder text to textarea and text inputs:

```html
<textarea
    id="description"
    wire:model.live="model.description"
    rows="4"
    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 text-xs sm:text-sm py-1.5 px-2"
    placeholder="Describe the issue in detail..."
></textarea>
```

## Modal Structure Example

Here's a complete example of a modal with proper styling and visual organization:

```html
<div
    class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
>
    <div
        class="bg-white rounded-lg shadow-lg w-full max-w-6xl p-6 overflow-y-auto max-h-[90vh]"
    >
        <!-- Modal Header -->
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-medium text-gray-900">
                <i
                    class="fas {{ $isEditing ? 'fa-edit' : 'fa-toolbox' }} mr-2"
                ></i>
                {{ $isEditing ? 'Edit' : 'Create' }} Record
            </h3>
            <button
                type="button"
                class="text-gray-500 hover:text-gray-700 text-xl"
                wire:click="closeModal"
            >
                <i class="fas fa-times"></i>
            </button>
        </div>

        <!-- Error Summary -->
        @if($errors->any())
        <div class="mb-4 p-4 bg-red-50 border-l-4 border-red-500 text-red-700">
            <p class="font-bold flex items-center">
                <i class="fas fa-exclamation-circle mr-2"></i>
                Please correct the following errors:
            </p>
            <ul class="mt-2 list-disc list-inside text-sm">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <!-- Form -->
        <form wire:submit.prevent="save">
            <!-- Basic Information Section -->
            <div class="bg-gray-50 p-3 rounded-md mb-4">
                <h4
                    class="text-sm font-medium text-gray-700 mb-2 flex items-center"
                >
                    <i class="fas fa-info-circle mr-2 text-blue-500"></i> Basic
                    Information
                </h4>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3 sm:gap-4">
                    <!-- Form fields for basic information -->
                </div>
            </div>

            <!-- Additional sections here -->

            <!-- Action Buttons -->
            <div class="flex justify-end space-x-3 mt-4">
                <button
                    type="button"
                    class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 flex items-center"
                    wire:click="closeModal"
                >
                    <i class="fas fa-times mr-2"></i> Cancel
                </button>
                <button
                    type="button"
                    class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-150 flex items-center"
                    wire:click="edit({{ $record->id }})"
                >
                    <i class="fas fa-edit mr-2"></i> Edit Record
                </button>
            </div>
        </form>
    </div>
</div>
```

By implementing these patterns consistently across your application, you'll create more intuitive, user-friendly interfaces with clear visual cues that guide users through complex forms.

# Lists, Filters and Tables Best Practices

When displaying data in tabular format, it's essential to provide users with intuitive filtering options, clear sorting mechanisms, and responsive tables. This section outlines best practices for implementing list views with filters and tables.

## Filter Section Structure

Filters should be organized in a dedicated section with a clear heading and relevant icons. Implement the following structure for a comprehensive filter section:

```html
<div class="mb-6 bg-white p-4 rounded-lg shadow-sm border border-gray-200">
    <h4 class="text-sm font-medium text-gray-700 mb-3 flex items-center">
        <i class="fas fa-filter mr-2 text-blue-500"></i> Filters and Search
    </h4>
    <div class="grid grid-cols-1 md:grid-cols-6 gap-4">
        <!-- Search field (spans 2 columns) -->
        <div class="md:col-span-2">
            <label
                for="search"
                class="block text-xs font-medium text-gray-700 mb-1 flex items-center"
            >
                <i class="fas fa-search mr-1 text-gray-500"></i> Search
            </label>
            <div class="relative rounded-md shadow-sm">
                <div
                    class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"
                >
                    <i class="fas fa-search text-gray-400"></i>
                </div>
                <input
                    type="text"
                    id="search"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Search for records..."
                    class="focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 pr-3 py-2 sm:text-sm border-gray-300 rounded-md"
                />
            </div>
        </div>

        <!-- Filter fields -->
        <div>
            <label
                for="filterExample"
                class="block text-xs font-medium text-gray-700 mb-1 flex items-center"
            >
                <i class="fas fa-calendar-alt mr-1 text-gray-500"></i> Filter
                Label
            </label>
            <div class="relative rounded-md shadow-sm">
                <select
                    id="filterExample"
                    wire:model.live="filterExample"
                    class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                >
                    <option value="">All Options</option>
                    @foreach($exampleOptions as $key => $value)
                    <option value="{{ $key }}">{{ $value }}</option>
                    @endforeach
                </select>
                <div
                    class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none"
                >
                    <i class="fas fa-chevron-down text-gray-400 text-xs"></i>
                </div>
            </div>
        </div>

        <!-- More filter fields... -->
    </div>

    <!-- Clear filters button -->
    <div class="flex justify-end mt-3">
        <button
            wire:click="clearFilters"
            class="inline-flex items-center px-3 py-1.5 border border-gray-300 shadow-sm text-xs font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
        >
            <i class="fas fa-times-circle mr-1"></i> Clear Filters
        </button>
    </div>
</div>
```

### Filter Implementation Tips

1. **Use a grid layout** to arrange filters responsively
2. **Include clear labels** with relevant icons for each filter
3. **Add visual cues** like dropdown indicators for select fields
4. **Include a clear filters button** to reset all filters at once
5. **Use debounce** on text search fields to prevent excessive queries

### Implementing Effective Filter Clearing

A crucial part of any filtering system is the ability to quickly reset all filters. Here's how to implement a robust "Clear Filters" pattern that ensures both the backend data and frontend UI are properly reset:

#### Controller Logic (Livewire Component)

Add this method to your Livewire component:

```php
/**
 * Clear all filters and reset to default values
 */
public function clearFilters()
{
    // Reset all filter values using Livewire's reset() method
    $this->reset(['search', 'filterStatus', 'filterEquipment', 'filterMonth']);

    // Set default values where needed (like current year)
    $this->filterYear = now()->year;

    // Reset pagination to first page
    $this->resetPage();

    // Dispatch an event to force UI refresh
    $this->dispatch('filters-cleared');
}
```

Key implementation notes:

-   Use Livewire's `reset()` method to cleanly reset multiple properties at once
-   Set any default values explicitly (like current year or default status)
-   Always call `resetPage()` to return pagination to the first page
-   Dispatch a custom event to trigger UI updates for select elements

#### View Implementation (Blade Template)

1. Add the "Clear Filters" button in your filters section:

```html
<div class="flex justify-end mt-3">
    <button
        wire:click="clearFilters"
        class="inline-flex items-center px-3 py-1.5 border border-gray-300 shadow-sm text-xs font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
    >
        <i class="fas fa-times-circle mr-1"></i> Clear Filters
    </button>
</div>
```

2. Add JavaScript event listener to sync UI state with backend changes:

```html
<script>
    document.addEventListener("livewire:initialized", () => {
        // Listen for filters-cleared event to update selects visually
        Livewire.on("filters-cleared", () => {
            // Force UI update for select elements
            document.getElementById("filterEquipment").value = "";
            document.getElementById("filterStatus").value = "";
            document.getElementById("filterMonth").value = "";

            // Optional: Add visual feedback
            const clearBtn = document.querySelector(
                'button[wire\\:click="clearFilters"]'
            );
            if (clearBtn) {
                clearBtn.classList.add("bg-blue-50");
                setTimeout(() => {
                    clearBtn.classList.remove("bg-blue-50");
                }, 300);
            }
        });
    });
</script>
```

3. Include a "clear filters" option in empty state messaging:

```html
<p class="text-sm text-gray-500 mt-1 flex items-center">
    @if($search || $filterStatus || $filterEquipment || $filterYear ||
    $filterMonth)
    <i class="fas fa-filter mr-1"></i> Try adjusting your search filters or
    <button
        wire:click="clearFilters"
        class="ml-1 text-blue-600 hover:text-blue-800 underline flex items-center"
    >
        <i class="fas fa-times-circle mr-1"></i> clear all filters
    </button>
    @else
    <i class="fas fa-info-circle mr-1"></i> No records found in this view @endif
</p>
```

#### Why This Pattern Works

This approach solves several common issues with filter clearing:

1. **Backend/Frontend Synchronization**: The JavaScript event listener ensures visual select elements match the backend state
2. **Browser History Management**: Using `wire:model` with Livewire's URL features preserves filter state in browser history
3. **Visual Feedback**: The subtle button highlight provides user confirmation that filters were cleared
4. **Multiple Reset Points**: Offers filter reset both in filter controls and empty state messaging

With this implementation, users can confidently use and reset filters, knowing both the data and UI will remain in sync.

## Table Implementation

Tables should have clear headers with sorting indicators, consistent column alignment, and appropriate icons for better visual scanning.

### Table Headers With Sorting

```html
<thead class="bg-gray-50">
    <tr>
        <th
            scope="col"
            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer"
            wire:click="sortBy('column_name')"
        >
            <div class="flex items-center space-x-1">
                <i class="fas fa-icon-name text-gray-400 mr-1"></i>
                <span>Column Title</span>
                @if($sortField === 'column_name')
                <i
                    class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} text-blue-500"
                ></i>
                @else
                <i class="fas fa-sort text-gray-300"></i>
                @endif
            </div>
        </th>
        <!-- More columns... -->
    </tr>
</thead>
```

### Table Rows

```html
<tbody class="bg-white divide-y divide-gray-200">
    @forelse($records as $record)
    <tr class="hover:bg-gray-50">
        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
            {{ $record->field_name }}
        </td>
        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
            <div class="flex items-center">
                <i class="fas fa-appropriate-icon text-gray-400 mr-1"></i>
                {{ $record->another_field }}
            </div>
        </td>
        <!-- Other columns... -->
        <td class="px-6 py-4 whitespace-nowrap">
            <span
                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full items-center
                    {{ $record->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}"
            >
                <i
                    class="fas {{ $record->status === 'active' ? 'fa-check-circle' : 'fa-question-circle' }} mr-1"
                ></i>
                {{ $record->status_label }}
            </span>
        </td>
        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
            <div class="flex justify-end space-x-2">
                <button
                    wire:click.prevent="view({{ $record->id }})"
                    class="text-blue-600 hover:text-blue-900 w-8 h-8 rounded-full flex items-center justify-center hover:bg-blue-100"
                    title="View Details"
                >
                    <i class="fas fa-eye"></i>
                </button>
                <button
                    wire:click="edit({{ $record->id }})"
                    class="text-indigo-600 hover:text-indigo-900 w-8 h-8 rounded-full flex items-center justify-center hover:bg-indigo-100"
                    title="Edit"
                >
                    <i class="fas fa-edit"></i>
                </button>
                <button
                    wire:click="confirmDelete({{ $record->id }})"
                    class="text-red-600 hover:text-red-900 w-8 h-8 rounded-full flex items-center justify-center hover:bg-red-100"
                    title="Delete"
                >
                    <i class="fas fa-trash-alt"></i>
                </button>
            </div>
        </td>
    </tr>
    @empty
    <tr>
        <td
            colspan="[number_of_columns]"
            class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center"
        >
            <div class="flex flex-col items-center justify-center py-8">
                <div class="bg-gray-100 rounded-full p-3 mb-4">
                    <i class="fas fa-clipboard-list text-gray-400 text-4xl"></i>
                </div>
                <p class="text-lg font-medium">No records found</p>
                <p class="text-sm text-gray-500 mt-1 flex items-center">
                    @if($hasActiveFilters)
                    <i class="fas fa-filter mr-1"></i> Try adjusting your search
                    filters or
                    <button
                        wire:click="clearFilters"
                        class="ml-1 text-blue-600 hover:text-blue-800 underline flex items-center"
                    >
                        <i class="fas fa-times-circle mr-1"></i> clear all
                        filters
                    </button>
                    @else
                    <i class="fas fa-info-circle mr-1"></i> Click "Add New" to
                    create your first record @endif
                </p>
            </div>
        </td>
    </tr>
    @endforelse
</tbody>
```

### Pagination

Always include pagination for tables with many records:

```html
<div class="px-4 py-3 bg-white border-t border-gray-200 sm:px-6">
    {{ $records->links() }}
</div>
```

## Table Cell Content Types

### Status Indicators

Use color-coded status badges with appropriate icons:

```html
<span
    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full items-center
    {{ $record->status === 'open' ? 'bg-red-100 text-red-800' :
       ($record->status === 'in_progress' ? 'bg-yellow-100 text-yellow-800' :
       ($record->status === 'resolved' ? 'bg-green-100 text-green-800' :
       'bg-gray-100 text-gray-800')) }}"
>
    <i
        class="fas
        {{ $record->status === 'open' ? 'fa-exclamation-circle' :
           ($record->status === 'in_progress' ? 'fa-spinner fa-spin' :
           ($record->status === 'resolved' ? 'fa-check-circle' :
           'fa-question-circle')) }} mr-1"
    ></i>
    {{ $statusLabels[$record->status] }}
</span>
```

### Action Buttons

Create consistent, interactive action buttons:

```html
<div class="flex justify-end space-x-2">
    <button
        wire:click.prevent="view({{ $record->id }})"
        class="text-blue-600 hover:text-blue-900 w-8 h-8 rounded-full flex items-center justify-center hover:bg-blue-100"
        title="View Details"
    >
        <i class="fas fa-eye"></i>
    </button>
    <button
        wire:click="edit({{ $record->id }})"
        class="text-indigo-600 hover:text-indigo-900 w-8 h-8 rounded-full flex items-center justify-center hover:bg-indigo-100"
        title="Edit"
    >
        <i class="fas fa-edit"></i>
    </button>
    <button
        wire:click="confirmDelete({{ $record->id }})"
        class="text-red-600 hover:text-red-900 w-8 h-8 rounded-full flex items-center justify-center hover:bg-red-100"
        title="Delete"
    >
        <i class="fas fa-trash-alt"></i>
    </button>
</div>
```

## Icon Selection

Use consistent icons for specific data types across all tables:

| Data Type       | Recommended Icon          |
| --------------- | ------------------------- |
| Date/Year       | `fa-calendar-alt`         |
| Month           | `fa-calendar-day`         |
| Week            | `fa-calendar-week`        |
| Time            | `fa-clock`                |
| Duration        | `fa-hourglass-half`       |
| ID              | `fa-hashtag`              |
| Equipment/Tools | `fa-tools` or `fa-wrench` |
| Status          | `fa-toggle-on`            |
| Process         | `fa-sitemap`              |
| Category        | `fa-tag` or `fa-tags`     |
| Error/Failure   | `fa-exclamation-circle`   |
| Person          | `fa-user`                 |
| View action     | `fa-eye`                  |
| Edit action     | `fa-edit`                 |
| Delete action   | `fa-trash-alt`            |
| Empty state     | `fa-clipboard-list`       |

## Backend Implementation

### Livewire Component Class Methods

Implement these methods in your Livewire component class to handle filtering and sorting:

```php
// Define URL parameters for filters
#[Url]
public $search = '';

#[Url]
public $filterStatus = '';

#[Url]
public $filterCategory = '';

#[Url]
public $sortField = 'created_at';

#[Url]
public $sortDirection = 'desc';

// Computed property for filtered and sorted data
#[Computed]
public function getRecordsProperty()
{
    return ModelName::query()
        ->when($this->search, function ($query) {
            return $query->where(function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%');
            });
        })
        ->when($this->filterStatus, function ($query) {
            return $query->where('status', $this->filterStatus);
        })
        ->when($this->filterCategory, function ($query) {
            return $query->where('category_id', $this->filterCategory);
        })
        ->orderBy($this->sortField, $this->sortDirection)
        ->paginate(10);
}

// Method to handle column sorting
public function sortBy($field)
{
    if ($this->sortField === $field) {
        $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
    } else {
        $this->sortField = $field;
        $this->sortDirection = 'asc';
    }
}

// Method to clear all filters
public function clearFilters()
{
    $this->search = '';
    $this->filterStatus = '';
    $this->filterCategory = '';
    // Reset any other filter properties
}

// Helper method to check if any filters are active
public function getHasActiveFiltersProperty()
{
    return $this->search || $this->filterStatus || $this->filterCategory;
}
```

## Responsive Considerations

1. **Use overflow-x-auto** for tables to handle horizontal scrolling on mobile devices
2. **Prioritize important columns** - consider hiding less important columns on smaller screens
3. **Use compact text** with `text-xs` or `text-sm` for better table density
4. **Stack filters vertically** on mobile with the responsive grid

## Empty State Design

Create informative and helpful empty states when no records match the filters:

1. Use a **prominent icon** to indicate the empty state
2. Include **clear text** explaining why no results are shown
3. Offer **helpful actions** like clearing filters or creating a new record
4. Use **friendly, conversational language** to guide the user

By implementing these patterns consistently across your application, you'll create an intuitive and efficient data browsing experience for your users.

# Modal Views and Detail Pages Best Practices

When implementing CRUD functionality, every entity in your system should have well-designed modal views for displaying detailed information. A good detail view improves user experience and provides clear context about the data being viewed.

## Why Every CRUD Needs Detail Views

Every CRUD operation in your project should include a dedicated "View Details" modal or page for the following reasons:

1. **Contextual Understanding**: Users need to see complete information before taking actions
2. **Decision Support**: Detailed views help users make informed decisions about editing or deleting records
3. **Visual Verification**: Users can verify that created/updated records contain the correct information
4. **Hierarchical Data Exploration**: Detail views can show related records and parent-child relationships
5. **Audit Trail Visibility**: Detail views often show metadata like creation date, last update, and user info

## Detail Modal Structure

Detail modals should follow this standardized structure:

```html
<!-- View Modal -->
<div
    class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
>
    <div
        class="bg-white rounded-lg shadow-xl w-full max-w-6xl p-6 overflow-y-auto max-h-[90vh]"
    >
        <!-- Enhanced Header with Icon -->
        <div
            class="flex justify-between items-center mb-6 border-b border-gray-200 pb-4"
        >
            <h3 class="text-xl font-semibold text-gray-800 flex items-center">
                <span class="bg-blue-100 text-blue-600 p-2 rounded-full mr-3">
                    <i class="fas fa-clipboard-check text-lg"></i>
                </span>
                Record Details
            </h3>
            <div class="flex items-center space-x-2">
                <button
                    type="button"
                    class="bg-indigo-100 text-indigo-600 hover:bg-indigo-200 transition-colors duration-150 p-2 rounded-full"
                    wire:click="edit({{ $record->id }})"
                    title="Edit Record"
                >
                    <i class="fas fa-edit"></i>
                </button>
                <button
                    type="button"
                    class="bg-gray-100 text-gray-600 hover:bg-gray-200 transition-colors duration-150 p-2 rounded-full"
                    wire:click="closeViewModal"
                    title="Close"
                >
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>

        <!-- Status Summary Card -->
        <div class="bg-gray-50 rounded-lg mb-6 shadow-sm">
            <div class="p-4 border-b border-gray-200">
                <div
                    class="flex flex-col md:flex-row md:justify-between md:items-center"
                >
                    <div class="flex items-center mb-3 md:mb-0">
                        <span
                            class="px-3 py-1.5 inline-flex items-center rounded-full text-sm font-medium
                            {{ $record->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}"
                        >
                            <i
                                class="fas 
                                {{ $record->status === 'active' ? 'fa-check-circle' : 'fa-question-circle' }} mr-2"
                            ></i>
                            {{ $record->status_label }}
                        </span>
                        <span
                            class="ml-4 bg-blue-50 text-blue-700 px-3 py-1 rounded-full text-sm flex items-center"
                        >
                            <i class="fas fa-hashtag mr-1"></i>
                            ID: {{ $record->id }}
                        </span>
                    </div>
                    <div class="flex items-center text-sm text-gray-600">
                        <i class="far fa-calendar-alt mr-2"></i>
                        <span
                            >Creation Date: {{
                            $record->created_at->format('Y-m-d') }}</span
                        >
                    </div>
                </div>
            </div>
            <!-- Primary Record Fields Summary -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 p-4">
                <div class="flex items-start">
                    <span
                        class="bg-blue-50 p-2 rounded-full text-blue-600 mr-3"
                    >
                        <i class="fas fa-info-circle"></i>
                    </span>
                    <div>
                        <p class="text-xs text-gray-500 uppercase">
                            Primary Field
                        </p>
                        <p class="text-sm font-medium">
                            {{ $record->primary_field }}
                        </p>
                    </div>
                </div>
                <div class="flex items-start">
                    <span
                        class="bg-blue-50 p-2 rounded-full text-blue-600 mr-3"
                    >
                        <i class="fas fa-tag"></i>
                    </span>
                    <div>
                        <p class="text-xs text-gray-500 uppercase">
                            Secondary Field
                        </p>
                        <p class="text-sm font-medium">
                            {{ $record->secondary_field }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detail Content Sections -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <!-- Section 1 -->
            <div class="bg-white border border-gray-200 rounded-lg shadow-sm">
                <div
                    class="bg-blue-50 px-4 py-2 rounded-t-lg flex items-center"
                >
                    <i class="fas fa-chart-bar text-blue-600 mr-2"></i>
                    <h4 class="text-sm font-semibold text-blue-800">
                        Section Title
                    </h4>
                </div>
                <div class="p-4">
                    <div class="mb-4 flex">
                        <div class="w-8 text-center mr-2">
                            <i class="fas fa-info-circle text-blue-500"></i>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 uppercase">
                                Field Label
                            </p>
                            <p class="text-sm">
                                {{ $record->field_value ?: 'N/A' }}
                            </p>
                        </div>
                    </div>
                    <!-- Additional fields using same pattern -->
                </div>
            </div>

            <!-- Section 2 (use appropriate colors for different sections) -->
            <div class="bg-white border border-gray-200 rounded-lg shadow-sm">
                <div
                    class="bg-green-50 px-4 py-2 rounded-t-lg flex items-center"
                >
                    <i class="fas fa-list-alt text-green-600 mr-2"></i>
                    <h4 class="text-sm font-semibold text-green-800">
                        Another Section
                    </h4>
                </div>
                <div class="p-4">
                    <!-- Fields using same pattern -->
                </div>
            </div>
        </div>

        <!-- Long Text Content Section (full width) -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div class="bg-white border border-gray-200 rounded-lg shadow-sm">
                <div
                    class="bg-gray-50 px-4 py-2 rounded-t-lg flex items-center"
                >
                    <i class="fas fa-align-left text-gray-600 mr-2"></i>
                    <h4 class="text-sm font-semibold text-gray-700">
                        Description
                    </h4>
                </div>
                <div class="p-4 h-40 overflow-y-auto">
                    @if($record->description)
                    <p class="text-sm leading-relaxed">
                        {{ $record->description }}
                    </p>
                    @else
                    <div
                        class="flex items-center justify-center h-full text-gray-400"
                    >
                        <i class="fas fa-file-alt mr-2"></i>
                        <span>No description provided</span>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Another long text section -->
            <div class="bg-white border border-gray-200 rounded-lg shadow-sm">
                <div
                    class="bg-gray-50 px-4 py-2 rounded-t-lg flex items-center"
                >
                    <i class="fas fa-clipboard-list text-gray-600 mr-2"></i>
                    <h4 class="text-sm font-semibold text-gray-700">Notes</h4>
                </div>
                <div class="p-4 h-40 overflow-y-auto">
                    <!-- Content or empty state -->
                </div>
            </div>
        </div>

        <!-- Related Records Section (if applicable) -->
        <div class="bg-white border border-gray-200 rounded-lg shadow-sm mb-6">
            <div class="bg-purple-50 px-4 py-2 rounded-t-lg flex items-center">
                <i class="fas fa-link text-purple-600 mr-2"></i>
                <h4 class="text-sm font-semibold text-purple-800">
                    Related Records
                </h4>
            </div>
            <div class="p-4">
                <!-- Related records list or empty state -->
            </div>
        </div>

        <!-- Footer Action Buttons -->
        <div class="flex justify-end space-x-3 border-t border-gray-200 pt-4">
            <button
                type="button"
                class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-150 flex items-center"
                wire:click="closeViewModal"
            >
                <i class="fas fa-times mr-2"></i> Close
            </button>
            <button
                type="button"
                class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-150 flex items-center"
                wire:click="edit({{ $record->id }})"
            >
                <i class="fas fa-edit mr-2"></i> Edit Record
            </button>
        </div>
    </div>
</div>
```

## Detail View Design Principles

### 1. Visual Hierarchy

Organize information using clear visual hierarchy with:

-   **Header with Distinctive Icon**: Use a relevant icon in a colored circular background
-   **Status Summary Card**: Show ID, status, and primary metadata at the top
-   **Organized Sections**: Group related fields into distinct sections with colored headers
-   **Proper Spacing**: Use consistent spacing to separate different sections

### 2. Color Coding and Icons

Use consistent color schemes to differentiate types of information:

| Section Type      | Background | Icon Color       | Recommended Icon          |
| ----------------- | ---------- | ---------------- | ------------------------- |
| Primary Info      | blue-50    | blue-600         | `fa-info-circle`          |
| Status/Time       | gray-50    | varies by status | `fa-check-circle`, etc.   |
| Technical/Process | blue-50    | blue-600         | `fa-cogs` or `fa-wrench`  |
| Description       | gray-50    | gray-600         | `fa-align-left`           |
| Notes/Comments    | gray-50    | gray-600         | `fa-clipboard-list`       |
| Related Records   | purple-50  | purple-600       | `fa-link`                 |
| Financial         | green-50   | green-600        | `fa-dollar-sign`          |
| Warning/Issues    | orange-50  | orange-600       | `fa-exclamation-triangle` |

### 3. Field Layout

For individual fields within sections:

-   **Consistent Left Alignment** with icon column for visual scanning
-   **Label-Value Pattern** with uppercase small labels and normal text values
-   **Fixed Icon Column Width** (typically 2rem) for vertical alignment
-   **Appropriate Icons** matching the data type of each field

### 4. Empty States

Always handle empty states gracefully:

```html
@if($record->field_value)
<p class="text-sm leading-relaxed">{{ $record->field_value }}</p>
@else
<div class="flex items-center justify-center h-full text-gray-400">
    <i class="fas fa-appropriate-icon mr-2"></i>
    <span>No data available</span>
</div>
@endif
```

### 5. Action Buttons

-   Place action buttons at the footer with clear visual separation (border-top)
-   Use contrasting colors for primary (edit) and secondary (close) actions
-   Include relevant icons with each button
-   Implement hover effects and transitions for better interactivity

### 6. Responsive Design

-   Use a grid system that adapts to different screen sizes
-   Set appropriate max-height with overflow scrolling for lengthy content
-   Implement a flexible layout that works well on mobile devices
-   Stack sections vertically on smaller screens with full width

## Livewire Implementation

Your Livewire component should include these methods for view modal functionality:

```php
public $showViewModal = false;
public $viewingRecord = null;

public function view($id)
{
    $this->viewingRecord = YourModel::with(['relationship1', 'relationship2'])->findOrFail($id);
    $this->showViewModal = true;
}

public function closeViewModal()
{
    $this->showViewModal = false;
    $this->viewingRecord = null;
}
```

## Interactive Elements

Add these interactive elements to make your detail views more engaging:

1. **Hover Effects**: Add `hover:bg-gray-50` to rows or sections that benefit from highlighting
2. **Transitions**: Use `transition-colors duration-150` for smooth color changes on hover
3. **Tooltips**: Add `title` attributes to icons or buttons to provide additional context
4. **Dynamic Icons**: Change icons based on record state or values
5. **Color-coded Status Indicators**: Use appropriate colors for different status values

## CRUD Integration

When implementing the full CRUD pattern:

1. **List View**: Include a view button in each row using `fa-eye` icon
2. **View Modal**: Show all record details with "Edit" action available
3. **Edit Modal**: Pre-fill form with data from viewed record
4. **Delete Confirmation**: Reference key details from the record being deleted

By implementing these detail view patterns consistently across your application, you'll create a more intuitive and informative user experience that helps users better understand and interact with your data.

# Making CRUD Modals Required

## Why Every Entity Needs View Modals

In a professional Laravel application, every entity managed through CRUD operations **must have** a dedicated view modal for the following reasons:

1. **Complete Data Context**: Users need to see complete information before taking actions
2. **Informed Decision Making**: Detailed views help users make better decisions
3. **Consistent User Experience**: A standardized approach to data viewing creates familiarity
4. **Reduced Error Rates**: Users can verify data before editing or deleting
5. **Better Data Relationships Visibility**: View modals can show related records clearly

## Implementation Checklist

When creating any new CRUD component, ensure you include:

-   [ ] A list view with a dedicated "View" button/action
-   [ ] A complete view modal showing all relevant fields
-   [ ] Easy transition from view to edit mode
-   [ ] Appropriate empty states for all fields
-   [ ] Consistent layouts matching other view modals in the application
-   [ ] Proper handling of relationships and nested data
-   [ ] Mobile-responsive design for all modal elements

## Connecting View, Edit and Delete

The view modal serves as the central hub connecting all CRUD operations:

1. **From List to View**: Clicking view button in list opens the view modal
2. **From View to Edit**: Edit button in view modal opens the edit form
3. **From View to Delete**: Delete button in view modal triggers deletion confirmation
4. **From Create/Edit to View**: After saving, show the view modal to confirm changes

Following this pattern creates a natural, intuitive flow that guides users through data interactions in a consistent way.

# Implementação de Modais de Visualização (View/Show)

Uma parte essencial de qualquer CRUD completo é a funcionalidade de visualização detalhada dos registros. Essa visualização deve mostrar todos os dados do registro em uma interface bem organizada e fornecer acesso rápido às ações relacionadas (editar, excluir).

## Componente Livewire (Controller)

Adicione estas propriedades e métodos ao seu componente Livewire para implementar a funcionalidade de visualização:

```php
// Propriedades para o modal de visualização
public $showViewModal = false;
public $viewingRecord = null;

// Métodos para o modal de visualização
public function view($id)
{
    // Carregue o registro completo, incluindo relacionamentos se necessário
    $this->viewingRecord = YourModel::with(['relationship1', 'relationship2'])
                                ->findOrFail($id);
    $this->showViewModal = true;
}

public function closeViewModal()
{
    $this->showViewModal = false;
    $this->viewingRecord = null;
}

// Método para fechar todos os modais (útil para listeners de eventos)
public function closeAllModals()
{
    $this->closeEditModal(); // ou closeModal() dependendo da sua nomenclatura
    $this->closeViewModal();
}

// Adicione isso ao seu listener de eventos para fechar os modais com ESC
protected function getListeners()
{
    return [
        'escape-pressed' => 'closeAllModals'
    ];
}

// Ao editar a partir da visualização, é preciso fechar o modal de visualização
public function edit($id)
{
    // Fechar o modal de visualização se estiver aberto
    if ($this->showViewModal) {
        $this->closeViewModal();
    }

    // Lógica padrão de edição...
    $record = YourModel::findOrFail($id);
    $this->recordId = $record->id;
    // Preencha os campos do formulário...

    $this->openEditModal();
}
```

## Template Blade (View)

Implemente o modal de visualização em seu template Blade seguindo este modelo:

```html
<!-- View Modal -->
@if($showViewModal)
<div
    class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
>
    <div
        class="bg-white rounded-lg shadow-xl w-full max-w-6xl p-6 overflow-y-auto max-h-[90vh]"
    >
        <!-- Enhanced Header with Icon -->
        <div
            class="flex justify-between items-center mb-6 border-b border-gray-200 pb-4"
        >
            <h3 class="text-xl font-semibold text-gray-800 flex items-center">
                <span class="bg-blue-100 text-blue-600 p-2 rounded-full mr-3">
                    <i class="fas fa-clipboard-check text-lg"></i>
                </span>
                Detalhes do Registro
            </h3>
            <div class="flex items-center space-x-2">
                <button
                    type="button"
                    class="bg-indigo-100 text-indigo-600 hover:bg-indigo-200 transition-colors duration-150 p-2 rounded-full"
                    wire:click="edit({{ $viewingRecord->id }})"
                    title="Editar Registro"
                >
                    <i class="fas fa-edit"></i>
                </button>
                <button
                    type="button"
                    class="bg-gray-100 text-gray-600 hover:bg-gray-200 transition-colors duration-150 p-2 rounded-full"
                    wire:click="closeViewModal"
                    title="Fechar"
                >
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>

        <!-- Status Summary Card -->
        <div class="bg-gray-50 rounded-lg mb-6 shadow-sm">
            <div class="p-4 border-b border-gray-200">
                <div
                    class="flex flex-col md:flex-row md:justify-between md:items-center"
                >
                    <div class="flex items-center mb-3 md:mb-0">
                        <span
                            class="ml-4 bg-blue-50 text-blue-700 px-3 py-1 rounded-full text-sm flex items-center"
                        >
                            <i class="fas fa-hashtag mr-1"></i>
                            ID: {{ $viewingRecord->id }}
                        </span>
                    </div>
                    <div class="flex items-center text-sm text-gray-600">
                        <i class="far fa-calendar-alt mr-2"></i>
                        <span
                            >Criado em: {{
                            $viewingRecord->created_at->format('d/m/Y H:i')
                            }}</span
                        >
                    </div>
                </div>
            </div>
            <!-- Primary Fields Summary -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 p-4">
                <div class="flex items-start">
                    <span
                        class="bg-blue-50 p-2 rounded-full text-blue-600 mr-3"
                    >
                        <i class="fas fa-info-circle"></i>
                    </span>
                    <div>
                        <p class="text-xs text-gray-500 uppercase">
                            Campo Principal
                        </p>
                        <p class="text-sm font-medium">
                            {{ $viewingRecord->primary_field }}
                        </p>
                    </div>
                </div>
                <div class="flex items-start">
                    <span
                        class="bg-blue-50 p-2 rounded-full text-blue-600 mr-3"
                    >
                        <i class="fas fa-tag"></i>
                    </span>
                    <div>
                        <p class="text-xs text-gray-500 uppercase">
                            Campo Secundário
                        </p>
                        <p class="text-sm font-medium">
                            {{ $viewingRecord->secondary_field }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detail Content Section - Customize according to entity -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <!-- Section 1 -->
            <div class="bg-white border border-gray-200 rounded-lg shadow-sm">
                <div
                    class="bg-blue-50 px-4 py-2 rounded-t-lg flex items-center"
                >
                    <i class="fas fa-info-circle text-blue-600 mr-2"></i>
                    <h4 class="text-sm font-semibold text-blue-800">
                        Informações Básicas
                    </h4>
                </div>
                <div class="p-4">
                    <div class="mb-4 flex">
                        <div class="w-8 text-center mr-2">
                            <i class="fas fa-info-circle text-blue-500"></i>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 uppercase">
                                Nome do Campo
                            </p>
                            <p class="text-sm">
                                {{ $viewingRecord->field_name ?: 'N/A' }}
                            </p>
                        </div>
                    </div>
                    <!-- Adicione mais campos conforme necessário -->
                </div>
            </div>

            <!-- Section 2 -->
            <div class="bg-white border border-gray-200 rounded-lg shadow-sm">
                <div
                    class="bg-green-50 px-4 py-2 rounded-t-lg flex items-center"
                >
                    <i class="fas fa-cogs text-green-600 mr-2"></i>
                    <h4 class="text-sm font-semibold text-green-800">
                        Informações Técnicas
                    </h4>
                </div>
                <div class="p-4">
                    <!-- Campos adicionais -->
                </div>
            </div>
        </div>

        <!-- Description Section -->
        <div class="bg-white border border-gray-200 rounded-lg shadow-sm mb-6">
            <div class="bg-gray-50 px-4 py-2 rounded-t-lg flex items-center">
                <i class="fas fa-align-left text-gray-600 mr-2"></i>
                <h4 class="text-sm font-semibold text-gray-700">Descrição</h4>
            </div>
            <div class="p-4 h-40 overflow-y-auto">
                @if($viewingRecord->description)
                <p class="text-sm leading-relaxed">
                    {{ $viewingRecord->description }}
                </p>
                @else
                <div
                    class="flex items-center justify-center h-full text-gray-400"
                >
                    <i class="fas fa-file-alt mr-2"></i>
                    <span>Nenhuma descrição fornecida</span>
                </div>
                @endif
            </div>
        </div>

        <!-- Related Records Section (if applicable) -->
        @if($viewingRecord->relationshipName &&
        $viewingRecord->relationshipName->count() > 0)
        <div class="bg-white border border-gray-200 rounded-lg shadow-sm mb-6">
            <div class="bg-purple-50 px-4 py-2 rounded-t-lg flex items-center">
                <i class="fas fa-link text-purple-600 mr-2"></i>
                <h4 class="text-sm font-semibold text-purple-800">
                    Registros Relacionados
                </h4>
            </div>
            <div class="p-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    @foreach($viewingRecord->relationshipName as $related)
                    <div
                        class="flex items-start border border-gray-100 rounded p-2 hover:bg-gray-50"
                    >
                        <div class="mr-2">
                            <i class="fas fa-check-circle text-green-500"></i>
                        </div>
                        <div>
                            <p class="text-sm font-medium">
                                {{ $related->name }}
                            </p>
                            <p class="text-xs text-gray-500">
                                {{ $related->description }}
                            </p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        <!-- Footer Action Buttons -->
        <div class="flex justify-end space-x-3 border-t border-gray-200 pt-4">
            <button
                type="button"
                class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-150 flex items-center"
                wire:click="closeViewModal"
            >
                <i class="fas fa-times mr-2"></i> Fechar
            </button>
            <button
                type="button"
                class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-150 flex items-center"
                wire:click="edit({{ $viewingRecord->id }})"
            >
                <i class="fas fa-edit mr-2"></i> Editar Registro
            </button>
        </div>
    </div>
</div>
@endif
```

## Considerações Importantes

1. **Carregamento de Relacionamentos**: No método `view()`, use `with()` para carregar relacionamentos e evitar problemas de N+1 queries
2. **Organização Visual**: Mantenha os dados organizados em seções lógicas com cabeçalhos claros
3. **Estados Vazios**: Sempre forneça feedback visual quando campos estiverem vazios
4. **Navegação entre Modais**: Ao transicionar da visualização para edição, certifique-se de fechar o modal de visualização primeiro
5. **Responsividade**: Utilize o sistema de grid para garantir boa experiência em todos os tamanhos de tela

## Conectando Ações CRUD

No fluxo completo de um CRUD, a modal de visualização serve como ponto central:

1. Da **Listagem** para **Visualização**: Usuários clicam no botão "Visualizar" em uma linha da tabela
2. Da **Visualização** para **Edição**: O botão "Editar" no modal de visualização abre o formulário de edição
3. Da **Visualização** para **Exclusão**: Um botão de exclusão pode iniciar o processo de confirmação de exclusão
4. Da **Edição/Criação** para **Visualização**: Após salvar, você pode mostrar o modal de visualização para confirmar as alterações

# Como Implementar um CRUD Completo (Comando para IA)

Ao trabalhar com um assistente de IA para criar CRUDs, você pode usar o seguinte comando para receber uma implementação completa:

```
Gere um CRUD completo de Livewire para a entidade [NOME_DA_ENTIDADE] com os seguintes campos:
[LISTA_DOS_CAMPOS_E_TIPOS]

Inclua:
- Componente Livewire com todas as propriedades e métodos necessários
- Template Blade com tabela responsiva, modais de criação/edição/visualização/exclusão
- Validação completa para todos os campos
- Sistema de notificação para feedback ao usuário
- Filtros de pesquisa para os campos: [CAMPOS_FILTRÁVEIS]
- Ordenação para os campos: [CAMPOS_ORDENÁVEIS]
- Paginação com [NÚMERO] itens por página
- [RELACIONAMENTOS_SE_APLICÁVEL]

Baseie a implementação nas melhores práticas do Laravel Livewire CRUD Guide.
```

Exemplo de uso:

```
Gere um CRUD completo de Livewire para a entidade "Produto" com os seguintes campos:
- nome (string, obrigatório)
- descrição (text, opcional)
- preço (decimal, obrigatório)
- categoria_id (foreign key, obrigatório)
- status (enum: 'ativo', 'inativo', 'esgotado', obrigatório)
- data_criacao (timestamp, automático)
- data_atualizacao (timestamp, automático)

Inclua:
- Componente Livewire com todas as propriedades e métodos necessários
- Template Blade com tabela responsiva, modais de criação/edição/visualização/exclusão
- Validação completa para todos os campos
- Sistema de notificação para feedback ao usuário
- Filtros de pesquisa para os campos: nome, categoria_id, status
- Ordenação para os campos: nome, preço, data_criacao
- Paginação com 10 itens por página
- Relacionamento com modelo Categoria (belongs to)

Baseie a implementação nas melhores práticas do Laravel Livewire CRUD Guide.
```

Com este comando, você receberá uma implementação completa e pronta para uso de um CRUD com todas as funcionalidades modernas e seguindo as melhores práticas definidas neste guia.

## Table Implementation
