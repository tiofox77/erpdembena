<div>
    <div class="p-4 bg-white rounded-lg shadow-md">
        <!-- Tabs Selector apenas com Livewire -->
        <div>
            <div class="border-b border-gray-200 mb-4">
                <ul class="flex flex-wrap -mb-px">
                    <li class="mr-2">
                        <button wire:click="setActiveTab('shifts')" 
                            class="inline-block py-2 px-4 border-b-2 font-medium text-sm {{ $activeTab === 'shifts' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                            <i class="fas fa-clock mr-2"></i> Shifts
                        </button>
                    </li>
                    <li class="mr-2">
                        <button wire:click="setActiveTab('assignments')" 
                            class="inline-block py-2 px-4 border-b-2 font-medium text-sm {{ $activeTab === 'assignments' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                            <i class="fas fa-user-clock mr-2"></i> Assignments
                        </button>
                    </li>
                </ul>
            </div>

            <!-- Conteúdo da tab Shifts -->
            <div class="{{ $activeTab === 'shifts' ? 'block' : 'hidden' }} mt-4">
                <div class="flex flex-col md:flex-row justify-between items-center mb-4 space-y-2 md:space-y-0">
                    <div class="flex items-center space-x-2 w-full md:w-auto">
                        <input type="text" wire:model.debounce.300ms="searchShift" placeholder="Search shifts..." 
                            class="px-3 py-2 placeholder-gray-500 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        
                        <select wire:model="filters.is_active" class="block pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
                            <option value="">All Status</option>
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                        
                        <button wire:click="resetFilters" class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <i class="fas fa-sync-alt mr-2"></i> Reset
                        </button>
                    </div>
                    
                    <div class="flex space-x-2 w-full md:w-auto justify-end">
                        <button wire:click="exportShiftsPDF" class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <i class="fas fa-file-pdf mr-2 text-red-500"></i> Export PDF
                        </button>
                        
                        <button wire:click="createShift" class="inline-flex items-center px-3 py-2 border border-transparent shadow-sm text-sm leading-4 font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <i class="fas fa-plus mr-2"></i> Add Shift
                        </button>
                    </div>
                </div>
                
                <!-- Tabela de Turnos -->
                <div class="overflow-x-auto bg-white rounded-lg border">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sortByShift('name')">
                                    Name
                                    @if($sortFieldShift === 'name')
                                        <i class="fas fa-sort-{{ $sortDirectionShift === 'asc' ? 'up' : 'down' }} ml-1"></i>
                                    @endif
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sortByShift('start_time')">
                                    Start Time
                                    @if($sortFieldShift === 'start_time')
                                        <i class="fas fa-sort-{{ $sortDirectionShift === 'asc' ? 'up' : 'down' }} ml-1"></i>
                                    @endif
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sortByShift('end_time')">
                                    End Time
                                    @if($sortFieldShift === 'end_time')
                                        <i class="fas fa-sort-{{ $sortDirectionShift === 'asc' ? 'up' : 'down' }} ml-1"></i>
                                    @endif
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Interval
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Type
                                </th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($shifts as $shift)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $shift->name }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-500">{{ $shift->start_time->format('H:i') }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-500">{{ $shift->end_time->format('H:i') }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-500">{{ $shift->break_duration ?? 0 }} min</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $shift->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                            {{ $shift->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $shift->is_night_shift ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800' }}">
                                            {{ $shift->is_night_shift ? 'Night Shift' : 'Day Shift' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <button wire:click="editShift({{ $shift->id }})" class="text-indigo-600 hover:text-indigo-900 mr-3">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button wire:click="confirmDeleteShift({{ $shift->id }})" class="text-red-600 hover:text-red-900">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                                        No shifts found
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                <!-- Paginação -->
                <div class="mt-4">
                    {{ $shifts->links() }}
                </div>
            </div>

            <!-- Conteúdo da tab Atribuições -->
            <div class="{{ $activeTab === 'assignments' ? 'block' : 'hidden' }} mt-4">
                <!-- Cabeçalho da Secção -->
                <div class="bg-white rounded-lg shadow-md overflow-hidden border border-gray-200">
                    <div class="flex items-center justify-between bg-gradient-to-r from-blue-600 to-blue-700 px-4 py-3">
                        <h3 class="text-lg font-medium text-white flex items-center">
                            <i class="fas fa-user-clock mr-2 animate-pulse"></i>
                            Atribuições de Turno
                        </h3>
                        <div class="flex space-x-2">
                            <button wire:click="exportAssignmentsPDF" class="inline-flex items-center px-3 py-2 border border-white shadow-sm text-sm leading-4 font-medium rounded-md text-white bg-transparent hover:bg-white hover:bg-opacity-10 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-white transition-all duration-200 ease-in-out transform hover:scale-105">
                                <i class="fas fa-file-pdf mr-2"></i> Exportar PDF
                            </button>
                            
                            <button wire:click="createAssignment" class="inline-flex items-center px-3 py-2 border border-white shadow-sm text-sm leading-4 font-medium rounded-md text-blue-700 bg-white hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-white transition-all duration-200 ease-in-out transform hover:scale-105">
                                <i class="fas fa-plus-circle mr-2 text-blue-600"></i> Nova Atribuição
                            </button>
                        </div>
                    </div>
                    
                    <!-- Filtros com Estilo Consistente -->
                    <div class="p-4 bg-gray-50 border-b border-gray-200">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <!-- Campo de pesquisa -->
                            <div class="md:col-span-2">
                                <div class="relative">
                                    <input wire:model.debounce.300ms="searchAssignment" type="text" placeholder="Pesquisar por nome do funcionário..." 
                                        class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                                    <div class="absolute left-3 top-2 text-gray-400">
                                        <i class="fas fa-search"></i>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Departamento -->
                            <div>
                                <select wire:model="filters.department_id" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                                    <option value="">Todos os Departamentos</option>
                                    @foreach($departments as $department)
                                        <option value="{{ $department->id }}">{{ $department->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <!-- Turno -->
                            <div>
                                <select wire:model="filters.shift_id" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                                    <option value="">Todos os Turnos</option>
                                    @foreach($shiftsForSelect as $shiftOption)
                                        <option value="{{ $shiftOption->id }}">{{ $shiftOption->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        
                        <!-- Segunda linha de filtros -->
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-4">
                            <!-- Registros por página -->
                            <div>
                                <select wire:model="perPage" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                                    <option value="10">10 por página</option>
                                    <option value="25">25 por página</option>
                                    <option value="50">50 por página</option>
                                </select>
                            </div>
                            
                            <!-- Botão Reset -->
                            <div>
                                <button wire:click="resetFilters" class="w-full inline-flex justify-center items-center px-3 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 ease-in-out transform hover:scale-105">
                                    <i class="fas fa-sync-alt mr-2"></i>
                                    Redefinir Filtros
                                </button>
                            </div>
                        </div>
                    </div>
                
                <!-- Tabela de Atribuições -->
                <div class="overflow-hidden bg-white rounded-lg shadow-sm">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr class="bg-gray-50">
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer transition-all duration-200 hover:bg-gray-100" wire:click="sortByAssignment('employee_id')">
                                    <div class="flex items-center">
                                        <i class="fas fa-user text-blue-500 mr-2"></i>
                                        Funcionário
                                        @if($sortFieldAssignment === 'employee_id')
                                            <i class="fas fa-sort-{{ $sortDirectionAssignment === 'asc' ? 'up' : 'down' }} ml-2 text-blue-600"></i>
                                        @endif
                                    </div>
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <div class="flex items-center">
                                        <i class="fas fa-building text-blue-500 mr-2"></i>
                                        Departamento
                                    </div>
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer transition-all duration-200 hover:bg-gray-100" wire:click="sortByAssignment('shift_id')">
                                    <div class="flex items-center">
                                        <i class="fas fa-clock text-blue-500 mr-2"></i>
                                        Turno
                                        @if($sortFieldAssignment === 'shift_id')
                                            <i class="fas fa-sort-{{ $sortDirectionAssignment === 'asc' ? 'up' : 'down' }} ml-2 text-blue-600"></i>
                                        @endif
                                    </div>
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer transition-all duration-200 hover:bg-gray-100" wire:click="sortByAssignment('start_date')">
                                    <div class="flex items-center">
                                        <i class="fas fa-calendar-plus text-blue-500 mr-2"></i>
                                        Data Início
                                        @if($sortFieldAssignment === 'start_date')
                                            <i class="fas fa-sort-{{ $sortDirectionAssignment === 'asc' ? 'up' : 'down' }} ml-2 text-blue-600"></i>
                                        @endif
                                    </div>
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer transition-all duration-200 hover:bg-gray-100" wire:click="sortByAssignment('end_date')">
                                    <div class="flex items-center">
                                        <i class="fas fa-calendar-minus text-blue-500 mr-2"></i>
                                        Data Fim
                                        @if($sortFieldAssignment === 'end_date')
                                            <i class="fas fa-sort-{{ $sortDirectionAssignment === 'asc' ? 'up' : 'down' }} ml-2 text-blue-600"></i>
                                        @endif
                                    </div>
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <div class="flex items-center">
                                        <i class="fas fa-tag text-blue-500 mr-2"></i>
                                        Tipo
                                    </div>
                                </th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <div class="flex items-center justify-end">
                                        <i class="fas fa-cogs text-blue-500 mr-2"></i>
                                        Ações
                                    </div>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($shiftAssignments as $assignment)
                                <tr class="hover:bg-gray-50 transition-colors duration-200">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center">
                                                <i class="fas fa-user text-blue-500"></i>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">{{ $assignment->employee->full_name }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-500">{{ $assignment->employee->department->name ?? 'N/A' }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900 font-medium">{{ $assignment->shift->name }}</div>
                                        <div class="text-xs text-gray-500">{{ $assignment->shift->start_time }} - {{ $assignment->shift->end_time }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $assignment->start_date->format('d/m/Y') }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $assignment->end_date ? $assignment->end_date->format('d/m/Y') : '—' }}</div>
                                        @if(!$assignment->end_date)
                                            <div class="text-xs text-gray-500">Contínuo</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $assignment->is_permanent ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800' }}">
                                            {{ $assignment->is_permanent ? 'Permanente' : 'Temporário' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <button wire:click="editAssignment({{ $assignment->id }})" class="text-indigo-600 hover:text-indigo-900 hover:scale-110 transform transition-all duration-200 mr-3" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button wire:click="confirmDeleteAssignment({{ $assignment->id }})" class="text-red-600 hover:text-red-900 hover:scale-110 transform transition-all duration-200" title="Excluir">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                                        <div class="flex flex-col items-center justify-center">
                                            <i class="fas fa-calendar-times text-gray-300 text-4xl mb-3"></i>
                                            <span class="text-sm">Nenhuma atribuição de turno encontrada</span>
                                            <button wire:click="createAssignment" class="mt-3 inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 ease-in-out transform hover:scale-105">
                                                <i class="fas fa-plus-circle mr-1"></i> Nova Atribuição
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                <!-- Paginação -->
                <div class="mt-4 bg-white p-3 rounded-lg shadow-sm border border-gray-200">
                    {{ $shiftAssignments->links() }}
                </div>
            </div>

            <!-- Assignment Modal (Padrão ERPDEMBENA) -->
            <div x-data="{ open: @entangle('showAssignmentModal') }" 
                 x-show="open" 
                 x-cloak 
                 class="fixed inset-0 bg-gray-600 bg-opacity-75 overflow-y-auto h-full w-full z-50" 
                 role="dialog" 
                 aria-modal="true"
                 x-transition:enter="transition ease-out duration-300" 
                 x-transition:enter-start="opacity-0" 
                 x-transition:enter-end="opacity-100" 
                 x-transition:leave="transition ease-in duration-200" 
                 x-transition:leave-start="opacity-100" 
                 x-transition:leave-end="opacity-0">
                <div class="relative top-20 mx-auto p-1 w-full max-w-4xl">
                    <div class="relative bg-white rounded-lg shadow-xl transform transition-all duration-300 ease-in-out" 
                         x-transition:enter="transition ease-out duration-300" 
                         x-transition:enter-start="transform opacity-0 scale-95" 
                         x-transition:enter-end="transform opacity-100 scale-100" 
                         x-transition:leave="transition ease-in duration-200" 
                         x-transition:leave-start="transform opacity-100 scale-100" 
                         x-transition:leave-end="transform opacity-0 scale-95">
                        
                        <!-- Cabeçalho com gradiente -->
                        <div class="bg-gradient-to-r from-blue-600 to-blue-700 rounded-t-lg px-4 py-3 flex justify-between items-center">
                            <h3 class="text-lg font-medium text-white flex items-center">
                                <i class="fas {{ $isEditing ? 'fa-edit' : 'fa-plus-circle' }} mr-2 animate-pulse"></i>
                                {{ $isEditing ? 'Editar Atribuição de Turno' : 'Nova Atribuição de Turno' }}
                            </h3>
                            <button type="button" wire:click="closeAssignmentModal" class="text-white hover:text-gray-200 focus:outline-none transition-all duration-200 ease-in-out transform hover:scale-110 hover:rotate-90">
                                <i class="fas fa-times text-xl"></i>
                            </button>
                        </div>
                        
                        <!-- Corpo da modal -->
                        <div class="p-6 space-y-6">
                            <!-- Informações do funcionário e turno -->
                            <div class="bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden">
                                <div class="flex items-center bg-gradient-to-r from-blue-50 to-blue-100 px-4 py-3 border-b border-gray-200">
                                    <i class="fas fa-user-clock text-blue-600 mr-2"></i>
                                    <h3 class="text-base font-medium text-gray-700">Detalhes da Atribuição</h3>
                                </div>
                                <div class="p-4 space-y-4">
                                    <!-- Funcionário -->
                                    <div>
                                        <label for="employee_id" class="block text-sm font-medium text-gray-700">
                                            <i class="fas fa-user mr-1 text-gray-500"></i> Funcionário
                                        </label>
                                        <select wire:model.defer="employee_id" id="employee_id" 
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50 sm:text-sm bg-white">
                                            <option value="">Selecionar Funcionário</option>
                                            @foreach($employees as $employee)
                                                <option value="{{ $employee->id }}">{{ $employee->full_name }}</option>
                                            @endforeach
                                        </select>
                                        @error('employee_id') <div class="mt-1 text-red-600 text-sm flex items-center"><i class="fas fa-exclamation-circle mr-1"></i> {{ $message }}</div> @enderror
                                    </div>
                                    
                                    <!-- Turno -->
                                    <div>
                                        <label for="shift_id_assignment" class="block text-sm font-medium text-gray-700">
                                            <i class="fas fa-clock mr-1 text-gray-500"></i> Turno
                                        </label>
                                        <select wire:model.defer="shift_id_assignment" id="shift_id_assignment" 
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50 sm:text-sm bg-white">
                                            <option value="">Selecionar Turno</option>
                                            @foreach($shiftsForSelect as $shift)
                                                <option value="{{ $shift->id }}">{{ $shift->name }} ({{ $shift->start_time }} - {{ $shift->end_time }})</option>
                                            @endforeach
                                        </select>
                                        @error('shift_id_assignment') <div class="mt-1 text-red-600 text-sm flex items-center"><i class="fas fa-exclamation-circle mr-1"></i> {{ $message }}</div> @enderror
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Datas -->
                            <div class="bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden">
                                <div class="flex items-center bg-gradient-to-r from-green-50 to-green-100 px-4 py-3 border-b border-gray-200">
                                    <i class="fas fa-calendar text-green-600 mr-2"></i>
                                    <h3 class="text-base font-medium text-gray-700">Período</h3>
                                </div>
                                <div class="p-4">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <!-- Data de início -->
                                        <div>
                                            <label for="start_date" class="block text-sm font-medium text-gray-700">
                                                <i class="fas fa-calendar-plus mr-1 text-gray-500"></i> Data de Início
                                            </label>
                                            <input type="date" wire:model.defer="start_date" id="start_date" 
                                                placeholder="Selecione a data de início"
                                                class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md bg-white">
                                            @error('start_date') <div class="mt-1 text-red-600 text-sm flex items-center"><i class="fas fa-exclamation-circle mr-1"></i> {{ $message }}</div> @enderror
                                        </div>
                                        
                                        <!-- Data de término -->
                                        <div>
                                            <label for="end_date" class="block text-sm font-medium text-gray-700">
                                                <i class="fas fa-calendar-minus mr-1 text-gray-500"></i> Data de Término
                                            </label>
                                            <input type="date" wire:model.defer="end_date" id="end_date" 
                                                placeholder="Selecione a data de término"
                                                class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md bg-white">
                                            @error('end_date') <div class="mt-1 text-red-600 text-sm flex items-center"><i class="fas fa-exclamation-circle mr-1"></i> {{ $message }}</div> @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Configurações adicionais -->
                            <div class="bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden">
                                <div class="flex items-center bg-gradient-to-r from-purple-50 to-purple-100 px-4 py-3 border-b border-gray-200">
                                    <i class="fas fa-cogs text-purple-600 mr-2"></i>
                                    <h3 class="text-base font-medium text-gray-700">Configurações Adicionais</h3>
                                </div>
                                <div class="p-4 space-y-4">
                                    <!-- Permanente -->
                                    <div class="flex items-center">
                                        <div>
                                            <label for="is_permanent" class="flex items-center cursor-pointer">
                                                <div class="relative">
                                                    <!-- Input escondido -->
                                                    <input type="checkbox" wire:model.defer="is_permanent" id="is_permanent" class="sr-only">
                                                    <!-- Track (fundo do toggle) -->
                                                    <div class="w-12 h-6 bg-gray-300 rounded-full shadow-inner transition-all duration-300 ease-in-out"></div>
                                                    <!-- Dot (bolinha do toggle) -->
                                                    <div class="dot absolute w-6 h-6 bg-white rounded-full shadow left-0 top-0 transition-transform duration-300 ease-in-out transform" 
                                                        :class="{'translate-x-6 bg-green-500': $wire.is_permanent, 'bg-white': !$wire.is_permanent}"></div>
                                                </div>
                                            </label>
                                        </div>
                                        <div class="ml-3">
                                            <label for="is_permanent" class="text-sm font-medium text-gray-700 cursor-pointer">
                                                Atribuição Permanente
                                            </label>
                                            <p class="text-xs text-gray-500 mt-1">
                                                Marque esta opção se a atribuição não tem data de término prevista
                                            </p>
                                        </div>
                                    </div>
                                    
                                    <!-- Padrão de Rotação -->
                                    <div>
                                        <label for="rotation_pattern" class="block text-sm font-medium text-gray-700">
                                            <i class="fas fa-sync-alt mr-1 text-gray-500"></i> Padrão de Rotação
                                        </label>
                                        <select wire:model.defer="rotation_pattern" id="rotation_pattern" 
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50 sm:text-sm bg-white">
                                            <option value="">Selecionar Padrão de Rotação</option>
                                            <option value="daily">Diário</option>
                                            <option value="weekly">Semanal</option>
                                            <option value="biweekly">Quinzenal</option>
                                            <option value="monthly">Mensal</option>
                                            <option value="none">Nenhum</option>
                                        </select>
                                        @error('rotation_pattern') <div class="mt-1 text-red-600 text-sm flex items-center"><i class="fas fa-exclamation-circle mr-1"></i> {{ $message }}</div> @enderror
                                    </div>
                                    
                                    <!-- Notas -->
                                    <div>
                                        <label for="notes" class="block text-sm font-medium text-gray-700">
                                            <i class="fas fa-sticky-note mr-1 text-gray-500"></i> Notas
                                        </label>
                                        <textarea wire:model.defer="notes" id="notes" rows="3" 
                                            placeholder="Adicione observações sobre esta atribuição de turno"
                                            class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md bg-white"></textarea>
                                        @error('notes') <div class="mt-1 text-red-600 text-sm flex items-center"><i class="fas fa-exclamation-circle mr-1"></i> {{ $message }}</div> @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Botões de ação com estado de Loading e Animações -->
                        <div class="bg-gray-50 px-4 py-3 rounded-b-lg flex justify-end space-x-3 border-t border-gray-200">
                            <button type="button" wire:click="closeAssignmentModal" class="inline-flex justify-center items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 ease-in-out transform hover:scale-105">
                                <i class="fas fa-times mr-2"></i>
                                Cancelar
                            </button>
                            <button type="button" wire:click="saveAssignment" wire:loading.attr="disabled" class="inline-flex justify-center items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 ease-in-out transform hover:scale-105 disabled:opacity-75 disabled:cursor-not-allowed">
                                <span wire:loading.remove wire:target="saveAssignment">
                                    <i class="fas {{ $isEditing ? 'fa-save' : 'fa-plus-circle' }} mr-2"></i>
                                    {{ $isEditing ? 'Atualizar' : 'Guardar' }}
                                </span>
                                <span wire:loading wire:target="saveAssignment" class="inline-flex items-center">
                                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    Processando...
                                </span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Shift Modal -->
            @if($showShiftModal)
            <div class="fixed inset-0 overflow-y-auto z-50" role="dialog">
                <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity ease-out duration-300"></div>
                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen"></span>
                    <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6 opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95
                        ease-out duration-300 transform transition-opacity transition-transform
                        @if($showShiftModal) opacity-100 translate-y-0 sm:scale-100 @endif">
                        
                        <div class="absolute top-0 right-0 pt-4 pr-4">
                            <button wire:click="closeShiftModal" type="button" class="bg-white rounded-md text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                <span class="sr-only">Close</span>
                                <i class="fas fa-times text-xl"></i>
                            </button>
                        </div>
                        
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 sm:mx-0 sm:h-10 sm:w-10">
                                <i class="fas fa-clock text-blue-600"></i>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                <h3 class="text-lg leading-6 font-medium text-gray-900">
                                    {{ $isEditing ? 'Edit Shift' : 'Add New Shift' }}
                                </h3>
                                
                                <div class="mt-4 space-y-4">
                                    <div>
                                        <label for="name" class="block text-sm font-medium text-gray-700">
                                            Name
                                        </label>
                                        <input type="text" wire:model.lazy="name" id="name" 
                                            class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                                            placeholder="Enter category name">
                                        @error('name') <div class="mt-1 text-red-600 text-sm flex items-center"><i class="fas fa-exclamation-circle mr-1"></i> {{ $message }}</div> @enderror
                                    </div>
                                    
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label for="start_time" class="block text-sm font-medium text-gray-700">
                                                Start Time
                                            </label>
                                            <input type="time" wire:model.lazy="start_time" id="start_time" 
                                                class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                            @error('start_time') <div class="mt-1 text-red-600 text-sm flex items-center"><i class="fas fa-exclamation-circle mr-1"></i> {{ $message }}</div> @enderror
                                        </div>
                                        
                                        <div>
                                            <label for="end_time" class="block text-sm font-medium text-gray-700">
                                                End Time
                                            </label>
                                            <input type="time" wire:model.lazy="end_time" id="end_time" 
                                                class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                            @error('end_time') <div class="mt-1 text-red-600 text-sm flex items-center"><i class="fas fa-exclamation-circle mr-1"></i> {{ $message }}</div> @enderror
                                        </div>
                                    </div>
                                    
                                    <div>
                                        <label for="break_duration" class="block text-sm font-medium text-gray-700">
                                            Break Duration (minutes)
                                        </label>
                                        <input type="number" wire:model.lazy="break_duration" id="break_duration" 
                                            class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                                            placeholder="Enter break duration in minutes">
                                        @error('break_duration') <div class="mt-1 text-red-600 text-sm flex items-center"><i class="fas fa-exclamation-circle mr-1"></i> {{ $message }}</div> @enderror
                                    </div>
                                    
                                    <div>
                                        <label for="description" class="block text-sm font-medium text-gray-700">
                                            Description
                                        </label>
                                        <textarea wire:model.lazy="description" id="description" rows="3" 
                                            class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                                            placeholder="Enter category description"></textarea>
                                        @error('description') <div class="mt-1 text-red-600 text-sm flex items-center"><i class="fas fa-exclamation-circle mr-1"></i> {{ $message }}</div> @enderror
                                    </div>
                                    
                                    <div class="flex items-center space-x-6">
                                        <div class="flex items-center">
                                            <input type="checkbox" wire:model.lazy="is_night_shift" id="is_night_shift" 
                                                class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded">
                                            <label for="is_night_shift" class="ml-2 block text-sm text-gray-700">
                                                Night Shift
                                            </label>
                                        </div>
                                        
                                        <div class="flex items-center">
                                            <input type="checkbox" wire:model.lazy="is_active" id="is_active" 
                                                class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded">
                                            <label for="is_active" class="ml-2 block text-sm text-gray-700">
                                                Active
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                            <button wire:click="saveShift" type="button" 
                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm transition-colors duration-200">
                                <i class="fas fa-save mr-2"></i> {{ $isEditing ? 'Update' : 'Save' }}
                            </button>
                            <button wire:click="closeShiftModal" type="button" 
                                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm transition-colors duration-200">
                                <i class="fas fa-times mr-2"></i> Cancel
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Delete Confirmation Modal -->
            @if($showDeleteModal)
            <div class="fixed inset-0 overflow-y-auto z-50" role="dialog">
                <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity ease-out duration-300"></div>
                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen"></span>
                    <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6 opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95
                        ease-out duration-300 transform transition-opacity transition-transform
                        @if($showDeleteModal) opacity-100 translate-y-0 sm:scale-100 @endif">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                <i class="fas fa-exclamation-triangle text-red-600"></i>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                <h3 class="text-lg leading-6 font-medium text-gray-900">
                                    Confirm Deletion
                                </h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500">
                                        Are you sure you want to delete this item? This action cannot be undone.
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                            <button type="button" wire:click="delete" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm transition-colors duration-200">
                                <i class="fas fa-trash mr-2"></i> Delete
                            </button>
                            <button type="button" wire:click="closeDeleteModal" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm transition-colors duration-200">
                                <i class="fas fa-times mr-2"></i> Cancel
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
