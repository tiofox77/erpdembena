<div>
    <div class="py-4">
        <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                <div class="p-4 sm:p-6">
                    <!-- Header -->
                    <div class="bg-gradient-to-r from-purple-600 to-purple-700 rounded-lg px-6 py-8 mb-6 shadow-lg">
                        <div class="flex justify-between items-center">
                            <div>
                                <h1 class="text-2xl font-bold text-white flex items-center">
                                    <i class="fas fa-graduation-cap mr-3 text-purple-200 animate-pulse"></i>
                                    {{ __('messages.trainings') }}
                                </h1>
                                <p class="text-purple-100 mt-2">{{ __('messages.manage_trainings_description') }}</p>
                            </div>
                            <div class="flex space-x-3">
                                <button wire:click="create" class="inline-flex items-center px-4 py-2 bg-white border border-transparent rounded-md font-semibold text-purple-700 hover:bg-purple-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 transition-all duration-200 ease-in-out transform hover:scale-105">
                                    <i class="fas fa-plus mr-2"></i>
                                    {{ __('messages.new_training') }}
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
                                        <input wire:model.live="search" 
                                               type="text" 
                                               placeholder="{{ __('messages.search_by_employee_or_training') }}"
                                               class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center">
                                            <i class="fas fa-search text-gray-400"></i>
                                        </div>
                                    </div>
                                </div>

                                <!-- Status Filter -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('messages.status') }}</label>
                                    <select wire:model.live="statusFilter" class="w-full pl-4 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                                        <option value="">{{ __('messages.all_statuses') }}</option>
                                        @foreach($statuses as $key => $status)
                                            <option value="{{ $key }}">{{ __('messages.training_status_' . $key) }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Type Filter -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('messages.training_type') }}</label>
                                    <select wire:model.live="typeFilter" class="w-full pl-4 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                                        <option value="">{{ __('messages.all_types') }}</option>
                                        @foreach($trainingTypes as $key => $type)
                                            <option value="{{ $key }}">{{ __('messages.training_type_' . $key) }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Date Filter -->
                                <div class="md:col-span-1">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('messages.start_date_from') }}</label>
                                    <input wire:model.live="dateFilter" 
                                           type="date" 
                                           class="w-full pl-4 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Trainings List -->
                    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.employee') }}</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.training') }}</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.type') }}</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.dates') }}</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.status') }}</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.completion') }}</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse($trainings as $training)
                                        <tr class="hover:bg-gray-50 transition-colors duration-200">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <div class="h-10 w-10 rounded-full bg-purple-100 flex items-center justify-center">
                                                        <span class="text-sm font-medium text-purple-700">
                                                            {{ substr($training->employee->full_name, 0, 2) }}
                                                        </span>
                                                    </div>
                                                    <div class="ml-4">
                                                        <div class="text-sm font-medium text-gray-900">{{ $training->employee->full_name }}</div>
                                                        <div class="text-sm text-gray-500">{{ $training->employee->id_card }}</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4">
                                                <div class="text-sm font-medium text-gray-900">{{ $training->training_title }}</div>
                                                @if($training->provider)
                                                    <div class="text-sm text-gray-500">{{ __('messages.provider') }}: {{ $training->provider }}</div>
                                                @endif
                                                @if($training->duration_hours)
                                                    <div class="text-xs text-gray-400">{{ $training->duration_hours }}h</div>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $training->training_type_color }}">
                                                    {{ __('messages.training_type_' . $training->training_type) }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                <div>{{ __('messages.start') }}: {{ $training->start_date?->format('d/m/Y') }}</div>
                                                @if($training->end_date)
                                                    <div>{{ __('messages.end') }}: {{ $training->end_date->format('d/m/Y') }}</div>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $training->status_color }}">
                                                    {{ __('messages.training_status_' . $training->status) }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex flex-col">
                                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $training->completion_status_color }} mb-1">
                                                        {{ __('messages.completion_status_' . $training->completion_status) }}
                                                    </span>
                                                    @if($training->certification_received)
                                                        <span class="text-xs text-green-600 font-medium">
                                                            <i class="fas fa-certificate mr-1"></i>{{ __('messages.certified') }}
                                                        </span>
                                                    @endif
                                                    @if($training->evaluation_score)
                                                        <span class="text-xs text-gray-600">
                                                            {{ __('messages.score') }}: {{ number_format($training->evaluation_score, 1) }}/10
                                                        </span>
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <div class="flex justify-end space-x-2">
                                                    <button wire:click="view({{ $training->id }})" 
                                                            class="text-purple-600 hover:text-purple-900 transition-colors duration-200" 
                                                            title="{{ __('messages.view') }}">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <button wire:click="edit({{ $training->id }})" 
                                                            class="text-indigo-600 hover:text-indigo-900 transition-colors duration-200" 
                                                            title="{{ __('messages.edit') }}">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button wire:click="delete({{ $training->id }})" 
                                                            class="text-red-600 hover:text-red-900 transition-colors duration-200" 
                                                            title="{{ __('messages.delete') }}">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="px-6 py-12 text-center">
                                                <div class="flex flex-col items-center justify-center">
                                                    <i class="fas fa-graduation-cap text-6xl text-gray-300 mb-4"></i>
                                                    <h3 class="text-lg font-medium text-gray-900 mb-2">{{ __('messages.no_trainings_found') }}</h3>
                                                    <p class="text-gray-500 max-w-sm">{{ __('messages.no_trainings_description') }}</p>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        @if($trainings->hasPages())
                            <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                                {{ $trainings->links() }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Auto Modal Includes (DO NOT REMOVE MARKER) --}}
    @includeIf("livewire.hr.trainings.modals.create-edit-modal")
    @includeIf("livewire.hr.trainings.modals.view-modal")
    @includeIf("livewire.hr.trainings.modals.delete-modal")
    @includeIf("livewire.hr.trainings.modals.employee-search-modal")
    {{-- /Auto Modal Includes --}}
</div>
