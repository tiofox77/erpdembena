{{-- Create/Edit Quarterly Performance Evaluation Modal --}}
@if($showModal)
<div class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-6xl max-h-[95vh] flex flex-col overflow-hidden">
        
        {{-- Header --}}
        <div class="bg-gradient-to-r from-indigo-600 to-indigo-700 p-4 text-white flex-shrink-0">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="bg-white/20 p-2 rounded-lg">
                        <i class="fas fa-clipboard-check text-xl"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold">
                            {{ $isEditing ? 'Editar Avaliação' : 'Nova Avaliação Trimestral' }}
                        </h2>
                        <p class="text-indigo-100 text-sm">Quarterly Performance Appraisal Paper</p>
                    </div>
                </div>
                <button wire:click="closeModal" class="text-white/80 hover:text-white p-2">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
        </div>

        {{-- Form Content --}}
        <div class="flex-1 p-6 overflow-y-auto">
            <form wire:submit.prevent="save" class="space-y-6">
                
                {{-- Section 1: Employee Details --}}
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                        <span class="bg-indigo-600 text-white rounded-full w-6 h-6 flex items-center justify-center text-sm mr-2">1</span>
                        Employee Details / Dados do Funcionário
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        {{-- Employee Selection --}}
                        <div class="lg:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Funcionário <span class="text-red-500">*</span>
                            </label>
                            @if($selectedEmployee)
                                <div class="flex items-center justify-between bg-white p-3 rounded-lg border">
                                    <div class="flex items-center space-x-3">
                                        <div class="h-10 w-10 bg-indigo-500 rounded-full flex items-center justify-center">
                                            <span class="text-white font-medium text-sm">
                                                {{ substr($selectedEmployee->full_name ?? 'N', 0, 1) }}
                                            </span>
                                        </div>
                                        <div>
                                            <div class="text-sm font-medium text-gray-900">{{ $selectedEmployee->full_name }}</div>
                                            <div class="text-xs text-gray-500">
                                                ID: {{ $selectedEmployee->id_card ?? $selectedEmployee->id }} | 
                                                {{ $selectedEmployee->position->title ?? 'N/A' }}
                                            </div>
                                        </div>
                                    </div>
                                    <button type="button" wire:click="removeEmployee" class="text-red-500 hover:text-red-700">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            @else
                                <button type="button" wire:click="openEmployeeSearch" 
                                        class="w-full flex items-center justify-center px-4 py-3 border-2 border-dashed border-gray-300 rounded-lg text-gray-500 hover:border-indigo-400 hover:text-indigo-600 transition-colors">
                                    <i class="fas fa-user-plus mr-2"></i>
                                    Selecionar Funcionário
                                </button>
                            @endif
                            @error('employee_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        {{-- Quarter --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Trimestre <span class="text-red-500">*</span>
                            </label>
                            <select wire:model.live="evaluation_quarter" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                                @foreach($quarters as $key => $quarter)
                                    <option value="{{ $key }}">{{ $quarter }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Year --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Ano <span class="text-red-500">*</span>
                            </label>
                            <select wire:model.live="evaluation_year" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                                @foreach($years as $year)
                                    <option value="{{ $year }}">{{ $year }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                        {{-- Period Start --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Período Início</label>
                            <input type="date" wire:model="period_start" readonly
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-100">
                        </div>

                        {{-- Period End --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Período Fim</label>
                            <input type="date" wire:model="period_end" readonly
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-100">
                        </div>

                        {{-- Evaluation Date --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Data da Avaliação</label>
                            <input type="date" wire:model="evaluation_date"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                        </div>
                    </div>
                </div>

                {{-- Section 2: Performance Criteria --}}
                <div class="bg-indigo-50 p-4 rounded-lg">
                    <h3 class="text-lg font-semibold text-indigo-800 mb-4 flex items-center">
                        <span class="bg-indigo-600 text-white rounded-full w-6 h-6 flex items-center justify-center text-sm mr-2">2</span>
                        Performance Criteria / Critérios de Desempenho
                        <span class="ml-2 text-xs font-normal text-indigo-600">(Rating 1-5: 1=Poor, 2=Fair, 3=Satisfactory, 4=Good, 5=Excellent)</span>
                    </h3>

                    <div class="space-y-3">
                        {{-- 1. Productivity / Output --}}
                        <div class="bg-white p-3 rounded-lg border">
                            <div class="flex flex-col md:flex-row md:items-center gap-3">
                                <div class="flex-1">
                                    <span class="font-medium text-gray-800">1. Produtividade / Resultado</span>
                                    <p class="text-xs text-gray-500">Meets or exceeds daily/weekly production targets</p>
                                </div>
                                <div class="flex items-center gap-2">
                                    <select wire:model.live="productivity_output" class="w-32 px-2 py-1 border rounded text-center focus:ring-2 focus:ring-indigo-500">
                                        <option value="">-</option>
                                        @for($i = 1; $i <= 5; $i++) <option value="{{ $i }}">{{ $i }}</option> @endfor
                                    </select>
                                    <input type="text" wire:model="productivity_output_remarks" class="flex-1 md:w-48 px-2 py-1 border rounded text-sm" placeholder="Remarks...">
                                </div>
                            </div>
                        </div>

                        {{-- 2. Quality of Work --}}
                        <div class="bg-white p-3 rounded-lg border">
                            <div class="flex flex-col md:flex-row md:items-center gap-3">
                                <div class="flex-1">
                                    <span class="font-medium text-gray-800">2. Qualidade do Trabalho</span>
                                    <p class="text-xs text-gray-500">Produces work that meets quality standards with minimal rework</p>
                                </div>
                                <div class="flex items-center gap-2">
                                    <select wire:model.live="quality_of_work" class="w-32 px-2 py-1 border rounded text-center focus:ring-2 focus:ring-indigo-500">
                                        <option value="">-</option>
                                        @for($i = 1; $i <= 5; $i++) <option value="{{ $i }}">{{ $i }}</option> @endfor
                                    </select>
                                    <input type="text" wire:model="quality_of_work_remarks" class="flex-1 md:w-48 px-2 py-1 border rounded text-sm" placeholder="Remarks...">
                                </div>
                            </div>
                        </div>

                        {{-- 3. Attendance & Punctuality --}}
                        <div class="bg-white p-3 rounded-lg border">
                            <div class="flex flex-col md:flex-row md:items-center gap-3">
                                <div class="flex-1">
                                    <span class="font-medium text-gray-800">3. Assiduidade e Pontualidade</span>
                                    <p class="text-xs text-gray-500">Reports to work on time; follows shift schedules</p>
                                </div>
                                <div class="flex items-center gap-2">
                                    <select wire:model.live="attendance_punctuality" class="w-32 px-2 py-1 border rounded text-center focus:ring-2 focus:ring-indigo-500">
                                        <option value="">-</option>
                                        @for($i = 1; $i <= 5; $i++) <option value="{{ $i }}">{{ $i }}</option> @endfor
                                    </select>
                                    <input type="text" wire:model="attendance_punctuality_remarks" class="flex-1 md:w-48 px-2 py-1 border rounded text-sm" placeholder="Remarks...">
                                </div>
                            </div>
                        </div>

                        {{-- 4. Safety Compliance --}}
                        <div class="bg-white p-3 rounded-lg border">
                            <div class="flex flex-col md:flex-row md:items-center gap-3">
                                <div class="flex-1">
                                    <span class="font-medium text-gray-800">4. Conformidade de Segurança</span>
                                    <p class="text-xs text-gray-500">Follows safety procedures, uses PPE correctly</p>
                                </div>
                                <div class="flex items-center gap-2">
                                    <select wire:model.live="safety_compliance" class="w-32 px-2 py-1 border rounded text-center focus:ring-2 focus:ring-indigo-500">
                                        <option value="">-</option>
                                        @for($i = 1; $i <= 5; $i++) <option value="{{ $i }}">{{ $i }}</option> @endfor
                                    </select>
                                    <input type="text" wire:model="safety_compliance_remarks" class="flex-1 md:w-48 px-2 py-1 border rounded text-sm" placeholder="Remarks...">
                                </div>
                            </div>
                        </div>

                        {{-- 5. Machine Operation Skills --}}
                        <div class="bg-white p-3 rounded-lg border">
                            <div class="flex flex-col md:flex-row md:items-center gap-3">
                                <div class="flex-1">
                                    <span class="font-medium text-gray-800">5. Habilidades de Operação de Máquinas</span>
                                    <p class="text-xs text-gray-500">Efficient in operating assigned machines/equipment</p>
                                </div>
                                <div class="flex items-center gap-2">
                                    <select wire:model.live="machine_operation_skills" class="w-32 px-2 py-1 border rounded text-center focus:ring-2 focus:ring-indigo-500">
                                        <option value="">-</option>
                                        @for($i = 1; $i <= 5; $i++) <option value="{{ $i }}">{{ $i }}</option> @endfor
                                    </select>
                                    <input type="text" wire:model="machine_operation_skills_remarks" class="flex-1 md:w-48 px-2 py-1 border rounded text-sm" placeholder="Remarks...">
                                </div>
                            </div>
                        </div>

                        {{-- 6. Teamwork & Cooperation --}}
                        <div class="bg-white p-3 rounded-lg border">
                            <div class="flex flex-col md:flex-row md:items-center gap-3">
                                <div class="flex-1">
                                    <span class="font-medium text-gray-800">6. Trabalho em Equipa e Cooperação</span>
                                    <p class="text-xs text-gray-500">Works well with team members, supports others</p>
                                </div>
                                <div class="flex items-center gap-2">
                                    <select wire:model.live="teamwork_cooperation" class="w-32 px-2 py-1 border rounded text-center focus:ring-2 focus:ring-indigo-500">
                                        <option value="">-</option>
                                        @for($i = 1; $i <= 5; $i++) <option value="{{ $i }}">{{ $i }}</option> @endfor
                                    </select>
                                    <input type="text" wire:model="teamwork_cooperation_remarks" class="flex-1 md:w-48 px-2 py-1 border rounded text-sm" placeholder="Remarks...">
                                </div>
                            </div>
                        </div>

                        {{-- 7. Adaptability & Learning --}}
                        <div class="bg-white p-3 rounded-lg border">
                            <div class="flex flex-col md:flex-row md:items-center gap-3">
                                <div class="flex-1">
                                    <span class="font-medium text-gray-800">7. Adaptabilidade e Aprendizagem</span>
                                    <p class="text-xs text-gray-500">Responds positively to new tasks and training</p>
                                </div>
                                <div class="flex items-center gap-2">
                                    <select wire:model.live="adaptability_learning" class="w-32 px-2 py-1 border rounded text-center focus:ring-2 focus:ring-indigo-500">
                                        <option value="">-</option>
                                        @for($i = 1; $i <= 5; $i++) <option value="{{ $i }}">{{ $i }}</option> @endfor
                                    </select>
                                    <input type="text" wire:model="adaptability_learning_remarks" class="flex-1 md:w-48 px-2 py-1 border rounded text-sm" placeholder="Remarks...">
                                </div>
                            </div>
                        </div>

                        {{-- 8. Housekeeping (5S) --}}
                        <div class="bg-white p-3 rounded-lg border">
                            <div class="flex flex-col md:flex-row md:items-center gap-3">
                                <div class="flex-1">
                                    <span class="font-medium text-gray-800">8. Organização (5S)</span>
                                    <p class="text-xs text-gray-500">Keeps workstation clean and organized</p>
                                </div>
                                <div class="flex items-center gap-2">
                                    <select wire:model.live="housekeeping_5s" class="w-32 px-2 py-1 border rounded text-center focus:ring-2 focus:ring-indigo-500">
                                        <option value="">-</option>
                                        @for($i = 1; $i <= 5; $i++) <option value="{{ $i }}">{{ $i }}</option> @endfor
                                    </select>
                                    <input type="text" wire:model="housekeeping_5s_remarks" class="flex-1 md:w-48 px-2 py-1 border rounded text-sm" placeholder="Remarks...">
                                </div>
                            </div>
                        </div>

                        {{-- 9. Discipline & Attitude --}}
                        <div class="bg-white p-3 rounded-lg border">
                            <div class="flex flex-col md:flex-row md:items-center gap-3">
                                <div class="flex-1">
                                    <span class="font-medium text-gray-800">9. Disciplina e Atitude</span>
                                    <p class="text-xs text-gray-500">Follows company rules, shows positive attitude</p>
                                </div>
                                <div class="flex items-center gap-2">
                                    <select wire:model.live="discipline_attitude" class="w-32 px-2 py-1 border rounded text-center focus:ring-2 focus:ring-indigo-500">
                                        <option value="">-</option>
                                        @for($i = 1; $i <= 5; $i++) <option value="{{ $i }}">{{ $i }}</option> @endfor
                                    </select>
                                    <input type="text" wire:model="discipline_attitude_remarks" class="flex-1 md:w-48 px-2 py-1 border rounded text-sm" placeholder="Remarks...">
                                </div>
                            </div>
                        </div>

                        {{-- 10. Initiative & Responsibility --}}
                        <div class="bg-white p-3 rounded-lg border">
                            <div class="flex flex-col md:flex-row md:items-center gap-3">
                                <div class="flex-1">
                                    <span class="font-medium text-gray-800">10. Iniciativa e Responsabilidade</span>
                                    <p class="text-xs text-gray-500">Takes ownership of tasks, suggests improvements</p>
                                </div>
                                <div class="flex items-center gap-2">
                                    <select wire:model.live="initiative_responsibility" class="w-32 px-2 py-1 border rounded text-center focus:ring-2 focus:ring-indigo-500">
                                        <option value="">-</option>
                                        @for($i = 1; $i <= 5; $i++) <option value="{{ $i }}">{{ $i }}</option> @endfor
                                    </select>
                                    <input type="text" wire:model="initiative_responsibility_remarks" class="flex-1 md:w-48 px-2 py-1 border rounded text-sm" placeholder="Remarks...">
                                </div>
                            </div>
                        </div>

                        {{-- Rating Scale Legend --}}
                        <div class="flex flex-wrap gap-2 text-xs text-gray-600 mt-2 p-2 bg-gray-100 rounded">
                            <span><strong>1</strong>=Poor</span>
                            <span><strong>2</strong>=Fair</span>
                            <span><strong>3</strong>=Satisfactory</span>
                            <span><strong>4</strong>=Good</span>
                            <span><strong>5</strong>=Excellent</span>
                        </div>
                    </div>
                </div>

                {{-- Section 3: Overall Performance Summary --}}
                <div class="bg-green-50 p-4 rounded-lg">
                    <h3 class="text-lg font-semibold text-green-800 mb-4 flex items-center">
                        <span class="bg-green-600 text-white rounded-full w-6 h-6 flex items-center justify-center text-sm mr-2">3</span>
                        Overall Performance Summary / Resumo do Desempenho
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        {{-- Average Score --}}
                        <div class="bg-white p-4 rounded-lg border border-green-200">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Average Score / Média</label>
                            <div class="flex items-center space-x-3">
                                <span class="text-4xl font-bold {{ $average_score >= 4 ? 'text-green-600' : ($average_score >= 3 ? 'text-yellow-600' : ($average_score >= 2 ? 'text-orange-600' : 'text-red-600')) }}">
                                    {{ $average_score ? number_format($average_score, 2) : '-' }}
                                </span>
                                <span class="text-gray-500">/ 5.00</span>
                            </div>
                        </div>

                        {{-- Performance Level --}}
                        <div class="bg-white p-4 rounded-lg border border-green-200">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Performance Level / Nível</label>
                            <div class="space-y-1">
                                @foreach($performanceLevels as $key => $level)
                                    <label class="flex items-center space-x-2 cursor-pointer">
                                        <input type="radio" wire:model="performance_level" value="{{ $key }}"
                                               class="text-indigo-600 focus:ring-indigo-500"
                                               {{ $performance_level === $key ? 'checked' : '' }}>
                                        <span class="text-sm {{ $performance_level === $key ? 'font-bold text-indigo-600' : 'text-gray-700' }}">
                                            {{ $level }}
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        {{-- Eligible for Bonus --}}
                        <div class="bg-white p-4 rounded-lg border border-green-200">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Eligible for Bonus / Elegível para Bónus</label>
                            <div class="flex items-center space-x-4 mt-2">
                                <label class="flex items-center space-x-2 cursor-pointer">
                                    <input type="radio" wire:model="eligible_for_bonus" value="1"
                                           class="text-green-600 focus:ring-green-500">
                                    <span class="text-sm font-medium text-green-700">Sim</span>
                                </label>
                                <label class="flex items-center space-x-2 cursor-pointer">
                                    <input type="radio" wire:model="eligible_for_bonus" value="0"
                                           class="text-red-600 focus:ring-red-500">
                                    <span class="text-sm font-medium text-gray-700">Não</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Section 4 & 5: Comments --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {{-- Supervisor Comments --}}
                    <div class="bg-yellow-50 p-4 rounded-lg">
                        <h3 class="text-lg font-semibold text-yellow-800 mb-3 flex items-center">
                            <span class="bg-yellow-600 text-white rounded-full w-6 h-6 flex items-center justify-center text-sm mr-2">4</span>
                            Supervisor's Comments
                        </h3>
                        <textarea wire:model="supervisor_comments" rows="4" 
                                  class="w-full px-3 py-2 border border-yellow-300 rounded-lg focus:ring-2 focus:ring-yellow-500"
                                  placeholder="Summarize key strengths, areas for improvement, and recommendations..."></textarea>
                    </div>

                    {{-- Employee Comments --}}
                    <div class="bg-blue-50 p-4 rounded-lg">
                        <h3 class="text-lg font-semibold text-blue-800 mb-3 flex items-center">
                            <span class="bg-blue-600 text-white rounded-full w-6 h-6 flex items-center justify-center text-sm mr-2">5</span>
                            Employee's Comments
                        </h3>
                        <textarea wire:model="employee_comments" rows="4" 
                                  class="w-full px-3 py-2 border border-blue-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                  placeholder="Employee can share feedback or concerns..."></textarea>
                    </div>
                </div>

                {{-- Status --}}
                <div class="bg-gray-50 p-4 rounded-lg">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status da Avaliação</label>
                    <select wire:model="status" class="w-full md:w-1/3 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                        @foreach($statuses as $key => $statusName)
                            <option value="{{ $key }}">{{ $statusName }}</option>
                        @endforeach
                    </select>
                </div>

            </form>
        </div>

        {{-- Footer --}}
        <div class="flex-shrink-0 bg-gray-50 px-6 py-4 flex justify-between items-center border-t border-gray-200">
            <div class="text-sm text-gray-500">
                @if($average_score)
                    <span class="font-medium">Média: {{ number_format($average_score, 2) }}</span>
                    <span class="mx-2">|</span>
                    <span>Nível: {{ $performanceLevels[$performance_level] ?? 'N/A' }}</span>
                @endif
            </div>
            <div class="flex space-x-3">
                <button type="button" wire:click="closeModal" 
                        class="px-6 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors">
                    Cancelar
                </button>
                <button wire:click="save" 
                        class="px-6 py-2 rounded-lg text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 transition-colors flex items-center">
                    <span wire:loading wire:target="save"><i class="fas fa-spinner fa-spin mr-2"></i></span>
                    <span wire:loading.remove wire:target="save"><i class="fas fa-save mr-2"></i></span>
                    {{ $isEditing ? 'Atualizar' : 'Salvar' }}
                </button>
            </div>
        </div>
    </div>
</div>
@endif
