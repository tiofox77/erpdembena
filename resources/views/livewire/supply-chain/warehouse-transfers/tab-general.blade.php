<!-- ============================================
     TAB 1: GENERAL INFORMATION
     ============================================ -->

<div class="space-y-8">
    <!-- Page Title -->
    <div class="text-center">
        <h2 class="text-2xl font-bold text-gray-900 mb-2">{{ __('Transfer Information') }}</h2>
        <p class="text-gray-600">{{ __('Configure the basic details for your warehouse transfer request') }}</p>
    </div>

    <!-- Warehouses Section -->
    <div class="bg-gradient-to-br from-blue-50 to-indigo-100 rounded-xl border border-blue-200 overflow-hidden shadow-sm">
        <div class="bg-gradient-to-r from-blue-600 to-indigo-600 px-6 py-4">
            <h3 class="text-lg font-semibold text-white flex items-center">
                <i class="fas fa-warehouse mr-3"></i>
                {{ __('Warehouse Selection') }}
            </h3>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Source Location -->
                <div class="space-y-3">
                    <label for="source_location_id" class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-shipping-fast mr-2 text-blue-600"></i>
                        {{ __('From Warehouse') }} <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <select id="source_location_id" 
                                wire:model="transferRequest.source_location_id" 
                                class="block w-full pl-12 pr-4 py-4 text-base border-2 border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50 bg-white transition-all duration-200">
                            <option value="">{{ __('Select source warehouse...') }}</option>
                            @foreach($locations as $id => $name)
                                <option value="{{ $id }}">{{ $name }}</option>
                            @endforeach
                        </select>
                        <div class="absolute left-4 top-1/2 transform -translate-y-1/2">
                            <i class="fas fa-building text-gray-400"></i>
                        </div>
                    </div>
                    @error('transferRequest.source_location_id')
                        <p class="mt-2 text-sm text-red-600 bg-red-50 border border-red-200 rounded-lg px-3 py-2">
                            <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
                        </p>
                    @enderror
                </div>
                
                <!-- Destination Location -->
                <div class="space-y-3">
                    <label for="destination_location_id" class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-map-marker-alt mr-2 text-green-600"></i>
                        {{ __('To Warehouse') }} <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <select id="destination_location_id" 
                                wire:model="transferRequest.destination_location_id" 
                                class="block w-full pl-12 pr-4 py-4 text-base border-2 border-gray-300 rounded-lg shadow-sm focus:border-green-500 focus:ring focus:ring-green-500 focus:ring-opacity-50 bg-white transition-all duration-200">
                            <option value="">{{ __('Select destination warehouse...') }}</option>
                            @foreach($locations as $id => $name)
                                <option value="{{ $id }}">{{ $name }}</option>
                            @endforeach
                        </select>
                        <div class="absolute left-4 top-1/2 transform -translate-y-1/2">
                            <i class="fas fa-building text-gray-400"></i>
                        </div>
                    </div>
                    @error('transferRequest.destination_location_id')
                        <p class="mt-2 text-sm text-red-600 bg-red-50 border border-red-200 rounded-lg px-3 py-2">
                            <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
                        </p>
                    @enderror
                </div>
            </div>
        </div>
    </div>

    <!-- Scheduling Section -->
    <div class="bg-gradient-to-br from-purple-50 to-pink-100 rounded-xl border border-purple-200 overflow-hidden shadow-sm">
        <div class="bg-gradient-to-r from-purple-600 to-pink-600 px-6 py-4">
            <h3 class="text-lg font-semibold text-white flex items-center">
                <i class="fas fa-calendar-alt mr-3"></i>
                {{ __('Schedule & Priority') }}
            </h3>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Priority -->
                <div class="space-y-3">
                    <label for="priority" class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-flag mr-2 text-orange-600"></i>
                        {{ __('Priority Level') }} <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <select id="priority" 
                                wire:model.defer="transferRequest.priority" 
                                class="block w-full pl-12 pr-4 py-4 text-base border-2 border-gray-300 rounded-lg shadow-sm focus:border-orange-500 focus:ring focus:ring-orange-500 focus:ring-opacity-50 bg-white transition-all duration-200">
                            @foreach($priorities as $key => $value)
                                <option value="{{ $key }}">
                                    @if($key === 'urgent') 🔴
                                    @elseif($key === 'high') 🟠  
                                    @elseif($key === 'normal') 🟡
                                    @else 🟢
                                    @endif
                                    {{ $value }}
                                </option>
                            @endforeach
                        </select>
                        <div class="absolute left-4 top-1/2 transform -translate-y-1/2">
                            <i class="fas fa-exclamation text-gray-400"></i>
                        </div>
                    </div>
                    @error('transferRequest.priority')
                        <p class="mt-2 text-sm text-red-600 bg-red-50 border border-red-200 rounded-lg px-3 py-2">
                            <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
                        </p>
                    @enderror
                </div>
                
                <!-- Requested Date -->
                <div class="space-y-3">
                    <label for="requested_date" class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-calendar-plus mr-2 text-blue-600"></i>
                        {{ __('Request Date') }}
                    </label>
                    <div class="relative">
                        <input type="date" 
                               id="requested_date" 
                               wire:model.defer="transferRequest.requested_date" 
                               class="block w-full pl-12 pr-4 py-4 text-base border-2 border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50 bg-white transition-all duration-200">
                        <div class="absolute left-4 top-1/2 transform -translate-y-1/2">
                            <i class="fas fa-calendar text-gray-400"></i>
                        </div>
                    </div>
                    @error('transferRequest.requested_date')
                        <p class="mt-2 text-sm text-red-600 bg-red-50 border border-red-200 rounded-lg px-3 py-2">
                            <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
                        </p>
                    @enderror
                </div>
                
                <!-- Required By Date -->
                <div class="space-y-3">
                    <label for="required_by_date" class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-calendar-check mr-2 text-green-600"></i>
                        {{ __('Required By') }}
                    </label>
                    <div class="relative">
                        <input type="date" 
                               id="required_by_date" 
                               wire:model.defer="transferRequest.required_by_date" 
                               class="block w-full pl-12 pr-4 py-4 text-base border-2 border-gray-300 rounded-lg shadow-sm focus:border-green-500 focus:ring focus:ring-green-500 focus:ring-opacity-50 bg-white transition-all duration-200">
                        <div class="absolute left-4 top-1/2 transform -translate-y-1/2">
                            <i class="fas fa-calendar text-gray-400"></i>
                        </div>
                    </div>
                    @error('transferRequest.required_by_date')
                        <p class="mt-2 text-sm text-red-600 bg-red-50 border border-red-200 rounded-lg px-3 py-2">
                            <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
                        </p>
                    @enderror
                </div>
            </div>
        </div>
    </div>

    <!-- Notes Section -->
    <div class="bg-gradient-to-br from-yellow-50 to-orange-100 rounded-xl border border-yellow-200 overflow-hidden shadow-sm">
        <div class="bg-gradient-to-r from-yellow-600 to-orange-600 px-6 py-4">
            <h3 class="text-lg font-semibold text-white flex items-center">
                <i class="fas fa-sticky-note mr-3"></i>
                {{ __('Additional Information') }}
            </h3>
        </div>
        <div class="p-6">
            <div class="space-y-3">
                <label for="notes" class="block text-sm font-semibold text-gray-700 mb-2">
                    <i class="fas fa-comment-alt mr-2 text-yellow-600"></i>
                    {{ __('Notes & Instructions') }}
                </label>
                <textarea id="notes" 
                          wire:model.defer="transferRequest.notes" 
                          rows="4" 
                          placeholder="{{ __('Enter any special instructions, handling requirements, or additional notes for this transfer...') }}"
                          class="block w-full px-4 py-4 text-base border-2 border-gray-300 rounded-lg shadow-sm focus:border-yellow-500 focus:ring focus:ring-yellow-500 focus:ring-opacity-50 bg-white transition-all duration-200 resize-none"></textarea>
                @error('transferRequest.notes')
                    <p class="mt-2 text-sm text-red-600 bg-red-50 border border-red-200 rounded-lg px-3 py-2">
                        <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
                    </p>
                @enderror
            </div>
        </div>
    </div>
</div>
