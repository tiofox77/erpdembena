{{-- Equipment Create/Edit Modal --}}
@if($showEquipmentModal)
<div class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-4xl max-h-[90vh] flex flex-col overflow-hidden">
        
        {{-- Header --}}
        <div class="bg-gradient-to-r from-blue-600 to-blue-700 p-6 text-white">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="bg-white/20 p-2 rounded-lg">
                        <i class="fas fa-tools text-xl"></i>
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold">
                            {{ $isEditing ? __('messages.edit_equipment') : __('messages.add_equipment') }}
                        </h2>
                        <p class="text-blue-100">{{ __('messages.equipment_form_description') }}</p>
                    </div>
                </div>
                <button wire:click="closeEquipmentModal" class="text-white/80 hover:text-white p-2">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
        </div>

        {{-- Form Content --}}
        <div class="flex-1 p-6 overflow-y-auto">
            <form wire:submit.prevent="saveEquipment" class="space-y-6">
                
                {{-- Basic Information --}}
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h3 class="text-sm font-semibold text-gray-700 mb-4 flex items-center">
                        <i class="fas fa-info-circle mr-2 text-blue-600"></i>
                        {{ __('messages.basic_information') }}
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                {{ __('messages.name') }} <span class="text-red-500">*</span>
                            </label>
                            <input type="text" wire:model.defer="name" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                placeholder="{{ __('messages.enter_equipment_name') }}">
                            @error('name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                {{ __('messages.category') }} <span class="text-red-500">*</span>
                            </label>
                            <select wire:model.defer="equipment_type" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">{{ __('messages.select_category') }}</option>
                                @foreach($workEquipmentCategories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                            @error('equipment_type') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                {{ __('messages.status') }} <span class="text-red-500">*</span>
                            </label>
                            <select wire:model.defer="status" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">{{ __('messages.select_status') }}</option>
                                <option value="available">{{ __('messages.available') }}</option>
                                <option value="assigned">{{ __('messages.assigned') }}</option>
                                <option value="maintenance">{{ __('messages.maintenance') }}</option>
                                <option value="damaged">{{ __('messages.damaged') }}</option>
                                <option value="disposed">{{ __('messages.disposed') }}</option>
                            </select>
                            @error('status') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                {{-- Identification --}}
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h3 class="text-sm font-semibold text-gray-700 mb-4 flex items-center">
                        <i class="fas fa-barcode mr-2 text-blue-600"></i>
                        {{ __('messages.identification') }}
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                {{ __('messages.serial_number') }}
                            </label>
                            <input type="text" wire:model.defer="serial_number" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                placeholder="{{ __('messages.enter_serial_number') }}">
                            @error('serial_number') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                {{ __('messages.asset_code') }}
                            </label>
                            <input type="text" wire:model.defer="asset_code" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                placeholder="{{ __('messages.enter_asset_code') }}">
                            @error('asset_code') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                {{ __('messages.brand') }}
                            </label>
                            <input type="text" wire:model.defer="brand" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                placeholder="{{ __('messages.enter_brand') }}">
                            @error('brand') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                {{ __('messages.model') }}
                            </label>
                            <input type="text" wire:model.defer="model" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                placeholder="{{ __('messages.enter_model') }}">
                            @error('model') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                {{-- Purchase Information --}}
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h3 class="text-sm font-semibold text-gray-700 mb-4 flex items-center">
                        <i class="fas fa-shopping-cart mr-2 text-blue-600"></i>
                        {{ __('messages.purchase_information') }}
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                {{ __('messages.purchase_date') }}
                            </label>
                            <input type="date" wire:model.defer="purchase_date" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            @error('purchase_date') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                {{ __('messages.purchase_cost') }}
                            </label>
                            <input type="number" step="0.01" wire:model.defer="purchase_cost" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                placeholder="0.00">
                            @error('purchase_cost') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                {{ __('messages.warranty_expiry') }}
                            </label>
                            <input type="date" wire:model.defer="warranty_expiry" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            @error('warranty_expiry') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                {{-- Additional Information --}}
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h3 class="text-sm font-semibold text-gray-700 mb-4 flex items-center">
                        <i class="fas fa-clipboard mr-2 text-blue-600"></i>
                        {{ __('messages.additional_information') }}
                    </h3>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                {{ __('messages.condition') }}
                            </label>
                            <input type="text" wire:model.defer="condition" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                placeholder="{{ __('messages.enter_condition') }}">
                            @error('condition') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                {{ __('messages.notes') }}
                            </label>
                            <textarea wire:model.defer="notes" rows="3" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                placeholder="{{ __('messages.enter_notes') }}"></textarea>
                            @error('notes') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

            </form>
        </div>

        {{-- Footer --}}
        <div class="bg-gray-50 px-6 py-4 flex justify-end space-x-3 border-t">
            <button wire:click="closeEquipmentModal" type="button" 
                class="px-6 py-3 border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-colors">
                <i class="fas fa-times mr-2"></i>
                {{ __('messages.cancel') }}
            </button>
            <button wire:click="saveEquipment" type="button" 
                wire:loading.attr="disabled"
                wire:target="saveEquipment"
                class="px-6 py-3 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                <span wire:loading.remove wire:target="saveEquipment">
                    <i class="fas fa-save mr-2"></i>
                    {{ $isEditing ? __('messages.update') : __('messages.save') }}
                </span>
                <span wire:loading wire:target="saveEquipment" class="flex items-center">
                    <svg class="animate-spin h-4 w-4 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    {{ __('messages.saving') }}...
                </span>
            </button>
        </div>
    </div>
</div>
@endif
