{{-- View Quarterly Performance Evaluation Modal --}}
@if($showViewModal && $performanceEvaluationId)
@php
    $evaluation = \App\Models\HR\PerformanceEvaluation::with(['employee', 'supervisor', 'department', 'createdByUser'])->find($performanceEvaluationId);
    $criteriaList = [
        'productivity_output' => ['name' => 'Produtividade / Resultado', 'color' => 'indigo'],
        'quality_of_work' => ['name' => 'Qualidade do Trabalho', 'color' => 'blue'],
        'attendance_punctuality' => ['name' => 'Assiduidade e Pontualidade', 'color' => 'green'],
        'safety_compliance' => ['name' => 'Conformidade de Segurança', 'color' => 'yellow'],
        'machine_operation_skills' => ['name' => 'Operação de Máquinas', 'color' => 'purple'],
        'teamwork_cooperation' => ['name' => 'Trabalho em Equipa', 'color' => 'pink'],
        'adaptability_learning' => ['name' => 'Adaptabilidade', 'color' => 'orange'],
        'housekeeping_5s' => ['name' => 'Organização (5S)', 'color' => 'teal'],
        'discipline_attitude' => ['name' => 'Disciplina e Atitude', 'color' => 'red'],
        'initiative_responsibility' => ['name' => 'Iniciativa', 'color' => 'cyan'],
    ];
