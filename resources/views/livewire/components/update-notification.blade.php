<div>
    @if($updateAvailable && auth()->check() && (auth()->user()->hasRole(['admin', 'super-admin']) || (isset(auth()->user()->role) && in_array(auth()->user()->role, ['admin', 'super-admin']))))
        <!-- Evento JavaScript para abrir o modal automaticamente quando o evento for despachado -->
        <script>
            document.addEventListener('showUpdateModal', function() {
                setTimeout(function() {
                    Livewire.dispatch('openUpdateModal');
                }, 2000);
            });
        </script>

        <!-- Botão para abrir o modal manualmente (opcional) -->
        <button wire:click="openModal" class="hidden">Mostrar atualização</button>

        <!-- Modal de notificação de atualização -->
        @if($showModal)
            <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 flex items-center justify-center"
                x-data="{ open: true }"
                x-show="open"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0">

                <div class="relative p-6 bg-white w-full max-w-md m-auto rounded-lg shadow-xl"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 transform scale-95"
                    x-transition:enter-end="opacity-100 transform scale-100">

                    <!-- Ícone de atualização -->
                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100 mb-4">
                        <i class="fas fa-arrow-up text-green-600"></i>
                    </div>

                    <!-- Cabeçalho do modal -->
                    <div class="text-center mb-4">
                        <h3 class="text-lg font-medium leading-6 text-gray-900">New update available!</h3>
                        <p class="mt-2 text-sm text-gray-500">
                            Version {{ $latestVersion }} available for installation
                        </p>
                        <p class="text-xs text-gray-400 mt-1">Current version: {{ $currentVersion }}</p>
                    </div>

                    <!-- Corpo do modal -->
                    <div class="mt-3 text-center">
                        <div class="mt-2 px-1">
                            <div class="bg-gray-50 p-3 rounded-lg mb-3 text-left">
                                <h4 class="font-semibold">{{ $updateNotes['title'] ?? 'New version' }}</h4>
                                <p class="text-xs text-gray-500 mb-2">Published on: {{ $updateNotes['published_at'] ?? now()->format('d/m/Y') }}</p>
                                <div class="text-sm text-gray-700 whitespace-pre-line">
                                    {{ $updateNotes['body'] ?? 'This update includes improvements and bug fixes.' }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Botões de ação -->
                    <div class="flex justify-center space-x-3 mt-4">
                        <button wire:click="dismissUpdate" class="py-2 px-4 text-gray-600 rounded-lg border border-gray-300 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 text-sm">
                            Remind me later
                        </button>
                        <button wire:click="goToUpdatePage" class="py-2 px-4 bg-green-600 text-white rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 text-sm">
                            Update now
                        </button>
                    </div>

                    <!-- Botão para fechar modal -->
                    <button wire:click="closeModal" class="absolute top-0 right-0 mt-4 mr-4 text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        @endif
    @endif
</div>
