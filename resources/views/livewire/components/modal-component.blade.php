<div
    x-data="{ open: @entangle('isOpen') }"
    x-show="open"
    x-cloak
    class="fixed inset-0 z-50 overflow-y-auto"
    aria-labelledby="modal-title"
    role="dialog"
    aria-modal="true"
    style="display: none"
>
    <!-- Overlay de fundo -->
    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>

    <!-- Conteúdo do Modal -->
    <div class="flex min-h-screen items-center justify-center p-4 text-center sm:p-0">
        <div
            @click.outside="$wire.close()"
            x-show="open"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 w-full"
            :class="{
                'sm:max-w-sm': '{{ $modalSize }}' === 'sm',
                'sm:max-w-md': '{{ $modalSize }}' === 'md',
                'sm:max-w-lg': '{{ $modalSize }}' === 'lg',
                'sm:max-w-xl': '{{ $modalSize }}' === 'xl',
                'sm:max-w-2xl': '{{ $modalSize }}' === '2xl',
                'sm:max-w-3xl': '{{ $modalSize }}' === '3xl',
                'sm:max-w-4xl': '{{ $modalSize }}' === '4xl',
                'sm:max-w-5xl': '{{ $modalSize }}' === '5xl',
                'sm:max-w-6xl': '{{ $modalSize }}' === '6xl',
                'sm:max-w-7xl': '{{ $modalSize }}' === '7xl'
            }"
        >
            <!-- Cabeçalho do Modal -->
            @if($title)
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                        <h3 class="text-lg font-medium leading-6 text-gray-900" id="modal-title">
                            {{ $title }}
                        </h3>
                    </div>
                </div>
            </div>
            @endif

            <!-- Conteúdo do Modal -->
            <div class="bg-white px-4 py-3">
                @if(isset($slot))
                    {{ $slot }}
                @endif
            </div>

            <!-- Rodapé do Modal com botões -->
            @if (isset($footer))
            <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                {{ $footer }}
            </div>
            @elseif($showCloseButton)
            <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                <button wire:click="close" type="button" class="mt-3 inline-flex w-full justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-base font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                    Fechar
                </button>
            </div>
            @endif
        </div>
    </div>

    <!-- Estilo para esconder elementos com x-cloak antes do Alpine.js inicializar -->
    <style>
        [x-cloak] { display: none !important; }
    </style>
</div>