@endphp
@if($evaluation)
<div class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-5xl max-h-[95vh] flex flex-col overflow-hidden">
        
        {{-- Header --}}
        <div class="bg-gradient-to-r from-indigo-600 to-indigo-700 p-4 text-white flex-shrink-0">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="bg-white/20 p-2 rounded-lg">
                        <i class="fas fa-clipboard-check text-xl"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold">Avaliação de Desempenho</h2>
                        <p class="text-indigo-100 text-sm">{{ $evaluation->evaluation_quarter }} / {{ $evaluation->evaluation_year }}</p>
                    </div>
                </div>
                <button wire:click="closeViewModal" class="text-white/80 hover:text-white p-2">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
        </div>

        {{-- Content --}}
        <div class="flex-1 p-6 overflow-y-auto">
            
            {{-- Employee Information --}}
            <div class="bg-blue-50 p-4 rounded-lg mb-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <div class="h-14 w-14 bg-blue-500 rounded-full flex items-center justify-center">
                            <span class="text-white font-bold text-xl">
                                {{ substr($evaluation->employee->full_name ?? 'N', 0, 1) }}
                            </span>
                        </div>
                        <div>
                            <h4 class="text-lg font-bold text-gray-900">{{ $evaluation->employee->full_name ?? 'N/A' }}</h4>
                            <p class="text-gray-600 text-sm">ID: {{ $evaluation->employee->id_card ?? 'N/A' }}</p>
                            <p class="text-gray-500 text-sm">{{ $evaluation->department->name ?? $evaluation->employee->department->name ?? 'N/A' }}</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $evaluation->status_color }}">
                            {{ $evaluation->status_name }}
                        </span>
                        <p class="text-sm text-gray-500 mt-1">
                            {{ $evaluation->period_start?->format('d/m/Y') }} - {{ $evaluation->period_end?->format('d/m/Y') }}
                        </p>
                    </div>
                </div>
            </div>

            {{-- Overall Performance Summary --}}
            <div class="bg-green-50 p-4 rounded-lg mb-6">
                <h3 class="text-lg font-semibold text-green-800 mb-4">Resumo do Desempenho</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="bg-white p-4 rounded-lg text-center">
                        <div class="text-4xl font-bold {{ $evaluation->average_score >= 4 ? 'text-green-600' : ($evaluation->average_score >= 3 ? 'text-yellow-600' : 'text-red-600') }}">
                            {{ $evaluation->average_score ? number_format($evaluation->average_score, 2) : '-' }}
                        </div>
                        <div class="text-sm text-gray-500">Média / 5.00</div>
                    </div>
                    <div class="bg-white p-4 rounded-lg text-center">
                        @if($evaluation->performance_level)
                            <span class="inline-flex items-center px-3 py-2 rounded-full text-sm font-medium {{ $evaluation->performance_level_color }}">
                                {{ $evaluation->performance_level_name }}
                            </span>
                        @else
                            <span class="text-gray-400">-</span>
                        @endif
                        <div class="text-sm text-gray-500 mt-2">Nível de Desempenho</div>
                    </div>
                    <div class="bg-white p-4 rounded-lg text-center">
                        @if($evaluation->eligible_for_bonus)
                            <span class="inline-flex items-center px-3 py-2 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                <i class="fas fa-check mr-1"></i> Sim
                            </span>
                        @else
                            <span class="inline-flex items-center px-3 py-2 rounded-full text-sm font-medium bg-gray-100 text-gray-600">
                                Não
                            </span>
                        @endif
                        <div class="text-sm text-gray-500 mt-2">Elegível para Bónus</div>
                    </div>
                </div>
            </div>

            {{-- Performance Criteria --}}
            <div class="bg-indigo-50 p-4 rounded-lg mb-6">
                <h3 class="text-lg font-semibold text-indigo-800 mb-4">Critérios de Desempenho</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    @foreach($criteriaList as $key => $info)
                        @php $value = $evaluation->$key; @endphp
                        <div class="bg-white p-3 rounded-lg border flex items-center justify-between">
                            <div class="flex-1">
                                <span class="font-medium text-gray-800">{{ $info['name'] }}</span>
                                @if($evaluation->{$key . '_remarks'})
                                    <p class="text-xs text-gray-500 mt-1">{{ $evaluation->{$key . '_remarks'} }}</p>
                                @endif
                            </div>
                            <div class="flex items-center space-x-2">
                                @if($value)
                                    <span class="text-xl font-bold {{ $value >= 4 ? 'text-green-600' : ($value >= 3 ? 'text-yellow-600' : 'text-red-600') }}">
                                        {{ $value }}
                                    </span>
                                    <div class="w-20 bg-gray-200 rounded-full h-2">
                                        <div class="{{ $value >= 4 ? 'bg-green-500' : ($value >= 3 ? 'bg-yellow-500' : 'bg-red-500') }} h-2 rounded-full" 
                                             style="width: {{ ($value / 5) * 100 }}%"></div>
                                    </div>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Comments --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                @if($evaluation->supervisor_comments)
                    <div class="bg-yellow-50 p-4 rounded-lg">
                        <h4 class="font-semibold text-yellow-800 mb-2">
                            <i class="fas fa-user-tie mr-2"></i>Comentários do Supervisor
                        </h4>
                        <p class="text-gray-700 text-sm whitespace-pre-wrap">{{ $evaluation->supervisor_comments }}</p>
                    </div>
                @endif

                @if($evaluation->employee_comments)
                    <div class="bg-blue-50 p-4 rounded-lg">
                        <h4 class="font-semibold text-blue-800 mb-2">
                            <i class="fas fa-user mr-2"></i>Comentários do Funcionário
                        </h4>
                        <p class="text-gray-700 text-sm whitespace-pre-wrap">{{ $evaluation->employee_comments }}</p>
                    </div>
                @endif
            </div>

            {{-- Metadata --}}
            <div class="bg-gray-50 p-4 rounded-lg text-sm text-gray-600">
                <div class="flex flex-wrap gap-4">
                    @if($evaluation->evaluation_date)
                        <span><strong>Data da Avaliação:</strong> {{ $evaluation->evaluation_date->format('d/m/Y') }}</span>
                    @endif
                    @if($evaluation->createdByUser)
                        <span><strong>Criado por:</strong> {{ $evaluation->createdByUser->name }}</span>
                    @endif
                    <span><strong>Criado em:</strong> {{ $evaluation->created_at->format('d/m/Y H:i') }}</span>
                </div>
            </div>
        </div>

        {{-- Footer --}}
        <div class="flex-shrink-0 bg-gray-50 px-6 py-4 flex justify-end space-x-3 border-t border-gray-200">
            <button wire:click="closeViewModal" 
                    class="px-5 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors">
                Fechar
            </button>
            <a href="{{ route('hr.performance-evaluations.print', $performanceEvaluationId) }}" 
               target="_blank"
               class="px-5 py-2 rounded-lg text-sm font-medium text-white bg-green-600 hover:bg-green-700 transition-colors inline-flex items-center">
                <i class="fas fa-print mr-2"></i>Imprimir
            </a>
            <button wire:click="edit({{ $performanceEvaluationId }})" wire:click.then="closeViewModal"
                    class="px-5 py-2 rounded-lg text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 transition-colors">
                <i class="fas fa-edit mr-2"></i>Editar
            </button>
        </div>
    </div>
</div>
@endif
@endif
