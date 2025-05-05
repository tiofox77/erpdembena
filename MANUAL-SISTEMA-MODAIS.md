# Manual: Sistema de Modais para Gerenciamento de Fornecedores

## Visão Geral da Arquitetura

O sistema de modais para gerenciamento de fornecedores no ERPDEMBENA implementa uma solução completa baseada em Livewire e Alpine.js. Esta integração permite criar interações dinâmicas e responsivas sem recarregar a página, oferecendo uma experiência fluida para o usuário.

### Componentes Principais:

1. **Controlador Livewire**: `app/Livewire/SupplyChain/Suppliers.php`
2. **View Principal**: `resources/views/livewire/supply-chain/suppliers.blade.php`
3. **View de Modais**: `resources/views/livewire/supply-chain/suppliers-modals.blade.php`

## Funcionamento do Sistema de Modais

### 1. Abertura de Modal

#### 1.1 Botão para Abrir Modal
```php
<button wire:click="openAddModal" 
    class="inline-flex items-center px-4 py-2 bg-blue-600 ...">
    <i class="fas fa-plus-circle mr-2 animate-pulse"></i>
    {{ __('livewire/suppliers.add_supplier') }}
</button>
```

#### 1.2 Método do Controlador
```php
public function openAddModal()
{
    $this->create();
}

public function create()
{
    $this->resetForm();
    $this->showModal = true;
    $this->generateSupplierCode();
    
    // Emitir evento para abrir o modal
    $this->dispatch('showModal');
}
```

#### 1.3 Estrutura do Modal (Alpine.js + Livewire)
```php
@if($showModal)
<div 
    x-data="{ show: @entangle('showModal') }"
    x-show="show"
    x-cloak
    x-transition:enter="ease-out duration-300"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="ease-in duration-200"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed inset-0 z-50 overflow-y-auto"
    ...
>
```

### 2. Edição de Registro

#### 2.1 Botão de Edição
```php
<button wire:click="edit({{ $supplier->id }})" 
    class="text-indigo-600 hover:text-indigo-900 ...">
    <i class="fas fa-edit"></i>
</button>
```

#### 2.2 Método de Edição
```php
public function edit($id)
{
    $this->resetForm();
    $this->supplier_id = $id;
    $supplier = Supplier::findOrFail($id);
    
    // Carregar dados do fornecedor
    $this->name = $supplier->name;
    $this->code = $supplier->code;
    $this->contact_person = $supplier->contact_person;
    // ...outros campos
    
    $this->showModal = true;
}
```

### 3. Salvamento de Dados

#### 3.1 Formulário com Prevenção de Reload
```php
<form wire:submit.prevent="save">
    <!-- Campos do formulário -->
</form>
```

#### 3.2 Botão de Salvar
```php
<button type="button" wire:click="save"
    class="w-full inline-flex justify-center rounded-md...">
    <i class="fas fa-save mr-2"></i>
    <span wire:loading.remove wire:target="save">
        {{ __('livewire/layout.save') }}
    </span>
    <span wire:loading wire:target="save">
        {{ __('livewire/layout.saving') }}...
    </span>
</button>
```

#### 3.3 Método de Salvamento
```php
public function save()
{
    // Validação dos dados
    $validatedData = $this->validate($this->rules());
    
    try {
        DB::beginTransaction();
        
        if ($this->supplier_id) {
            // Atualizar fornecedor existente
            $supplier = Supplier::findOrFail($this->supplier_id);
        } else {
            // Criar novo fornecedor
            $supplier = new Supplier();
        }
        
        // Atribuir valores validados
        $supplier->name = $this->name;
        $supplier->code = $this->code;
        // ...outros campos
        
        $supplier->save();
        
        DB::commit();
        
        // Notificação de sucesso
        $this->dispatch('notify', [
            'type' => 'success', 
            'title' => __('livewire/suppliers.success'), 
            'message' => $this->supplier_id 
                ? __('livewire/suppliers.supplier_updated') 
                : __('livewire/suppliers.supplier_created')
        ]);
        
        // Fechar modal
        $this->closeModal();
        
    } catch (\Exception $e) {
        DB::rollBack();
        // Notificação de erro
        $this->dispatch('notify', [
            'type' => 'error', 
            'title' => __('livewire/suppliers.error'), 
            'message' => $e->getMessage()
        ]);
    }
}
```

### 4. Exclusão de Registro

#### 4.1 Botão para Confirmar Exclusão
```php
<button wire:click="confirmDelete({{ $supplier->id }})" 
    class="text-red-600 hover:text-red-900 ...">
    <i class="fas fa-trash"></i>
</button>
```

#### 4.2 Método de Confirmação
```php
public function confirmDelete($id)
{
    $supplier = Supplier::findOrFail($id);
    $this->deleteSupplierName = $supplier->name;
    $this->deleteSupplier = $supplier;
    $this->showConfirmDelete = true;
    $this->itemToDelete = $id;
    $this->showDeleteModal = true;
}
```

