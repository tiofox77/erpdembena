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
                
                <button wire:click="create" 
                    class="w-full md:w-auto inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-white hover:bg-blue-700 focus:outline-none transition-colors">
                    <i class="fas fa-plus-circle mr-2"></i>
                    Novo Formulário
                </button>
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
                                    
                                    <button wire:click="createField({{ $form->id }})" 
                                        class="text-blue-600 hover:text-blue-900 transition-colors">
                                        <i class="fas fa-plus-circle"></i>
                                    </button>
                                    
                                    <button wire:click="previewForm({{ $form->id }})" 
                                        class="text-green-600 hover:text-green-900 transition-colors">
                                        <i class="fas fa-eye"></i>
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
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-lg max-w-lg w-full mx-auto">
            <div class="flex items-center justify-between bg-gradient-to-r from-green-600 to-green-700 px-6 py-4 rounded-t-lg">
                <h3 class="text-lg font-medium text-white">
                    {{ $currentFieldId ? 'Editar Campo' : 'Novo Campo' }}
                </h3>
                <button wire:click="closeModal" class="text-white hover:text-gray-200">
                    <i class="fas fa-times"></i>
                </button>
            </div>
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
                        <select wire:model="currentField.type" id="field_type" 
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
</div>
