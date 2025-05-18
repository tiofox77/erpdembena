@php
use Illuminate\Support\Str;
@endphp

<div>
    <!-- Lista de Formulários -->
    <div class="mb-6 bg-white rounded-lg shadow-md">
        <div class="flex items-center bg-gradient-to-r from-blue-600 to-blue-700 px-4 py-3">
            <h2 class="text-lg font-medium text-white flex items-center">
                <i class="fas fa-list mr-2"></i>
                Formulários Disponíveis
            </h2>
        </div>
        
        <div class="p-4">
            <!-- Barra de ferramentas superior -->
            <div class="flex flex-col md:flex-row justify-between items-center mb-4 gap-2">
                <div class="relative w-full md:w-64">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-search text-gray-400"></i>
                    </div>
                    <input wire:model.debounce.300ms="search" type="search" 
                        class="pl-10 pr-3 py-2 w-full border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                        placeholder="Buscar formulários...">
                </div>
                
                <div class="flex flex-col sm:flex-row gap-2">
                    <label for="import-form-input" 
                        class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-all duration-200 ease-in-out transform hover:scale-105 hover:shadow-lg cursor-pointer">
                        <i class="fas fa-file-import mr-2"></i>
                        Importar
                    </label>
                    <input id="import-form-input" type="file" wire:model.live="importFile" accept=".json" class="hidden" />
                    
                    <button wire:click="create" 
                        class="w-full sm:w-auto inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 ease-in-out transform hover:scale-105 hover:shadow-lg">
                        <i class="fas fa-plus-circle mr-2"></i>
                        Novo Formulário
                    </button>
                </div>
            </div>
            
            <!-- Tabela de formulários -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <div class="flex items-center">
                                    <button wire:click="sortBy('name')" class="uppercase tracking-wider">
                                        Nome
                                    </button>
                                    @if($sortField === 'name')
                                        <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1"></i>
                                    @endif
                                </div>
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Descrição
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <div class="flex items-center">
                                    <button wire:click="sortBy('is_active')" class="uppercase tracking-wider">
                                        Status
                                    </button>
                                    @if($sortField === 'is_active')
                                        <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1"></i>
                                    @endif
                                </div>
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <div class="flex items-center">
                                    <button wire:click="sortBy('created_at')" class="uppercase tracking-wider">
                                        Criado em
                                    </button>
                                    @if($sortField === 'created_at')
                                        <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1"></i>
                                    @endif
                                </div>
                            </th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Ações
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($forms as $form)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $form->name }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-500 line-clamp-2">{{ $form->description }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    {{ $form->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                    {{ $form->is_active ? 'Ativo' : 'Inativo' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $form->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex justify-end space-x-2">
                                    <button wire:click="edit({{ $form->id }})" 
                                        class="text-indigo-600 hover:text-indigo-900 transition-colors">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    
                                    <button wire:click="previewForm({{ $form->id }})" 
                                        class="text-blue-600 hover:text-blue-900 transition-colors transform hover:scale-110">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    
                                    <button wire:click="exportForm({{ $form->id }})" 
                                        class="text-green-600 hover:text-green-900 transition-colors transform hover:scale-110">
                                        <i class="fas fa-file-export"></i>
                                    </button>
                                    
                                    <button wire:click="createField({{ $form->id }})" 
                                        class="text-blue-600 hover:text-blue-900 transition-colors transform hover:scale-110">
                                        <i class="fas fa-plus-circle"></i>
                                    </button>
                                    
                                    <button wire:click="confirmDelete({{ $form->id }})" 
                                        class="text-red-600 hover:text-red-900 transition-colors">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-6 py-4 whitespace-nowrap">
                                <div class="flex flex-col items-center justify-center py-6 space-y-2">
                                    <div class="flex-shrink-0 bg-gray-100 p-3 rounded-full">
                                        <i class="fas fa-clipboard-list text-gray-400 text-2xl"></i>
                                    </div>
                                    <p class="text-gray-500 text-sm">Nenhum formulário personalizado encontrado</p>
                                    <button wire:click="create" 
                                        class="mt-2 inline-flex items-center px-3 py-1 bg-blue-600 border border-transparent rounded-md text-sm font-medium text-white hover:bg-blue-700 focus:outline-none transition-colors">
                                        <i class="fas fa-plus-circle mr-1"></i>
                                        Criar o primeiro formulário
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- Paginação -->
            <div class="mt-4">
                {{ $forms->links() }}
            </div>
        </div>
    </div>
    
    <!-- Campos do Formulário (quando um formulário está selecionado) -->
    @if($currentFormId)
    <div class="mb-6 bg-white rounded-lg shadow-md">
        <div class="flex items-center justify-between bg-gradient-to-r from-green-600 to-green-700 px-4 py-3">
            <h2 class="text-lg font-medium text-white flex items-center">
                <i class="fas fa-list-alt mr-2"></i>
                Campos do Formulário
            </h2>
            <button wire:click="createField({{ $currentFormId }})" 
                class="inline-flex items-center px-3 py-1 bg-white border border-transparent rounded-md text-sm font-medium text-green-700 hover:bg-gray-100 focus:outline-none transition-colors">
                <i class="fas fa-plus-circle mr-1"></i>
                Adicionar Campo
            </button>
        </div>
        
        <div class="p-4">
            @if(count($formFields) > 0)
                <ul class="divide-y divide-gray-200">
                    @foreach($formFields as $field)
                    <li class="py-4">
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <div class="flex items-center">
                                    <span class="flex-shrink-0 h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center mr-3">
                                        <i class="
                                            @if($field->type === 'text') fas fa-font
                                            @elseif($field->type === 'textarea') fas fa-paragraph
                                            @elseif($field->type === 'number') fas fa-hashtag
                                            @elseif($field->type === 'email') fas fa-envelope
                                            @elseif($field->type === 'date') fas fa-calendar
                                            @elseif($field->type === 'select') fas fa-list
                                            @elseif($field->type === 'checkbox') fas fa-check-square
                                            @elseif($field->type === 'radio') fas fa-dot-circle
                                            @elseif($field->type === 'file') fas fa-file-upload
                                            @else fas fa-question
                                            @endif text-blue-600"></i>
                                    </span>
                                    <div>
                                        <h4 class="text-md font-medium text-gray-900">{{ $field->label }}</h4>
                                        <div class="flex items-center text-sm text-gray-500">
                                            <span class="mr-2">
                                                <i class="fas fa-tag mr-1"></i>
                                                {{ $field->name }}
                                            </span>
                                            <span class="mr-2">|</span>
                                            <span class="mr-2">
                                                <i class="fas fa-code mr-1"></i>
                                                {{ $fieldTypes[$field->type] ?? $field->type }}
                                            </span>
                                            @if($field->is_required)
                                            <span class="mr-2">|</span>
                                            <span class="text-red-600">
                                                <i class="fas fa-asterisk mr-1"></i>
                                                Obrigatório
                                            </span>
                                            @endif
                                        </div>
                                        @if($field->description)
                                        <p class="mt-1 text-xs text-gray-500">{{ $field->description }}</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2">
                                <button wire:click="editField({{ $field->id }})" class="text-indigo-600 hover:text-indigo-900 transition-colors">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button wire:click="deleteField({{ $field->id }})" class="text-red-600 hover:text-red-900 transition-colors">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </li>
                    @endforeach
                </ul>
            @else
                <div class="flex flex-col items-center justify-center py-6 space-y-2">
                    <div class="flex-shrink-0 bg-gray-100 p-3 rounded-full">
                        <i class="fas fa-clipboard-list text-gray-400 text-2xl"></i>
                    </div>
                    <p class="text-gray-500 text-sm">Nenhum campo adicionado a este formulário</p>
                    <button wire:click="createField({{ $currentFormId }})" 
                        class="mt-2 inline-flex items-center px-3 py-1 bg-green-600 border border-transparent rounded-md text-sm font-medium text-white hover:bg-green-700 focus:outline-none transition-colors">
                        <i class="fas fa-plus-circle mr-1"></i>
                        Adicionar o primeiro campo
                    </button>
                </div>
            @endif
        </div>
    </div>
    @endif
    
    <!-- Modal para criar/editar formulário -->
    @if($showFormModal)
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-lg max-w-lg w-full mx-auto">
            <div class="flex items-center justify-between bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-4 rounded-t-lg">
                <h3 class="text-lg font-medium text-white">
                    {{ $currentFormId ? 'Editar Formulário' : 'Novo Formulário' }}
                </h3>
                <button wire:click="closeModal" class="text-white hover:text-gray-200">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="p-6">
                <form wire:submit.prevent="save">
                    <div class="mb-4">
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nome do Formulário *</label>
                        <input wire:model="currentForm.name" type="text" id="name" 
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50
                                  @error('currentForm.name') border-red-300 focus:border-red-500 focus:ring-red-500 @enderror">
                        @error('currentForm.name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    
                    <div class="mb-4">
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Descrição</label>
                        <textarea wire:model="currentForm.description" id="description" rows="3" 
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50
                                  @error('currentForm.description') border-red-300 focus:border-red-500 focus:ring-red-500 @enderror"></textarea>
                        @error('currentForm.description') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    
                    <div class="mb-4">
                        <label for="entity_type" class="block text-sm font-medium text-gray-700 mb-1">Tipo de Entidade *</label>
                        <input type="text" id="entity_type" wire:model="currentForm.entity_type" readonly
                            class="w-full rounded-md border-gray-300 bg-gray-100 shadow-sm">
                        <p class="mt-1 text-xs text-gray-500">Estes formulários são específicos para notas de envio.</p>
                    </div>
                    
                    <div class="mb-4 flex items-center">
                        <input wire:model="currentForm.is_active" type="checkbox" id="is_active" 
                            class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50">
                        <label for="is_active" class="ml-2 block text-sm text-gray-700">Formulário Ativo</label>
                    </div>
                    
                    <div class="flex justify-end space-x-2 mt-6">
                        <button type="button" wire:click="closeModal" 
                            class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Cancelar
                        </button>
                        <button type="submit" 
                            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            {{ $currentFormId ? 'Atualizar' : 'Criar' }} Formulário
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
    
    <!-- Modal para confirmação de exclusão -->
    @if($showDeleteModal)
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-lg max-w-md w-full mx-auto">
            <div class="flex items-center justify-between bg-gradient-to-r from-red-600 to-red-700 px-6 py-4 rounded-t-lg">
                <h3 class="text-lg font-medium text-white flex items-center">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    Confirmar Exclusão
                </h3>
                <button wire:click="closeModal" class="text-white hover:text-gray-200">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="p-6">
                <p class="text-gray-700 mb-4">Tem certeza que deseja excluir este formulário? Esta ação não pode ser desfeita.</p>
                
                <div class="flex justify-end space-x-2">
                    <button type="button" wire:click="closeModal" 
                        class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                        Cancelar
                    </button>
                    <button type="button" wire:click="delete" 
                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                        Excluir Formulário
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Modal para criar/editar campo do formulário -->
    @if($showFieldModal)
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4 overflow-y-auto">
        <div class="bg-white rounded-lg shadow-lg w-full max-w-6xl mx-auto my-8" style="width: 80%;">
            <div class="flex items-center justify-between bg-gradient-to-r from-green-600 to-green-700 px-6 py-4 rounded-t-lg">
                <h3 class="text-lg font-medium text-white">
                    <i class="fas fa-plus-circle mr-2"></i>
                    {{ $currentFieldId ? 'Editar Campo' : 'Novo Campo' }}
                </h3>
                <button wire:click="closeModal" class="text-white hover:text-gray-200 focus:outline-none" @click="console.log('Fechando modal de campo')">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <script>
                document.addEventListener('livewire:initialized', () => {
                    console.log('Modal de campo inicializado');
                    @this.on('field-added', () => {
                        console.log('Campo adicionado com sucesso');
                        // Fecha o modal após adicionar o campo
                        @this.set('showFieldModal', false);
                    });
                    
                    @this.on('field-updated', () => {
                        console.log('Campo atualizado com sucesso');
                        // Fecha o modal após atualizar o campo
                        @this.set('showFieldModal', false);
                    });
                    
                    @this.on('error', (message) => {
                        console.error('Erro no Livewire:', message);
                        alert('Ocorreu um erro: ' + message);
                    });
                });
            </script>
            <div class="p-6">
                <form wire:submit.prevent="saveField">
                    <div class="mb-4">
                        <label for="field_label" class="block text-sm font-medium text-gray-700 mb-1">Rótulo do Campo *</label>
                        <input wire:model="currentField.label" type="text" id="field_label" 
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring focus:ring-green-500 focus:ring-opacity-50
                                  @error('currentField.label') border-red-300 focus:border-red-500 focus:ring-red-500 @enderror">
                        @error('currentField.label') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    
                    <div class="mb-4">
                        <label for="field_name" class="block text-sm font-medium text-gray-700 mb-1">Nome do Campo *</label>
                        <div class="relative">
                            <input wire:model="currentField.name" type="text" id="field_name" 
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring focus:ring-green-500 focus:ring-opacity-50
                                      @error('currentField.name') border-red-300 focus:border-red-500 focus:ring-red-500 @enderror">
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                <i class="fas fa-code text-gray-400"></i>
                            </div>
                        </div>
                        <p class="mt-1 text-xs text-gray-500">Identificador único (apenas letras, números e sublinhados).</p>
                        @error('currentField.name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    
                    <div class="mb-4">
                        <label for="field_type" class="block text-sm font-medium text-gray-700 mb-1">Tipo de Campo *</label>
                        <select wire:model.live="currentField.type" id="field_type" 
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring focus:ring-green-500 focus:ring-opacity-50">
                            @foreach($fieldTypes as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="mb-4">
                        <label for="field_description" class="block text-sm font-medium text-gray-700 mb-1">Descrição</label>
                        <textarea wire:model="currentField.description" id="field_description" rows="2" 
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring focus:ring-green-500 focus:ring-opacity-50"></textarea>
                        <p class="mt-1 text-xs text-gray-500">Texto de ajuda exibido abaixo do campo.</p>
                    </div>
                    
                    <!-- Opções para select, checkbox, radio -->
                    @if(in_array($currentField['type'], ['select', 'checkbox', 'radio']))
                    <div class="mb-4 p-4 bg-gray-50 rounded-md">
                        <h4 class="font-medium text-gray-700 mb-2">Opções do Campo</h4>
                        
                        <div class="space-y-2 mb-3">
                            @foreach($currentField['options'] as $index => $option)
                            <div class="flex items-center space-x-2">
                                <span class="bg-green-100 text-green-800 px-2 py-1 rounded-md text-xs">{{ $option['label'] }}</span>
                                <span class="text-gray-500 text-xs">({{ $option['value'] }})</span>
                                <button type="button" wire:click="removeOption({{ $index }})" class="text-red-500 hover:text-red-700">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                            @endforeach
                        </div>
                        
                        <div class="flex flex-col sm:flex-row sm:space-x-2 space-y-2 sm:space-y-0">
                            <div class="flex-1">
                                <input wire:model="tempOptionLabel" type="text" placeholder="Rótulo da opção" 
                                    class="w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-green-500 focus:ring focus:ring-green-500 focus:ring-opacity-50">
                            </div>
                            <div class="flex-1">
                                <input wire:model="tempOptionValue" type="text" placeholder="Valor da opção" 
                                    class="w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-green-500 focus:ring focus:ring-green-500 focus:ring-opacity-50">
                            </div>
                            <div>
                                <button type="button" wire:click="addOption" 
                                    class="w-full sm:w-auto inline-flex items-center justify-center px-3 py-2 bg-green-600 border border-transparent rounded-md text-sm font-medium text-white hover:bg-green-700 focus:outline-none transition-colors">
                                    <i class="fas fa-plus mr-1"></i> Adicionar
                                </button>
                            </div>
                        </div>
                        @error('tempOptionLabel') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        @error('tempOptionValue') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    @endif
                    
                    <!-- Configuração de relacionamento -->
                    @if($currentField['type'] === 'relationship')
                    <div class="mb-4 p-4 bg-gray-50 rounded-md">
                        <h4 class="font-medium text-gray-700 mb-3">Configuração de Relacionamento</h4>
                        
                        <div class="space-y-4">
                            <!-- Model Selection -->
                            <div>
                                <div class="flex items-center justify-between mb-1">
                                    <label class="block text-sm font-medium text-gray-700">Modelo Relacionado</label>
                                    @if($relationshipConfig['model'] && $availableColumns === null)
                                        <span class="flex items-center text-xs text-yellow-600">
                                            <svg class="animate-spin -ml-1 mr-1 h-3 w-3 text-yellow-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                            Carregando campos...
                                        </span>
                                    @endif
                                </div>
                                <select wire:model.live="relationshipConfig.model" 
                                    wire:change="$set('relationshipConfig.display_field', '')"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring focus:ring-green-500 focus:ring-opacity-50 {{ $errors->has('relationshipConfig.model') ? 'border-red-300 text-red-900 placeholder-red-300 focus:border-red-500 focus:ring-red-500' : '' }}"
                                    @if($relationshipConfig['model'] && $availableColumns === null) disabled @endif>
                                    <option value="">Selecione um modelo</option>
                                    @foreach($relationshipModels as $modelClass => $modelLabel)
                                        <option value="{{ $modelClass }}" {{ $modelClass === $relationshipConfig['model'] ? 'selected' : '' }}>
                                            {{ $modelLabel }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('relationshipConfig.model')
                                    <p class="mt-1 text-sm text-red-600">
                                        <i class="fas fa-exclamation-circle mr-1"></i> {{ $message }}
                                    </p>
                                @enderror
                            </div>
                            
                            @if($relationshipConfig['model'])
                                <!-- Display Field Selection -->
                                <div>
                                    <div class="flex items-center justify-between mb-1">
                                        <label class="block text-sm font-medium text-gray-700">Campo de Exibição</label>
                                        @if(!empty($relationshipSampleData) && $relationshipConfig['display_field'])
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                {{ count($relationshipSampleData) }} itens carregados
                                            </span>
                                        @endif
                                    </div>
                                    
                                    @if($availableColumns === null && $relationshipConfig['model'])
                                        <div class="flex items-center justify-center p-4 bg-gray-50 rounded-md">
                                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                            <span class="text-sm text-gray-600">Carregando campos disponíveis...</span>
                                        </div>
                                    @elseif(!empty($availableColumns))
                                        <select wire:model.live="relationshipConfig.display_field" 
                                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring focus:ring-green-500 focus:ring-opacity-50 {{ $errors->has('relationshipConfig.display_field') ? 'border-red-300 text-red-900 placeholder-red-300 focus:border-red-500 focus:ring-red-500' : '' }}">
                                            <option value="">Selecione um campo</option>
                                            @foreach($availableColumns as $column)
                                                <option value="{{ $column }}" {{ $column === $relationshipConfig['display_field'] ? 'selected' : '' }}>
                                                    {{ $column }}
                                                </option>
                                            @endforeach
                                        </select>
                                    @else
                                        <div class="p-3 rounded-md bg-yellow-50">
                                            <div class="flex">
                                                <div class="flex-shrink-0">
                                                    <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                                    </svg>
                                                </div>
                                                <div class="ml-3">
                                                    <h3 class="text-sm font-medium text-yellow-800">Não foi possível carregar os campos</h3>
                                                    <div class="mt-2 text-sm text-yellow-700">
                                                        <p>Verifique se o modelo está correto e se você tem permissão para acessá-lo.</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                    
                                    @error('relationshipConfig.display_field')
                                        <p class="mt-1 text-sm text-red-600">
                                            <i class="fas fa-exclamation-circle mr-1"></i> {{ $message }}
                                        </p>
                                    @enderror
                                    
                                    @if(!empty($errors->get('relationship_sample_data')))
                                        <p class="mt-1 text-sm text-red-600">
                                            <i class="fas fa-exclamation-circle mr-1"></i> {{ $errors->first('relationship_sample_data') }}
                                        </p>
                                    @endif
                                </div>
                                
                                <!-- Relationship Type -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de Relacionamento</label>
                                    <select wire:model.live="relationshipConfig.relationship_type" 
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring focus:ring-green-500 focus:ring-opacity-50">
                                        <option value="belongsTo" {{ ($relationshipConfig['relationship_type'] ?? 'belongsTo') === 'belongsTo' ? 'selected' : '' }}>
                                            Pertence a (Seleção Única)
                                        </option>
                                        <option value="hasMany" {{ ($relationshipConfig['relationship_type'] ?? 'belongsTo') === 'hasMany' ? 'selected' : '' }}>
                                            Tem Muitos (Seleção Múltipla)
                                        </option>
                                    </select>
                                    @error('relationshipConfig.relationship_type')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                
                                <!-- Preview of how the field will look -->
                                <div class="mt-4 p-4 bg-white border border-gray-200 rounded-md shadow-sm">
                                    <div class="flex items-center justify-between mb-3">
                                        <h4 class="text-sm font-medium text-gray-800">
                                            <i class="fas fa-eye mr-1.5 text-blue-500"></i>
                                            Prévia do Campo de Seleção
                                        </h4>
                                        <div class="flex items-center space-x-2">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $relationshipConfig['relationship_type'] === 'belongsTo' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800' }}">
                                                {{ $relationshipConfig['relationship_type'] === 'belongsTo' ? 'Seleção Única' : 'Seleção Múltipla' }}
                                            </span>
                                            @if(!empty($relationshipSampleData) && count($relationshipSampleData) > 0)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                                    <svg class="h-3 w-3 mr-1 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                    </svg>
                                                    {{ count($relationshipSampleData) }} itens carregados
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                    
                                    <div class="space-y-3">
                                        @if(empty($relationshipSampleData) || empty($relationshipConfig['display_field']))
                                            <div class="p-3 rounded-md bg-yellow-50 border border-yellow-100">
                                                <div class="flex">
                                                    <div class="flex-shrink-0">
                                                        <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                                        </svg>
                                                    </div>
                                                    <div class="ml-3">
                                                        <h3 class="text-sm font-medium text-yellow-800">Nenhum dado de amostra disponível</h3>
                                                        <div class="mt-1 text-sm text-yellow-700">
                                                            <p>Selecione um modelo e um campo de exibição válido para visualizar uma prévia.</p>
                                                            @if($relationshipConfig['model'] && empty($relationshipSampleData) && $availableColumns !== null)
                                                                <p class="mt-1">Nenhum registro encontrado no modelo selecionado.</p>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @else
                                            <div class="relative rounded-md shadow-sm
                                                @if($relationshipConfig['relationship_type'] === 'hasMany') 
                                                    border-2 border-dashed border-gray-200 rounded-md p-2 bg-gray-50 min-h-[60px]
                                                @endif">
                                                <select class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 transition duration-150 ease-in-out" 
                                                    @if($relationshipConfig['relationship_type'] === 'hasMany') multiple @endif
                                                    disabled>
                                                    <option value="" disabled {{ empty($relationshipSampleData) ? 'selected' : '' }}>Selecione {{ $relationshipModels[$relationshipConfig['model']] ?? 'um item' }}</option>
                                                    @foreach($relationshipSampleData as $item)
                                                        @php
                                                            $displayField = $relationshipConfig['display_field'];
                                                            $displayValue = $item[$displayField] ?? '';
                                                            $displayValue = is_scalar($displayValue) ? $displayValue : json_encode($displayValue);
                                                            $truncatedValue = Str::limit($displayValue, 50);
                                                        @endphp
                                                        <option value="{{ $item['id'] }}" title="{{ $displayValue }}">
                                                            {{ $truncatedValue }}
                                                            @if(strlen($displayValue) > 50) ... @endif
                                                        </option>
                                                    @endforeach
                                                </select>
                                                
                                                <!-- Dica sobre como o campo será exibido -->
                                                <div class="mt-2 text-xs text-gray-500 flex items-start">
                                                    <svg class="h-4 w-4 mr-1 text-blue-400 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                    <span>
                                                        @if($relationshipConfig['relationship_type'] === 'belongsTo')
                                                            Os usuários verão uma lista suspensa com os itens acima.
                                                        @else
                                                            Os usuários poderão selecionar múltiplos itens da lista acima.
                                                        @endif
                                                    </span>
                                                </div>
                                            </div>
                                            
                                            <!-- Mostrar os primeiros 3 itens como exemplo -->
                                            @if(count($relationshipSampleData) > 0)
                                                <div class="mt-2">
                                                    <p class="text-xs font-medium text-gray-500 mb-1">Exemplo de itens ({{ count($relationshipSampleData) }} no total):</p>
                                                    <ul class="text-xs text-gray-600 space-y-1 max-h-40 overflow-y-auto border border-gray-100 rounded p-2 bg-gray-50">
                                                        @foreach(array_slice($relationshipSampleData, 0, 5) as $item)
                                                            @php
                                                                $displayField = $relationshipConfig['display_field'];
                                                                $displayValue = $item[$displayField] ?? '';
                                                                $displayValue = is_scalar($displayValue) ? $displayValue : json_encode($displayValue);
                                                                $truncatedValue = Str::limit($displayValue, 60);
                                                            @endphp
                                                            <li class="flex items-start">
                                                                <span class="text-gray-400 mr-2">#{{ $item['id'] ?? '?' }}</span>
                                                                <span title="{{ $displayValue }}">
                                                                    {{ $truncatedValue }}
                                                                    @if(strlen($displayValue) > 60) ... @endif
                                                                </span>
                                                            </li>
                                                        @endforeach
                                                        @if(count($relationshipSampleData) > 5)
                                                            <li class="text-gray-400 text-xs italic">
                                                                +{{ count($relationshipSampleData) - 5 }} mais itens...
                                                            </li>
                                                        @endif
                                                    </ul>
                                                </div>
                                            @endif
                                        @endif
                                    </div>

                                    <div class="mt-3 pt-3 border-t border-gray-100">
                                        <div class="flex items-start">
                                            <div class="flex-shrink-0">
                                                <i class="fas fa-info-circle text-blue-500 mt-0.5"></i>
                                            </div>
                                            <div class="ml-2">
                                                @if($relationshipConfig['display_field'])
                                                    <p class="text-xs text-gray-600">
                                                        <span class="font-medium">Campo de exibição:</span> 
                                                        <code class="text-xs bg-blue-50 text-blue-700 px-1.5 py-0.5 rounded">{{ $relationshipConfig['display_field'] }}</code>
                                                    </p>
                                                    @if(empty($relationshipSampleData))
                                                        <p class="text-xs text-yellow-600 mt-1 flex items-center">
                                                            <i class="fas fa-exclamation-triangle mr-1"></i>
                                                            Nenhum dado de exemplo encontrado. Verifique se existem registros no modelo selecionado.
                                                        </p>
                                                    @else
                                                        <p class="text-xs text-green-600 mt-1 flex items-center">
                                                            <i class="fas fa-check-circle mr-1"></i>
                                                            {{ count($relationshipSampleData) }} itens de exemplo carregados
                                                        </p>
                                                    @endif
                                                @else
                                                    <p class="text-xs text-red-600 flex items-center">
                                                        <i class="fas fa-exclamation-triangle mr-1"></i>
                                                        Selecione um campo para exibição
                                                    </p>
                                                @endif
                                                
                                                @if($relationshipConfig['relationship_type'] === 'hasMany')
                                                    <p class="text-xs text-purple-600 mt-1 flex items-center">
                                                        <i class="fas fa-mouse-pointer mr-1"></i>
                                                        Use Ctrl+Clique para seleção múltipla
                                                    </p>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                    @endif
                    
                    <!-- Regras de validação -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Regras de Validação</label>
                        <div class="flex flex-wrap gap-2">
                            @foreach($commonValidationRules as $rule => $description)
                            <label class="inline-flex items-center p-2 bg-gray-50 rounded-md cursor-pointer hover:bg-gray-100 transition-colors">
                                <input wire:click="toggleValidationRule('{{ $rule }}')" type="checkbox" 
                                    class="rounded border-gray-300 text-green-600 shadow-sm focus:border-green-500 focus:ring focus:ring-green-500 focus:ring-opacity-50"
                                    {{ $this->hasValidationRule($rule) ? 'checked' : '' }}>
                                <span class="ml-2 text-sm text-gray-700">{{ $description }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>
                    
                    <div class="mb-4 flex items-center">
                        <input wire:model="currentField.is_required" type="checkbox" id="field_required" 
                            class="rounded border-gray-300 text-green-600 shadow-sm focus:border-green-500 focus:ring focus:ring-green-500 focus:ring-opacity-50">
                        <label for="field_required" class="ml-2 block text-sm text-gray-700">Campo Obrigatório</label>
                    </div>
                    
                    <div class="flex justify-end space-x-2 mt-6">
                        <button type="button" wire:click="closeModal" 
                            class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                            Cancelar
                        </button>
                        <button type="submit" 
                            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                            {{ $currentFieldId ? 'Atualizar' : 'Adicionar' }} Campo
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
    
    <!-- Script para download automático quando exportando formulários -->
    <script>
        // Adiciona logs de debug para o Livewire
        document.addEventListener('livewire:initialized', () => {
            console.log('Componente Livewire inicializado');
            
            // Log para eventos de sucesso/erro
            @this.on('saved', (message) => {
                console.log('Sucesso:', message);
                // Fecha o modal após salvar
                @this.set('showFieldModal', false);
            });
            
            @this.on('error', (message) => {
                console.error('Erro no Livewire:', message);
                alert('Ocorreu um erro: ' + message);
            });
            
            // Log para eventos de campo
            @this.on('field-added', () => {
                console.log('Campo adicionado com sucesso');
                @this.set('showFieldModal', false);
            });
            
            @this.on('field-updated', () => {
                console.log('Campo atualizado com sucesso');
                @this.set('showFieldModal', false);
            });
        });
        
        // Configuração do download de arquivos
        document.addEventListener('livewire:init', function () {
            Livewire.on('download-file', (event) => {
                console.log('Iniciando download do arquivo:', event.filename || 'formulario.json');
                const url = event.url;
                // Criar link temporário e acionar download
                const link = document.createElement('a');
                link.href = url;
                link.setAttribute('download', event.filename || 'formulario.json');
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                console.log('Download concluído');
            });
        });
        
        // Processar importação automaticamente quando um arquivo é selecionado
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM carregado, configurando listeners');
            
            const importInput = document.getElementById('import-form-input');
            if (importInput) {
                importInput.addEventListener('change', function() {
                    console.log('Arquivo selecionado para importação:', this.files[0]?.name);
                    if (this.files.length > 0) {
                        // Aguardar o upload do arquivo pelo Livewire
                        setTimeout(() => {
                            console.log('Chamando método importForm');
                            @this.importForm();
                        }, 500);
                    }
                });
            }
            
            // Adiciona listener para o botão de adicionar campo
            document.querySelectorAll('[wire\\:click*="createField"]').forEach(button => {
                button.addEventListener('click', function(e) {
                    console.log('Botão de criar campo clicado');
                    console.log('Form ID:', this.getAttribute('wire:click'));
                });
            });
            
            // Adiciona listener para o formulário de campo
            const fieldForm = document.querySelector('form[wire\\:submit\\.prevent="saveField"]');
            if (fieldForm) {
                fieldForm.addEventListener('submit', function(e) {
                    console.log('Formulário de campo submetido');
                    console.log('Dados do formulário:', new FormData(this));
                });
            }
        });
    </script>
</div>
