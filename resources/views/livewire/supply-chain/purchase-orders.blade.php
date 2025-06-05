<div>
    <div class="container mx-auto px-4 py-6">
        <div class="flex flex-col sm:flex-row justify-between items-center gap-4 mb-6">
            <h1 class="text-2xl font-semibold text-gray-800 flex items-center">
                <i class="fas fa-shopping-cart text-blue-600 mr-3"></i>
                {{ __('messages.purchase_orders_management') }}
            </h1>
            <button wire:click="openCreateModal" 
                class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 ease-in-out transform hover:scale-105 hover:shadow-lg">
                <i class="fas fa-plus-circle mr-2 animate-pulse"></i>
                {{ __('messages.create_order') }}
            </button>
        </div>

        <!-- Cartão de Busca e Filtros -->
        <div class="mb-6 bg-white rounded-lg shadow-md overflow-hidden border border-gray-200 transition-all duration-300 ease-in-out hover:shadow-lg transform hover:scale-[1.01]">
            <!-- Cabeçalho do cartão com gradiente -->
            <div class="flex items-center bg-gradient-to-r from-blue-50 to-blue-100 px-4 py-3 border-b border-gray-200">
                <i class="fas fa-filter text-blue-600 mr-2"></i>
                <h2 class="text-base font-medium text-gray-700">{{ __('messages.filters_and_search') }}</h2>
            </div>
            <!-- Conteúdo do cartão -->
            <div class="p-4">
                <div class="flex flex-col gap-4">
                    <!-- Campo de busca -->
                    <div>
                        <label for="search" class="block text-sm font-medium text-gray-700 mb-1">
                            <i class="fas fa-search text-gray-500 mr-1"></i>
                            {{ __('messages.search') }}
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-search text-gray-400"></i>
                            </div>
                            <input wire:model.live.debounce.300ms="search" id="search" 
                                class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-all duration-200 ease-in-out" 
                                placeholder="{{ __('messages.search_orders') }}" 
                                type="search">
                        </div>
                        <p class="mt-1 text-xs text-gray-500">{{ __('messages.search_by_order_number_or_supplier') }}</p>
                    </div>
                    
                    <!-- Linha de filtros -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <!-- Filtro de Status -->
                        <div>
                            <label for="statusFilter" class="block text-sm font-medium text-gray-700 mb-1">
                                <i class="fas fa-tag text-gray-500 mr-1"></i>
                                {{ __('messages.status') }}
                            </label>
                            <select wire:model.live="statusFilter" id="statusFilter" 
                                class="block w-full pl-3 pr-10 py-2 border border-gray-300 rounded-md leading-5 bg-white focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-all duration-200 ease-in-out">
                                <option value="">{{ __('messages.all_statuses') }}</option>
                                <option value="draft">{{ __('messages.draft') }}</option>
                                <option value="pending_approval">{{ __('messages.pending_approval') }}</option>
                                <option value="approved">{{ __('messages.approved') }}</option>
                                <option value="ordered">{{ __('messages.ordered') }}</option>
                                <option value="partially_received">{{ __('messages.partially_received') }}</option>
                                <option value="completed">{{ __('messages.completed') }}</option>
                                <option value="cancelled">{{ __('messages.cancelled') }}</option>
                            </select>
                        </div>
                        
                        <!-- Filtro de Fornecedor -->
                        <div>
                            <label for="supplierFilter" class="block text-sm font-medium text-gray-700 mb-1">
                                <i class="fas fa-building text-gray-500 mr-1"></i>
                                {{ __('messages.supplier') }}
                            </label>
                            <select wire:model.live="supplierFilter" id="supplierFilter" 
                                class="block w-full pl-3 pr-10 py-2 border border-gray-300 rounded-md leading-5 bg-white focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-all duration-200 ease-in-out">
                                <option value="">{{ __('messages.all_suppliers') }}</option>
                                @foreach($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <!-- Filtro de Status Ativo/Inativo -->
                        <div>
                            <label for="activeFilter" class="block text-sm font-medium text-gray-700 mb-1">
                                <i class="fas fa-toggle-on text-gray-500 mr-1"></i>
                                {{ __('messages.status_active') }}
                            </label>
                            <select wire:model.live="activeFilter" id="activeFilter" 
                                class="block w-full pl-3 pr-10 py-2 border border-gray-300 rounded-md leading-5 bg-white focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-all duration-200 ease-in-out">
                                <option value="active">{{ __('messages.only_active') }}</option>
                                <option value="inactive">{{ __('messages.only_inactive') }}</option>
                                <option value="all">{{ __('messages.all_active_status') }}</option>
                            </select>
                        </div>

                        <!-- Filtro de Formulários Personalizados -->
                        <div>
                            <label for="customFormFilter" class="block text-sm font-medium text-gray-700 mb-1">
                                <i class="fas fa-file-alt text-gray-500 mr-1"></i>
                                {{ __('messages.custom_forms') }}
                            </label>
                            <select wire:model.live="customFormFilter" id="customFormFilter" 
                                class="block w-full pl-3 pr-10 py-2 border border-gray-300 rounded-md leading-5 bg-white focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-all duration-200 ease-in-out">
                                <option value="">{{ __('messages.all_forms') }}</option>
                                @foreach($customForms as $form)
                                    <option value="{{ $form->id }}">{{ $form->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <!-- Filtro de Status do Formulário Personalizado (aparece apenas quando um formulário é selecionado) -->
                        @if($customFormFilter)
                        <div>
                            <label for="customFormStatusFilter" class="block text-sm font-medium text-gray-700 mb-1">
                                <i class="fas fa-tag text-gray-500 mr-1"></i>
                                {{ __('messages.form_status') }}
                            </label>
                            <select wire:model.live="customFormStatusFilter" id="customFormStatusFilter" 
                                class="block w-full pl-3 pr-10 py-2 border border-gray-300 rounded-md leading-5 bg-white focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-all duration-200 ease-in-out">
                                <option value="">{{ __('messages.all_statuses') }}</option>
                                @php
                                    $selectedForm = $customForms->firstWhere('id', $customFormFilter);
                                    $statusOptions = [];
                                    
                                    if ($selectedForm && isset($selectedForm->status_display_config['field_id'])) {
                                        $statusField = \App\Models\SupplyChain\CustomFormField::find($selectedForm->status_display_config['field_id']);
                                        
                                        if ($statusField && in_array($statusField->type, ['select', 'radio'])) {
                                            $statusOptions = $statusField->options ?? [];
                                        }
                                    }
                                @endphp
                                
                                @if(is_array($statusOptions))
                                    @foreach($statusOptions as $option)
                                        @if(is_string($option))
                                            <option value="{{ $option }}">{{ $option }}</option>
                                        @elseif(is_array($option) && isset($option['value']))
                                            <option value="{{ $option['value'] }}">{{ $option['label'] ?? $option['value'] }}</option>
                                        @endif
                                    @endforeach
                                @endif
                            </select>
                        </div>
                        @endif
                        
                        <!-- Registros por página -->
                        <div>
                            <label for="perPage" class="block text-sm font-medium text-gray-700 mb-1">
                                <i class="fas fa-list-ol text-gray-500 mr-1"></i>
                                {{ __('messages.records_per_page') }}
                            </label>
                            <select wire:model.live="perPage" id="perPage" 
                                class="block w-full pl-3 pr-10 py-2 border border-gray-300 rounded-md leading-5 bg-white focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-all duration-200 ease-in-out">
                                <option value="10">10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Linha de filtros de data -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4 p-3 bg-blue-50 rounded-lg border border-blue-100">
                        <!-- Cabeçalho -->
                        <div class="md:col-span-3 mb-2">
                            <h3 class="text-sm font-medium text-blue-700 flex items-center">
                                <i class="fas fa-calendar-alt text-blue-600 mr-2"></i>
                                {{ __('messages.date_filters') }}
                            </h3>
                        </div>
                        
                        <!-- Filtro de Campo de Data -->
                        <div>
                            <label for="dateField" class="block text-sm font-medium text-gray-700 mb-1">
                                <i class="fas fa-calendar text-gray-500 mr-1"></i>
                                {{ __('messages.date_field') }}
                            </label>
                            <select wire:model.live="dateField" id="dateField" 
                                class="block w-full pl-3 pr-10 py-2 border border-gray-300 rounded-md leading-5 bg-white focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-all duration-200 ease-in-out">
                                <option value="order_date">{{ __('messages.order_date') }}</option>
                                <option value="expected_delivery_date">{{ __('messages.expected_delivery') }}</option>
                                <option value="delivery_date">{{ __('messages.delivery_date') }}</option>
                            </select>
                        </div>
                        
                        <!-- Filtro de Mês -->
                        <div>
                            <label for="monthFilter" class="block text-sm font-medium text-gray-700 mb-1">
                                <i class="fas fa-calendar-day text-gray-500 mr-1"></i>
                                {{ __('messages.month') }}
                            </label>
                            <select wire:model.live="monthFilter" id="monthFilter" 
                                class="block w-full pl-3 pr-10 py-2 border border-gray-300 rounded-md leading-5 bg-white focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-all duration-200 ease-in-out">
                                <option value="">{{ __('messages.all_months') }}</option>
                                <option value="1">{{ __('messages.january') }}</option>
                                <option value="2">{{ __('messages.february') }}</option>
                                <option value="3">{{ __('messages.march') }}</option>
                                <option value="4">{{ __('messages.april') }}</option>
                                <option value="5">{{ __('messages.may') }}</option>
                                <option value="6">{{ __('messages.june') }}</option>
                                <option value="7">{{ __('messages.july') }}</option>
                                <option value="8">{{ __('messages.august') }}</option>
                                <option value="9">{{ __('messages.september') }}</option>
                                <option value="10">{{ __('messages.october') }}</option>
                                <option value="11">{{ __('messages.november') }}</option>
                                <option value="12">{{ __('messages.december') }}</option>
                            </select>
                        </div>
                        
                        <!-- Filtro de Ano -->
                        <div>
                            <label for="yearFilter" class="block text-sm font-medium text-gray-700 mb-1">
                                <i class="fas fa-calendar-alt text-gray-500 mr-1"></i>
                                {{ __('messages.year') }}
                            </label>
                            <select wire:model.live="yearFilter" id="yearFilter" 
                                class="block w-full pl-3 pr-10 py-2 border border-gray-300 rounded-md leading-5 bg-white focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-all duration-200 ease-in-out">
                                <option value="">{{ __('messages.all_years') }}</option>
                                @php
                                    $currentYear = (int)date('Y');
                                    $startYear = $currentYear - 5;
                                    $endYear = $currentYear + 2;
                                @endphp
                                @for($year = $startYear; $year <= $endYear; $year++)
                                    <option value="{{ $year }}">{{ $year }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>
                    
                    <!-- Botões de ação -->
                    <div class="flex justify-end space-x-3">
                        <button wire:click="generatePdfList" 
                            class="inline-flex justify-center items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-all duration-200 ease-in-out transform hover:scale-105">
                            <i class="fas fa-file-pdf mr-2"></i>
                            {{ __('messages.generate_list_pdf') }}
                        </button>
                        
                        <button type="button" wire:click="resetFilters" 
                            class="inline-flex justify-center items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-gray-700 hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 ease-in-out transform hover:scale-105">
                            <i class="fas fa-redo-alt mr-2"></i>
                            {{ __('messages.reset_filters') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Alertas -->
        @if (session()->has('message'))
            <div class="mb-4 flex w-full overflow-hidden bg-white rounded-lg shadow-md">
                <div class="flex items-center justify-center w-12 bg-green-500">
                    <i class="fas fa-check text-white"></i>
                </div>
                <div class="px-4 py-2 -mx-3">
                    <div class="mx-3">
                        <p class="text-sm text-gray-600">{{ session('message') }}</p>
                    </div>
                </div>
            </div>
        @endif

        @if (session()->has('error'))
            <div class="mb-4 flex w-full overflow-hidden bg-white rounded-lg shadow-md">
                <div class="flex items-center justify-center w-12 bg-red-500">
                    <i class="fas fa-exclamation-circle text-white"></i>
                </div>
                <div class="px-4 py-2 -mx-3">
                    <div class="mx-3">
                        <p class="text-sm text-gray-600">{{ session('error') }}</p>
                    </div>
                </div>
            </div>
        @endif

        <!-- Tabela de Ordens de Compra -->
        <div class="bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden transition-all duration-300 ease-in-out hover:shadow-lg">
            <!-- Cabeçalho da Tabela -->
            <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-4 py-3">
                <h2 class="text-lg font-medium text-white flex items-center">
                    <i class="fas fa-shopping-cart mr-2"></i>
                    {{ __('messages.purchase_orders_list') }}
                </h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <div class="flex items-center cursor-pointer" wire:click="sortBy('order_number')">
                                    {{ __('messages.order_number') }}
                                    @if($sortField === 'order_number')
                                        <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1"></i>
                                    @else
                                        <i class="fas fa-sort ml-1 opacity-50"></i>
                                    @endif
                                </div>
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <div class="flex items-center cursor-pointer" wire:click="sortBy('other_reference')">
                                    {{ __('messages.other_reference') }}
                                    @if($sortField === 'other_reference')
                                        <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1"></i>
                                    @else
                                        <i class="fas fa-sort ml-1 opacity-50"></i>
                                    @endif
                                </div>
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('messages.supplier') }}
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <div class="flex items-center cursor-pointer" wire:click="sortBy('status')">
                                    {{ __('messages.status') }}
                                    @if($sortField === 'status')
                                        <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1"></i>
                                    @else
                                        <i class="fas fa-sort ml-1 opacity-50"></i>
                                    @endif
                                </div>
                            </th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <div class="flex items-center justify-center cursor-pointer" wire:click="sortBy('is_active')">
                                    {{ __('messages.status_active') }}
                                    @if($sortField === 'is_active')
                                        <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1"></i>
                                    @else
                                        <i class="fas fa-sort ml-1 opacity-50"></i>
                                    @endif
                                </div>
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('messages.delivery_date') }}
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('messages.total') }}
                            </th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('messages.actions') }}
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($purchaseOrders as $order)
                            <tr class="hover:bg-gray-50 transition-colors duration-150">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div wire:click="editOrder({{ $order->id }})" class="flex items-center text-sm font-medium text-blue-700 cursor-pointer hover:text-blue-900 hover:bg-blue-50 transition-all duration-150 ease-in-out px-2 py-1 rounded border-b-2 border-blue-400 shadow-sm hover:shadow">
                                        <i class="fas fa-edit mr-1 text-xs opacity-70"></i>
                                        {{ $order->order_number }}
                                        <i class="fas fa-external-link-alt ml-1 text-xs opacity-70"></i>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div wire:click="viewOrder({{ $order->id }})" class="flex items-center text-sm font-medium text-purple-700 cursor-pointer hover:text-purple-900 hover:bg-purple-50 transition-all duration-150 ease-in-out px-2 py-1 rounded border-b-2 border-purple-400 shadow-sm hover:shadow">
                                        <i class="fas fa-eye mr-1 text-xs opacity-70"></i>
                                        {{ $order->other_reference }}
                                        <i class="fas fa-external-link-alt ml-1 text-xs opacity-70"></i>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $order->supplier->name }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($order->status)
                                        <div class="flex items-center">
                                            <span wire:click="openShippingNotes({{ $order->id }})" 
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium cursor-pointer hover:shadow-md transition-all duration-200 ease-in-out transform hover:scale-105 
                                                @php
                                                    // Verificar se o status é um dos valores padrão do sistema
                                                    $standardStatuses = [
                                                        'draft', 'pending_approval', 'approved', 'ordered', 
                                                        'partially_received', 'completed', 'cancelled',
                                                        'order_placed', 'proforma_invoice_received', 'payment_completed',
                                                        'du_in_process', 'goods_acquired', 'shipped_to_port',
                                                        'shipping_line_booking_confirmed', 'container_loaded',
                                                        'on_board', 'arrived_at_port', 'customs_clearance', 'delivered'
                                                    ];
                                                    
                                                    $isStandardStatus = in_array($order->status, $standardStatuses);
                                                @endphp
                                                
                                                @if(!$isStandardStatus)
                                                    bg-blue-100 text-blue-800 border border-blue-200
                                                @elseif($order->status == 'draft') bg-gray-100 text-gray-800
                                                @elseif($order->status == 'pending_approval') bg-yellow-100 text-yellow-800
                                                @elseif($order->status == 'approved') bg-blue-100 text-blue-800
                                                @elseif($order->status == 'ordered') bg-indigo-100 text-indigo-800
                                                @elseif($order->status == 'partially_received') bg-purple-100 text-purple-800
                                                @elseif($order->status == 'completed') bg-green-100 text-green-800
                                                @elseif($order->status == 'cancelled') bg-red-100 text-red-800
                                                @elseif($order->status == 'order_placed') bg-gray-100 text-gray-800
                                                @elseif($order->status == 'proforma_invoice_received') bg-blue-100 text-blue-800
                                                @elseif($order->status == 'payment_completed') bg-indigo-100 text-indigo-800
                                                @elseif($order->status == 'du_in_process') bg-purple-100 text-purple-800
                                                @elseif($order->status == 'goods_acquired') bg-green-100 text-green-800
                                                @elseif($order->status == 'shipped_to_port') bg-emerald-100 text-emerald-800
                                                @elseif($order->status == 'shipping_line_booking_confirmed') bg-yellow-100 text-yellow-800
                                                @elseif($order->status == 'container_loaded') bg-orange-100 text-orange-800
                                                @elseif($order->status == 'on_board') bg-red-100 text-red-800
                                                @elseif($order->status == 'arrived_at_port') bg-pink-100 text-pink-800
                                                @elseif($order->status == 'customs_clearance') bg-teal-100 text-teal-800
                                                @elseif($order->status == 'delivered') bg-cyan-100 text-cyan-800
                                                @else bg-blue-100 text-blue-800
                                                @endif">
                                                
                                                @if(!$isStandardStatus)
                                                    <!-- Ícone para formulários personalizados -->
                                                    <i class="fas fa-clipboard-list mr-1 text-xs"></i>
                                                    {{ $order->status }}
                                                @else
                                                    <!-- Ícones para status padrão -->
                                                    <i class="fas 
                                                    @if($order->status == 'draft') fa-pencil-alt
                                                    @elseif($order->status == 'pending_approval') fa-clock
                                                    @elseif($order->status == 'approved') fa-check
                                                    @elseif($order->status == 'ordered') fa-shopping-cart
                                                    @elseif($order->status == 'partially_received') fa-truck
                                                    @elseif($order->status == 'completed') fa-check-circle
                                                    @elseif($order->status == 'cancelled') fa-times-circle
                                                    @elseif($order->status == 'order_placed') fa-shopping-cart 
                                                    @elseif($order->status == 'proforma_invoice_received') fa-file-invoice-dollar
                                                    @elseif($order->status == 'payment_completed') fa-money-bill-wave
                                                    @elseif($order->status == 'du_in_process') fa-file-alt
                                                    @elseif($order->status == 'goods_acquired') fa-boxes
                                                    @elseif($order->status == 'shipped_to_port') fa-dolly
                                                    @elseif($order->status == 'shipping_line_booking_confirmed') fa-calendar-check
                                                    @elseif($order->status == 'container_loaded') fa-box
                                                    @elseif($order->status == 'on_board') fa-ship 
                                                    @elseif($order->status == 'arrived_at_port') fa-anchor
                                                    @elseif($order->status == 'customs_clearance') fa-clipboard-check
                                                    @elseif($order->status == 'delivered') fa-check-circle
                                                    @endif mr-1 text-xs"></i>
                                                    
                                                    @php
                                                        // Verificar se existe uma tradução para este status
                                                        $translationKey = 'messages.shipping_status_'.$order->status;
                                                        $translationExists = \Illuminate\Support\Facades\Lang::has($translationKey);
                                                    @endphp
                                                    
                                                    @if($translationExists)
                                                        {{ __($translationKey) }}
                                                    @else
                                                        {{ __("messages.status_{$order->status}") }}
                                                    @endif
                                                @endif
                                                
                                                <!-- Exibir valor do campo personalizado ao lado do status -->
                                                <span class="ml-2 font-normal border-l border-current pl-2">
                                                    @php
                                                        $statusValue = $order->getStatusDisplayFieldValue();
                                                        if (isset($statusValue['custom_field_name'])) {
                                                            echo $statusValue['custom_field_name'] . ': ' . ($statusValue['custom_field_value'] ?? 'N/A');
                                                        } else {
                                                            echo 'PO Status: ' . $statusValue['status'];
                                                        }
                                                    @endphp
                                                </span>
                                            </span>
                                            @if($order->shipping_status_date)
                                                <span class="text-xs text-gray-500 ml-2">
                                                    {{ $order->shipping_status_date->format('d/m/Y') }}
                                                </span>
                                            @endif
                                        </div>
                                    @else
                                        <span wire:click="openShippingNotes({{ $order->id }})" 
                                              class="cursor-pointer text-blue-600 hover:text-blue-800 transition-colors duration-200 ease-in-out text-sm">
                                            <i class="fas fa-plus-circle mr-1"></i>
                                            {{ __('messages.add_shipping_status') }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    @if($order->is_active)
                                        <span class="inline-flex items-center justify-center px-2 py-1 text-xs font-medium leading-none rounded-full bg-green-100 text-green-800">
                                            <i class="fas fa-check-circle mr-1"></i> {{ __('messages.active') }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center justify-center px-2 py-1 text-xs font-medium leading-none rounded-full bg-gray-100 text-gray-800">
                                            <i class="fas fa-times-circle mr-1"></i> {{ __('messages.inactive') }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="block text-sm font-medium 
                                        {{ $order->is_overdue ? 'text-red-600' : 
                                          (strtotime($order->expected_delivery_date) <= strtotime('+15 days') && 
                                           strtotime($order->expected_delivery_date) >= strtotime('now') ? 
                                           'text-amber-600' : 'text-gray-900') }}">
                                        {{ $order->expected_delivery_date ? date('d/m/Y', strtotime($order->expected_delivery_date)) : '-' }}
                                        @if($order->is_overdue)
                                            <i class="fas fa-exclamation-circle text-red-500 ml-1 animate-pulse" 
                                               title="{{ __('messages.overdue_order') }}"></i>
                                        @elseif(strtotime($order->expected_delivery_date) <= strtotime('+15 days') && 
                                               strtotime($order->expected_delivery_date) >= strtotime('now'))
                                            <i class="fas fa-exclamation-triangle text-amber-500 ml-1" 
                                               title="{{ __('messages.delivery_approaching') }}" 
                                               x-data="{}" 
                                               x-tooltip.raw="{{ __('messages.delivery_within_15_days') }}"></i>
                                        @endif
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium {{ $order->total_amount > 1000 ? 'text-green-600' : 'text-gray-900' }}">
                                        {{ number_format($order->total_amount, 2) }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex justify-end space-x-2">
                                        <button wire:click="viewOrder({{ $order->id }})" 
                                            class="text-blue-600 hover:text-blue-900 transition-colors duration-150 transform hover:scale-110"
                                            title="{{ __('messages.view_order') }}">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        
                                        <button wire:click="editOrder({{ $order->id }})" 
                                            class="text-indigo-600 hover:text-indigo-900 transition-colors duration-150 transform hover:scale-110"
                                            title="{{ __('messages.edit_order') }}">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        
                                        <button wire:click="openShippingNotes({{ $order->id }})"
                                            class="text-amber-600 hover:text-amber-800 transition-colors duration-150 transform hover:scale-110"
                                            title="{{ __('messages.shipping_notes') }}">
                                            <i class="fas fa-shipping-fast"></i>
                                        </button>
                                        
                                        <button wire:click="generatePdf({{ $order->id }})" 
                                            class="text-green-600 hover:text-green-900 transition-colors duration-150 transform hover:scale-110"
                                            title="{{ __('messages.generate_pdf') }}">
                                            <i class="fas fa-file-pdf"></i>
                                        </button>
                                        
                                        <button wire:click="confirmDeleteOrder({{ $order->id }})" 
                                            class="text-red-600 hover:text-red-900 transition-colors duration-150 transform hover:scale-110"
                                            title="{{ __('messages.delete') }}">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex flex-col items-center justify-center py-6 space-y-2">
                                        <div class="flex-shrink-0 bg-gray-100 p-3 rounded-full">
                                            <i class="fas fa-shopping-cart text-gray-400 text-2xl"></i>
                                        </div>
                                        <p class="text-gray-500 text-sm">{{ __('messages.no_purchase_orders_found') }}</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- Paginação -->
            <div class="px-4 py-3 bg-white border-t border-gray-200">
                {{ $purchaseOrders->links() }}
            </div>
        </div>
    </div>

    <!-- Modals -->
    @include('livewire.supply-chain.purchase-orders-modals')
    @include('livewire.supply-chain.purchase-orders-modal-view')
    @include('livewire.supply-chain.purchase-orders-modal-shipping')
    
    <!-- Script de paginação específico para esta página -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Script de paginação específico carregado');
            
            // Função para aplicar valor salvo
            function applyPerPageValue() {
                const select = document.getElementById('perPage');
                const savedValue = localStorage.getItem('erpdembena_per_page');
                
                console.log('PO: Select encontrado:', select ? 'Sim' : 'Não');
                console.log('PO: Valor salvo:', savedValue);
                
                if (select && savedValue) {
                    // Verificar se a opção existe
                    const optionExists = Array.from(select.options).some(opt => opt.value === savedValue);
                    
                    if (optionExists) {
                        // 1. Diretamente no DOM
                        select.value = savedValue;
                        console.log('PO: Valor definido no select:', savedValue);
                        
                        // 2. Via Alpine.js se disponível
                        if (window.Alpine) {
                            console.log('PO: Alpine.js encontrado, tentando definir valor');
                            try {
                                const wireEl = select.closest('[wire\\:id]');
                                if (wireEl) {
                                    Alpine.$data(select).$wire.set('perPage', savedValue);
                                }
                            } catch(e) {
                                console.error('PO: Erro ao definir via Alpine:', e);
                            }
                        }
                        
                        // 3. Via Livewire diretamente
                        try {
                            const wireEl = select.closest('[wire\\:id]');
                            if (wireEl) {
                                const wireId = wireEl.getAttribute('wire:id');
                                console.log('PO: Componente Livewire encontrado:', wireId);
                                
                                if (window.Livewire) {
                                    console.log('PO: Atualizando via Livewire 3');
                                    window.Livewire.find(wireId).$wire.set('perPage', savedValue);
                                } else if (window.livewire) {
                                    console.log('PO: Atualizando via Livewire 2');
                                    window.livewire.find(wireId).set('perPage', savedValue);
                                }
                            }
                        } catch(e) {
                            console.error('PO: Erro ao definir via Livewire:', e);
                        }
                        
                        // 4. Simulando evento de mudança
                        try {
                            console.log('PO: Disparando evento de mudança');
                            select.dispatchEvent(new Event('change', { bubbles: true }));
                        } catch(e) {
                            console.error('PO: Erro ao disparar evento:', e);
                        }
                    }
                }
            }
            
            // Salvar valor quando mudar
            document.addEventListener('change', function(e) {
                if (e.target && e.target.id === 'perPage') {
                    console.log('PO: Salvando valor:', e.target.value);
                    localStorage.setItem('erpdembena_per_page', e.target.value);
                }
            });
            
            // Aplicar valor salvo em diferentes momentos
            applyPerPageValue(); // Imediatamente
            setTimeout(applyPerPageValue, 500); // Após 500ms
            setTimeout(applyPerPageValue, 1000); // Após 1s
            
            // Quando o Livewire terminar de processar
            if (window.Livewire) {
                window.Livewire.hook('message.processed', function() {
                    console.log('PO: Livewire processou mensagem, tentando aplicar valor');
                    setTimeout(applyPerPageValue, 100);
                });
            }
            
            // Observar mudanças no DOM
            const observer = new MutationObserver(function() {
                setTimeout(applyPerPageValue, 100);
            });
            
            observer.observe(document.body, { childList: true, subtree: true });
        });
    </script>
</div>
