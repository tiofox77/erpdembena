{{-- Performance Evaluations Management --}}
<div>
    {{-- Header --}}
    <div class="bg-gradient-to-r from-indigo-600 to-indigo-700 rounded-xl p-6 text-white mb-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <div class="bg-white/20 p-3 rounded-lg">
                    <i class="fas fa-chart-line text-2xl"></i>
                </div>
                <div>
                    <h1 class="text-3xl font-bold">{{ __('messages.performance_evaluations') }}</h1>
                    <p class="text-indigo-100">{{ __('messages.performance_evaluations_description') }}</p>
                </div>
            </div>
            <button wire:click="openModal" 
                    class="bg-white/20 hover:bg-white/30 px-6 py-3 rounded-lg transition-colors flex items-center space-x-2">
                <i class="fas fa-plus"></i>
                <span>{{ __('messages.new_evaluation') }}</span>
            </button>
        </div>
    </div>

    {{-- Filters and Search --}}
    <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            {{-- Search --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('messages.search') }}</label>
                <div class="relative">
                    <input wire:model.live="search" 
                           type="text" 
                           placeholder="{{ __('messages.search_employee') }}"
                           class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center">
                        <i class="fas fa-search text-gray-400"></i>
                    </div>
                </div>
            </div>

            {{-- Status Filter --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('messages.status') }}</label>
                <select wire:model.live="statusFilter" class="w-full pl-4 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">{{ __('messages.all_statuses') }}</option>
                    @foreach($statuses as $key => $status)
                        <option value="{{ $key }}">{{ __('messages.evaluation_status_' . $key) }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Type Filter --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('messages.evaluation_type') }}</label>
                <select wire:model.live="typeFilter" class="w-full pl-4 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">{{ __('messages.all_types') }}</option>
                    @foreach($evaluationTypes as $key => $type)
                        <option value="{{ $key }}">{{ __('messages.evaluation_type_' . $key) }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Date Filter --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('messages.evaluation_date_from') }}</label>
                <input wire:model.live="dateFilter" 
                       type="date" 
                       class="w-full pl-4 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            </div>
        </div>
    </div>

    {{-- Evaluations List --}}
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {{ __('messages.employee') }}
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {{ __('messages.evaluation_type') }}
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {{ __('messages.evaluation_period') }}
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {{ __('messages.overall_score') }}
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {{ __('messages.status') }}
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {{ __('messages.evaluation_date') }}
                        </th>
                        <th class="px-6 py-4 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {{ __('messages.actions') }}
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($evaluations as $evaluation)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center space-x-3">
                                    <div class="h-10 w-10 bg-indigo-500 rounded-full flex items-center justify-center">
                                        <span class="text-white font-medium text-sm">
                                            {{ substr($evaluation->employee->full_name, 0, 1) }}
                                        </span>
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">{{ $evaluation->employee->full_name }}</div>
                                        <div class="text-sm text-gray-500">{{ $evaluation->employee->id_card }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $evaluation->evaluation_type_color }}">
                                    {{ __('messages.evaluation_type_' . $evaluation->evaluation_type) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $evaluation->period_start->format('d/m/Y') }} - {{ $evaluation->period_end->format('d/m/Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($evaluation->overall_score)
                                    <div class="flex items-center space-x-2">
                                        <span class="text-2xl font-bold text-gray-900">{{ number_format($evaluation->overall_score, 1) }}</span>
                                        <div class="flex flex-col">
                                            <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium {{ $evaluation->performance_rating_color }}">
                                                {{ __('messages.rating_' . strtolower(str_replace(' ', '_', $evaluation->performance_rating))) }}
                                            </span>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-gray-400">{{ __('messages.not_scored') }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $evaluation->status_color }}">
                                    {{ __('messages.evaluation_status_' . $evaluation->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $evaluation->evaluation_date->format('d/m/Y') }}
                                @if($evaluation->is_overdue)
                                    <span class="ml-2 text-red-500">
                                        <i class="fas fa-exclamation-triangle"></i>
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end space-x-2">
                                    <button wire:click="view({{ $evaluation->id }})" 
                                            class="text-indigo-600 hover:text-indigo-900 p-2 rounded-lg hover:bg-indigo-50 transition-colors">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button wire:click="edit({{ $evaluation->id }})" 
                                            class="text-blue-600 hover:text-blue-900 p-2 rounded-lg hover:bg-blue-50 transition-colors">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button wire:click="confirmDelete({{ $evaluation->id }})" 
                                            class="text-red-600 hover:text-red-900 p-2 rounded-lg hover:bg-red-50 transition-colors">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center">
                                    <i class="fas fa-chart-line text-4xl text-gray-300 mb-4"></i>
                                    <h3 class="text-lg font-medium text-gray-900 mb-2">{{ __('messages.no_evaluations_found') }}</h3>
                                    <p class="text-gray-500 mb-4">{{ __('messages.no_evaluations_description') }}</p>
                                    <button wire:click="openModal" 
                                            class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg transition-colors">
                                        {{ __('messages.create_first_evaluation') }}
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($evaluations->hasPages())
            <div class="bg-gray-50 px-6 py-3">
                {{ $evaluations->links() }}
            </div>
        @endif
    </div>

    {{-- Auto Modal Includes (DO NOT REMOVE MARKER) --}}
    @includeIf("livewire.hr.performance-evaluations.modals.create-edit-modal")
    @includeIf("livewire.hr.performance-evaluations.modals.view-modal") 
    @includeIf("livewire.hr.performance-evaluations.modals.delete-modal")
    @includeIf("livewire.hr.performance-evaluations.modals.employee-search-modal")
    {{-- /Auto Modal Includes --}}
</div>
