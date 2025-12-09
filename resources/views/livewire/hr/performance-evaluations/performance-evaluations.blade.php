{{-- Quarterly Performance Evaluations Management --}}
<div>
    {{-- Header --}}
    <div class="bg-gradient-to-r from-indigo-600 to-indigo-700 rounded-xl p-6 text-white mb-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <div class="bg-white/20 p-3 rounded-lg">
                    <i class="fas fa-clipboard-check text-2xl"></i>
                </div>
                <div>
                    <h1 class="text-3xl font-bold">Avaliação de Desempenho</h1>
                    <p class="text-indigo-100">Performance Appraisal - Gestão de avaliações semestrais e especiais</p>
                </div>
            </div>
            <div class="flex items-center space-x-3">
                <a href="{{ route('hr.performance-evaluations.print-all', ['year' => $yearFilter, 'quarter' => $quarterFilter, 'status' => $statusFilter]) }}" 
                   target="_blank"
                   class="bg-white/20 hover:bg-white/30 px-4 py-3 rounded-lg transition-colors flex items-center space-x-2">
                    <i class="fas fa-file-pdf"></i>
                    <span>Imprimir Todos</span>
                </a>
                <button wire:click="openModal" 
                        class="bg-white/20 hover:bg-white/30 px-6 py-3 rounded-lg transition-colors flex items-center space-x-2">
                    <i class="fas fa-plus"></i>
                    <span>Nova Avaliação</span>
                </button>
            </div>
        </div>
    </div>

    {{-- Flash Message --}}
    @if(session()->has('message'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-lg" role="alert">
            <div class="flex items-center">
                <i class="fas fa-check-circle mr-2"></i>
                {{ session('message') }}
            </div>
        </div>
    @endif

    {{-- Filters and Search --}}
    <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
            {{-- Search --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Pesquisar</label>
                <div class="relative">
                    <input wire:model.live="search" 
                           type="text" 
                           placeholder="Nome ou ID do funcionário..."
                           class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center">
                        <i class="fas fa-search text-gray-400"></i>
                    </div>
                </div>
            </div>

            {{-- Year Filter --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Ano</label>
                <select wire:model.live="yearFilter" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">Todos os Anos</option>
                    @foreach($years as $year)
                        <option value="{{ $year }}">{{ $year }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Period Filter --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Período</label>
                <select wire:model.live="quarterFilter" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">Todos</option>
                    @foreach($quarters as $key => $quarter)
                        <option value="{{ $key }}">{{ $quarter }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Status Filter --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                <select wire:model.live="statusFilter" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">Todos</option>
                    @foreach($statuses as $key => $status)
                        <option value="{{ $key }}">{{ $status }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Per Page --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Por Página</label>
                <select wire:model.live="perPage" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="10">10</option>
                    <option value="15">15</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                </select>
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
                            Funcionário
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Departamento
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Período
                        </th>
                        <th class="px-6 py-4 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Média / %
                        </th>
                        <th class="px-6 py-4 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Nível
                        </th>
                        <th class="px-6 py-4 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Bónus
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Status
                        </th>
                        <th class="px-6 py-4 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Ações
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
                                            {{ substr($evaluation->employee->full_name ?? 'N', 0, 1) }}
                                        </span>
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">{{ $evaluation->employee->full_name ?? 'N/A' }}</div>
                                        <div class="text-sm text-gray-500">ID: {{ $evaluation->employee->id_card ?? 'N/A' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $evaluation->department->name ?? $evaluation->employee->department->name ?? 'N/A' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">
                                    {{ $evaluation->evaluation_quarter }} / {{ $evaluation->evaluation_year }}
                                </div>
                                <div class="text-xs text-gray-500">
                                    {{ $evaluation->period_start?->format('d/m') }} - {{ $evaluation->period_end?->format('d/m/Y') }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                @if($evaluation->average_score)
                                    <div class="flex flex-col items-center">
                                        <span class="text-xl font-bold {{ $evaluation->average_score >= 4 ? 'text-green-600' : ($evaluation->average_score >= 3 ? 'text-blue-600' : ($evaluation->average_score >= 2 ? 'text-yellow-600' : 'text-red-600')) }}">
                                            {{ number_format($evaluation->average_score, 1) }}
                                        </span>
                                        <span class="text-sm font-medium text-gray-500">
                                            ({{ number_format($evaluation->average_score * 20, 0) }}%)
                                        </span>
                                    </div>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                @if($evaluation->performance_level)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $evaluation->performance_level_color }}">
                                        {{ $evaluation->performance_level_name }}
                                    </span>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                @if($evaluation->eligible_for_bonus)
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <i class="fas fa-check mr-1"></i> Sim
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600">
                                        Não
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $evaluation->status_color }}">
                                    {{ $evaluation->status_name }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end space-x-2">
                                    <button wire:click="view({{ $evaluation->id }})" 
                                            class="text-indigo-600 hover:text-indigo-900 p-2 rounded-lg hover:bg-indigo-50 transition-colors"
                                            title="Ver Detalhes">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <a href="{{ route('hr.performance-evaluations.print', $evaluation->id) }}" 
                                       target="_blank"
                                       class="text-green-600 hover:text-green-900 p-2 rounded-lg hover:bg-green-50 transition-colors"
                                       title="Imprimir">
                                        <i class="fas fa-print"></i>
                                    </a>
                                    <button wire:click="edit({{ $evaluation->id }})" 
                                            class="text-blue-600 hover:text-blue-900 p-2 rounded-lg hover:bg-blue-50 transition-colors"
                                            title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button wire:click="confirmDelete({{ $evaluation->id }})" 
                                            class="text-red-600 hover:text-red-900 p-2 rounded-lg hover:bg-red-50 transition-colors"
                                            title="Excluir">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center">
                                    <i class="fas fa-clipboard-check text-4xl text-gray-300 mb-4"></i>
                                    <h3 class="text-lg font-medium text-gray-900 mb-2">Nenhuma avaliação encontrada</h3>
                                    <p class="text-gray-500 mb-4">Comece criando a primeira avaliação trimestral</p>
                                    <button wire:click="openModal" 
                                            class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg transition-colors">
                                        <i class="fas fa-plus mr-2"></i> Criar Primeira Avaliação
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
