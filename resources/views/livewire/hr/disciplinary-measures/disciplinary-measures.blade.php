<div>
    <div class="py-4">
        <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                <div class="p-4 sm:p-6">
                    <!-- Header -->
                    <div class="bg-gradient-to-r from-red-600 to-red-700 rounded-lg px-6 py-8 mb-6 shadow-lg">
                        <div class="flex justify-between items-center">
                            <div>
                                <h1 class="text-2xl font-bold text-white flex items-center">
                                    <i class="fas fa-gavel mr-3 text-red-200 animate-pulse"></i>
                                    {{ __('messages.disciplinary_measures') }}
                                </h1>
                                <p class="text-red-100 mt-2">{{ __('messages.manage_disciplinary_measures_description') }}</p>
                            </div>
                            <div class="flex space-x-3">
                                <button wire:click="create" class="inline-flex items-center px-4 py-2 bg-white border border-transparent rounded-md font-semibold text-red-700 hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-all duration-200 ease-in-out transform hover:scale-105">
                                    <i class="fas fa-plus mr-2"></i>
                                    {{ __('messages.new_measure') }}
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Filters and Search -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6 overflow-hidden">
                        <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 border-b border-gray-200">
                            <div class="flex items-center">
                                <i class="fas fa-filter text-gray-600 mr-2"></i>
                                <h3 class="text-lg font-medium text-gray-700">{{ __('messages.search_and_filters') }}</h3>
                            </div>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                <!-- Search -->
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('messages.search') }}</label>
                                    <div class="relative">
                                        <input type="text" wire:model.live.debounce.300ms="search" 
                                               placeholder="{{ __('messages.search_employee_reason_description') }}"
                                               class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <i class="fas fa-search text-gray-400"></i>
                                        </div>
                                    </div>
                                </div>

                                <!-- Status Filter -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('messages.status') }}</label>
                                    <select wire:model.live="filterStatus" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500">
                                        <option value="">{{ __('messages.all_statuses') }}</option>
                                        <option value="active">{{ __('messages.active') }}</option>
                                        <option value="completed">{{ __('messages.completed') }}</option>
                                        <option value="cancelled">{{ __('messages.cancelled') }}</option>
                                    </select>
                                </div>

                                <!-- Measure Type Filter -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('messages.measure_type') }}</label>
                                    <select wire:model.live="filterMeasureType" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500">
                                        <option value="">{{ __('messages.all_types') }}</option>
                                        <option value="verbal_warning">{{ __('messages.verbal_warning') }}</option>
                                        <option value="written_warning">{{ __('messages.written_warning') }}</option>
                                        <option value="suspension">{{ __('messages.suspension') }}</option>
                                        <option value="termination">{{ __('messages.termination') }}</option>
                                        <option value="fine">{{ __('messages.fine') }}</option>
                                        <option value="other">{{ __('messages.other') }}</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="mt-4 flex justify-end">
                                <button wire:click="resetFilters" class="text-gray-500 hover:text-gray-700 text-sm">
                                    <i class="fas fa-undo mr-1"></i>
                                    {{ __('messages.reset_filters') }}
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Measures Table -->
                    <div class="bg-white shadow overflow-hidden sm:rounded-md">
                        <div class="px-4 py-5 sm:p-6">
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th wire:click="sortBy('employee_id')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100">
                                                <div class="flex items-center">
                                                    {{ __('messages.employee') }}
                                                    <i class="fas fa-sort ml-1"></i>
                                                </div>
                                            </th>
                                            <th wire:click="sortBy('measure_type')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100">
                                                <div class="flex items-center">
                                                    {{ __('messages.measure_type') }}
                                                    <i class="fas fa-sort ml-1"></i>
                                                </div>
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                {{ __('messages.reason') }}
                                            </th>
                                            <th wire:click="sortBy('applied_date')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100">
                                                <div class="flex items-center">
                                                    {{ __('messages.applied_date') }}
                                                    <i class="fas fa-sort ml-1"></i>
                                                </div>
                                            </th>
                                            <th wire:click="sortBy('status')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100">
                                                <div class="flex items-center">
                                                    {{ __('messages.status') }}
                                                    <i class="fas fa-sort ml-1"></i>
                                                </div>
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                {{ __('messages.actions') }}
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @forelse($measures as $measure)
                                            <tr class="hover:bg-gray-50">
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="flex items-center">
                                                        <div class="flex-shrink-0 h-10 w-10">
                                                            <div class="h-10 w-10 rounded-full bg-red-500 flex items-center justify-center">
                                                                <span class="text-white font-medium text-sm">
                                                                    {{ substr($measure->employee->full_name, 0, 1) }}
                                                                </span>
                                                            </div>
                                                        </div>
                                                        <div class="ml-4">
                                                            <div class="text-sm font-medium text-gray-900">
                                                                {{ $measure->employee->full_name }}
                                                            </div>
                                                            <div class="text-sm text-gray-500">
                                                                {{ $measure->employee->id_card }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $measure->measure_type_color }}">
                                                        {{ $measure->measure_type_name }}
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4">
                                                    <div class="text-sm text-gray-900">{{ Str::limit($measure->reason, 50) }}</div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    {{ $measure->applied_date->format('d/m/Y') }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $measure->status_color }}">
                                                        {{ $measure->status_name }}
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                    <div class="flex space-x-2">
                                                        <button wire:click="view({{ $measure->id }})" class="text-blue-600 hover:text-blue-900">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                        <button wire:click="edit({{ $measure->id }})" class="text-indigo-600 hover:text-indigo-900">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <button wire:click="delete({{ $measure->id }})" class="text-red-600 hover:text-red-900">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                                    <div class="flex flex-col items-center py-8">
                                                        <i class="fas fa-gavel text-gray-300 text-4xl mb-4"></i>
                                                        <p class="text-lg font-medium">{{ __('messages.no_measures_found') }}</p>
                                                        <p class="text-sm">{{ __('messages.create_first_measure') }}</p>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination -->
                            <div class="mt-4">
                                {{ $measures->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modals -->
    @include('livewire.hr.disciplinary-measures.modals.create-edit-modal')
    @include('livewire.hr.disciplinary-measures.modals.view-modal')
    @include('livewire.hr.disciplinary-measures.modals.delete-modal')
    @include('livewire.hr.disciplinary-measures.modals.employee-search-modal')

    <!-- Flash Messages -->
    @if (session()->has('message'))
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show"
             class="fixed bottom-4 right-4 bg-green-500 text-white py-2 px-4 rounded-md shadow-md z-50">
            {{ session('message') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 5000)" x-show="show"
             class="fixed bottom-4 right-4 bg-red-500 text-white py-2 px-4 rounded-md shadow-md z-50">
            {{ session('error') }}
        </div>
    @endif
</div>
