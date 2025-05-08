<!-- Create/Edit Modal -->
@if($showModal)
    <div class="fixed z-10 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <!-- Background overlay -->
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>

            <!-- Modal panel -->
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                {{ $isEditing ? __('messages.edit_part_request') : __('messages.create_new_part_request') }}
                            </h3>
                            <div class="mt-2">
                                <form wire:submit.prevent="save">
                                    <!-- Form errors -->
                                    @if($errors->any())
                                        <div class="rounded-md bg-red-50 p-4 mb-4">
                                            <div class="flex">
                                                <div class="flex-shrink-0">
                                                    <i class="fas fa-exclamation-circle text-red-400"></i>
                                                </div>
                                                <div class="ml-3">
                                                    <h3 class="text-sm font-medium text-red-800">
                                                        {!! __('messages.error_with_submission', ['count' => $errors->count()]) !!}
                                                    </h3>
                                                    <div class="mt-2 text-sm text-red-700">
                                                        <ul class="list-disc pl-5 space-y-1">
                                                            @foreach($errors->all() as $error)
                                                                <li>{{ $error }}</li>
                                                            @endforeach
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        <!-- Reference Number -->
                                        <div>
                                            <label for="reference_number" class="block text-sm font-medium text-gray-700">{{ __('messages.dembena_reference') }}</label>
                                            <input 
                                                type="text" 
                                                id="reference_number" 
                                                wire:model="request.reference_number" 
                                                class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                                                {{ $isEditing ? 'readonly' : '' }}
                                            >
                                        </div>

                                        <!-- Items Section Header -->
                                        <div class="col-span-2 mt-4 mb-2">
                                            <h3 class="text-md font-semibold text-gray-700 flex justify-between items-center">
                                                <span>{{ __('messages.items') }}</span>
                                                <button 
                                                    type="button" 
                                                    wire:click="addItem"
                                                    class="inline-flex items-center px-3 py-1 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                                                >
                                                    <i class="fas fa-plus mr-1"></i> {{ __('messages.add_item') }}
                                                </button>
                                            </h3>
                                        </div>

                                        <!-- Items List (span full width) -->
                                        <div class="col-span-2 border rounded-md p-3">
                                            @if(isset($requestItems) && is_array($requestItems))
                                                @foreach($requestItems as $index => $item)
                                                    <div class="mb-4 p-3 border rounded-md bg-gray-50" wire:key="item-{{ $index }}">
                                                    <div class="flex justify-between items-center mb-2">
                                                        <h4 class="font-medium text-gray-700">Item #{{ $index + 1 }}</h4>
                                                        @if(count($requestItems) > 1)
                                                            <button 
                                                                type="button" 
                                                                wire:click="removeItem({{ $index }})"
                                                                class="text-red-500 hover:text-red-700"
                                                            >
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        @endif
                                                    </div>
                                                    
                                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                                        <!-- Equipment Part Selection -->
                                                        <div class="relative">
                                                            <label class="block text-sm font-medium text-gray-700">{{ __('messages.part') }}</label>
                                                            <div class="relative">
                                                                <button 
                                                                    type="button" 
                                                                    wire:click="searchForItem({{ $index }})"
                                                                    class="mt-1 w-full inline-flex justify-between items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                                                                >
                                                                    {{ isset($item['part_details']) ? $item['part_details']['name'] : __('messages.select_part') . '...' }}
                                                                    <i class="fas fa-search"></i>
                                                                </button>
                                                                
                                                                @if(isset($item['part_details']) && $item['part_details'])
                                                                    <div class="mt-2 p-2 border rounded-md bg-gray-100">
                                                                        <p class="text-sm text-gray-600">{{ $item['part_details']['description'] }}</p>
                                                                        <p class="text-xs text-gray-500">Part Number: {{ $item['part_details']['part_number'] }}</p>
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        </div>
                                                        
                                                        <!-- Item Supplier Reference -->
                                                        <div>
                                                            <label class="block text-sm font-medium text-gray-700">Supplier Reference/CODE</label>
                                                            <input 
                                                                type="text"
                                                                wire:model="requestItems.{{ $index }}.supplier_reference"
                                                                class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                                                            >
                                                        </div>
                                                        
                                                        <!-- Item Quantity -->
                                                        <div>
                                                            <label class="block text-sm font-medium text-gray-700">Quantity Required</label>
                                                            <input 
                                                                type="number" 
                                                                min="1"
                                                                wire:model="requestItems.{{ $index }}.quantity_required"
                                                                class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                                                            >
                                                        </div>
                                                        
                                                        <!-- Item Unit -->
                                                        <div>
                                                            <label class="block text-sm font-medium text-gray-700">Units</label>
                                                            <select 
                                                                wire:model="requestItems.{{ $index }}.unit"
                                                                class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                                            >
                                                                <option value="pcs">Pieces (pcs)</option>
                                                                <option value="kg">Kilograms (kg)</option>
                                                                <option value="m">Meters (m)</option>
                                                                <option value="l">Liters (l)</option>
                                                                <option value="set">Set</option>
                                                                <option value="box">Box</option>
                                                                <option value="roll">Roll</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                                @endforeach
                                            @else
                                                <div class="p-4 text-center text-gray-500">
                                                    Nenhum item adicionado ainda. Clique em "Add Item" para adicionar um item à requisição.
                                                </div>
                                            @endif
                                        </div>
                                        <!-- Suggested Vendor (Common) -->
                                        <div>
                                            <label for="suggested_vendor" class="block text-sm font-medium text-gray-700">Suggested Vendor Name</label>
                                            <input 
                                                type="text" 
                                                id="suggested_vendor" 
                                                wire:model="request.suggested_vendor" 
                                                class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                                            >
                                        </div>

                                        <!-- Delivery Date -->
                                        <div>
                                            <label for="quantity_required" class="block text-sm font-medium text-gray-700">Quantity Required</label>
                                            <input 
                                                type="number" 
                                                id="quantity_required" 
                                                wire:model="request.quantity_required" 
                                                min="1"
                                                class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                                            >
                                        </div>

                                        <!-- Unit -->
                                        <div>
                                            <label for="unit" class="block text-sm font-medium text-gray-700">Units</label>
                                            <select 
                                                id="unit" 
                                                wire:model="request.unit" 
                                                class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                                            >
                                                <option value="pcs">Pieces (pcs)</option>
                                                <option value="kg">Kilograms (kg)</option>
                                                <option value="m">Meters (m)</option>
                                                <option value="l">Liters (l)</option>
                                                <option value="set">Set</option>
                                                <option value="box">Box</option>
                                                <option value="pair">Pair</option>
                                            </select>
                                        </div>

                                        <!-- Suggested Vendor -->
                                        <div>
                                            <label for="suggested_vendor" class="block text-sm font-medium text-gray-700">Suggested Vendor Name</label>
                                            <input 
                                                type="text" 
                                                id="suggested_vendor" 
                                                wire:model="request.suggested_vendor" 
                                                class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                                            >
                                        </div>

                                        <!-- Delivery Date -->
                                        <div>
                                            <label for="delivery_date" class="block text-sm font-medium text-gray-700">E.T. Delivery Date</label>
                                            <input 
                                                type="date" 
                                                id="delivery_date" 
                                                wire:model="request.delivery_date" 
                                                class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                                                value="{{ isset($request['delivery_date']) && $request['delivery_date'] ? date('Y-m-d', strtotime($request['delivery_date'])) : '' }}"
                                            >
                                        </div>
                                    </div>

                                    <!-- Remarks -->
                                    <div class="mt-4">
                                        <label for="remarks" class="block text-sm font-medium text-gray-700">Remarks</label>
                                        <textarea 
                                            id="remarks" 
                                            wire:model="request.remarks" 
                                            rows="3"
                                            class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                                            placeholder="Additional information about this request..."
                                        ></textarea>
                                    </div>

                                    <!-- Image Upload -->
                                    <div class="mt-4">
                                        <label class="block text-sm font-medium text-gray-700">Item Pictures (Max. 10)</label>
                                        <div 
                                            class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md"
                                            x-data="{ dragOver: false }"
                                            x-on:dragover.prevent="dragOver = true"
                                            x-on:dragleave.prevent="dragOver = false"
                                            x-on:drop.prevent="dragOver = false"
                                            x-bind:class="{ 'bg-blue-50 border-blue-300': dragOver }"
                                        >
                                            <div class="space-y-1 text-center">
                                                <i class="fas fa-image text-gray-400 text-3xl"></i>
                                                <div class="flex flex-col text-sm text-gray-600">
                                                    <div class="flex justify-center">
                                                        <!-- Upload múltiplo -->
                                                        <label for="file-upload-multiple" class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500 mx-1">
                                                            <span>Upload múltiplo</span>
                                                            <input 
                                                                id="file-upload-multiple" 
                                                                name="file-upload-multiple" 
                                                                type="file" 
                                                                class="sr-only"
                                                                wire:model="images" 
                                                                multiple
                                                                {{ (count($images) + count($existingImages)) >= $maxImages ? 'disabled' : '' }}
                                                            >
                                                        </label>
                                                        
                                                        <!-- Upload individual -->
                                                        <label for="file-upload-single" class="relative cursor-pointer bg-white rounded-md font-medium text-green-600 hover:text-green-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-green-500 mx-1">
                                                            <span>Upload individual</span>
                                                            <input 
                                                                id="file-upload-single" 
                                                                name="file-upload-single" 
                                                                type="file" 
                                                                class="sr-only"
                                                                wire:model="images" 
                                                                {{ (count($images) + count($existingImages)) >= $maxImages ? 'disabled' : '' }}
                                                            >
                                                        </label>
                                                    </div>
                                                    <p class="mt-2">ou arraste e solte arquivos aqui</p>
                                                </div>
                                                <p class="text-xs text-gray-500">
                                                    PNG, JPG, GIF até 5MB
                                                </p>
                                                <p class="text-xs text-gray-500">
                                                    {{ count($existingImages) }} existentes / {{ count($images) }} novos / {{ $maxImages }} máximo
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Image Preview -->
                                    @if(count($existingImages) > 0 || count($images) > 0)
                                        <div class="mt-6">
                                            <h4 class="text-sm font-medium text-gray-700 mb-3">Pré-visualização de Imagens</h4>
                                            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
                                                <!-- Imagens existentes -->  
                                                @foreach($existingImages as $image)
                                                    <div class="border rounded-md overflow-hidden shadow-sm hover:shadow-md transition-shadow bg-white">
                                                        <div class="relative">
                                                            <img src="{{ Storage::url($image->image_path) }}" class="w-full h-32 object-cover">
                                                            <button 
                                                                type="button" 
                                                                wire:click="removeExistingImage({{ $image->id }})"
                                                                class="absolute top-2 right-2 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center hover:bg-red-600 focus:outline-none"
                                                                title="Remover imagem"
                                                            >
                                                                <i class="fas fa-times text-xs"></i>
                                                            </button>
                                                            <div class="absolute bottom-0 left-0 right-0 bg-black bg-opacity-60 text-white text-xs p-1 truncate">
                                                                {{ $image->original_filename }}
                                                            </div>
                                                        </div>
                                                        <div class="p-2">
                                                            <label class="block text-xs text-gray-500 mb-1">Legenda:</label>
                                                            <input 
                                                                type="text" 
                                                                placeholder="Adicionar legenda..."
                                                                class="w-full text-xs border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                                                                value="{{ $image->caption }}"
                                                                readonly
                                                            >
                                                        </div>
                                                    </div>
                                                @endforeach
                                                
                                                <!-- Novas imagens -->  
                                                @foreach($images as $index => $image)
                                                    <div class="border rounded-md overflow-hidden shadow-sm hover:shadow-md transition-shadow bg-white">
                                                        <div class="relative">
                                                            <img src="{{ $image->temporaryUrl() }}" class="w-full h-32 object-cover">
                                                            <button 
                                                                type="button" 
                                                                wire:click="removeImage({{ $index }})"
                                                                class="absolute top-2 right-2 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center hover:bg-red-600 focus:outline-none"
                                                                title="Remover imagem"
                                                            >
                                                                <i class="fas fa-times text-xs"></i>
                                                            </button>
                                                            <div class="absolute bottom-0 left-0 right-0 bg-black bg-opacity-60 text-white text-xs p-1 truncate">
                                                                {{ $image->getClientOriginalName() }}
                                                            </div>
                                                        </div>
                                                        <div class="p-2">
                                                            <label class="block text-xs text-gray-500 mb-1">Legenda:</label>
                                                            <input 
                                                                type="text" 
                                                                wire:model="imageCaptions.{{ $index }}" 
                                                                placeholder="Adicionar legenda..."
                                                                class="w-full text-xs border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                                                            >
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                </form>
                                
                                <!-- Incluir a modal de pesquisa de peças -->
                                @include('livewire.equipment-part-requests.part-search-modal')
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button 
                        type="button" 
                        wire:click="save"
                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm"
                    >
                        {{ $isEditing ? __('messages.update') : __('messages.create') }}
                    </button>
                    <button 
                        type="button" 
                        wire:click="closeModal" 
                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
                    >
                        {{ __('messages.cancel') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
@endif

<!-- Delete Confirmation Modal -->
@if($showDeleteModal)
    <div class="fixed z-10 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>

            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                            <i class="fas fa-exclamation-triangle text-red-600"></i>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                {{ __('messages.delete_part_request') }}
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500">
                                    {{ __('messages.delete_confirm_message') }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button 
                        type="button" 
                        wire:click="delete"
                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm"
                    >
                        Delete
                    </button>
                    <button 
                        type="button" 
                        wire:click="closeModal"
                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
                    >
                        {{ __('messages.cancel') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
@endif
