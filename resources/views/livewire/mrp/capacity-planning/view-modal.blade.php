<!-- Modal para Visualizar Plano de Capacidade -->
<div x-cloak
    class="fixed inset-0 z-30 flex items-center justify-center overflow-auto bg-gray-800 bg-opacity-75 transition-opacity"
    x-show="$wire.showViewModal"
    @keydown.escape.window="$wire.closeModal()">
    <div class="relative w-full max-w-4xl mx-auto my-8 px-4 sm:px-0"
        x-show="$wire.showViewModal"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0 transform scale-95"
        x-transition:enter-end="opacity-100 transform scale-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100 transform scale-100"
        x-transition:leave-end="opacity-0 transform scale-95"
        @click.away="$wire.closeModal()">
        <div class="relative bg-white rounded-lg shadow-xl overflow-hidden" @click.stop>
            <!-- Cabeçalho do Modal -->
            <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-4 py-4 sm:px-6 flex justify-between items-center">
                <h3 class="text-lg font-medium text-white">
                    <i class="fas fa-eye mr-2"></i>
                    {{ __('messages.capacity_plan_details') }}
                </h3>
                <button @click="$wire.closeModal()" class="text-white hover:text-gray-200 focus:outline-none">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            @if($selectedPlan)
                <div class="p-6">
                    <!-- Badge de Status -->
                    <div class="mb-6 flex justify-center">
                        <span class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-medium
                            @if($selectedPlan->status === 'draft') bg-gray-100 text-gray-800
                            @elseif($selectedPlan->status === 'confirmed') bg-blue-100 text-blue-800
                            @elseif($selectedPlan->status === 'in_progress') bg-yellow-100 text-yellow-800
                            @elseif($selectedPlan->status === 'completed') bg-green-100 text-green-800
                            @elseif($selectedPlan->status === 'cancelled') bg-red-100 text-red-800
                            @endif">
                            <i class="mr-1.5
                                @if($selectedPlan->status === 'draft') fas fa-pencil-alt
                                @elseif($selectedPlan->status === 'confirmed') fas fa-clipboard-check
                                @elseif($selectedPlan->status === 'in_progress') fas fa-spinner fa-spin
                                @elseif($selectedPlan->status === 'completed') fas fa-check-circle
                                @elseif($selectedPlan->status === 'cancelled') fas fa-ban
                                @endif"></i>
                            {{ $statuses[$selectedPlan->status] ?? $selectedPlan->status }}
                        </span>
                    </div>
                    
                    <!-- Grade de Informações -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Coluna 1: Informações do Recurso -->
                        <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                            <h4 class="font-semibold text-gray-800 mb-4 flex items-center">
                                <i class="fas fa-cogs text-blue-600 mr-2"></i>
                                {{ __('messages.resource_information') }}
                            </h4>
                            
                            <dl class="space-y-3">
                                <!-- Recurso -->
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">{{ __('messages.resource') }}:</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $selectedPlan->resource->name ?? 'N/A' }}</dd>
                                </div>
                                
                                <!-- Tipo de Recurso -->
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">{{ __('messages.resource_type') }}:</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $selectedPlan->resourceType->name ?? 'N/A' }}</dd>
                                </div>
                                
                                <!-- Departamento -->
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">{{ __('messages.department') }}:</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $selectedPlan->department->name ?? 'N/A' }}</dd>
                                </div>
                                
                                <!-- Localização -->
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">{{ __('messages.location') }}:</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $selectedPlan->location->name ?? 'N/A' }}</dd>
                                </div>
                            </dl>
                        </div>
                        
                        <!-- Coluna 2: Informações de Capacidade -->
                        <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                            <h4 class="font-semibold text-gray-800 mb-4 flex items-center">
                                <i class="fas fa-chart-bar text-blue-600 mr-2"></i>
                                {{ __('messages.capacity_information') }}
                            </h4>
                            
                            <dl class="space-y-3">
                                <!-- Período -->
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">{{ __('messages.plan_period') }}:</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        {{ optional($selectedPlan->start_date)->format('d/m/Y') }} - 
                                        {{ optional($selectedPlan->end_date)->format('d/m/Y') }}
                                        @php
                                            $days = optional($selectedPlan->start_date)->diffInDays($selectedPlan->end_date) + 1;
                                        @endphp
                                        <span class="text-xs text-gray-500 ml-1">({{ $days }} {{ __('messages.days') }})</span>
                                    </dd>
                                </div>
                                
                                <!-- Capacidade Disponível -->
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">{{ __('messages.available_capacity') }}:</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        {{ number_format($selectedPlan->available_capacity, 2) }}
                                        {{ $capacityUnits[$selectedPlan->capacity_uom] ?? $selectedPlan->capacity_uom }}
                                    </dd>
                                </div>
                                
                                <!-- Fator de Eficiência -->
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">{{ __('messages.efficiency_factor') }}:</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $selectedPlan->efficiency_factor }}%</dd>
                                </div>
                                
                                <!-- Capacidade Efetiva (Calculada) -->
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">{{ __('messages.effective_capacity') }}:</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        @php
                                            $effectiveCapacity = round(($selectedPlan->available_capacity * $selectedPlan->efficiency_factor) / 100, 2);
                                        @endphp
                                        {{ number_format($effectiveCapacity, 2) }}
                                        {{ $capacityUnits[$selectedPlan->capacity_uom] ?? $selectedPlan->capacity_uom }}
                                    </dd>
                                </div>
                                
                                <!-- Capacidade Planejada -->
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">{{ __('messages.planned_capacity') }}:</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        {{ $selectedPlan->planned_capacity > 0 
                                            ? number_format($selectedPlan->planned_capacity, 2) . ' ' . ($capacityUnits[$selectedPlan->capacity_uom] ?? $selectedPlan->capacity_uom)
                                            : 'N/A' }}
                                    </dd>
                                </div>
                                
                                <!-- Utilização (Calculada) -->
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">{{ __('messages.capacity_utilization') }}:</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        @php
                                            $utilization = $effectiveCapacity > 0 && $selectedPlan->planned_capacity > 0
                                                ? min(round(($selectedPlan->planned_capacity / $effectiveCapacity) * 100, 1), 100)
                                                : 0;
                                        @endphp
                                        <div class="flex items-center">
                                            <div class="w-full bg-gray-200 rounded-full h-2.5 mr-2">
                                                <div class="h-2.5 rounded-full 
                                                    @if($utilization < 50) bg-green-600
                                                    @elseif($utilization < 80) bg-yellow-500
                                                    @else bg-red-600
                                                    @endif" 
                                                    style="width: {{ $utilization }}%">
                                                </div>
                                            </div>
                                            <span>{{ $utilization }}%</span>
                                        </div>
                                    </dd>
                                </div>
                            </dl>
                        </div>
                    </div>
                    
                    <!-- Notas e Informações Adicionais -->
                    <div class="mt-6 bg-gray-50 rounded-lg p-4 border border-gray-200">
                        <h4 class="font-semibold text-gray-800 mb-4 flex items-center">
                            <i class="fas fa-clipboard-list text-blue-600 mr-2"></i>
                            {{ __('messages.additional_information') }}
                        </h4>
                        
                        <!-- Notas -->
                        <div class="mb-4">
                            <h5 class="text-sm font-medium text-gray-500 mb-1">{{ __('messages.notes') }}:</h5>
                            <div class="p-3 bg-white border border-gray-200 rounded-md">
                                <p class="text-sm text-gray-700 whitespace-pre-line">
                                    {{ $selectedPlan->notes ?: __('messages.no_notes_available') }}
                                </p>
                            </div>
                        </div>
                        
                        <!-- Metadados -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Criado Por / Em -->
                            <div>
                                <h5 class="text-sm font-medium text-gray-500 mb-1">{{ __('messages.created_info') }}:</h5>
                                <p class="text-sm text-gray-700">
                                    <i class="fas fa-user-edit text-gray-400 mr-1"></i>
                                    {{ $selectedPlan->createdBy->name ?? 'N/A' }}
                                    <span class="ml-2 text-gray-500">
                                        <i class="fas fa-calendar-alt text-gray-400 mr-1"></i>
                                        {{ optional($selectedPlan->created_at)->format('d/m/Y H:i') }}
                                    </span>
                                </p>
                            </div>
                            
                            <!-- Atualizado Por / Em -->
                            <div>
                                <h5 class="text-sm font-medium text-gray-500 mb-1">{{ __('messages.updated_info') }}:</h5>
                                <p class="text-sm text-gray-700">
                                    <i class="fas fa-user-edit text-gray-400 mr-1"></i>
                                    {{ $selectedPlan->updatedBy->name ?? 'N/A' }}
                                    <span class="ml-2 text-gray-500">
                                        <i class="fas fa-calendar-alt text-gray-400 mr-1"></i>
                                        {{ optional($selectedPlan->updated_at)->format('d/m/Y H:i') }}
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Rodapé do Modal -->
                <div class="bg-gray-50 px-4 py-3 sm:px-6 flex flex-col sm:flex-row justify-between gap-2">
                    <div>
                        @if(!in_array($selectedPlan->status, ['completed', 'cancelled']))
                            <button @click="$wire.updateStatus({{ $selectedPlan->id }}, 'cancelled')" 
                                class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-all duration-200 ease-in-out">
                                <i class="fas fa-ban mr-1.5"></i>
                                {{ __('messages.cancel_plan') }}
                            </button>
                        @endif
                    </div>
                    
                    <div class="flex space-x-2">
                        <button type="button" wire:click="edit({{ $selectedPlan->id }})" 
                            class="inline-flex justify-center items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200 ease-in-out">
                            <i class="fas fa-edit mr-2"></i>
                            {{ __('messages.edit') }}
                        </button>
                        
                        <button type="button" wire:click="closeModal" 
                            class="inline-flex justify-center items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 ease-in-out">
                            <i class="fas fa-times mr-2"></i>
                            {{ __('messages.close') }}
                        </button>
                    </div>
                </div>
            @else
                <div class="p-6 flex justify-center items-center">
                    <div class="flex flex-col items-center space-y-2">
                        <div class="flex-shrink-0 bg-gray-100 p-3 rounded-full">
                            <i class="fas fa-exclamation-circle text-gray-400 text-2xl"></i>
                        </div>
                        <p class="text-gray-500">{{ __('messages.plan_not_found') }}</p>
                    </div>
                </div>
                
                <div class="bg-gray-50 px-4 py-3 sm:px-6 flex justify-end">
                    <button type="button" wire:click="closeModal" 
                        class="inline-flex justify-center items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 ease-in-out">
                        <i class="fas fa-times mr-2"></i>
                        {{ __('messages.close') }}
                    </button>
                </div>
            @endif
        </div>
    </div>
</div>
