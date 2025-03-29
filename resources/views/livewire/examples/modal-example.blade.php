<div>
    <div class="py-4">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-semibold">Exemplo de Uso do Modal</h2>

                <!-- Botão para abrir o modal de Linha -->
                <button
                    wire:click="$dispatch('openModal', { modalId: 'line-form', title: 'Nova Linha de Produção' })"
                    class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 mr-2 rounded inline-flex items-center"
                >
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Adicionar Linha
                </button>

                <!-- Botão para abrir o modal de Área -->
                <button
                    wire:click="$dispatch('openModal', { modalId: 'area-form', title: 'Nova Área' })"
                    class="bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded inline-flex items-center"
                >
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Adicionar Área
                </button>
            </div>

            <!-- Conteúdo da página -->
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                <p>Este é um exemplo de como usar o componente de modal reutilizável.</p>
                <p class="mt-2">Clique em um dos botões acima para abrir os modais.</p>
            </div>
        </div>
    </div>

    <!-- Componente Modal para o formulário de Linha -->
    <livewire:components.modal-component>
        <x-slot:title>
            Formulário de Linha
        </x-slot:title>

        <!-- Conteúdo do Modal de Linha -->
        <form wire:submit.prevent="saveLine">
            <div class="space-y-4">
                <div>
                    <label for="lineName" class="block text-sm font-medium text-gray-700">Nome</label>
                    <input type="text" id="lineName" wire:model="lineName" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    @error('lineName') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="lineDescription" class="block text-sm font-medium text-gray-700">Descrição</label>
                    <textarea id="lineDescription" wire:model="lineDescription" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"></textarea>
                </div>

                <div>
                    <label for="lineStatus" class="block text-sm font-medium text-gray-700">Status</label>
                    <select id="lineStatus" wire:model="lineStatus" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        <option value="active">Ativo</option>
                        <option value="inactive">Inativo</option>
                        <option value="maintenance">Em Manutenção</option>
                    </select>
                    @error('lineStatus') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>
            </div>
        </form>

        <!-- Botões de rodapé personalizados -->
        <x-slot:footer>
            <button wire:click="saveLine" type="button" class="inline-flex justify-center rounded-md border border-transparent bg-blue-600 px-4 py-2 text-base font-medium text-white shadow-sm hover:bg-blue-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                Salvar
            </button>
            <button wire:click="$dispatch('closeModal')" type="button" class="mt-3 inline-flex w-full justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-base font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                Cancelar
            </button>
        </x-slot:footer>
    </livewire:components.modal-component>

    <!-- Componente Modal para o formulário de Área -->
    <livewire:components.modal-component>
        <x-slot:title>
            Formulário de Área
        </x-slot:title>

        <!-- Conteúdo do Modal de Área -->
        <form wire:submit.prevent="saveArea">
            <div class="space-y-4">
                <div>
                    <label for="areaName" class="block text-sm font-medium text-gray-700">Nome</label>
                    <input type="text" id="areaName" wire:model="areaName" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    @error('areaName') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="areaLineId" class="block text-sm font-medium text-gray-700">Linha</label>
                    <select id="areaLineId" wire:model="areaLineId" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        <option value="">Selecione uma linha</option>
                        @foreach($lines as $line)
                            <option value="{{ $line->id }}">{{ $line->name }}</option>
                        @endforeach
                    </select>
                    @error('areaLineId') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="areaDescription" class="block text-sm font-medium text-gray-700">Descrição</label>
                    <textarea id="areaDescription" wire:model="areaDescription" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"></textarea>
                </div>
            </div>
        </form>

        <!-- Botões de rodapé personalizados -->
        <x-slot:footer>
            <button wire:click="saveArea" type="button" class="inline-flex justify-center rounded-md border border-transparent bg-green-600 px-4 py-2 text-base font-medium text-white shadow-sm hover:bg-green-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                Salvar
            </button>
            <button wire:click="$dispatch('closeModal')" type="button" class="mt-3 inline-flex w-full justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-base font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                Cancelar
            </button>
        </x-slot:footer>
    </livewire:components.modal-component>
</div>
