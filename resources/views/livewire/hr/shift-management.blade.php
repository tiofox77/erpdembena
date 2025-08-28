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
                
                {{-- Modals de Turnos --}}
                @includeIf("livewire.hr.shift-management.Modals._CreateShiftModal")
                @includeIf("livewire.hr.shift-management.Modals._DeleteShiftModal")
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
                                        {{ __('shifts.actions') }}
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

            {{-- Modals de Atribuições --}}
            @includeIf("livewire.hr.shift-management.Modals._CreateAssignmentModal")
            @includeIf("livewire.hr.shift-management.Modals._DeleteAssignmentModal") 

        </div>
    </div>

</div>
