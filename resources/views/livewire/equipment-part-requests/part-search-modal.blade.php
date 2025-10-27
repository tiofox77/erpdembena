<!-- Parts Search Modal -->
@if($showPartResults && $currentItemIndex !== null)
    <div class="fixed inset-0 bg-black bg-opacity-25 z-40" wire:click="$set('showPartResults', false)"></div>
    <div class="fixed z-50 top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 bg-white shadow-xl rounded-lg w-11/12 md:w-3/4 lg:w-1/2 max-h-3/4 overflow-hidden">
        <div class="p-4 border-b">
            <div class="flex justify-between items-center">
                <h3 class="text-lg font-medium">Search for a Part (Item #{{ $currentItemIndex + 1 }})</h3>
                <button type="button" wire:click="$set('showPartResults', false)" class="text-gray-400 hover:text-gray-500">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="mt-2">
                <input 
                    type="text" 
                    wire:model.live.debounce.300ms="partSearch"
                    placeholder="Search by name, part number, or description..."
                    class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                    autocomplete="off"
                >
            </div>
        </div>
        
        <div class="max-h-96 overflow-y-auto p-2">
            @if(!empty($filteredParts))
                <ul class="divide-y divide-gray-200">
                    @foreach($filteredParts as $part)
                        <li 
                            wire:key="part-{{ $part->id }}" 
                            wire:click="selectPart({{ $part->id }})"
                            class="p-3 hover:bg-gray-100 cursor-pointer"
                        >
                            <div>
                                <div class="font-semibold">{{ $part->name }}</div>
                                <div class="text-sm text-gray-600">{{ $part->description }}</div>
                                <div class="text-xs text-gray-500">
                                    Part #: {{ $part->part_number }}
                                    @if($part->bar_code)
                                        | Barcode: {{ $part->bar_code }}
                                    @endif
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @else
                <div class="p-4 text-center text-gray-500">
                    @if(strlen($partSearch) >= 2)
                        No parts found matching "{{ $partSearch }}"
                    @else
                        Type at least 2 characters to search
                    @endif
                </div>
            @endif
        </div>
    </div>
@endif
