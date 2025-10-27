<div>
    <div x-data="{ open: @entangle('showViewModal') }" 
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
        <div class="relative top-20 mx-auto p-1 w-full max-w-3xl">
            <div class="relative bg-white rounded-lg shadow-xl transform transition-all duration-300 ease-in-out" 
                 x-transition:enter="transition ease-out duration-300" 
                 x-transition:enter-start="transform opacity-0 scale-95" 
                 x-transition:enter-end="transform opacity-100 scale-100" 
                 x-transition:leave="transition ease-in duration-200" 
                 x-transition:leave-start="transform opacity-100 scale-100" 
                 x-transition:leave-end="transform opacity-0 scale-95">
                
                <!-- Cabeçalho com gradiente verde -->
                <div class="bg-gradient-to-r from-green-600 to-green-700 rounded-t-lg px-4 py-3 flex justify-between items-center">
                    <h3 class="text-lg font-medium text-white flex items-center">
                        <i class="fas fa-eye mr-2"></i>
                        {{ __('messages.view_line_details') }}
                    </h3>
                    <button type="button" wire:click="closeViewModal" class="text-white hover:text-gray-200 focus:outline-none transition-all duration-200 ease-in-out transform hover:scale-110 hover:rotate-90">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                
                <!-- Corpo do modal -->
                <div class="p-6 space-y-6">
                    @if(isset($line))
                        <!-- Informações Principais -->
                        <div class="bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden">
                            <div class="flex items-center bg-gradient-to-r from-blue-50 to-blue-100 px-4 py-3 border-b border-gray-200">
                                <i class="fas fa-info-circle text-blue-600 mr-2"></i>
                                <h3 class="text-base font-medium text-gray-700">{{ __('messages.line_details') }}</h3>
                            </div>
                            <div class="p-4">
                                <div class="flex justify-between items-center mb-4">
                                    <h2 class="text-xl font-semibold text-gray-800">{{ $line['name'] ?? 'N/A' }}</h2>
                                    <div>
                                        <span class="px-2 py-1 text-xs font-medium rounded bg-gray-100 text-gray-800">
                                            {{ $line['code'] ?? 'N/A' }}
                                        </span>
                                        <span class="ml-2">
                                            @if(isset($line['is_active']) && $line['is_active'])
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                    {{ __('messages.active') }}
                                                </span>
                                            @else
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                    {{ __('messages.inactive') }}
                                                </span>
                                            @endif
                                        </span>
                                    </div>
                                </div>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                                    <!-- Capacidade Produtiva -->
                                    <div class="bg-gray-50 p-3 rounded-lg">
                                        <h4 class="text-sm font-medium text-gray-700 mb-2 flex items-center">
                                            <i class="fas fa-tachometer-alt text-blue-500 mr-2"></i>
                                            {{ __('messages.production_capacity') }}
                                        </h4>
                                        <div class="flex items-center">
                                            <span class="text-2xl font-bold text-gray-800">{{ number_format($line['capacity_per_hour'] ?? 0, 1) }}</span>
                                            <span class="text-sm text-gray-600 ml-2">{{ __('messages.units_per_hour') }}</span>
                                        </div>
                                    </div>
                                    
                                    <!-- Localização e Departamento -->
                                    <div class="bg-gray-50 p-3 rounded-lg">
                                        <h4 class="text-sm font-medium text-gray-700 mb-2 flex items-center">
                                            <i class="fas fa-map-marker-alt text-green-500 mr-2"></i>
                                            {{ __('messages.location_and_department') }}
                                        </h4>
                                        <div class="flex flex-col">
                                            <span class="text-sm">
                                                <span class="font-medium">{{ __('messages.location') }}:</span> 
                                                {{ isset($line['location']) ? $line['location']['name'] : __('messages.not_assigned') }}
                                            </span>
                                            <span class="text-sm mt-1">
                                                <span class="font-medium">{{ __('messages.department') }}:</span> 
                                                {{ isset($line['department']) ? $line['department']['name'] : __('messages.not_assigned') }}
                                            </span>
                                        </div>
                                    </div>
                                    
                                    <!-- Gerente -->
                                    <div class="bg-gray-50 p-3 rounded-lg">
                                        <h4 class="text-sm font-medium text-gray-700 mb-2 flex items-center">
                                            <i class="fas fa-user-tie text-indigo-500 mr-2"></i>
                                            {{ __('messages.manager') }}
                                        </h4>
                                        <div class="flex items-center">
                                            @if(isset($line['manager']))
                                                <div class="w-8 h-8 bg-indigo-100 rounded-full flex items-center justify-center text-indigo-500 mr-2">
                                                    <i class="fas fa-user"></i>
                                                </div>
                                                <span>{{ $line['manager']['name'] }}</span>
                                            @else
                                                <span class="text-gray-500">{{ __('messages.not_assigned') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    
                                    <!-- Turnos -->
                                    <div class="bg-gray-50 p-3 rounded-lg md:col-span-2">
                                        <h4 class="text-sm font-medium text-gray-700 mb-2 flex items-center">
                                            <i class="fas fa-clock text-purple-500 mr-2"></i>
                                            {{ __('messages.associated_shifts') }}
                                        </h4>
                                        <div class="flex flex-wrap gap-2">
                                            @if(isset($line['shifts']) && count($line['shifts']) > 0)
                                                @foreach($line['shifts'] as $shift)
                                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                        <span class="w-2 h-2 rounded-full mr-1" style="background-color: {{ $shift['color_code'] }}"></span>
                                                        {{ $shift['name'] }} ({{ $shift['start_time'] }} - {{ $shift['end_time'] }})
                                                    </span>
                                                @endforeach
                                            @else
                                                <span class="text-sm text-gray-500">{{ __('messages.no_shifts_assigned') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    
                                    <!-- Descrição -->
                                    @if(!empty($line['description']))
                                        <div class="bg-gray-50 p-3 rounded-lg md:col-span-2">
                                            <h4 class="text-sm font-medium text-gray-700 mb-2 flex items-center">
                                                <i class="fas fa-align-left text-gray-500 mr-2"></i>
                                                {{ __('messages.description') }}
                                            </h4>
                                            <p class="text-sm text-gray-800">{{ $line['description'] }}</p>
                                        </div>
                                    @endif
                                    
                                    <!-- Observações -->
                                    @if(!empty($line['notes']))
                                        <div class="bg-gray-50 p-3 rounded-lg md:col-span-2">
                                            <h4 class="text-sm font-medium text-gray-700 mb-2 flex items-center">
                                                <i class="fas fa-sticky-note text-yellow-500 mr-2"></i>
                                                {{ __('messages.notes') }}
                                            </h4>
                                            <p class="text-sm text-gray-800">{{ $line['notes'] }}</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        
                        <!-- Metadados -->
                        <div class="bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden">
                            <div class="flex items-center bg-gradient-to-r from-gray-50 to-gray-100 px-4 py-3 border-b border-gray-200">
                                <i class="fas fa-history text-gray-600 mr-2"></i>
                                <h3 class="text-base font-medium text-gray-700">{{ __('messages.metadata') }}</h3>
                            </div>
                            <div class="p-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <span class="text-sm font-medium text-gray-500">{{ __('messages.created_at') }}:</span>
                                    <span class="text-sm text-gray-800 ml-1">
                                        {{ isset($line['created_at']) ? \Carbon\Carbon::parse($line['created_at'])->format('d/m/Y H:i') : 'N/A' }}
                                    </span>
                                </div>
                                <div>
                                    <span class="text-sm font-medium text-gray-500">{{ __('messages.created_by') }}:</span>
                                    <span class="text-sm text-gray-800 ml-1">
                                        {{ isset($line['created_by']) && isset($line['created_by']['name']) ? $line['created_by']['name'] : 'N/A' }}
                                    </span>
                                </div>
                                <div>
                                    <span class="text-sm font-medium text-gray-500">{{ __('messages.updated_at') }}:</span>
                                    <span class="text-sm text-gray-800 ml-1">
                                        {{ isset($line['updated_at']) ? \Carbon\Carbon::parse($line['updated_at'])->format('d/m/Y H:i') : 'N/A' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-gray-100 mb-4">
                                <i class="fas fa-exclamation-circle text-gray-600 text-lg"></i>
                            </div>
                            <h3 class="text-lg leading-6 font-medium text-gray-900">
                                {{ __('messages.line_not_found') }}
                            </h3>
                        </div>
                    @endif
                </div>
                
                <!-- Botões de Ação -->
                <div class="bg-gray-50 px-4 py-3 rounded-b-lg flex justify-end space-x-3 border-t border-gray-200">
                    <button type="button" wire:click="closeViewModal" class="inline-flex justify-center items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 ease-in-out transform hover:scale-105">
                        <i class="fas fa-times mr-2"></i>
                        {{ __('messages.close') }}
                    </button>
                    @if(isset($line['id']))
                        <button type="button" wire:click="edit({{ $line['id'] }})" class="inline-flex justify-center items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 ease-in-out transform hover:scale-105">
                            <i class="fas fa-edit mr-2"></i>
                            {{ __('messages.edit') }}
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
