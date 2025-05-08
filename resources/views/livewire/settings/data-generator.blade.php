<div>
    <!-- Modal do Gerador de Dados -->
    <div x-data="{ open: @entangle('showGeneratorModal') }" 
         x-show="open" 
         x-cloak 
         class="fixed inset-0 bg-gray-600 bg-opacity-75 overflow-y-auto h-full w-full z-50" 
         role="dialog" 
         aria-modal="true"
         x-transition:enter="transition ease-out duration-300" 
         x-transition:enter-start="opacity-0" 
         x-transition:enter-end="opacity-100" 
         x-transition:leave="transition ease-in duration-200" 
         x-transition:leave-start="opacity-100" 
         x-transition:leave-end="opacity-0">
        <div class="relative top-10 mx-auto p-1 w-full max-w-5xl">
            <div class="relative bg-white rounded-lg shadow-xl transform transition-all duration-300 ease-in-out" 
                 x-transition:enter="transition ease-out duration-300" 
                 x-transition:enter-start="transform opacity-0 scale-95" 
                 x-transition:enter-end="transform opacity-100 scale-100" 
                 x-transition:leave="transition ease-in duration-200" 
                 x-transition:leave-start="transform opacity-100 scale-100" 
                 x-transition:leave-end="transform opacity-0 scale-95">
                
                <!-- Cabeçalho com gradiente -->
                <div class="bg-gradient-to-r from-purple-600 to-purple-700 rounded-t-lg px-4 py-3 flex justify-between items-center">
                    <h3 class="text-lg font-medium text-white flex items-center">
                        <i class="fas fa-database mr-2 animate-pulse"></i>
                        Gerador de Dados para Teste
                    </h3>
                    <button type="button" wire:click="closeGeneratorModal" class="text-white hover:text-gray-200 focus:outline-none transition-all duration-200 ease-in-out transform hover:scale-110 hover:rotate-90">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                
                <!-- Barra de progresso dos passos -->
                <div class="p-4 border-b border-gray-200 bg-gray-50">
                    <div class="flex items-center">
                        <div class="w-full">
                            <div class="relative pt-1">
                                <div class="flex items-center justify-between mb-2">
                                    <div>
                                        <span class="text-xs font-semibold inline-block py-1 px-2 uppercase rounded-full {{ $currentStep >= 1 ? 'bg-purple-600 text-white' : 'bg-gray-200 text-gray-600' }}">
                                            1
                                        </span>
                                        <span class="ml-1 text-xs {{ $currentStep >= 1 ? 'text-purple-600 font-semibold' : 'text-gray-500' }}">Selecionar Tabelas</span>
                                    </div>
                                    <div class="text-right">
                                        <span class="text-xs font-semibold inline-block py-1 px-2 uppercase rounded-full {{ $currentStep >= 2 ? 'bg-purple-600 text-white' : 'bg-gray-200 text-gray-600' }}">
                                            2
                                        </span>
                                        <span class="ml-1 text-xs {{ $currentStep >= 2 ? 'text-purple-600 font-semibold' : 'text-gray-500' }}">Configurar Campos</span>
                                    </div>
                                    <div class="text-right">
                                        <span class="text-xs font-semibold inline-block py-1 px-2 uppercase rounded-full {{ $currentStep >= 3 ? 'bg-purple-600 text-white' : 'bg-gray-200 text-gray-600' }}">
                                            3
                                        </span>
                                        <span class="ml-1 text-xs {{ $currentStep >= 3 ? 'text-purple-600 font-semibold' : 'text-gray-500' }}">Gerar Dados</span>
                                    </div>
                                </div>
                                <div class="overflow-hidden h-2 text-xs flex rounded bg-gray-200">
                                    <div style="width: {{ ($currentStep / $totalSteps) * 100 }}%" class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-purple-600 transition-all duration-500 ease-in-out"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Conteúdo do modal - Passo 1: Seleção de Tabelas -->
                <div class="p-6 max-h-[70vh] overflow-y-auto" x-show="$wire.currentStep === 1">
                    <div class="mb-4">
                        <h4 class="text-lg font-medium text-gray-900 mb-2">Selecione as Tabelas</h4>
                        <p class="text-sm text-gray-600">
                            Selecione as tabelas que deseja incluir na geração de dados de teste.
                        </p>
                    </div>
                    
                    <!-- Campo de pesquisa -->
                    <div class="mb-4">
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-search text-gray-400"></i>
                            </div>
                            <input 
                                wire:model.live="tableSearch" 
                                type="text"
                                class="pl-10 block w-full rounded-md border-gray-300 shadow-sm focus:ring-purple-500 focus:border-purple-500 sm:text-sm"
                                placeholder="Pesquisar tabelas..."
                            >
                        </div>
                    </div>
                    
                    <!-- Lista de tabelas -->
                    <div class="mb-4">
                        <div class="flex items-center justify-between mb-2">
                            <button type="button" wire:click="toggleSelectAllTables" class="text-sm text-purple-600 hover:text-purple-800">
                                {{ count($selectedTables) === count($tables) ? 'Desmarcar Todas' : 'Selecionar Todas' }}
                            </button>
                            <span class="text-xs text-gray-500">
                                {{ count($selectedTables) }} de {{ count($tables) }} tabelas selecionadas
                            </span>
                        </div>
                        
                        <div class="bg-white border border-gray-200 rounded-md overflow-hidden">
                            <div class="max-h-64 overflow-y-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Tabela
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Quantidade
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @forelse($filteredTables as $table)
                                            <tr class="hover:bg-gray-50">
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <label class="inline-flex items-center">
                                                        <input 
                                                            type="checkbox" 
                                                            wire:model.live="selectedTables" 
                                                            value="{{ $table }}" 
                                                            class="rounded border-gray-300 text-purple-600 shadow-sm focus:border-purple-300 focus:ring focus:ring-purple-200 focus:ring-opacity-50"
                                                        >
                                                        <span class="ml-2 text-sm text-gray-700">{{ $table }}</span>
                                                    </label>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <input 
                                                        type="number" 
                                                        wire:model.live="quantities.{{ $table }}" 
                                                        min="1"
                                                        max="100"
                                                        class="mt-1 block w-20 rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500 sm:text-sm"
                                                        {{ !in_array($table, $selectedTables) ? 'disabled' : '' }}
                                                    >
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="2" class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500">
                                                    Nenhuma tabela encontrada.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Opções gerais -->
                    <div class="mb-6 bg-gray-50 p-4 rounded-md border border-gray-200">
                        <h5 class="font-medium text-sm text-gray-700 mb-2">Opções Gerais</h5>
                        
                        <div class="mb-3">
                            <label class="inline-flex items-center">
                                <input 
                                    type="checkbox" 
                                    wire:model.live="truncateBeforeInsert" 
                                    class="rounded border-gray-300 text-purple-600 shadow-sm focus:border-purple-300 focus:ring focus:ring-purple-200 focus:ring-opacity-50"
                                >
                                <span class="ml-2 text-sm text-gray-700">Limpar tabelas antes de inserir novos dados</span>
                            </label>
                            <p class="text-xs text-gray-500 ml-6">Se marcado, todos os dados existentes nas tabelas selecionadas serão removidos.</p>
                        </div>
                        
                        <div>
                            <label class="inline-flex items-center">
                                <input 
                                    type="checkbox" 
                                    wire:model.live="generateForeignKeys" 
                                    class="rounded border-gray-300 text-purple-600 shadow-sm focus:border-purple-300 focus:ring focus:ring-purple-200 focus:ring-opacity-50"
                                >
                                <span class="ml-2 text-sm text-gray-700">Manter relações entre tabelas</span>
                            </label>
                            <p class="text-xs text-gray-500 ml-6">Se marcado, os relacionamentos entre tabelas serão mantidos usando chaves estrangeiras existentes.</p>
                        </div>
                    </div>
                    
                    @if(!empty($relationships))
                        <div class="mb-4 bg-blue-50 p-4 rounded-md border border-blue-200">
                            <h5 class="font-medium text-sm text-blue-700 mb-2 flex items-center">
                                <i class="fas fa-link mr-2"></i>
                                Relacionamentos Detectados
                            </h5>
                            <div class="text-xs text-blue-600 max-h-36 overflow-y-auto">
                                @foreach($relationships as $table => $relations)
                                    @if(in_array($table, $selectedTables))
                                        <div class="mb-1">
                                            <strong>{{ $table }}</strong> depende de:
                                            <ul class="list-disc list-inside ml-2">
                                                @foreach($relations as $relation)
                                                    <li>
                                                        {{ $relation['foreignTable'] }} 
                                                        ({{ $relation['localColumn'] }} → {{ $relation['foreignColumn'] }})
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
                
                <!-- Conteúdo do modal - Passo 2: Configuração de Campos -->
                <div class="p-6 max-h-[70vh] overflow-y-auto" x-show="$wire.currentStep === 2">
                    <div class="mb-4">
                        <h4 class="text-lg font-medium text-gray-900 mb-2">Configuração de Campos</h4>
                        <p class="text-sm text-gray-600">
                            Configure como os dados serão gerados para cada campo das tabelas selecionadas.
                        </p>
                    </div>
                    
                    <div class="space-y-6">
                        @foreach($selectedTables as $table)
                            <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
                                <div class="bg-gray-50 px-4 py-3 border-b border-gray-200">
                                    <h5 class="font-medium text-gray-700">{{ $table }}</h5>
                                </div>
                                <div class="p-4">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Campo
                                                </th>
                                                <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Tipo
                                                </th>
                                                <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Método de Geração
                                                </th>
                                                <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Valor Personalizado
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            @if(isset($fieldConfigs[$table]))
                                                @foreach($fieldConfigs[$table] as $column => $config)
                                                    <tr class="hover:bg-gray-50">
                                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">
                                                            {{ $column }}
                                                        </td>
                                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                                                            {{ $config['type'] }}
                                                        </td>
                                                        <td class="px-4 py-3 whitespace-nowrap">
                                                            <select 
                                                                wire:model.live="fieldConfigs.{{ $table }}.{{ $column }}.faker_method"
                                                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500 sm:text-sm"
                                                                {{ $fieldConfigs[$table][$column]['use_custom'] ? 'disabled' : '' }}
                                                            >
                                                                <option value="word">Palavra</option>
                                                                <option value="text">Texto</option>
                                                                <option value="sentence">Frase</option>
                                                                <option value="paragraph">Parágrafo</option>
                                                                <option value="name">Nome completo</option>
                                                                <option value="firstName">Nome</option>
                                                                <option value="lastName">Sobrenome</option>
                                                                <option value="email">E-mail</option>
                                                                <option value="phoneNumber">Telefone</option>
                                                                <option value="address">Endereço</option>
                                                                <option value="city">Cidade</option>
                                                                <option value="country">País</option>
                                                                <option value="postcode">CEP</option>
                                                                <option value="numberBetween">Número</option>
                                                                <option value="randomFloat">Decimal</option>
                                                                <option value="boolean">Booleano</option>
                                                                <option value="dateTimeBetween">Data e hora</option>
                                                                <option value="url">URL</option>
                                                                <option value="imageUrl">URL de imagem</option>
                                                                <option value="password">Senha</option>
                                                            </select>
                                                        </td>
                                                        <td class="px-4 py-3 whitespace-nowrap">
                                                            <div class="flex items-center">
                                                                <label class="inline-flex items-center mr-2">
                                                                    <input 
                                                                        type="checkbox" 
                                                                        wire:model.live="fieldConfigs.{{ $table }}.{{ $column }}.use_custom" 
                                                                        class="rounded border-gray-300 text-purple-600 shadow-sm focus:border-purple-300 focus:ring focus:ring-purple-200 focus:ring-opacity-50"
                                                                    >
                                                                    <span class="ml-1 text-xs text-gray-500">Personalizado</span>
                                                                </label>
                                                                @if($fieldConfigs[$table][$column]['use_custom'])
                                                                    <input 
                                                                        type="text" 
                                                                        wire:model.live="fieldConfigs.{{ $table }}.{{ $column }}.custom_value"
                                                                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500 sm:text-sm"
                                                                        placeholder="Valor personalizado"
                                                                    >
                                                                @endif
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            @endif
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                
                <!-- Conteúdo do modal - Passo 3: Gerar Dados -->
                <div class="p-6 max-h-[70vh] overflow-y-auto" x-show="$wire.currentStep === 3">
                    <div class="mb-4">
                        <h4 class="text-lg font-medium text-gray-900 mb-2">Gerar Dados de Teste</h4>
                        <p class="text-sm text-gray-600">
                            Revise as configurações e clique em "Gerar Dados" para iniciar o processo.
                        </p>
                    </div>
                    
                    <!-- Resumo da configuração -->
                    <div class="mb-6 bg-gray-50 p-4 rounded-md border border-gray-200">
                        <h5 class="font-medium text-sm text-gray-700 mb-2">Resumo da Configuração</h5>
                        
                        <ul class="space-y-2 text-sm">
                            <li class="flex items-start">
                                <span class="text-gray-500 mr-2">•</span>
                                <div>
                                    <span class="text-gray-700">Tabelas selecionadas:</span>
                                    <span class="text-gray-600">{{ count($selectedTables) }}</span>
                                </div>
                            </li>
                            <li class="flex items-start">
                                <span class="text-gray-500 mr-2">•</span>
                                <div>
                                    <span class="text-gray-700">Total de registros a serem gerados:</span>
                                    <span class="text-gray-600">
                                        {{ array_sum(array_intersect_key($quantities, array_flip($selectedTables))) }}
                                    </span>
                                </div>
                            </li>
                            <li class="flex items-start">
                                <span class="text-gray-500 mr-2">•</span>
                                <div>
                                    <span class="text-gray-700">Limpar tabelas antes de inserir:</span>
                                    <span class="text-gray-600">{{ $truncateBeforeInsert ? 'Sim' : 'Não' }}</span>
                                </div>
                            </li>
                            <li class="flex items-start">
                                <span class="text-gray-500 mr-2">•</span>
                                <div>
                                    <span class="text-gray-700">Manter relações entre tabelas:</span>
                                    <span class="text-gray-600">{{ $generateForeignKeys ? 'Sim' : 'Não' }}</span>
                                </div>
                            </li>
                        </ul>
                        
                        <div class="mt-4 grid grid-cols-1 gap-2 text-sm">
                            <span class="font-medium text-gray-700">Detalhes por tabela:</span>
                            @foreach($selectedTables as $table)
                                <div class="flex items-center">
                                    <span class="text-gray-700 mr-2">{{ $table }}:</span>
                                    <span class="text-gray-600">{{ $quantities[$table] }} registros</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    
                    <div class="mb-6">
                        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-exclamation-triangle text-yellow-400"></i>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-yellow-700">
                                        <strong>Atenção:</strong> A geração de dados substituirá os dados existentes nas tabelas selecionadas se a opção "Limpar tabelas antes de inserir" estiver marcada. Recomenda-se fazer um backup do banco de dados antes de prosseguir.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    @if(!empty($generationOutput))
                        <div class="mb-6">
                            <h5 class="font-medium text-sm text-gray-700 mb-2">Resultado da Geração</h5>
                            <pre class="bg-gray-800 text-green-400 p-4 rounded-md overflow-auto max-h-60 text-sm font-mono">{{ $generationOutput }}</pre>
                        </div>
                    @endif
                    
                    <div class="mt-6">
                        <button
                            type="button"
                            wire:click="generateData"
                            wire:loading.attr="disabled"
                            class="inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 disabled:opacity-50 disabled:cursor-not-allowed"
                            {{ $processing ? 'disabled' : '' }}
                        >
                            <span wire:loading.remove wire:target="generateData">
                                <i class="fas fa-play mr-2"></i>
                                Gerar Dados
                            </span>
                            <span wire:loading wire:target="generateData" class="inline-flex items-center">
                                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Gerando Dados...
                            </span>
                        </button>
                    </div>
                </div>
                
                <!-- Rodapé com botões de ação -->
                <div class="bg-gray-50 px-4 py-3 rounded-b-lg flex justify-between border-t border-gray-200">
                    <div>
                        @if($currentStep > 1)
                            <button type="button" wire:click="previousStep" class="inline-flex justify-center items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 transition-all duration-200 ease-in-out transform hover:scale-105">
                                <i class="fas fa-chevron-left mr-2"></i>
                                Voltar
                            </button>
                        @endif
                    </div>
                    <div>
                        <button type="button" wire:click="closeGeneratorModal" class="mr-3 inline-flex justify-center items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 transition-all duration-200 ease-in-out transform hover:scale-105">
                            <i class="fas fa-times mr-2"></i>
                            Cancelar
                        </button>
                        
                        @if($currentStep < $totalSteps)
                            <button type="button" wire:click="nextStep" class="inline-flex justify-center items-center px-4 py-2 bg-purple-600 border border-transparent rounded-md font-semibold text-white hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 transition-all duration-200 ease-in-out transform hover:scale-105">
                                Próximo
                                <i class="fas fa-chevron-right ml-2"></i>
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Botão para abrir modal de gerador de dados -->
    <button 
        type="button"
        wire:click="openGeneratorModal"
        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 transition-all duration-200 ease-in-out transform hover:scale-105 mt-6"
    >
        <i class="fas fa-database mr-2"></i>
        Gerador de Dados para Teste
    </button>
</div>
