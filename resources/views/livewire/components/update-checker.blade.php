<div>
    @if($updateAvailable)
        <div
            class="cursor-pointer inline-flex items-center px-2.5 py-1.5 rounded-full text-sm font-medium transition-colors duration-150 ease-in-out bg-gradient-to-r from-blue-50 to-indigo-50 hover:from-blue-100 hover:to-indigo-100 border border-blue-200 shadow-sm mr-2"
            x-data="{showTooltip: false}"
            x-on:mouseenter="showTooltip = true"
            x-on:mouseleave="showTooltip = false"
            wire:click="goToUpdatePage"
        >
            <span class="flex items-center">
                <i class="fas fa-arrow-up text-blue-500 mr-1.5"></i>
                <span class="text-blue-700">Atualizar</span>
                <span class="ml-1 px-1.5 py-0.5 text-xs bg-blue-100 text-blue-800 rounded-full">{{ $latestVersion }}</span>
            </span>

            <div
                x-cloak
                x-show="showTooltip"
                class="absolute right-0 mt-12 bg-white shadow-xl rounded-lg p-3 text-xs w-56 z-50 border border-gray-200"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 transform scale-95"
                x-transition:enter-end="opacity-100 transform scale-100"
            >
                <div class="font-semibold border-b pb-2 mb-2 text-gray-800 flex items-center">
                    <i class="fas fa-info-circle text-blue-500 mr-1.5"></i>
                    Nova versão disponível!
                </div>
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Atual:</span>
                        <span class="font-medium text-gray-800">{{ $currentVersion }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Nova:</span>
                        <span class="font-semibold text-green-600">{{ $latestVersion }}</span>
                    </div>
                    <div class="mt-2 pt-2 border-t border-gray-100 flex items-center text-blue-600">
                        <i class="fas fa-mouse-pointer mr-1 text-blue-400"></i>
                        Clique para atualizar
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Botão para verificar atualizações manualmente (somente para admins) -->
    @if(auth()->check() && (auth()->user()->hasRole(['admin', 'super-admin']) || (isset(auth()->user()->role) && in_array(auth()->user()->role, ['admin', 'super-admin']))))
        @if(!$updateAvailable)
            <button
                wire:click="checkForUpdates"
                class="inline-flex items-center px-2.5 py-1.5 rounded-full text-sm font-medium transition-colors duration-150 ease-in-out bg-gray-100 text-gray-700 hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-400"
                wire:loading.attr="disabled"
                wire:target="checkForUpdates"
                title="Verificar atualizações"
            >
                <svg wire:loading.remove wire:target="checkForUpdates" class="h-4 w-4 mr-1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M15.312 11.424a5.5 5.5 0 01-9.201 2.466l-.312-.311h2.433a.75.75 0 000-1.5H3.989a.75.75 0 00-.75.75v4.242a.75.75 0 001.5 0v-2.43l.31.31a7 7 0 0011.712-3.138.75.75 0 00-1.449-.39zm1.23-3.723a.75.75 0 00.219-.53V2.929a.75.75 0 00-1.5 0V5.36l-.31-.31A7 7 0 003.239 8.188a.75.75 0 101.448.389A5.5 5.5 0 0113.89 6.11l.311.31h-2.432a.75.75 0 000 1.5h4.243a.75.75 0 00.53-.219z" clip-rule="evenodd" />
                </svg>
                <svg wire:loading wire:target="checkForUpdates" class="animate-spin h-4 w-4 mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span wire:loading.remove wire:target="checkForUpdates">Verificar</span>
                <span wire:loading wire:target="checkForUpdates">Verificando...</span>
            </button>
        @endif
    @endif
</div>
