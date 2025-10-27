<!-- Modal para Visualizar Plano de Compra -->
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
                    {{ __('messages.purchase_plan_details') }}
                </h3>
                <button @click="$wire.closeModal()" class="text-white hover:text-gray-200 focus:outline-none">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            @if($selectedPlan)
                <div class="p-6">
                    <!-- Badges de Status e Prioridade -->
                    <div class="mb-6 flex justify-center space-x-4">
                        <span class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-medium
                            @if($selectedPlan->status === 'planned') bg-gray-100 text-gray-800
                            @elseif($selectedPlan->status === 'approved') bg-blue-100 text-blue-800
                            @elseif($selectedPlan->status === 'ordered') bg-green-100 text-green-800
                            @else bg-red-100 text-red-800
                            @endif">
                            <i class="mr-1.5
                                @if($selectedPlan->status === 'planned') fas fa-clipboard
                                @elseif($selectedPlan->status === 'approved') fas fa-clipboard-check
                                @elseif($selectedPlan->status === 'ordered') fas fa-shopping-cart
                                @else fas fa-ban
                                @endif"></i>
                            {{ $statuses[$selectedPlan->status] ?? $selectedPlan->status }}
                        </span>
                        
                        <span class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-medium
                            @if($selectedPlan->priority === 'low') bg-blue-100 text-blue-800
                            @elseif($selectedPlan->priority === 'medium') bg-green-100 text-green-800
                            @elseif($selectedPlan->priority === 'high') bg-yellow-100 text-yellow-800
                            @else bg-red-100 text-red-800
                            @endif">
                            <i class="mr-1.5
                                @if($selectedPlan->priority === 'low') fas fa-arrow-down
                                @elseif($selectedPlan->priority === 'medium') fas fa-equals
                                @elseif($selectedPlan->priority === 'high') fas fa-arrow-up
                                @else fas fa-exclamation-circle
                                @endif"></i>
                            {{ $priorities[$selectedPlan->priority] ?? $selectedPlan->priority }}
                        </span>
                    </div>
                    
                    <!-- Grade de Informações -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Coluna 1: Informações de Produto e Fornecedor -->
                        <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                            <h4 class="font-semibold text-gray-800 mb-4 flex items-center">
                                <i class="fas fa-box text-blue-600 mr-2"></i>
                                {{ __('messages.product_and_supplier') }}
                            </h4>
                            
                            <dl class="space-y-3">
                                <!-- Número do Plano -->
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Número do Plano:</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        {{ $selectedPlan->plan_number }}
                                    </dd>
                                </div>
                                
                                <!-- Título do Plano -->
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Título:</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        {{ $selectedPlan->title }}
                                    </dd>
                                </div>
                                
                                <!-- Fornecedor -->
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">{{ __('messages.supplier') }}:</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $selectedPlan->supplier->name ?? 'N/A' }}</dd>
                                </div>
                                
                                <!-- Data Necessária -->
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">{{ __('messages.required_date') }}:</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        {{ optional($selectedPlan->required_date)->format('d/m/Y') }}
                                    </dd>
                                </div>
                                
                                <!-- Data Planejada -->
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Data Planejada:</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        {{ optional($selectedPlan->planned_date)->format('d/m/Y') }}
                                    </dd>
                                </div>
                            </dl>
                        </div>
                        
                        <!-- Coluna 2: Informações de Quantidade e Preço -->
                        <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                            <h4 class="font-semibold text-gray-800 mb-4 flex items-center">
                                <i class="fas fa-calculator text-blue-600 mr-2"></i>
                                Resumo do Plano
                            </h4>
                            
                            <dl class="space-y-3">
                                <!-- Total de Produtos -->
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Total de Produtos:</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        {{ $selectedPlan->items->count() }} {{ $selectedPlan->items->count() > 1 ? 'itens' : 'item' }}
                                    </dd>
                                </div>
                                
                                <!-- Preço Total -->
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">{{ __('messages.total_price') }}:</dt>
                                    <dd class="mt-1 text-sm font-medium text-blue-600">
                                        {{ number_format($selectedPlan->total_value, 2) }} {{ __('messages.currency') }}
                                    </dd>
                                </div>
                                
                                <!-- Status e Prioridade -->
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Criado por:</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        {{ $selectedPlan->createdBy->name ?? 'N/A' }} em {{ optional($selectedPlan->created_at)->format('d/m/Y H:i') }}
                                    </dd>
                                </div>
                            </dl>
                        </div>
                    </div>
                    
                    <!-- Lista de Produtos do Plano -->
                    <div class="mt-6 bg-white rounded-lg p-4 border border-gray-200 overflow-x-auto">
                        <h4 class="font-semibold text-gray-800 mb-4 flex items-center">
                            <i class="fas fa-box text-blue-600 mr-2"></i>
                            Produtos no Plano
                        </h4>
                        
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Produto</th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantidade</th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unidade</th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Preço Unitário</th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($selectedPlan->items as $item)
                                <tr>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $item->product->name ?? 'N/A' }}</div>
                                        <div class="text-xs text-gray-500">{{ $item->product->sku ?? '' }}</div>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ number_format($item->quantity, 2) }}</div>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $item->unit_of_measure }}</div>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ number_format($item->unit_price, 2) }}</div>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <div class="text-sm font-medium text-blue-600">{{ number_format($item->total_price, 2) }}</div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
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
                        @if(!in_array($selectedPlan->status, ['cancelled']))
                            <button @click="$wire.updateStatus({{ $selectedPlan->id }}, 'cancelled')" 
                                class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-all duration-200 ease-in-out">
                                <i class="fas fa-ban mr-1.5"></i>
                                {{ __('messages.cancel_plan') }}
                            </button>
                        @endif
                        
                        @if($selectedPlan->status === 'planned')
                            <button @click="$wire.updateStatus({{ $selectedPlan->id }}, 'approved')" 
                                class="ml-2 inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 ease-in-out">
                                <i class="fas fa-clipboard-check mr-1.5"></i>
                                {{ __('messages.approve_plan') }}
                            </button>
                        @endif
                        
                        @if($selectedPlan->status === 'approved')
                            <button @click="$wire.updateStatus({{ $selectedPlan->id }}, 'ordered')" 
                                class="ml-2 inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-all duration-200 ease-in-out">
                                <i class="fas fa-shopping-cart mr-1.5"></i>
                                {{ __('messages.mark_as_ordered') }}
                            </button>
                        @endif
                    </div>
                    
                    <div class="flex space-x-2">
                        <button type="button" wire:click="editMultiProduct({{ $selectedPlan->id }})" 
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