#### 4.3 Método de Exclusão
```php
public function delete()
{
    try {
        $supplier = Supplier::findOrFail($this->itemToDelete);
        $supplier->delete();
        
        // Notificação de sucesso
        $this->dispatch('notify', [
            'type' => 'success', 
            'title' => __('livewire/suppliers.success'), 
            'message' => __('livewire/suppliers.supplier_deleted')
        ]);
        
    } catch (\Exception $e) {
        // Notificação de erro
        $this->dispatch('notify', [
            'type' => 'error', 
            'title' => __('livewire/suppliers.error'), 
            'message' => $e->getMessage()
        ]);
    }
    
    // Fechar modal
    $this->closeDeleteModal();
}
```

### 5. Validação em Tempo Real

#### 5.1 Definição de Regras
```php
protected function rules()
{
    return [
        'name' => 'required|string|max:255',
        'code' => ['required', 'string', 'max:50', 
            Rule::unique('sc_suppliers', 'code')->ignore($this->supplier_id)],
        'contact_person' => 'nullable|string|max:255',
        // Outras regras
    ];
}
```

#### 5.2 Método de Validação em Tempo Real
```php
public function updated($propertyName)
{
    $this->validateOnly($propertyName);
}
```

#### 5.3 Exibição de Erros
```php
<input type="text" id="name" wire:model="name"
    class="block w-full bg-white border border-gray-300 rounded-md ...">
@error('name') 
    <p class="mt-1 text-sm text-red-600">{{ $message }}</p> 
@enderror
```

### 6. Fechamento de Modal

#### 6.1 Botão para Fechar
```php
<button type="button" wire:click="closeModal"
    class="text-white hover:text-gray-200 ...">
    <i class="fas fa-times"></i>
</button>
```

#### 6.2 Método de Fechamento
```php
public function closeModal()
{
    $this->showModal = false;
    $this->showViewModal = false;
    $this->resetForm();
    
    // Emitir eventos para fechar os modais
    $this->dispatch('hideModal');
    $this->dispatch('hideViewModal');
}
```

## Implementação Passo a Passo

Para implementar esta solução em um novo projeto, siga estes passos:

### Passo 1: Configurar o Controlador Livewire

1. Crie um novo componente Livewire:
   ```bash
   php artisan make:livewire YourModule/YourComponent
   ```

2. Configure as propriedades necessárias:
   ```php
   // Propriedades para o formulário
   public $item_id;
   public $name;
   public $code;
   // ...outras propriedades
   
   // Propriedades para controle de estado
   public $search = '';
   public $sortField = 'name';
   public $sortDirection = 'asc';
   public $showModal = false;
   public $showDeleteModal = false;
   public $itemToDelete = null;
   ```

3. Implemente os métodos principais:
   - `rules()` - Regras de validação
   - `updated($propertyName)` - Validação em tempo real
   - `resetForm()` - Limpar formulário
   - `create()` - Abrir modal de criação
   - `edit($id)` - Abrir modal de edição
   - `save()` - Salvar dados
   - `confirmDelete($id)` - Confirmar exclusão
   - `delete()` - Excluir registro
   - `closeModal()` - Fechar modal

### Passo 2: Criar o Template Principal

1. Crie uma tabela para listar os registros
2. Adicione botões para criar, editar e excluir
3. Inclua o arquivo de modais:
   ```php
   @include('livewire.your-module.your-component-modals')
   ```

### Passo 3: Criar os Modais

1. Crie um arquivo separado para os modais
2. Implemente as modais usando Alpine.js e Livewire:
   ```php
   @if($showModal)
   <div 
       x-data="{ show: @entangle('showModal') }"
       x-show="show"
       x-cloak
       x-transition:enter="ease-out duration-300"
       x-transition:enter-start="opacity-0"
       x-transition:enter-end="opacity-100"
       ...
   >
       <!-- Conteúdo do modal -->
   </div>
   @endif
   ```

3. Crie os formulários com validação em tempo real:
   ```php
   <form wire:submit.prevent="save">
       <!-- Campos do formulário com wire:model -->
   </form>
   ```

### Passo 4: Integrar com Alpine.js

1. Certifique-se de que Alpine.js está incluído no seu projeto
2. Use a diretiva `@entangle` para sincronizar propriedades Livewire com Alpine.js:
   ```php
   x-data="{ show: @entangle('showModal') }"
   ```
3. Adicione transições para melhorar a experiência:
   ```php
   x-transition:enter="ease-out duration-300"
   x-transition:enter-start="opacity-0"
   x-transition:enter-end="opacity-100"
   ```

### Passo 5: Adicionar Notificações

1. Implemente um sistema de notificações toast:
   ```php
   $this->dispatch('notify', [
       'type' => 'success', 
       'title' => 'Sucesso', 
       'message' => 'Operação realizada com sucesso'
   ]);
   ```

## Dicas e Boas Práticas

1. **Validação em Tempo Real**: Use `wire:model.debounce.xxx` para evitar validações excessivas.
2. **Tratamento de Erros**: Sempre use blocos try-catch e transações de banco de dados.
3. **Indicadores de Carregamento**: Use `wire:loading` para mostrar feedback durante operações.
4. **Otimização de Performance**: Evite carregar dados desnecessários nas propriedades públicas.
5. **Segurança**: Sempre valide os dados antes de salvar no banco de dados.
6. **Tradução**: Use sistema de tradução para textos para facilitar a internacionalização.
