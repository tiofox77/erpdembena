{{-- Payroll Batch Management Interface --}}
<div class="min-h-screen bg-gray-50">
    <div class="w-full h-full">
        <div class="flex flex-col min-h-screen">
            
            {{-- Header Section with Gradient --}}
            <div class="bg-gradient-to-br from-purple-600 via-purple-700 to-indigo-800 px-6 py-8 text-white flex-shrink-0">
                <div class="w-full flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <div class="bg-white/20 backdrop-blur-sm rounded-lg p-3">
                            <i class="fas fa-layer-group text-2xl"></i>
                        </div>
                        <div>
                            <h1 class="text-3xl font-bold">{{ __('livewire/hr/payroll-batch.page_title') }}</h1>
                            <p class="text-purple-100 mt-1">{{ __('livewire/hr/payroll-batch.page_description') }}</p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-3">
                        @if($this->canProcessBatch())
                        <button
                            wire:click="openBatchModal"
                            class="bg-white/10 hover:bg-white/20 backdrop-blur-sm text-white px-6 py-3 rounded-lg font-medium transition-all duration-300 hover:scale-105 flex items-center space-x-2 border border-white/20"
                        >
                            <i class="fas fa-plus text-lg"></i>
                            <span>{{ __('livewire/hr/payroll-batch.create_new_batch') }}</span>
                        </button>
                        @endif
                        <button
                            wire:click="resetFilters"
                            class="bg-gray-500/90 hover:bg-gray-400 text-white px-6 py-3 rounded-lg font-medium transition-all duration-300 hover:scale-105 flex items-center space-x-2"
                        >
                            <i class="fas fa-undo text-lg"></i>
                            <span>{{ __('livewire/hr/payroll-batch.clear_filters') }}</span>
                        </button>
                    </div>
                </div>
            </div>

            {{-- Stats Cards --}}
            <div class="bg-white border-b border-gray-200 px-6 py-6 flex-shrink-0">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div class="bg-gradient-to-br from-blue-50 to-blue-100 p-6 rounded-xl border border-blue-200 hover:shadow-lg transition-all duration-300">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-blue-600 font-medium text-sm">{{ __('livewire/hr/payroll-batch.total_batches') }}</p>
                                <p class="text-3xl font-bold text-blue-700">{{ $batches->total() }}</p>
                            </div>
                            <div class="bg-blue-500 p-3 rounded-full">
                                <i class="fas fa-layer-group text-white text-xl"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 p-6 rounded-xl border border-yellow-200 hover:shadow-lg transition-all duration-300">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-yellow-600 font-medium text-sm">{{ __('livewire/hr/payroll-batch.processing_batches') }}</p>
                                <p class="text-3xl font-bold text-yellow-700">{{ $batches->where('status', 'processing')->count() }}</p>
                            </div>
                            <div class="bg-yellow-500 p-3 rounded-full">
                                <i class="fas fa-cog fa-spin text-white text-xl"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-gradient-to-br from-green-50 to-emerald-100 p-6 rounded-xl border border-green-200 hover:shadow-lg transition-all duration-300">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-green-600 font-medium text-sm">{{ __('livewire/hr/payroll-batch.completed_batches') }}</p>
                                <p class="text-3xl font-bold text-green-700">{{ $batches->where('status', 'completed')->count() }}</p>
                            </div>
                            <div class="bg-green-500 p-3 rounded-full">
                                <i class="fas fa-check text-white text-xl"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-gradient-to-br from-purple-50 to-purple-100 p-6 rounded-xl border border-purple-200 hover:shadow-lg transition-all duration-300">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-purple-600 font-medium text-sm">{{ __('livewire/hr/payroll-batch.approved_batches') }}</p>
                                <p class="text-3xl font-bold text-purple-700">{{ $batches->where('status', 'approved')->count() }}</p>
                            </div>
                            <div class="bg-purple-500 p-3 rounded-full">
                                <i class="fas fa-user-check text-white text-xl"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Filters Section --}}
            <div class="bg-white border-b border-gray-200 px-6 py-6 flex-shrink-0">
                <div class="bg-gradient-to-r from-gray-50 to-gray-100 rounded-xl p-6 border border-gray-200">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-lg font-semibold text-gray-700 flex items-center">
                            <i class="fas fa-filter text-purple-500 mr-2"></i>
                            {{ __('livewire/hr/payroll-batch.filters_and_search') }}
                        </h3>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                        <div>
                            <label for="search" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-search text-gray-400 mr-1"></i>
                                {{ __('livewire/hr/payroll-batch.search_batch') }}
                            </label>
                            <div class="relative">
                                <input
                                    type="text"
                                    id="search"
                                    wire:model.live.debounce.300ms="search"
                                    placeholder="{{ __('livewire/hr/payroll-batch.batch_name_placeholder') }}"
                                    class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all duration-200"
                                >
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-search text-gray-400"></i>
                                </div>
                            </div>
                        </div>
                        <div>
                            <label for="status_filter" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-flag text-gray-400 mr-1"></i>
                                {{ __('livewire/hr/payroll-batch.status') }}
                            </label>
                            <select
                                id="status_filter"
                                wire:model.live="filters.status"
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all duration-200"
                            >
                                <option value="">{{ __('livewire/hr/payroll-batch.all_status') }}</option>
                                <option value="draft">{{ __('livewire/hr/payroll-batch.status_draft') }}</option>
                                <option value="ready_to_process">{{ __('livewire/hr/payroll-batch.status_ready_to_process') }}</option>
                                <option value="processing">{{ __('livewire/hr/payroll-batch.status_processing') }}</option>
                                <option value="completed">{{ __('livewire/hr/payroll-batch.status_completed') }}</option>
                                <option value="failed">{{ __('livewire/hr/payroll-batch.status_failed') }}</option>
                                <option value="approved">{{ __('livewire/hr/payroll-batch.status_approved') }}</option>
                                <option value="paid">{{ __('livewire/hr/payroll-batch.status_paid') }}</option>
                            </select>
                        </div>
                        <div>
                            <label for="department_filter" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-building text-gray-400 mr-1"></i>
                                {{ __('livewire/hr/payroll-batch.department') }}
                            </label>
                            <select
                                id="department_filter"
                                wire:model.live="filters.department_id"
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all duration-200"
                            >
                                <option value="">{{ __('livewire/hr/payroll-batch.all_departments') }}</option>
                                @foreach($departments as $department)
                                    <option value="{{ $department->id }}">{{ $department->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="period_filter" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-calendar text-gray-400 mr-1"></i>
                                {{ __('livewire/hr/payroll-batch.period') }}
                            </label>
                            <select
                                id="period_filter"
                                wire:model.live="filters.period_id"
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all duration-200"
                            >
                                <option value="">{{ __('livewire/hr/payroll-batch.all_periods') }}</option>
                                @foreach($payrollPeriods as $period)
                                    <option value="{{ $period->id }}">{{ $period->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Main Content Area - Batch Table --}}
            <div class="flex-1 bg-white px-6 py-6">
                <div class="bg-gradient-to-r from-gray-50 to-gray-100 rounded-xl p-6 border border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-700 flex items-center mb-4">
                        <i class="fas fa-table text-purple-500 mr-2"></i>
                        {{ __('livewire/hr/payroll-batch.page_title') }}
                    </h3>
                    
                    {{-- Batch Cards --}}
                    <div class="space-y-4">
                        @forelse($batches as $batch)
                            <div class="bg-white border border-gray-200 rounded-xl p-6 hover:shadow-lg transition-all duration-300 hover:border-purple-300">
                                <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 items-center">
                                    
                                    {{-- Batch Info --}}
                                    <div class="lg:col-span-3">
                                        <div class="space-y-1">
                                            <h4 class="text-lg font-semibold text-gray-900">{{ $batch->name }}</h4>
                                            @if($batch->description)
                                                <p class="text-sm text-gray-600">{{ $batch->description }}</p>
                                            @endif
                                            <div class="flex items-center space-x-2 text-sm text-gray-500">
                                                <i class="fas fa-calendar text-purple-400"></i>
                                                <span>{{ $batch->formatted_batch_date }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    {{-- Period Info --}}
                                    <div class="lg:col-span-2">
                                        <div class="space-y-1">
                                            <div class="flex items-center space-x-2">
                                                <div class="w-2 h-2 bg-blue-400 rounded-full"></div>
                                                <span class="text-sm font-medium text-gray-900">{{ $batch->payrollPeriod->name ?? 'N/A' }}</span>
                                            </div>
                                            @if($batch->department)
                                                <p class="text-xs text-gray-500 ml-4">{{ $batch->department->name }}</p>
                                            @endif
                                        </div>
                                    </div>
                                    
                                    {{-- Employee Count & Progress --}}
                                    <div class="lg:col-span-2">
                                        <div class="space-y-2">
                                            <div class="flex items-center space-x-2 text-sm">
                                                <i class="fas fa-users text-blue-500"></i>
                                                <span class="text-gray-700 font-medium">{{ $batch->total_employees }} {{ __('livewire/hr/payroll-batch.employees_text') }}</span>
                                            </div>
                                            <div class="flex items-center space-x-2 text-sm">
                                                <i class="fas fa-check-circle text-green-500"></i>
                                                <span class="text-gray-700">{{ $batch->processed_employees }} {{ __('livewire/hr/payroll-batch.processed_text') }}</span>
                                            </div>
                                            @if($batch->total_employees > 0)
                                                <div class="w-full bg-gray-200 rounded-full h-2">
                                                    <div class="bg-purple-600 h-2 rounded-full transition-all duration-300" 
                                                         style="width: {{ $batch->progress_percentage }}%"></div>
                                                </div>
                                                <p class="text-xs text-gray-500">{{ $batch->progress_percentage }}% {{ __('livewire/hr/payroll-batch.progress_complete') }}</p>
                                            @endif
                                        </div>
                                    </div>
                                    
                                    {{-- Financial Summary --}}
                                    <div class="lg:col-span-3">
                                        <div class="bg-gradient-to-r from-green-50 to-emerald-50 rounded-lg p-3 border border-green-200">
                                            <div class="grid grid-cols-2 gap-3">
                                                <div class="text-center">
                                                    <p class="text-xs text-green-600 font-medium">{{ __('livewire/hr/payroll-batch.gross_value') }}</p>
                                                    <p class="text-sm font-bold text-green-700">{{ number_format($batch->total_gross_amount, 0) }}</p>
                                                </div>
                                                <div class="text-center">
                                                    <p class="text-xs text-green-600 font-medium">{{ __('livewire/hr/payroll-batch.net_value') }}</p>
                                                    <p class="text-sm font-bold text-green-800">{{ number_format($batch->total_net_amount, 0) }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    {{-- Status --}}
                                    <div class="lg:col-span-1">
                                        <div class="flex justify-center">
                                            <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-semibold 
                                                {{ $batch->status === 'draft' ? 'bg-gray-100 text-gray-800 border border-gray-200' : '' }}
                                                {{ $batch->status === 'ready_to_process' ? 'bg-blue-100 text-blue-800 border border-blue-200' : '' }}
                                                {{ $batch->status === 'processing' ? 'bg-yellow-100 text-yellow-800 border border-yellow-200' : '' }}
                                                {{ $batch->status === 'completed' ? 'bg-green-100 text-green-800 border border-green-200' : '' }}
                                                {{ $batch->status === 'failed' ? 'bg-red-100 text-red-800 border border-red-200' : '' }}
                                                {{ $batch->status === 'approved' ? 'bg-purple-100 text-purple-800 border border-purple-200' : '' }}
                                                {{ $batch->status === 'paid' ? 'bg-emerald-100 text-emerald-800 border border-emerald-200' : '' }}
                                            ">
                                                <div class="w-2 h-2 rounded-full mr-2
                                                    {{ $batch->status === 'draft' ? 'bg-gray-500' : '' }}
                                                    {{ $batch->status === 'ready_to_process' ? 'bg-blue-500' : '' }}
                                                    {{ $batch->status === 'processing' ? 'bg-yellow-500 animate-pulse' : '' }}
                                                    {{ $batch->status === 'completed' ? 'bg-green-500' : '' }}
                                                    {{ $batch->status === 'failed' ? 'bg-red-500' : '' }}
                                                    {{ $batch->status === 'approved' ? 'bg-purple-500' : '' }}
                                                    {{ $batch->status === 'paid' ? 'bg-emerald-500' : '' }}
                                                "></div>
                                                {{ __('livewire/hr/payroll-batch.status_' . $batch->status) }}
                                            </span>
                                        </div>
                                    </div>
                                    
                                    {{-- Actions --}}
                                    <div class="lg:col-span-1">
                                        <div class="flex items-center justify-end space-x-2">
                                            <div class="relative group">
                                                <button wire:click="viewBatch({{ $batch->id }})" 
                                                        class="flex items-center justify-center w-8 h-8 text-blue-600 bg-blue-50 hover:bg-blue-100 rounded-full transition-all duration-200 hover:scale-110">
                                                    <i class="fas fa-eye text-sm"></i>
                                                </button>
                                                <div class="absolute bottom-full mb-2 left-1/2 transform -translate-x-1/2 px-2 py-1 bg-gray-800 text-white text-xs rounded opacity-0 group-hover:opacity-100 transition-opacity duration-200 whitespace-nowrap z-10">
                                                    {{ __('livewire/hr/payroll-batch.view_details') }}
                                                </div>
                                            </div>
                                            
                                            @if($batch->canBeProcessed())
                                                <div class="relative group">
                                                    <button wire:click="processBatch({{ $batch->id }})" 
                                                            class="flex items-center justify-center w-8 h-8 text-green-600 bg-green-50 hover:bg-green-100 rounded-full transition-all duration-200 hover:scale-110">
                                                        <i class="fas fa-play text-sm"></i>
                                                    </button>
                                                    <div class="absolute bottom-full mb-2 left-1/2 transform -translate-x-1/2 px-2 py-1 bg-gray-800 text-white text-xs rounded opacity-0 group-hover:opacity-100 transition-opacity duration-200 whitespace-nowrap z-10">
                                                        {{ __('livewire/hr/payroll-batch.process_batch') }}
                                                    </div>
                                                </div>
                                            @endif
                                            
                                            @if($batch->isEditable())
                                                <div class="relative group">
                                                    <button wire:click="confirmDelete({{ $batch->id }})" 
                                                            class="flex items-center justify-center w-8 h-8 text-red-600 bg-red-50 hover:bg-red-100 rounded-full transition-all duration-200 hover:scale-110">
                                                        <i class="fas fa-trash text-sm"></i>
                                                    </button>
                                                    <div class="absolute bottom-full mb-2 left-1/2 transform -translate-x-1/2 px-2 py-1 bg-gray-800 text-white text-xs rounded opacity-0 group-hover:opacity-100 transition-opacity duration-200 whitespace-nowrap z-10">
                                                        {{ __('livewire/hr/payroll-batch.delete_batch') }}
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-12">
                                <div class="flex flex-col items-center space-y-4">
                                    <div class="bg-gray-100 rounded-full p-6">
                                        <i class="fas fa-layer-group text-4xl text-gray-400"></i>
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-medium text-gray-900">{{ __('livewire/hr/payroll-batch.no_batches_found') }}</h3>
                                        <p class="text-gray-500 mt-1">{{ __('livewire/hr/payroll-batch.create_first_batch') }}</p>
                                    </div>
                                    @if($this->canProcessBatch())
                                        <button
                                            wire:click="openBatchModal"
                                            class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-3 rounded-lg font-medium transition-all duration-300 flex items-center space-x-2"
                                        >
                                            <i class="fas fa-plus"></i>
                                            <span>{{ __('livewire/hr/payroll-batch.create_first_batch_button') }}</span>
                                        </button>
                                    @endif
                                </div>
                            </div>
                        @endforelse
                    </div>

                    {{-- Pagination --}}
                    @if($batches->hasPages())
                        <div class="mt-6">
                            {{ $batches->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Include Modals --}}
    @include('livewire.hr.payroll-batch.modals._create-batch-modal')
    @include('livewire.hr.payroll-batch.modals._view-batch-modal')
    @include('livewire.hr.payroll-batch.modals._delete-batch-modal')
</div>
