@if ($showSearchModal)
    <div x-data="{ open: @entangle('showSearchModal') }" 
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
        <div class="relative top-20 mx-auto p-1 w-full max-w-4xl">
            <div class="relative bg-white rounded-lg shadow-xl transform transition-all duration-300 ease-in-out" 
                 x-transition:enter="transition ease-out duration-300" 
                 x-transition:enter-start="transform opacity-0 scale-95" 
                 x-transition:enter-end="transform opacity-100 scale-100" 
                 x-transition:leave="transition ease-in duration-200" 
                 x-transition:leave-start="transform opacity-100 scale-100" 
                 x-transition:leave-end="transform opacity-0 scale-95">
                
                <!-- Modal Header -->
                <div class="bg-gradient-to-r from-blue-600 to-blue-700 rounded-t-lg px-6 py-4 flex justify-between items-center">
                    <h3 class="text-lg font-medium text-white flex items-center">
                        <i class="fas fa-search mr-2"></i>
                        Search Parts
                    </h3>
                    <button type="button" 
                            @click="$wire.closeSearchModal()" 
                            class="text-white hover:text-gray-200 focus:outline-none transition-all duration-200 ease-in-out transform hover:scale-110 hover:rotate-90">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                
                <!-- Modal Body -->
                <div class="p-6">
                    <!-- Search Box -->
                    <div class="mb-6 relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-search text-gray-400"></i>
                        </div>
                        <input 
                            type="text"
                            wire:model.live.debounce.300ms="partSearch"
                            placeholder="Search by name, part number or barcode..."
                            class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                            autofocus
                        >
                    </div>
                    
                    <!-- Parts List -->
                    <div class="border border-gray-200 rounded-md overflow-hidden">
                        <div class="max-h-96 overflow-y-auto">
                            @forelse($this->partsListForSearch as $part)
                                <div class="px-4 py-3 hover:bg-blue-50 cursor-pointer transition-colors duration-150 border-b border-gray-100 last:border-0 {{ $part->stock_quantity <= 0 ? 'opacity-50 cursor-not-allowed' : '' }}"
                                     @if($part->stock_quantity > 0) wire:click="selectPart({{ $part->id }})" @endif>
                                    <div class="flex justify-between items-start">
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-medium text-gray-900 flex items-center">
                                                {{ $part->name }}
                                                @if($part->part_number)
                                                    <span class="text-gray-500 text-xs ml-2">({{ $part->part_number }})</span>
                                                @endif
                                                @if($part->stock_quantity <= 0)
                                                    <span class="bg-red-100 text-red-800 text-xs font-medium mr-2 px-2.5 py-0.5 rounded ml-2">
                                                        Out of Stock
                                                    </span>
                                                @elseif($part->stock_quantity <= 3)
                                                    <span class="bg-orange-100 text-orange-800 text-xs font-medium mr-2 px-2.5 py-0.5 rounded ml-2">
                                                        Low Stock
                                                    </span>
                                                @endif
                                            </p>
                                            @if($part->equipment)
                                                <p class="text-xs text-gray-600 mt-1">
                                                    <i class="fas fa-tools mr-1"></i> {{ $part->equipment->name }}
                                                </p>
                                            @endif
                                            <p class="text-xs text-gray-600 mt-1">
                                                <i class="fas fa-boxes mr-1"></i> 
                                                {{ $part->stock_quantity }} in stock
                                                @if($part->minimum_stock_level !== null)
                                                    <span class="ml-2 {{ $part->stock_quantity <= $part->minimum_stock_level ? 'text-red-600 font-medium' : 'text-green-600' }}">
                                                        (Min: {{ $part->minimum_stock_level }})
                                                    </span>
                                                @endif
                                            </p>
                                            @if($part->bar_code)
                                                <p class="text-xs text-gray-600 mt-1">
                                                    <i class="fas fa-barcode mr-1"></i> {{ $part->bar_code }}
                                                </p>
                                            @endif
                                        </div>
                                        @if($part->stock_quantity > 0)
                                            <div class="ml-2 flex-shrink-0 text-blue-600">
                                                <i class="fas fa-chevron-right"></i>
                                            </div>
                                        @else
                                            <div class="ml-2 flex-shrink-0 text-red-600">
                                                <i class="fas fa-ban"></i>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <div class="px-4 py-8 text-center">
                                    <i class="fas fa-search text-gray-300 text-4xl mb-2"></i>
                                    <p class="text-sm text-gray-500">
                                        @if($partSearch)
                                            No parts found matching "{{ $partSearch }}"
                                        @else
                                            No parts available. Try changing your search criteria.
                                        @endif
                                    </p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
                
                <!-- Modal Footer -->
                <div class="bg-gray-50 px-6 py-3 rounded-b-lg flex justify-end space-x-3 border-t border-gray-200">
                    <button type="button" 
                            @click="$wire.closeSearchModal()" 
                            class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 ease-in-out">
                        <i class="fas fa-times mr-2"></i>
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
@endif
