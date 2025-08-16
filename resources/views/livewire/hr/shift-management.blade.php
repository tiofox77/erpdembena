<div>
    <div class="p-4 bg-white rounded-lg shadow-md">
        <!-- Tabs Selector apenas com Livewire -->
        <div>
            <div class="border-b border-gray-200 mb-4">
                <ul class="flex flex-wrap -mb-px">
                    <li class="mr-2">
                        <button wire:click="setActiveTab('shifts')" 
                            class="inline-block py-2 px-4 border-b-2 font-medium text-sm {{ $activeTab === 'shifts' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} transition-all duration-200">
                            <i class="fas fa-clock mr-2"></i> {{ __('shifts.shifts') }}
                        </button>
                    </li>
                    <li class="mr-2">
                        <button wire:click="setActiveTab('assignments')" 
                            class="inline-block py-2 px-4 border-b-2 font-medium text-sm {{ $activeTab === 'assignments' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} transition-all duration-200">
                            <i class="fas fa-user-clock mr-2"></i> {{ __('shifts.assignments') }}
                        </button>
                    </li>
                </ul>
            </div>

            <!-- Conteúdo da tab Shifts -->
            <div class="{{ $activeTab === 'shifts' ? 'block' : 'hidden' }} mt-4">
                <!-- Cabeçalho da Secção -->
                <div class="bg-white rounded-lg shadow-md overflow-hidden border border-gray-200">
                    <div class="flex items-center justify-between bg-gradient-to-r from-blue-600 to-blue-700 px-4 py-3">
                        <h3 class="text-lg font-medium text-white flex items-center">
                            <i class="fas fa-clock mr-2 animate-pulse"></i>
                            {{ __('shifts.manage_shifts') }}
                        </h3>
                        <div class="flex items-center space-x-2">
                            <button wire:click="exportShiftsPDF" class="inline-flex items-center px-3 py-2 bg-white bg-opacity-20 hover:bg-opacity-30 text-white text-sm font-medium rounded-md transition-all duration-200 transform hover:scale-105">
                                <i class="fas fa-file-pdf mr-2"></i>
                                {{ __('shifts.export_pdf') }}
                            </button>
                            <button wire:click="createShift" class="inline-flex items-center px-3 py-2 bg-green-500 hover:bg-green-600 text-white text-sm font-medium rounded-md transition-all duration-200 transform hover:scale-105">
                                <i class="fas fa-plus mr-2"></i>
                                {{ __('shifts.add_shift') }}
                            </button>
                        </div>
                    </div>
                    
                    <!-- Filtros -->
                    <div class="p-4 bg-gray-50 border-b border-gray-200">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <!-- Campo de pesquisa -->
                            <div class="md:col-span-2">
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-search text-gray-400"></i>
                                    </div>
                                    <input type="text" wire:model.debounce.300ms="searchShift" 
                                           placeholder="{{ __('shifts.search_shifts_placeholder') }}" 
                                           class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-all duration-200">
                                </div>
                            </div>
                            
                            <!-- Filtro de Status -->
                            <div>
                                <select wire:model="filters.is_active" class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md transition-all duration-200">
                                    <option value="">{{ __('shifts.all_status') }}</option>
                                    <option value="1">{{ __('shifts.active') }}</option>
                                    <option value="0">{{ __('shifts.inactive') }}</option>
                                </select>
                            </div>
                            
                            <!-- Botão Reset -->
                            <div>
                                <button wire:click="resetFilters" class="w-full inline-flex items-center justify-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200">
                                    <i class="fas fa-sync-alt mr-2"></i> 
                                    {{ __('shifts.reset_filters') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Tabela de Turnos -->
                <div class="overflow-hidden bg-white rounded-lg shadow-sm mt-4">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr class="bg-gray-50">
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer transition-all duration-200 hover:bg-gray-100" wire:click="sortByShift('name')">
                                    <div class="flex items-center">
                                        <i class="fas fa-clock text-blue-500 mr-2"></i>
                                        {{ __('shifts.name') }}
                                        @if($sortFieldShift === 'name')
                                            <i class="fas fa-sort-{{ $sortDirectionShift === 'asc' ? 'up' : 'down' }} ml-2 text-blue-600"></i>
                                        @endif
                                    </div>
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer transition-all duration-200 hover:bg-gray-100" wire:click="sortByShift('start_time')">
                                    <div class="flex items-center">
                                        <i class="fas fa-play text-blue-500 mr-2"></i>
                                        {{ __('shifts.start_time') }}
                                        @if($sortFieldShift === 'start_time')
                                            <i class="fas fa-sort-{{ $sortDirectionShift === 'asc' ? 'up' : 'down' }} ml-2 text-blue-600"></i>
                                        @endif
                                    </div>
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer transition-all duration-200 hover:bg-gray-100" wire:click="sortByShift('end_time')">
                                    <div class="flex items-center">
                                        <i class="fas fa-stop text-blue-500 mr-2"></i>
                                        {{ __('shifts.end_time') }}
                                        @if($sortFieldShift === 'end_time')
                                            <i class="fas fa-sort-{{ $sortDirectionShift === 'asc' ? 'up' : 'down' }} ml-2 text-blue-600"></i>
                                        @endif
                                    </div>
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <div class="flex items-center">
                                        <i class="fas fa-pause text-blue-500 mr-2"></i>
                                        {{ __('shifts.interval') }}
                                    </div>
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <div class="flex items-center">
                                        <i class="fas fa-info-circle text-blue-500 mr-2"></i>
                                        {{ __('shifts.status') }}
                                    </div>
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <div class="flex items-center">
                                        <i class="fas fa-tag text-blue-500 mr-2"></i>
                                        {{ __('shifts.type') }}
                                    </div>
                                </th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <div class="flex items-center justify-end">
                                        <i class="fas fa-cogs text-blue-500 mr-2"></i>
                                        {{ __('shifts.actions') }}
                                    </div>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($shifts as $shift)
                                <tr class="hover:bg-gray-50 transition-colors duration-200">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10 rounded-full bg-gradient-to-r {{ $shift->is_night_shift ? 'from-indigo-500 to-indigo-600' : 'from-blue-500 to-blue-600' }} flex items-center justify-center">
                                                <i class="fas {{ $shift->is_night_shift ? 'fa-moon' : 'fa-sun' }} text-white text-sm"></i>
                                            </div>
                                            <div class="ml-3">
                                                <div class="text-sm font-medium text-gray-900">{{ $shift->name }}</div>
                                                <div class="text-sm text-gray-500">
                                                    <i class="fas fa-clock mr-1"></i>{{ $shift->start_time->format('H:i') }} - {{ $shift->end_time->format('H:i') }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <i class="fas fa-play mr-2"></i>
                                            <span class="font-semibold">{{ $shift->start_time->format('H:i') }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            <i class="fas fa-stop mr-2"></i>
                                            <span class="font-semibold">{{ $shift->end_time->format('H:i') }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                            <i class="fas fa-pause mr-2"></i>
                                            <span class="font-semibold">{{ $shift->break_duration ?? 0 }}</span>
                                            <span class="ml-1 text-gray-600">min</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium {{ $shift->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                            <i class="fas {{ $shift->is_active ? 'fa-check-circle' : 'fa-times-circle' }} mr-2"></i>
                                            <span class="font-semibold">{{ $shift->is_active ? __('shifts.active') : __('shifts.inactive') }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium {{ $shift->is_night_shift ? 'bg-indigo-100 text-indigo-800' : 'bg-blue-100 text-blue-800' }}">
                                            <i class="fas {{ $shift->is_night_shift ? 'fa-moon' : 'fa-sun' }} mr-2"></i>
                                            <span class="font-semibold">{{ $shift->is_night_shift ? __('shifts.night_shift') : __('shifts.day_shift') }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex items-center justify-end space-x-2">
                                            <button wire:click="editShift({{ $shift->id }})" class="inline-flex items-center px-3 py-1 bg-blue-100 hover:bg-blue-200 text-blue-700 text-xs font-medium rounded-md transition-all duration-200 transform hover:scale-105">
                                                <i class="fas fa-edit mr-1"></i>
                                                {{ __('shifts.edit') }}
                                            </button>
                                            <button wire:click="deleteShift({{ $shift->id }})" 
                                                    wire:confirm="Tem certeza que deseja excluir este turno?"
                                                    onclick="console.log('Delete shift button clicked for ID: {{ $shift->id }}');"
                                                    class="inline-flex items-center px-3 py-1 bg-red-100 hover:bg-red-200 text-red-700 text-xs font-medium rounded-md transition-all duration-200 transform hover:scale-105">
                                                <i class="fas fa-trash mr-1"></i>
                                                {{ __('shifts.delete') }}
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-12 text-center">
                                        <div class="flex flex-col items-center">
                                            <i class="fas fa-clock text-gray-400 text-4xl mb-4"></i>
                                            <p class="text-gray-500 text-lg font-medium">{{ __('shifts.no_shifts_found') }}</p>
                                            <p class="text-gray-400 text-sm mt-2">{{ __('shifts.create_first_shift') }}</p>
                                        </div>
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
                            {{ __('shifts.shift_assignments') }}
                        </h3>
                        <div class="flex space-x-2">
                            <button wire:click="exportAssignmentsPDF" class="inline-flex items-center px-3 py-2 border border-white shadow-sm text-sm leading-4 font-medium rounded-md text-white bg-transparent hover:bg-white hover:bg-opacity-10 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-white transition-all duration-200 ease-in-out transform hover:scale-105">
                                <i class="fas fa-file-pdf mr-2"></i> {{ __('shifts.export_pdf') }}
                            </button>
                            
                            <button wire:click="createAssignment" class="inline-flex items-center px-3 py-2 border border-white shadow-sm text-sm leading-4 font-medium rounded-md text-blue-700 bg-white hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-white transition-all duration-200 ease-in-out transform hover:scale-105">
                                <i class="fas fa-plus-circle mr-2 text-blue-600"></i> {{ __('shifts.add_assignment') }}
                            </button>
                        </div>
                    </div>
                    
                    <!-- Filtros com Estilo Consistente -->
                    <div class="p-4 bg-gray-50 border-b border-gray-200">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <!-- Campo de pesquisa -->
                            <div class="md:col-span-2">
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-search text-gray-400"></i>
                                    </div>
                                    <input wire:model.debounce.300ms="searchAssignment" type="text" 
                                           placeholder="{{ __('shifts.search_employee_placeholder') }}" 
                                           class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-all duration-200">
                                </div>
                            </div>
                            
                            <!-- Departamento -->
                            <div>
                                <select wire:model="filters.department_id" class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md transition-all duration-200">
                                    <option value="">{{ __('shifts.all_departments') }}</option>
                                    @foreach($departments as $department)
                                        <option value="{{ $department->id }}">{{ $department->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <!-- Turno -->
                            <div>
                                <select wire:model="filters.shift_id" class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md transition-all duration-200">
                                    <option value="">{{ __('shifts.all_shifts') }}</option>
                                    @foreach($shiftsForSelect as $shiftOption)
                                        <option value="{{ $shiftOption->id }}">{{ $shiftOption->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <!-- Botão Reset -->
                            <div>
                                <button wire:click="resetFilters" class="w-full inline-flex items-center justify-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200">
                                    <i class="fas fa-sync-alt mr-2"></i> 
                                    {{ __('shifts.reset_filters') }}
                                </button>
                            </div>
                        </div>
                        
                        <!-- Segunda linha de filtros -->
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-4">
                            <!-- Registros por página -->
                            <div>
                                <select wire:model="perPage" class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md transition-all duration-200">
                                    <option value="10">{{ __('shifts.records_per_page', ['count' => 10]) }}</option>
                                    <option value="25">{{ __('shifts.records_per_page', ['count' => 25]) }}</option>
                                    <option value="50">{{ __('shifts.records_per_page', ['count' => 50]) }}</option>
                                </select>
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
                                        {{ __('shifts.employee') }}
                                        @if($sortFieldAssignment === 'employee_id')
                                            <i class="fas fa-sort-{{ $sortDirectionAssignment === 'asc' ? 'up' : 'down' }} ml-2 text-blue-600"></i>
                                        @endif
                                    </div>
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <div class="flex items-center">
                                        <i class="fas fa-clock text-blue-500 mr-2"></i>
                                        {{ __('shifts.shifts_assigned') }}
                                    </div>
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer transition-all duration-200 hover:bg-gray-100" wire:click="sortByAssignment('start_date')">
                                    <div class="flex items-center">
                                        <i class="fas fa-calendar-plus text-blue-500 mr-2"></i>
                                        {{ __('shifts.start_date') }}
                                        @if($sortFieldAssignment === 'start_date')
                                            <i class="fas fa-sort-{{ $sortDirectionAssignment === 'asc' ? 'up' : 'down' }} ml-2 text-blue-600"></i>
                                        @endif
                                    </div>
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer transition-all duration-200 hover:bg-gray-100" wire:click="sortByAssignment('end_date')">
                                    <div class="flex items-center">
                                        <i class="fas fa-calendar-minus text-blue-500 mr-2"></i>
                                        {{ __('shifts.end_date') }}
                                        @if($sortFieldAssignment === 'end_date')
                                            <i class="fas fa-sort-{{ $sortDirectionAssignment === 'asc' ? 'up' : 'down' }} ml-2 text-blue-600"></i>
                                        @endif
                                    </div>
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <div class="flex items-center">
                                        <i class="fas fa-info-circle text-blue-500 mr-2"></i>
                                        {{ __('shifts.status') }}
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
                                            <div class="flex-shrink-0 h-10 w-10 rounded-full bg-gradient-to-r from-blue-500 to-blue-600 flex items-center justify-center">
                                                <i class="fas fa-user text-white text-sm"></i>
                                            </div>
                                            <div class="ml-3">
                                                <div class="text-sm font-medium text-gray-900">{{ $assignment->employee->full_name }}</div>
                                                <div class="text-sm text-gray-500">
                                                    @if($assignment->employee->department)
                                                        <i class="fas fa-building mr-1"></i>{{ $assignment->employee->department->name }}
                                                    @else
                                                        <i class="fas fa-building mr-1"></i>{{ __('shifts.no_department') }}
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex flex-wrap gap-2">
                                            @foreach($assignment->shifts as $shift)
                                                <div class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium 
                                                     {{ strpos(strtolower($shift['name']), 'noite') !== false || strpos(strtolower($shift['name']), 'night') !== false ? 'bg-indigo-100 text-indigo-800' : 
                                                        (strpos(strtolower($shift['name']), 'tarde') !== false || strpos(strtolower($shift['name']), 'afternoon') !== false ? 'bg-orange-100 text-orange-800' : 
                                                        'bg-blue-100 text-blue-800') }}">
                                                    <i class="fas fa-clock mr-1"></i>
                                                    <span class="font-semibold">{{ $shift['name'] }}</span>
                                                    <span class="ml-1 text-gray-600">({{ $shift['start_time'] }}-{{ $shift['end_time'] }})</span>
                                                </div>
                                            @endforeach
                                        </div>
                                        @if(count($assignment->shifts) > 1)
                                            <div class="mt-1 text-xs text-purple-600 font-medium">
                                                <i class="fas fa-sync-alt mr-1"></i>{{ __('shifts.rotation') }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $assignment->start_date->format('d/m/Y') }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $assignment->end_date ? $assignment->end_date->format('d/m/Y') : '—' }}</div>
                                        @if(!$assignment->end_date)
                                            <div class="text-xs text-gray-500">{{ __('shifts.continuous') }}</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $assignment->is_permanent ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800' }}">
                                            {{ $assignment->is_permanent ? __('shifts.permanent') : __('shifts.temporary') }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <button wire:click="editAssignment({{ $assignment->id }})" 
                                                class="text-blue-600 hover:text-blue-900 hover:scale-110 transform transition-all duration-200 mr-2" 
                                                title="{{ __('shifts.edit') }}">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button wire:click="deleteAssignment({{ $assignment->id }})" 
                                                wire:confirm="Tem certeza que deseja excluir esta atribuição?"
                                                class="text-red-600 hover:text-red-900 hover:scale-110 transform transition-all duration-200" 
                                                title="{{ __('shifts.delete') }}">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                                        <div class="flex flex-col items-center justify-center">
                                            <i class="fas fa-calendar-times text-gray-300 text-4xl mb-3"></i>
                                            <span class="text-sm">{{ __('shifts.no_assignments_found') }}</span>
                                            <button wire:click="createAssignment" class="mt-3 inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 ease-in-out transform hover:scale-105">
                                                <i class="fas fa-plus-circle mr-1"></i> {{ __('shifts.new_assignment') }}
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

            <!-- Assignment Modal (Padrão ERPDEMBENA Modernizado) -->
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
                <div class="relative top-4 mx-auto p-2 w-full max-w-5xl min-h-screen flex items-center justify-center">
                    <div class="relative bg-white rounded-2xl shadow-2xl transform transition-all duration-300 ease-in-out w-full max-h-[90vh] overflow-hidden" 
                         x-transition:enter="transition ease-out duration-300" 
                         x-transition:enter-start="transform opacity-0 scale-95" 
                         x-transition:enter-end="transform opacity-100 scale-100" 
                         x-transition:leave="transition ease-in duration-200" 
                         x-transition:leave-start="transform opacity-100 scale-100" 
                         x-transition:leave-end="transform opacity-0 scale-95">
                        
                        <!-- Cabeçalho com gradiente melhorado -->
                        <div class="bg-gradient-to-r from-blue-600 via-blue-700 to-indigo-700 rounded-t-2xl px-6 py-4 flex justify-between items-center sticky top-0 z-10">
                            <div class="flex items-center">
                                <div class="bg-white bg-opacity-20 rounded-full p-2 mr-3">
                                    <i class="fas fa-user-plus text-white text-lg"></i>
                                </div>
                                <div>
                                    <h3 class="text-lg font-semibold text-white">
                                        {{ $assignment_id ? __('shifts.edit_assignment') : __('shifts.add_assignment') }}
                                    </h3>
                                    <p class="text-blue-100 text-sm opacity-90">
                                        {{ __('shifts.assignment_modal_subtitle') }}
                                    </p>
                                </div>
                            </div>
                            <button type="button" wire:click="closeAssignmentModal" class="bg-white bg-opacity-20 hover:bg-opacity-30 text-white p-2 rounded-full transition-all duration-200 ease-in-out transform hover:scale-110 hover:rotate-90">
                                <i class="fas fa-times text-lg"></i>
                            </button>
                        </div>
                        
                        <!-- Corpo da modal -->
                        <div class="max-h-[75vh] overflow-y-auto">
                            <form wire:submit.prevent="saveAssignment">
                                <div class="p-6 space-y-6">
                                    <!-- Informações do funcionário e turno -->
                                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                                        <div class="flex items-center bg-gradient-to-r from-blue-50 to-indigo-50 px-6 py-4 border-b border-gray-100">
                                            <div class="bg-blue-100 rounded-full p-2 mr-3">
                                                <i class="fas fa-user-clock text-blue-600"></i>
                                            </div>
                                            <div>
                                                <h3 class="text-base font-semibold text-gray-800">{{ __('shifts.employee_shift_info') }}</h3>
                                                <p class="text-sm text-gray-600">{{ __('shifts.employee_shift_description') }}</p>
                                            </div>
                                        </div>
                                        <div class="p-6 space-y-6">
                                            <!-- Employee selection -->
                                            <div>
                                                <label for="employee_id" class="block text-sm font-medium text-gray-700 mb-2">
                                                    <i class="fas fa-user text-blue-500 mr-2"></i>
                                                    {{ __('shifts.employee') }} *
                                                </label>
                                                <select wire:model="employee_id" id="employee_id" 
                                                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50 text-sm bg-white transition-all duration-200">
                                                    <option value="">{{ __('shifts.select_employee') }}</option>
                                                    @foreach($employees as $employee)
                                                        <option value="{{ $employee->id }}">{{ $employee->full_name }}</option>
                                                    @endforeach
                                                </select>
                                                @error('employee_id') 
                                                    <p class="mt-2 text-sm text-red-600 flex items-center">
                                                        <i class="fas fa-exclamation-triangle mr-2"></i>
                                                        {{ $message }}
                                                    </p>
                                                @enderror
                                            </div>
                                            
                                            <!-- Rotation setting -->
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-3">
                                                    <i class="fas fa-sync-alt text-purple-500 mr-2"></i>
                                                    {{ __('shifts.rotation_settings') }}
                                                </label>
                                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                                    <div class="relative">
                                                        <input type="radio" wire:model="has_rotation" value="0" id="fixed_shift" class="sr-only peer">
                                                        <label for="fixed_shift" class="flex items-center p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 peer-checked:border-blue-500 peer-checked:bg-blue-50 transition-all duration-200">
                                                            <div class="flex items-center">
                                                                <div class="w-4 h-4 border-2 border-gray-300 rounded-full peer-checked:border-blue-500 peer-checked:bg-blue-500 flex items-center justify-center">
                                                                    <div class="w-2 h-2 bg-white rounded-full opacity-0 peer-checked:opacity-100"></div>
                                                                </div>
                                                                <div class="ml-3">
                                                                    <div class="text-sm font-medium text-gray-900">{{ __('shifts.fixed_shift') }}</div>
                                                                    <div class="text-xs text-gray-500">{{ __('shifts.fixed_shift_description') }}</div>
                                                                </div>
                                                            </div>
                                                        </label>
                                                    </div>
                                                    <div class="relative">
                                                        <input type="radio" wire:model="has_rotation" value="1" id="rotation_shift" class="sr-only peer">
                                                        <label for="rotation_shift" class="flex items-center p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 peer-checked:border-purple-500 peer-checked:bg-purple-50 transition-all duration-200">
                                                            <div class="flex items-center">
                                                                <div class="w-4 h-4 border-2 border-gray-300 rounded-full peer-checked:border-purple-500 peer-checked:bg-purple-500 flex items-center justify-center">
                                                                    <div class="w-2 h-2 bg-white rounded-full opacity-0 peer-checked:opacity-100"></div>
                                                                </div>
                                                                <div class="ml-3">
                                                                    <div class="text-sm font-medium text-gray-900">{{ __('shifts.rotation_shift') }}</div>
                                                                    <div class="text-xs text-gray-500">{{ __('shifts.rotation_shift_description') }}</div>
                                                                </div>
                                                            </div>
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <!-- Rotation details (when enabled) -->
                                            <div x-show="$wire.has_rotation" 
                                                 x-transition:enter="transition ease-out duration-300" 
                                                 x-transition:enter-start="opacity-0 scale-95" 
                                                 x-transition:enter-end="opacity-100 scale-100" 
                                                 x-transition:leave="transition ease-in duration-200" 
                                                 x-transition:leave-start="opacity-100 scale-100" 
                                                 x-transition:leave-end="opacity-0 scale-95"
                                                 class="bg-purple-50 p-4 rounded-lg border border-purple-200">
                                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                                    <div>
                                                        <label for="rotation_type" class="block text-sm font-medium text-gray-700 mb-2">
                                                            <i class="fas fa-calendar-alt text-purple-500 mr-2"></i>
                                                            {{ __('shifts.rotation_type') }}
                                                        </label>
                                                        <select wire:model="rotation_type" id="rotation_type" 
                                                            class="mt-1 block w-full rounded-lg border-purple-300 shadow-sm focus:border-purple-500 focus:ring focus:ring-purple-500 focus:ring-opacity-50 text-sm bg-white transition-all duration-200">
                                                            <option value="">{{ __('shifts.select_rotation_type') }}</option>
                                                            <option value="weekly">{{ __('shifts.weekly') }}</option>
                                                            <option value="biweekly">{{ __('shifts.biweekly') }}</option>
                                                            <option value="monthly">{{ __('shifts.monthly') }}</option>
                                                        </select>
                                                        @error('rotation_type') 
                                                            <p class="mt-2 text-sm text-red-600 flex items-center">
                                                                <i class="fas fa-exclamation-triangle mr-2"></i>
                                                                {{ $message }}
                                                            </p>
                                                        @enderror
                                                    </div>
                                                    <div>
                                                        <label for="rotation_frequency" class="block text-sm font-medium text-gray-700 mb-2">
                                                            <i class="fas fa-hashtag text-purple-500 mr-2"></i>
                                                            {{ __('shifts.rotation_frequency') }}
                                                        </label>
                                                        <input type="number" wire:model="rotation_frequency" id="rotation_frequency" min="1" max="30" 
                                                            class="mt-1 block w-full rounded-lg border-purple-300 shadow-sm focus:border-purple-500 focus:ring focus:ring-purple-500 focus:ring-opacity-50 text-sm bg-white transition-all duration-200">
                                                        @error('rotation_frequency') 
                                                            <p class="mt-2 text-sm text-red-600 flex items-center">
                                                                <i class="fas fa-exclamation-triangle mr-2"></i>
                                                                {{ $message }}
                                                            </p>
                                                        @enderror
                                                    </div>
                                                    <div>
                                                        <label for="rotation_start_date" class="block text-sm font-medium text-gray-700 mb-2">
                                                            <i class="fas fa-play text-purple-500 mr-2"></i>
                                                            {{ __('shifts.rotation_start_date') }}
                                                        </label>
                                                        <input type="date" wire:model="rotation_start_date" id="rotation_start_date" 
                                                            class="mt-1 block w-full rounded-lg border-purple-300 shadow-sm focus:border-purple-500 focus:ring focus:ring-purple-500 focus:ring-opacity-50 text-sm bg-white transition-all duration-200">
                                                        @error('rotation_start_date') 
                                                            <p class="mt-2 text-sm text-red-600 flex items-center">
                                                                <i class="fas fa-exclamation-triangle mr-2"></i>
                                                                {{ $message }}
                                                            </p>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <!-- Single shift selection (when not rotating) -->
                                            <div x-show="!$wire.has_rotation" 
                                                 x-transition:enter="transition ease-out duration-300" 
                                                 x-transition:enter-start="opacity-0 scale-95" 
                                                 x-transition:enter-end="opacity-100 scale-100" 
                                                 x-transition:leave="transition ease-in duration-200" 
                                                 x-transition:leave-start="opacity-100 scale-100" 
                                                 x-transition:leave-end="opacity-0 scale-95">
                                                <label for="shift_id_assignment" class="block text-sm font-medium text-gray-700 mb-2">
                                                    <i class="fas fa-clock text-blue-500 mr-2"></i>
                                                    {{ __('shifts.shift') }} *
                                                </label>
                                                <select wire:model.defer="shift_id_assignment" id="shift_id_assignment" 
                                                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50 text-sm bg-white transition-all duration-200">
                                                    <option value="">{{ __('shifts.select_shift') }}</option>
                                                    @foreach($shiftsForSelect as $shift)
                                                        <option value="{{ $shift->id }}">{{ $shift->name }} ({{ $shift->start_time->format('H:i') }} - {{ $shift->end_time->format('H:i') }})</option>
                                                    @endforeach
                                                </select>
                                                @error('shift_id_assignment') 
                                                    <p class="mt-2 text-sm text-red-600 flex items-center">
                                                        <i class="fas fa-exclamation-triangle mr-2"></i>
                                                        {{ $message }}
                                                    </p>
                                                @enderror
                                            </div>
                                            
                                            <!-- Multiple Shifts selection (when rotation is enabled) -->
                                            <div x-show="$wire.has_rotation" 
                                                 x-transition:enter="transition ease-out duration-300" 
                                                 x-transition:enter-start="opacity-0 scale-95" 
                                                 x-transition:enter-end="opacity-100 scale-100" 
                                                 x-transition:leave="transition ease-in duration-200" 
                                                 x-transition:leave-start="opacity-100 scale-100" 
                                                 x-transition:leave-end="opacity-0 scale-95">
                                                <label class="block text-sm font-medium text-gray-700 mb-3">
                                                    <i class="fas fa-layer-group text-purple-500 mr-2"></i>
                                                    {{ __('shifts.select_shifts') }} *
                                                </label>
                                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3 max-h-64 overflow-y-auto p-3 border-2 border-purple-200 rounded-lg bg-purple-50">
                                                    @foreach($shiftsForSelect as $shift)
                                                        <div class="relative">
                                                            <input type="checkbox" wire:model="selected_shifts" value="{{ $shift->id }}" id="shift_{{ $shift->id }}" class="sr-only peer">
                                                            <label for="shift_{{ $shift->id }}" class="flex items-center p-3 border-2 border-gray-200 rounded-lg cursor-pointer hover:bg-white peer-checked:border-purple-500 peer-checked:bg-white transition-all duration-200">
                                                                <div class="flex items-center">
                                                                    <div class="w-4 h-4 border-2 border-gray-300 rounded peer-checked:border-purple-500 peer-checked:bg-purple-500 flex items-center justify-center">
                                                                        <i class="fas fa-check text-white text-xs opacity-0 peer-checked:opacity-100"></i>
                                                                    </div>
                                                                    <div class="ml-3 flex-1">
                                                                        <div class="text-sm font-medium text-gray-900">{{ $shift->name }}</div>
                                                                        <div class="text-xs text-gray-500">{{ $shift->start_time->format('H:i') }} - {{ $shift->end_time->format('H:i') }}</div>
                                                                        @if($shift->is_night_shift)
                                                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-800 mt-1">
                                                                                <i class="fas fa-moon mr-1"></i> {{ __('shifts.night') }}
                                                                            </span>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                            </label>
                                                        </div>
                                                    @endforeach
                                                </div>
                                                @error('selected_shifts') 
                                                    <p class="mt-2 text-sm text-red-600 flex items-center">
                                                        <i class="fas fa-exclamation-triangle mr-2"></i>
                                                        {{ $message }}
                                                    </p>
                                                @enderror
                                                @error('shifts') 
                                                    <p class="mt-2 text-sm text-red-600 flex items-center">
                                                        <i class="fas fa-exclamation-triangle mr-2"></i>
                                                        {{ $message }}
                                                    </p>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                            
                            <!-- Datas -->
                            <div class="bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden">
                                <div class="flex items-center bg-gradient-to-r from-green-50 to-green-100 px-4 py-3 border-b border-gray-200">
                                    <i class="fas fa-calendar text-green-600 mr-2"></i>
                                    <h3 class="text-base font-medium text-gray-700">{{ __('shifts.assignment_period') }}</h3>
                                </div>
                                <div class="p-4">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <!-- Data de início -->
                                        <div>
                                            <label for="start_date" class="block text-sm font-medium text-gray-700 mb-1">
                                                {{ __('shifts.start_date') }} *
                                            </label>
                                            <input type="date" wire:model.defer="start_date" id="start_date" 
                                                class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md bg-white">
                                            @error('start_date') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                        </div>
                                        
                                        <!-- Data de término -->
                                        <div>
                                            <label for="end_date" class="block text-sm font-medium text-gray-700 mb-1">
                                                {{ __('shifts.end_date') }}
                                            </label>
                                            <input type="date" wire:model.defer="end_date" id="end_date" 
                                                class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md bg-white">
                                            @error('end_date') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Configurações adicionais -->
                            <div class="bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden">
                                <div class="flex items-center bg-gradient-to-r from-purple-50 to-purple-100 px-4 py-3 border-b border-gray-200">
                                    <i class="fas fa-cogs text-purple-600 mr-2"></i>
                                    <h3 class="text-base font-medium text-gray-700">{{ __('shifts.additional_settings') }}</h3>
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
                                                {{ __('shifts.is_permanent') }}
                                            </label>
                                            <p class="text-xs text-gray-500 mt-1">
                                                {{ __('shifts.permanent_info') }}
                                            </p>
                                        </div>
                                    </div>
                                    
                                    <!-- Padrão de Rotação -->
                                    <div>
                                        <label for="rotation_pattern" class="block text-sm font-medium text-gray-700 mb-1">
                                            {{ __('shifts.rotation_pattern') }}
                                        </label>
                                        <select wire:model.defer="rotation_pattern" id="rotation_pattern" 
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50 sm:text-sm bg-white"
                                            placeholder="{{ __('shifts.enter_rotation_pattern') }}">
                                            <option value="">{{ __('shifts.enter_rotation_pattern') }}</option>
                                            <option value="daily">{{ __('shifts.daily') }}</option>
                                            <option value="weekly">{{ __('shifts.weekly') }}</option>
                                            <option value="monthly">{{ __('shifts.monthly') }}</option>
                                            <option value="custom">{{ __('shifts.custom') }}</option>
                                            <option value="none">{{ __('shifts.no_rotation') }}</option>
                                        </select>
                                        @error('rotation_pattern') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                    </div>
                                    
                                    <!-- Notas -->
                                    <div>
                                        <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">
                                            {{ __('shifts.notes') }}
                                        </label>
                                        <textarea wire:model.defer="notes" id="notes" rows="3" 
                                            placeholder="{{ __('shifts.enter_notes') }}"
                                            class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md bg-white"></textarea>
                                        @error('notes') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>
                            </div>
                            
                            <!-- Botões de ação com estado de Loading e Animações -->
                            <div class="bg-gray-50 px-4 py-3 rounded-b-lg flex justify-end space-x-3 border-t border-gray-200">
                                <button type="button" wire:click="closeAssignmentModal" class="inline-flex justify-center items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 ease-in-out transform hover:scale-105">
                                    <i class="fas fa-times mr-2"></i>
                                    {{ __('shifts.cancel') }}
                                </button>
                                <button type="submit" wire:loading.attr="disabled" class="inline-flex justify-center items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 ease-in-out transform hover:scale-105 disabled:opacity-75 disabled:cursor-not-allowed">
                                    <span wire:loading.remove wire:target="saveAssignment">
                                        <i class="fas {{ $isEditing ? 'fa-save' : 'fa-plus-circle' }} mr-2"></i>
                                        {{ $isEditing ? __('shifts.edit') : __('shifts.save') }}
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

        </div>
    </div>

    <!-- Modal Moderna de Turnos - Padrão ERPDEMBENA -->
    @if($showShiftModal)
    <div x-data="{ open: @entangle('showShiftModal') }" 
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
                        {{ $isEditing ? __('shifts.edit_shift') : __('shifts.add_shift') }}
                    </h3>
                    <button type="button" wire:click="closeShiftModal" class="text-white hover:text-gray-200 focus:outline-none transition-all duration-200 ease-in-out transform hover:scale-110 hover:rotate-90">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                
                <!-- Corpo da modal com cartões temáticos -->
                <div class="p-6 space-y-6">
                    <!-- Informações Gerais -->
                    <div class="bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden">
                        <div class="flex items-center bg-gradient-to-r from-blue-50 to-blue-100 px-4 py-3 border-b border-gray-200">
                            <i class="fas fa-info-circle text-blue-600 mr-2"></i>
                            <h3 class="text-base font-medium text-gray-700">{{ __('shifts.general_settings') }}</h3>
                        </div>
                        <div class="p-4 space-y-4">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                                    <i class="fas fa-tag text-blue-500 mr-1"></i>
                                    {{ __('shifts.name') }}
                                </label>
                                <input type="text" wire:model.defer="name" id="name" 
                                    class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md bg-white"
                                    placeholder="{{ __('shifts.enter_shift_name') }}">
                                @error('name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>
                            
                            <div>
                                <label for="description" class="block text-sm font-medium text-gray-700 mb-1">
                                    <i class="fas fa-file-alt text-blue-500 mr-1"></i>
                                    {{ __('shifts.description') }}
                                </label>
                                <textarea wire:model.defer="description" id="description" rows="3" 
                                    class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md bg-white"
                                    placeholder="{{ __('shifts.enter_description') }}"></textarea>
                                @error('description') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    
                    <!-- Configurações de Horário -->
                    <div class="bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden">
                        <div class="flex items-center bg-gradient-to-r from-green-50 to-green-100 px-4 py-3 border-b border-gray-200">
                            <i class="fas fa-clock text-green-600 mr-2"></i>
                            <h3 class="text-base font-medium text-gray-700">{{ __('shifts.time_settings') }}</h3>
                        </div>
                        <div class="p-4">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label for="start_time" class="block text-sm font-medium text-gray-700 mb-1">
                                        <i class="fas fa-play text-green-500 mr-1"></i>
                                        {{ __('shifts.start_time') }}
                                    </label>
                                    <input type="time" wire:model.defer="start_time" id="start_time" 
                                        class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md bg-white">
                                    @error('start_time') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>
                                
                                <div>
                                    <label for="end_time" class="block text-sm font-medium text-gray-700 mb-1">
                                        <i class="fas fa-stop text-red-500 mr-1"></i>
                                        {{ __('shifts.end_time') }}
                                    </label>
                                    <input type="time" wire:model.defer="end_time" id="end_time" 
                                        class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md bg-white">
                                    @error('end_time') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>
                                
                                <div>
                                    <label for="break_duration" class="block text-sm font-medium text-gray-700 mb-1">
                                        <i class="fas fa-pause text-yellow-500 mr-1"></i>
                                        {{ __('shifts.break_duration') }}
                                    </label>
                                    <div class="relative">
                                        <input type="number" wire:model.defer="break_duration" id="break_duration" 
                                            class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md bg-white pr-12"
                                            placeholder="0">
                                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                            <span class="text-gray-500 sm:text-sm">min</span>
                                        </div>
                                    </div>
                                    @error('break_duration') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Configurações Avançadas -->
                    <div class="bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden">
                        <div class="flex items-center bg-gradient-to-r from-purple-50 to-purple-100 px-4 py-3 border-b border-gray-200">
                            <i class="fas fa-cogs text-purple-600 mr-2"></i>
                            <h3 class="text-base font-medium text-gray-700">{{ __('shifts.shift_details') }}</h3>
                        </div>
                        <div class="p-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="flex items-center">
                                    <div>
                                        <label for="is_night_shift" class="flex items-center cursor-pointer">
                                            <div class="relative">
                                                <input type="checkbox" wire:model.defer="is_night_shift" id="is_night_shift" class="sr-only">
                                                <div class="w-12 h-6 bg-gray-300 rounded-full shadow-inner transition-all duration-300 ease-in-out"
                                                     :class="{ 'bg-indigo-500': $wire.is_night_shift, 'bg-gray-300': !$wire.is_night_shift }"></div>
                                                <div class="dot absolute w-6 h-6 bg-white rounded-full shadow left-0 top-0 transition-transform duration-300 ease-in-out transform" 
                                                     :class="{ 'translate-x-6': $wire.is_night_shift, 'translate-x-0': !$wire.is_night_shift }"></div>
                                            </div>
                                        </label>
                                    </div>
                                    <div class="ml-3">
                                        <label for="is_night_shift" class="text-sm font-medium text-gray-700 cursor-pointer flex items-center">
                                            <i class="fas fa-moon text-indigo-500 mr-2"></i>
                                            {{ __('shifts.night_shift') }}
                                        </label>
                                        <p class="text-xs text-gray-500 mt-1">Turno ocorre durante a noite</p>
                                    </div>
                                </div>
                                
                                <div class="flex items-center">
                                    <div>
                                        <label for="is_active" class="flex items-center cursor-pointer">
                                            <div class="relative">
                                                <input type="checkbox" wire:model.defer="is_active" id="is_active" class="sr-only">
                                                <div class="w-12 h-6 bg-gray-300 rounded-full shadow-inner transition-all duration-300 ease-in-out"
                                                     :class="{ 'bg-green-500': $wire.is_active, 'bg-gray-300': !$wire.is_active }"></div>
                                                <div class="dot absolute w-6 h-6 bg-white rounded-full shadow left-0 top-0 transition-transform duration-300 ease-in-out transform" 
                                                     :class="{ 'translate-x-6': $wire.is_active, 'translate-x-0': !$wire.is_active }"></div>
                                            </div>
                                        </label>
                                    </div>
                                    <div class="ml-3">
                                        <label for="is_active" class="text-sm font-medium text-gray-700 cursor-pointer flex items-center">
                                            <i class="fas fa-check-circle text-green-500 mr-2"></i>
                                            {{ __('shifts.active') }}
                                        </label>
                                        <p class="text-xs text-gray-500 mt-1">Turno disponível para atribuições</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Rodapé com botões de ação -->
                <div class="bg-gray-50 px-4 py-3 rounded-b-lg flex justify-end space-x-3 border-t border-gray-200">
                    <button type="button" wire:click="closeShiftModal" class="inline-flex justify-center items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 ease-in-out transform hover:scale-105">
                        <i class="fas fa-times mr-2"></i>
                        {{ __('shifts.cancel') }}
                    </button>
                    <button type="button" wire:click="saveShift" wire:loading.attr="disabled" class="inline-flex justify-center items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 ease-in-out transform hover:scale-105 disabled:opacity-75 disabled:cursor-not-allowed">
                        <span wire:loading.remove wire:target="saveShift">
                            <i class="fas {{ $isEditing ? 'fa-save' : 'fa-plus-circle' }} mr-2"></i>
                            {{ $isEditing ? __('shifts.update') : __('shifts.save') }}
                        </span>
                        <span wire:loading wire:target="saveShift" class="inline-flex items-center">
                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            {{ __('shifts.processing') }}
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
