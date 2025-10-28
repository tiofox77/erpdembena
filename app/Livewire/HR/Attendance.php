<?php

declare(strict_types=1);

namespace App\Livewire\HR;

use App\Models\HR\Attendance as AttendanceModel;
use App\Models\HR\Employee;
use App\Models\HR\Department;
use App\Models\HR\Shift;
use App\Models\HR\ShiftAssignment;
use App\Models\HR\HRSetting;
use App\Imports\AttendanceImport;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;

class Attendance extends Component
{
    use WithPagination, WithFileUploads;

    public $search = '';
    public $perPage = 10;
    public $sortField = 'date';
    public $sortDirection = 'desc';
    public $filters = [
        'department_id' => '',
        'status' => '',
        'start_date' => '',
        'end_date' => '',
    ];

    // Form properties
    public $attendance_id;
    public $employee_id;
    public $date;
    public $time_in;
    public $time_out;
    public $status;
    public $remarks;
    public $hourly_rate;
    public $affects_payroll = true;

    // Modal flags
    public $showModal = false;
    public $showDeleteModal = false;
    public $isEditing = false;
    
    // Calendar view properties
    public $viewMode = 'list'; // 'list' or 'calendar'
    public $currentMonth;
    public $currentYear;
    public $selectedDate;
    public $showCalendarModal = false;

    
    // Batch attendance properties
    public $selectedEmployees = [];
    public $batchStatus = 'present';
    public $batchTimeIn = '08:00';
    public $batchTimeOut = '17:00';
    public $shiftEmployees = [];
    public $shiftFilter = ''; // Para filtrar por shift
    public $selectAllEmployees = false; // Para marcar/desmarcar todos
    public $selectedShift = null; // Shift obrigatório selecionado
    public $availableShifts = []; // Shifts disponíveis
    
    // Import properties
    public $importFile = null;
    public $showImportModal = false;
    public array $importResults = [];
    public $showTimeConflictsModal = false;
    public array $timeConflicts = [];
    public array $selectedTimes = [];
    
    // Listeners
    protected $listeners = [
        'refreshAttendance' => '$refresh',
        'attendance-saved' => 'refreshData',
        'time-conflicts-found' => 'showTimeConflictsModal',
        'attendanceUpdated' => '$refresh'
    ];

    // Rules
    protected function rules()
    {
        return [
            'employee_id' => 'required|exists:employees,id',
            'date' => 'required|date',
            'time_in' => 'nullable',
            'time_out' => 'nullable',
            'status' => 'required|in:present,absent,late,half_day,leave',
            'remarks' => 'nullable',
            'hourly_rate' => 'nullable|numeric|min:0',
            'affects_payroll' => 'boolean'
        ];
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilters()
    {
        $this->resetPage();
    }

    public function mount()
    {
        $this->date = Carbon::today()->format('Y-m-d');
        // Não aplicar filtros por padrão - deixar vazios para mostrar todos os dados
        $this->filters['start_date'] = '';
        $this->filters['end_date'] = '';
        $this->currentMonth = Carbon::now()->month;
        $this->currentYear = Carbon::now()->year;
        

    }

    public function create()
    {
        // Reset form fields
        $this->reset([
            'attendance_id', 'employee_id', 'date', 'time_in', 'time_out',
            'status', 'remarks', 'hourly_rate', 'affects_payroll'
        ]);
        
        // Set defaults for new attendance records
        $this->date = Carbon::today()->format('Y-m-d');
        $this->status = 'present'; // Default to present
        $this->affects_payroll = true;
        
        $this->showModal = true;
    }
    
    public function openCalendar()
    {
        // Abrir modal de batch attendance ligada ao calendário
        $this->selectedDate = Carbon::today()->format('Y-m-d');
        $this->loadAllAvailableShifts();
        $this->resetBatchForm();
        $this->showCalendarModal = true;
    }
    
    /**
     * Método chamado quando um dia do calendário é clicado
     */
    public function selectDate($date)
    {
        $this->selectedDate = $date;
        $this->loadAllAvailableShifts();
        $this->resetBatchForm();
        $this->showCalendarModal = true;
    }
    
    /**
     * Carregar todos os shifts disponíveis para seleção
     */
    public function loadAllAvailableShifts()
    {
        $this->availableShifts = Shift::with(['shiftAssignments.employee.department'])
            ->where('is_active', true)
            ->get()
            ->map(function($shift) {
                $employeeCount = $this->getEmployeeCountForShift($shift->id, $this->selectedDate);
                
                return [
                    'id' => $shift->id,
                    'name' => $shift->name,
                    'start_time' => $shift->start_time->format('H:i'),
                    'end_time' => $shift->end_time->format('H:i'),
                    'employee_count' => $employeeCount,
                    'description' => $shift->description ?? '',
                ];
            })
            ->toArray();
    }
    
    /**
     * Obter contagem de funcionários para um shift numa data específica
     */
    private function getEmployeeCountForShift($shiftId, $date)
    {
        $targetDate = Carbon::parse($date);
        
        $assignments = ShiftAssignment::with(['employee'])
            ->where('start_date', '<=', $targetDate)
            ->where(function($query) use ($targetDate) {
                $query->whereNull('end_date')
                    ->orWhere('end_date', '>=', $targetDate);
            })
            ->get();
        
        return $assignments->filter(function($assignment) use ($shiftId, $targetDate) {
            if (!$assignment->hasRotation()) {
                return $assignment->shift_id === $shiftId;
            }
            
            $activeShiftId = $assignment->getActiveShiftForDate($targetDate);
            return $activeShiftId === $shiftId;
        })->count();
    }
    
    /**
     * Reset form de batch attendance
     */
    private function resetBatchForm()
    {
        $this->selectedEmployees = [];
        $this->selectedShift = null;
        $this->batchStatus = 'present';
        $this->batchTimeIn = '08:00';
        $this->batchTimeOut = '17:00';
        $this->batchRemarks = '';
        $this->shiftEmployees = [];
        $this->selectAllEmployees = false;
    }

    public function edit(AttendanceModel $attendance)
    {
        $this->attendance_id = $attendance->id;
        $this->employee_id = $attendance->employee_id;
        $this->date = $attendance->date->format('Y-m-d');
        $this->time_in = $attendance->time_in ? $attendance->time_in->format('H:i') : null;
        $this->time_out = $attendance->time_out ? $attendance->time_out->format('H:i') : null;
        $this->status = $attendance->status;
        $this->remarks = $attendance->remarks;
        $this->hourly_rate = $attendance->hourly_rate;
        $this->affects_payroll = $attendance->affects_payroll ?? true;

        $this->isEditing = true;
        $this->showModal = true;
    }

    public function confirmDelete(AttendanceModel $attendance)
    {
        $this->attendance_id = $attendance->id;
        $this->showDeleteModal = true;
    }

    public function save()
    {
        $validatedData = $this->validate();

        // Format time_in and time_out if they're not null
        if ($this->time_in) {
            $validatedData['time_in'] = Carbon::parse($this->date . ' ' . $this->time_in);
        }
        if ($this->time_out) {
            $validatedData['time_out'] = Carbon::parse($this->date . ' ' . $this->time_out);
        }
        
        // Garantir que status está definido
        if (!$this->isEditing) {
            $validatedData['status'] = $this->status ?: 'present';
        }

        try {
            if ($this->isEditing) {
                $attendance = AttendanceModel::find($this->attendance_id);
                $attendance->update($validatedData);
                $actionType = 'updated';
            } else {
                // Check if there's already an attendance record for this employee on this date
                $exists = AttendanceModel::where('employee_id', $this->employee_id)
                    ->where('date', $this->date)
                    ->exists();

                if ($exists) {
                    $this->dispatch('notify', 
                        type: 'error', 
                        message: __('attendance.messages.duplicate_found')
                    );
                    return;
                }
                
                // Para novos registos, se não for especificado, usar o valor base do funcionário
                if (empty($this->hourly_rate) && $this->employee_id) {
                    $employee = Employee::find($this->employee_id);
                    if ($employee) {
                        // Calcular valor hora com base nas configurações HR
                        $weeklyHours = (float) HRSetting::get('working_hours_per_week', 44);
                        $monthlyHours = $weeklyHours * 4.33; // média de semanas no mês
                        $validatedData['hourly_rate'] = $employee->base_salary > 0 ? round($employee->base_salary / $monthlyHours, 2) : 0.0;
                    }
                }

                AttendanceModel::create($validatedData);
                $actionType = 'created';
            }

            // Disparar notificação de sucesso
            $message = $actionType === 'updated' 
                ? __('attendance.messages.updated_successfully') 
                : __('attendance.messages.saved_successfully');
                
            $this->dispatch('notify', 
                type: 'success', 
                message: $message
            );
            
            // Fechar modal e resetar campos
            $this->closeModal();
            $this->dispatch('attendanceUpdated');
            
        } catch (\Exception $e) {
            $this->dispatch('notify', 
                type: 'error', 
                message: __('attendance.messages.error_saving', ['error' => $e->getMessage()])
            );
        }
    }

    public function delete()
    {
        try {
            $attendance = AttendanceModel::find($this->attendance_id);
            $attendance->delete();
            
            $this->dispatch('notify', 
                type: 'success', 
                message: __('attendance.messages.deleted_successfully')
            );
            
            $this->showDeleteModal = false;
            $this->dispatch('attendanceUpdated');
            
        } catch (\Exception $e) {
            $this->dispatch('notify', 
                type: 'error', 
                message: __('attendance.messages.error_deleting', ['error' => $e->getMessage()])
            );
        }
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->isEditing = false;
        $this->resetValidation();
        $this->reset([
            'attendance_id', 'employee_id', 'date', 'time_in', 'time_out',
            'status', 'remarks', 'hourly_rate', 'affects_payroll'
        ]);
    }
    
    public function closeDeleteModal()
    {
        $this->showDeleteModal = false;
    }
    
    public function resetFilters()
    {
        $this->reset('filters');
        $this->search = '';
        $this->resetPage();
    }
    
    public function switchView($mode)
    {
        $this->viewMode = $mode;
        $this->resetPage();
    }
    
    public function previousMonth()
    {
        $date = Carbon::createFromDate($this->currentYear, $this->currentMonth, 1)->subMonth();
        $this->currentMonth = $date->month;
        $this->currentYear = $date->year;
    }
    
    public function nextMonth()
    {
        $date = Carbon::createFromDate($this->currentYear, $this->currentMonth, 1)->addMonth();
        $this->currentMonth = $date->month;
        $this->currentYear = $date->year;
    }
    

    
    /**
     * Load available shifts for a specific date considering rotations
     */
    public function loadAvailableShifts($date)
    {
        $targetDate = Carbon::parse($date);
        
        // Get all active shifts
        $shifts = Shift::where('is_active', true)->get();
        
        // Get shift assignments that include rotations
        $shiftAssignments = ShiftAssignment::with(['employee', 'shift'])
            ->where('start_date', '<=', $targetDate)
            ->where(function($query) use ($targetDate) {
                $query->whereNull('end_date')
                    ->orWhere('end_date', '>=', $targetDate);
            })
            ->get();
        
        // Group assignments by shift considering rotations
        $this->availableShifts = $shifts->map(function($shift) use ($shiftAssignments, $targetDate) {
            // Find employees assigned to this shift (directly or through rotation)
            $employeesForShift = $shiftAssignments->filter(function($assignment) use ($shift, $targetDate) {
                if (!$assignment->hasRotation()) {
                    return $assignment->shift_id === $shift->id;
                }
                
                // Check if this shift is active for the target date in rotation
                $activeShiftId = $assignment->getActiveShiftForDate($targetDate);
                return $activeShiftId === $shift->id;
            });
            
            return [
                'id' => $shift->id,
                'name' => $shift->name,
                'start_time' => $shift->start_time->format('H:i'),
                'end_time' => $shift->end_time->format('H:i'),
                'employee_count' => $employeesForShift->count(),
                'employees' => $employeesForShift->map(function($assignment) use ($targetDate) {
                    $rotationInfo = $assignment->getRotationSummary();
                    return [
                        'id' => $assignment->employee->id,
                        'name' => $assignment->employee->full_name,
                        'department' => $assignment->employee->department->name ?? 'N/A',
                        'has_rotation' => $rotationInfo['has_rotation'],
                        'rotation_type' => $rotationInfo['type'],
                        'next_rotation' => $rotationInfo['next_rotation'],
                        'is_permanent' => $rotationInfo['is_permanent'] ?? $assignment->is_permanent ?? true,
                        'assignment_id' => $assignment->id,
                    ];
                })->toArray(),
            ];
        })->filter(function($shift) {
            return $shift['employee_count'] > 0;
        })->values()->toArray();
    }
    
    /**
     * Get employees with rotation information for a specific shift and date
     */
    public function getEmployeesWithRotationInfo($shiftId, $date)
    {
        $targetDate = Carbon::parse($date);
        
        $assignments = ShiftAssignment::with(['employee.department', 'shift'])
            ->where('start_date', '<=', $targetDate)
            ->where(function($query) use ($targetDate) {
                $query->whereNull('end_date')
                    ->orWhere('end_date', '>=', $targetDate);
            })
            ->get();
        
        return $assignments->filter(function($assignment) use ($shiftId, $targetDate) {
            if (!$assignment->hasRotation()) {
                return $assignment->shift_id === $shiftId;
            }
            
            $activeShiftId = $assignment->getActiveShiftForDate($targetDate);
            return $activeShiftId === $shiftId;
        })->map(function($assignment) use ($targetDate) {
            $rotationInfo = $assignment->getRotationSummary();
            
            return [
                'id' => $assignment->employee->id,
                'name' => $assignment->employee->full_name,
                'department' => $assignment->employee->department->name ?? 'N/A',
                'has_rotation' => $rotationInfo['has_rotation'],
                'rotation_type' => $rotationInfo['type'],
                'rotation_interval' => $rotationInfo['interval'],
                'next_rotation' => $rotationInfo['next_rotation'],
                'is_permanent' => $rotationInfo['is_permanent'],
                'assignment_start' => $assignment->start_date,
                'assignment_end' => $assignment->end_date,
                'current_shift_id' => $rotationInfo['current_shift_id'],
                'shifts_count' => $rotationInfo['shifts_count'],
            ];
        })->toArray();
    }
    

    
    public function setShiftDefaultTimes()
    {
        if ($this->selectedShift) {
            $shift = \App\Models\HR\Shift::find($this->selectedShift);
            if ($shift) {
                $this->batchTimeIn = $shift->start_time->format('H:i');
                $this->batchTimeOut = $shift->end_time->format('H:i');
            }
        }
    }
    
    /**
     * Método para selecionar um shift da modal do calendário
     */
    public function selectShift($shiftId)
    {
        $this->selectedShift = $shiftId;
        $this->selectedEmployees = []; // Limpar seleções anteriores
        $this->selectAllEmployees = false;
        
        // Carregar funcionários do shift selecionado
        $this->loadEmployeesForSelectedShift();
        
        // Preencher automaticamente horários do shift
        $this->setShiftDefaultTimes();
    }
    
    public function updatedSelectedShift()
    {
        if ($this->selectedShift) {
            $this->loadEmployeesForSelectedShift();
        }
    }
    
    /**
     * Carregar funcionários para o shift selecionado
     */
    public function loadEmployeesForSelectedShift()
    {
        if (!$this->selectedShift || !$this->selectedDate) {
            $this->shiftEmployees = [];
            return;
        }
        
        $targetDate = Carbon::parse($this->selectedDate);
        
        // Obter todas as atribuições de shift válidas para a data
        $assignments = ShiftAssignment::with(['employee.department', 'shift'])
            ->where('start_date', '<=', $targetDate)
            ->where(function($query) use ($targetDate) {
                $query->whereNull('end_date')
                    ->orWhere('end_date', '>=', $targetDate);
            })
            ->get();
        
        // Filtrar funcionários que devem estar no shift selecionado na data específica
        $shiftEmployees = $assignments->filter(function($assignment) use ($targetDate) {
            if (!$assignment->hasRotation()) {
                return $assignment->shift_id == $this->selectedShift;
            }
            
            $activeShiftId = $assignment->getActiveShiftForDate($targetDate);
            return $activeShiftId == $this->selectedShift;
        })->map(function($assignment) use ($targetDate) {
            $rotationInfo = $assignment->getRotationSummary();
            
            // Verificar se já tem presença registada para este dia
            $alreadyMarked = AttendanceModel::where('employee_id', $assignment->employee->id)
                ->where('date', $this->selectedDate)
                ->exists();
            
            return [
                'id' => $assignment->employee->id,
                'name' => $assignment->employee->full_name,
                'department' => $assignment->employee->department->name ?? 'N/A',
                'shift_name' => $assignment->shift->name,
                'has_rotation' => $rotationInfo['has_rotation'],
                'rotation_type' => $rotationInfo['type'] ?? null,
                'rotation_interval' => $rotationInfo['interval'] ?? null,
                'next_rotation' => $rotationInfo['next_rotation'] ?? null,
                'is_permanent' => $rotationInfo['is_permanent'] ?? true,
                'assignment_start' => $assignment->start_date,
                'assignment_end' => $assignment->end_date,
                'assignment_id' => $assignment->id,
                'already_marked' => $alreadyMarked,
            ];
        })->values()->toArray();
        
        $this->shiftEmployees = $shiftEmployees;
        
        // Preencher automaticamente os horários do shift selecionado
        $this->setShiftDefaultTimes();
    }
    
    /**
     * Get rotation display text for an employee
     */
    public function getRotationDisplayText($employee)
    {
        if (!$employee['has_rotation']) {
            return __('shifts.no_rotation');
        }
        
        $type = $employee['rotation_type'];
        $interval = $employee['rotation_interval'] ?? 1;
        $nextRotation = $employee['next_rotation'];
        
        $typeText = match($type) {
            'daily' => __('shifts.daily_rotation'),
            'weekly' => __('shifts.weekly_rotation'),
            'monthly' => __('shifts.monthly_rotation'),
            'yearly' => __('shifts.yearly_rotation'),
            default => __('shifts.custom_rotation'),
        };
        
        $intervalText = $interval > 1 ? " ({$interval}x)" : '';
        
        if ($nextRotation) {
            $nextDate = Carbon::parse($nextRotation)->format('d/m/Y');
            return "{$typeText}{$intervalText} - " . __('shifts.next_rotation') . ": {$nextDate}";
        }
        
        return "{$typeText}{$intervalText}";
    }
    
    public function loadShiftEmployees($date)
    {
        // Só carregar funcionários se um shift foi selecionado
        if ($this->selectedShift) {
            $this->loadEmployeesForSelectedShift();
        } else {
            $this->shiftEmployees = [];
        }
    }
    
    public function getAvailableShifts($date)
    {
        return \App\Models\HR\Shift::where('is_active', true)
            ->with('department:id,name')
            ->get();
    }
    
    public function toggleSelectAll()
    {
        if ($this->selectAllEmployees) {
            // Desmarcar todos
            $this->selectedEmployees = [];
            $this->selectAllEmployees = false;
        } else {
            // Marcar apenas os funcionários filtrados que NÃO têm presença registada
            $filteredEmployees = $this->getFilteredShiftEmployees();
            $this->selectedEmployees = $filteredEmployees
                ->where('has_attendance', false)
                ->pluck('id')
                ->toArray();
            $this->selectAllEmployees = true;
        }
    }
    
    public function getFilteredShiftEmployees()
    {
        $employees = collect($this->shiftEmployees);
        
        if (!empty($this->shiftFilter)) {
            $employees = $employees->where('shift_id', $this->shiftFilter);
        }
        
        return $employees;
    }
    
    public function updatedShiftFilter()
    {
        // Reset seleção quando filtro mudar
        $this->selectedEmployees = [];
        $this->selectAllEmployees = false;
    }
    
    public function updatedSelectedEmployees()
    {
        // Atualizar estado do "select all" baseado na seleção atual
        $filteredEmployees = $this->getFilteredShiftEmployees();
        $availableEmployees = $filteredEmployees->where('has_attendance', false);
        $this->selectAllEmployees = count($this->selectedEmployees) === $availableEmployees->count() && $availableEmployees->count() > 0;
    }

    public function saveBatchAttendance()
    {
        // Log de depuração
        \Log::info('Iniciando salvamento de presenças em lote', [
            'selectedShift' => $this->selectedShift,
            'selectedEmployees' => $this->selectedEmployees,
            'selectedDate' => $this->selectedDate,
            'batchStatus' => $this->batchStatus,
            'batchTimeIn' => $this->batchTimeIn,
            'batchTimeOut' => $this->batchTimeOut,
        ]);
        
        // Validar se um shift foi selecionado
        if (!$this->selectedShift) {
            $this->dispatch('notify', 
                type: 'error', 
                message: __('attendance.messages.select_shift_first')
            );
            return;
        }
        
        if (empty($this->selectedEmployees)) {
            $this->dispatch('notify', 
                type: 'error', 
                message: __('attendance.messages.no_employees_selected')
            );
            return;
        }
        
        $successCount = 0;
        $errors = [];
        
        foreach ($this->selectedEmployees as $employeeId) {
            try {
                \Log::info('Processando funcionário', ['employeeId' => $employeeId]);
                
                // Verificar se já existe presença para este funcionário nesta data
                $exists = AttendanceModel::where('employee_id', $employeeId)
                    ->whereDate('date', $this->selectedDate)
                    ->exists();
                    
                if ($exists) {
                    $employee = Employee::find($employeeId);
                    $errors[] = "Já existe registo para {$employee->full_name} em {$this->selectedDate}";
                    continue;
                }
                
                // Obter dados do funcionário para calcular taxa horária
                $employee = Employee::find($employeeId);
                $baseSalary = $employee->base_salary ?? 0;
                // Calcular valor hora com base nas configurações HR
                $weeklyHours = (float) HRSetting::get('working_hours_per_week', 44);
                $monthlyHours = $weeklyHours * 4.33; // média de semanas no mês
                $hourlyRate = $baseSalary > 0 ? round($baseSalary / $monthlyHours, 2) : 0.0;
                
                \Log::info('Dados do funcionário obtidos', [
                    'employee' => $employee->full_name,
                    'baseSalary' => $baseSalary,
                    'hourly_rate' => $hourlyRate
                ]);
                
                // Criar dados para salvar
                $attendanceData = [
                    'employee_id' => $employeeId,
                    'date' => Carbon::parse($this->selectedDate)->format('Y-m-d'),
                    'status' => $this->batchStatus,
                    'remarks' => $this->batchRemarks ?? '',
                    'hourly_rate' => $hourlyRate,
                    'affects_payroll' => true,
                ];
                
                // Adicionar horários se status for 'present'
                if ($this->batchStatus === 'present') {
                    if (!empty($this->batchTimeIn)) {
                        $attendanceData['time_in'] = Carbon::parse($this->selectedDate . ' ' . $this->batchTimeIn);
                    }
                    if (!empty($this->batchTimeOut)) {
                        $attendanceData['time_out'] = Carbon::parse($this->selectedDate . ' ' . $this->batchTimeOut);
                    }
                }
                
                \Log::info('Tentando salvar presença', ['attendanceData' => $attendanceData]);
                
                // Validar os dados antes de salvar
                if (empty($attendanceData['employee_id'])) {
                    throw new \Exception('Employee ID não pode estar vazio');
                }
                
                if (empty($attendanceData['date'])) {
                    throw new \Exception('Data não pode estar vazia');
                }
                
                // Tentar usar new + save em vez de create
                $attendance = new AttendanceModel();
                $attendance->fill($attendanceData);
                
                \Log::info('Dados preenchidos no modelo', [
                    'model_attributes' => $attendance->getAttributes(),
                    'fillable' => $attendance->getFillable()
                ]);
                
                $saved = $attendance->save();
                
                if (!$saved) {
                    throw new \Exception('Falha ao salvar registro de presença - save() retornou false');
                }
                
                if (!$attendance->id) {
                    throw new \Exception('Falha ao criar registro de presença - ID não foi gerado');
                }
                
                $successCount++;
                
                \Log::info('Presença salva com sucesso', [
                    'attendanceId' => $attendance->id,
                    'employeeId' => $attendance->employee_id,
                    'date' => $attendance->date
                ]);
                
            } catch (\Exception $e) {
                $employee = Employee::find($employeeId);
                $errors[] = "Erro ao salvar {$employee->full_name}: " . $e->getMessage();
                \Log::error('Erro ao salvar presença', [
                    'employeeId' => $employeeId,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }
        
        if ($successCount > 0) {
            $this->dispatch('notify', 
                type: 'success', 
                message: __('attendance.messages.batch_saved', ['count' => $successCount])
            );
            
            // Fechar a modal após salvar com sucesso
            $this->closeCalendarModal();
            
            // Forçar atualização dos dados do calendário
            $this->calendarData = null; // Resetar cache se existir
            
            // Emitir evento para atualizar a interface
            $this->dispatch('attendanceUpdated');
            \Log::info('Presenças registadas com sucesso', ['count' => $successCount]);
        }
        
        if (!empty($errors)) {
            // Mostra erros em notificações separadas
            foreach ($errors as $error) {
                $this->dispatch('notify', 
                    type: 'error', 
                    message: $error
                );
            }
            \Log::warning('Erros durante o registo de presenças', ['errors' => $errors]);
        }
        
        // Refresh dos dados após salvamento
        $this->dispatch('attendance-saved');
        
        $this->closeCalendarModal();
    }
    
    public function closeCalendarModal()
    {
        $this->showCalendarModal = false;
        $this->selectedEmployees = [];
        $this->batchStatus = 'present';
        $this->batchTimeIn = '08:00';
        $this->batchTimeOut = '17:00';
        $this->batchRemarks = '';
        $this->shiftEmployees = [];
        $this->shiftFilter = '';
        $this->selectAllEmployees = false;
        $this->selectedShift = null;
        $this->availableShifts = [];
    }
    
    /**
     * Refresh data after attendance operations
     */
    public function refreshData()
    {
        // Trigger component refresh to reload the attendance list
        $this->render();
        
        // Log for debugging
        \Log::info('Attendance data refreshed after save operation');
    }
    
    public function getCalendarData()
    {
        // Garantir que currentYear e currentMonth estejam definidos
        if (!$this->currentYear || !$this->currentMonth) {
            $this->currentMonth = Carbon::now()->month;
            $this->currentYear = Carbon::now()->year;
        }
        
        try {
            $startOfMonth = Carbon::createFromDate($this->currentYear, $this->currentMonth, 1);
            $endOfMonth = $startOfMonth->copy()->endOfMonth();
            
            $calendarData = collect();
            
            // Iterar sobre cada dia do mês
            for ($day = 1; $day <= $endOfMonth->day; $day++) {
                $currentDate = Carbon::createFromDate($this->currentYear, $this->currentMonth, $day);
                $dateString = $currentDate->format('Y-m-d');
                
                // Obter funcionários programados para trabalhar neste dia (baseado nos shifts)
                $scheduledEmployees = $this->getScheduledEmployeesForDate($currentDate);
                $totalScheduled = $scheduledEmployees->count();
                
                if ($totalScheduled === 0) {
                    continue; // Pular dias sem funcionários programados
                }
                
                // Obter presenças registradas para este dia
                $attendances = \DB::table('attendances')
                    ->whereDate('date', $dateString)
                    ->select('employee_id', 'status')
                    ->get()
                    ->groupBy('status');
                    
                $stats = [
                    'date' => $dateString,
                    'total_scheduled' => $totalScheduled, // Total programado para trabalhar
                    'total_attendances' => $attendances->flatten()->count(), // Total com registros de presença
                    'present' => $attendances->get('present', collect())->count(),
                    'absent' => $attendances->get('absent', collect())->count(),
                    'late' => $attendances->get('late', collect())->count(),
                    'half_day' => $attendances->get('half_day', collect())->count(),
                    'leave' => $attendances->get('leave', collect())->count(),
                    'sick_leave' => $attendances->get('sick_leave', collect())->count(),
                    'vacation' => $attendances->get('vacation', collect())->count(),
                    'overtime' => $attendances->get('overtime', collect())->count(),
                ];
                
                // Funcionários não marcados (sem registo de presença)
                $stats['not_marked'] = $totalScheduled - $stats['total_attendances'];
                
                // Calcular taxa de presença baseada no total programado
                $effectivePresent = $stats['present'] + $stats['late'] + $stats['half_day'];
                $stats['attendance_rate'] = $totalScheduled > 0 
                    ? round(($effectivePresent / $totalScheduled) * 100, 1) 
                    : 0;
                    
                // Taxa de cobertura (quantos foram marcados vs programados)
                $stats['coverage_rate'] = $totalScheduled > 0 
                    ? round(($stats['total_attendances'] / $totalScheduled) * 100, 1) 
                    : 0;
                    
                $calendarData->put($dateString, $stats);
            }
            
            return $calendarData;
            
        } catch (\Exception $e) {
            \Log::error('Erro ao carregar dados do calendário: ' . $e->getMessage());
            // Retornar collection vazia em caso de erro
            return collect();
        }
    }

    /**
     * Obter funcionários programados para trabalhar numa data específica
     */
    private function getScheduledEmployeesForDate($date)
    {
        $targetDate = Carbon::parse($date);
        
        // Considerar todos os funcionários ativos como programados para trabalhar
        // em todos os dias da semana (incluindo fins de semana)
        return \DB::table('employees')
            ->where('employment_status', 'active')
            ->select('id', 'full_name')
            ->get();
    }

    /**
     * Carrega funcionários para um shift específico numa data específica
     */
    public function loadEmployeesForShift($shiftId, $date)
    {
        $targetDate = Carbon::parse($date);
        
        // Obter funcionários atribuídos ao shift na data especificada
        $assignments = ShiftAssignment::where('shift_id', $shiftId)
            ->whereDate('start_date', '<=', $targetDate)
            ->where(function ($query) use ($targetDate) {
                $query->whereNull('end_date')
                    ->orWhereDate('end_date', '>=', $targetDate);
            })
            ->with([
                'employee:id,full_name,employee_id,department_id',
                'employee.department:id,name',
                'shift:id,name,start_time,end_time'
            ])
            ->get();
            
        $employees = [];
        
        foreach ($assignments as $assignment) {
            if (!$assignment->employee) {
                continue;
            }
            
            $employee = $assignment->employee;
            
            // Verificar se já existe presença para este funcionário na data
            $existingAttendance = AttendanceModel::where('employee_id', $employee->id)
                ->whereDate('date', $targetDate)
                ->first();
                
            // Determinar informações de rotação
            $hasRotation = $assignment->hasRotation();
            $rotationData = [];
            
            if ($hasRotation) {
                // Obter turno ativo para a data
                $activeShiftId = $assignment->getActiveShiftForDate($targetDate);
                $rotationData = [
                    'has_rotation' => true,
                    'rotation_type' => $assignment->getRotationType(),
                    'rotation_interval' => $assignment->getRotationInterval(),
                    'current_shift_id' => $activeShiftId,
                    'next_rotation' => $assignment->getNextRotationDate($targetDate),
                    'rotation_summary' => $assignment->getRotationSummary(),
                ];
            } else {
                $rotationData = [
                    'has_rotation' => false,
                    'current_shift_id' => $assignment->shift_id,
                ];
            }
            
            $employees[] = [
                'id' => $employee->id,
                'name' => $employee->full_name,
                'employee_id' => $employee->employee_id,
                'department' => $employee->department->name ?? 'N/A',
                'department_id' => $employee->department_id,
                'already_marked' => $existingAttendance !== null,
                'existing_status' => $existingAttendance ? $existingAttendance->status : null,
                'assignment_start' => $assignment->start_date,
                'assignment_end' => $assignment->end_date,
                'is_permanent' => $assignment->is_permanent,
            ] + $rotationData;
        }
        
        $this->shiftEmployees = $employees;
    }
    

    
    /**
     * Abre a modal de calendário para uma data específica
     */
    public function openCalendarModal($date)
    {
        $this->selectedDate = $date;
        $this->showCalendarModal = true;
        
        // Carregar shifts disponíveis para a data
        $this->availableShifts = Shift::where('is_active', true)
            ->with('department')
            ->get();
        
        // Resetar seleções
        $this->selectedShift = null;
        $this->shiftEmployees = [];
        $this->selectedEmployees = [];
        $this->selectAllEmployees = false;
    }

    /**
     * Open import modal
     */
    public function openImportModal()
    {
        $this->reset(['importFile', 'importResults']);
        $this->showImportModal = true;
    }

    /**
     * Close import modal
     */
    public function closeImportModal()
    {
        $this->showImportModal = false;
        $this->reset(['importFile', 'importResults']);
    }

    /**
     * Close time conflicts modal
     */
    public function closeTimeConflictsModal()
    {
        $this->showTimeConflictsModal = false;
        $this->reset(['timeConflicts', 'selectedTimes']);
    }

    /**
     * Process attendance after user resolved time conflicts
     */
    public function processConflictResolution()
    {
        \Log::info('Processing conflict resolution', [
            'conflicts_count' => count($this->timeConflicts),
            'selected_times' => $this->selectedTimes
        ]);
        
        try {
            // Validate that all conflicts have selected times
            $missingSelections = [];
            foreach ($this->timeConflicts as $index => $conflict) {
                $hasCheckInOptions = !empty($conflict['check_in_options']);
                $hasCheckOutOptions = !empty($conflict['check_out_options']);
                $selectedCheckIn = $this->selectedTimes[$index]['check_in'] ?? null;
                $selectedCheckOut = $this->selectedTimes[$index]['check_out'] ?? null;
                
                // Check if required selections are missing
                if ($hasCheckInOptions && !$selectedCheckIn) {
                    $missingSelections[] = $conflict['employee_name'] . ' (Hora de Entrada)';
                }
                if ($hasCheckOutOptions && !$selectedCheckOut) {
                    $missingSelections[] = $conflict['employee_name'] . ' (Hora de Saída)';
                }
            }
            
            // If there are missing selections, show error
            if (!empty($missingSelections)) {
                \Log::warning('Missing time selections', ['missing' => $missingSelections]);
                $this->dispatch('notify', 
                    type: 'error', 
                    message: 'Por favor, selecione os horários para: ' . implode(', ', $missingSelections)
                );
                return;
            }
            
            \Log::info('All selections valid, proceeding with import');
            
            $created = 0;
            $updated = 0;
            
            foreach ($this->timeConflicts as $index => $conflict) {
                $selectedCheckIn = $this->selectedTimes[$index]['check_in'] ?? null;
                $selectedCheckOut = $this->selectedTimes[$index]['check_out'] ?? null;
                
                // Calculate hourly rate
                $employee = \App\Models\HR\Employee::find($conflict['employee_id']);
                if (!$employee) continue;
                
                $baseSalary = $employee->base_salary ?? 0;
                $weeklyHours = (float) \App\Models\HR\HRSetting::get('working_hours_per_week', 44);
                $monthlyHours = $weeklyHours * 4.33;
                $hourlyRate = $baseSalary > 0 ? round($baseSalary / $monthlyHours, 2) : 0.0;
                
                // Determine status
                $status = 'present';
                if (!$selectedCheckIn && !$selectedCheckOut) {
                    $status = 'absent';
                }
                
                // Check if attendance already exists
                $existingAttendance = \App\Models\HR\Attendance::where('employee_id', $conflict['employee_id'])
                    ->whereDate('date', $conflict['date'])
                    ->first();
                
                $timeIn = $selectedCheckIn ? \Carbon\Carbon::parse($conflict['date'] . ' ' . $selectedCheckIn) : null;
                $timeOut = $selectedCheckOut ? \Carbon\Carbon::parse($conflict['date'] . ' ' . $selectedCheckOut) : null;
                
                // Prepare attendance data
                $attendanceData = [
                    'time_in' => $timeIn,
                    'time_out' => $timeOut,
                    'status' => $status,
                    'hourly_rate' => $hourlyRate,
                    'affects_payroll' => true,
                    'remarks' => 'Importado do sistema biométrico (hora selecionada manualmente)',
                ];
                
                if ($existingAttendance) {
                    // Check if there are changes
                    $hasChanges = false;
                    if ($existingAttendance->time_in != $timeIn) $hasChanges = true;
                    if ($existingAttendance->time_out != $timeOut) $hasChanges = true;
                    if ($existingAttendance->status != $status) $hasChanges = true;
                    
                    if ($hasChanges) {
                        // Update existing attendance
                        $existingAttendance->update($attendanceData);
                        $updated++;
                        \Log::info('Updated attendance', [
                            'employee_id' => $conflict['employee_id'],
                            'date' => $conflict['date']
                        ]);
                    } else {
                        \Log::info('No changes detected, skipping', [
                            'employee_id' => $conflict['employee_id'],
                            'date' => $conflict['date']
                        ]);
                    }
                } else {
                    // Create new attendance
                    $attendanceData['employee_id'] = $conflict['employee_id'];
                    $attendanceData['date'] = $conflict['date'];
                    \App\Models\HR\Attendance::create($attendanceData);
                    $created++;
                    \Log::info('Created new attendance', [
                        'employee_id' => $conflict['employee_id'],
                        'date' => $conflict['date']
                    ]);
                }
            }
            
            \Log::info('Successfully processed attendance records', [
                'created' => $created,
                'updated' => $updated
            ]);
            
            // Build message
            $messageParts = [];
            if ($created > 0) $messageParts[] = "{$created} criado(s)";
            if ($updated > 0) $messageParts[] = "{$updated} atualizado(s)";
            
            $skipped = count($this->timeConflicts) - ($created + $updated);
            if ($skipped > 0) $messageParts[] = "{$skipped} ignorado(s) (sem alterações)";
            
            if (empty($messageParts)) {
                $message = "ℹ️ Nenhum registo foi alterado (todos os dados já estavam atualizados)";
            } else {
                $message = "✅ Conflitos resolvidos com sucesso! " . implode(', ', $messageParts);
            }
            
            $this->dispatch('notify', 
                type: 'success', 
                message: $message
            );
            $this->closeTimeConflictsModal();
            $this->dispatch('attendanceUpdated');
            
        } catch (\Exception $e) {
            \Log::error('Conflict resolution error:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $this->dispatch('notify', 
                type: 'error', 
                message: '❌ Erro ao processar conflitos: ' . $e->getMessage()
            );
        }
    }

    /**
     * Import attendance from Excel file
     */
    public function importFromExcel()
    {
        \Log::info('DEBUG: importFromExcel method called');
        \Log::info('DEBUG: importFile value:', ['file' => $this->importFile]);
        
        try {
            // Validação manual mais permissiva para arquivos Excel antigos
            if ($this->importFile) {
                $extension = strtolower($this->importFile->getClientOriginalExtension());
                $allowedExtensions = ['xlsx', 'xls', 'csv'];
                
                \Log::info('DEBUG: File info', [
                    'mime' => $this->importFile->getMimeType(),
                    'extension' => $extension,
                    'name' => $this->importFile->getClientOriginalName(),
                    'size' => $this->importFile->getSize()
                ]);
                
                if (!in_array($extension, $allowedExtensions)) {
                    $this->dispatch('notify', 
                        type: 'error', 
                        message: 'Formato de arquivo inválido. Use: .xlsx, .xls ou .csv'
                    );
                    return;
                }
                
                if ($this->importFile->getSize() > 10240 * 1024) { // 10MB em bytes
                    $this->dispatch('notify', 
                        type: 'error', 
                        message: 'Arquivo muito grande. Máximo: 10MB'
                    );
                    return;
                }
            } else {
                $this->dispatch('notify', 
                    type: 'error', 
                    message: 'Selecione um arquivo para importar'
                );
                return;
            }
            
            \Log::info('DEBUG: Validation passed');

        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('DEBUG: Validation failed:', $e->errors());
            $this->dispatch('notify', 
                type: 'error', 
                message: __('attendance.messages.validation_error', ['error' => implode(', ', $e->validator->errors()->all())])
            );
            return;
        }

        try {
            \Log::info('DEBUG: Starting import process');
            $import = new AttendanceImport();
            Excel::import($import, $this->importFile->getRealPath());
            
            // Finalizar registos pendentes do ZKTime (se houver)
            $import->finalizePendingRecords();
            
            \Log::info('DEBUG: Import completed successfully');
            
            $importedCount = $import->getImportedCount();
            $updatedCount = $import->getUpdatedCount();
            $skippedCount = $import->getSkippedCount();
            $errors = $import->getErrors();
            $timeConflicts = $import->getTimeConflicts();
            $notFoundEmployees = $import->getNotFoundEmployees();
            $notFoundCount = $import->getNotFoundCount();
            
            // Check if there are time conflicts that need user confirmation
            if (!empty($timeConflicts)) {
                $this->timeConflicts = $timeConflicts;
                $this->closeImportModal();
                $this->showTimeConflictsModal = true;
                
                $this->dispatch('notify', 
                    type: 'warning', 
                    message: __('attendance.messages.conflicts_found', ['count' => count($timeConflicts)])
                );
                return;
            }
            
            $this->importResults = [
                'success' => true,
                'message' => __('messages.import_completed_successfully'),
                'details' => [
                    'imported' => $importedCount,
                    'updated' => $updatedCount,
                    'skipped' => $skippedCount,
                    'not_found' => $notFoundCount,
                    'errors' => $errors,
                ]
            ];

            // Build success message
            $messageParts = [];
            if ($importedCount > 0) $messageParts[] = "{$importedCount} novos registos";
            if ($updatedCount > 0) $messageParts[] = "{$updatedCount} atualizados";
            
            if (!empty($messageParts)) {
                $successMessage = "✅ Importação concluída: " . implode(', ', $messageParts);
            } else {
                $successMessage = "ℹ️ Importação concluída sem novos registos";
            }
            
            // Add info about not found employees
            if ($notFoundCount > 0) {
                $empIds = implode(', ', array_slice($notFoundEmployees, 0, 5));
                if ($notFoundCount > 5) {
                    $empIds .= '...';
                }
                
                $this->dispatch('notify', 
                    type: 'warning', 
                    message: __('attendance.messages.employees_not_found', [
                        'count' => $notFoundCount,
                        'ids' => $empIds
                    ])
                );
                
                \Log::warning('Employees not found during import', [
                    'count' => $notFoundCount,
                    'emp_ids' => $notFoundEmployees
                ]);
            }
            
            $this->dispatch('notify', 
                type: 'success', 
                message: $successMessage
            );
            
            if (!empty($errors) && count($errors) > $notFoundCount) {
                // Show other errors separately
                $otherErrors = array_filter($errors, function($error) {
                    return !str_contains($error, 'não encontrado');
                });
                if (!empty($otherErrors)) {
                    session()->flash('import_errors', $otherErrors);
                }
            }
            
            $this->closeImportModal();
            $this->dispatch('attendanceUpdated');
            
        } catch (\Exception $e) {
            \Log::error('DEBUG: Import failed with exception:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $this->importResults = [
                'success' => false,
                'message' => __('messages.import_failed') . ': ' . $e->getMessage(),
                'details' => []
            ];
            
            $this->dispatch('notify', 
                type: 'error', 
                message: __('messages.import_failed') . ': ' . $e->getMessage()
            );
        }
    }

    public function render()
    {
        $query = AttendanceModel::query()
            ->with('employee')
            ->when($this->search, function ($query) {
                return $query->whereHas('employee', function ($query) {
                    $query->where('full_name', 'like', "%{$this->search}%");
                });
            })
            ->when($this->filters['department_id'], function ($query) {
                return $query->whereHas('employee', function ($query) {
                    $query->where('department_id', $this->filters['department_id']);
                });
            })
            ->when($this->filters['status'], function ($query) {
                return $query->where('status', $this->filters['status']);
            })
            ->when($this->filters['start_date'], function ($query) {
                return $query->whereDate('date', '>=', $this->filters['start_date']);
            })
            ->when($this->filters['end_date'], function ($query) {
                return $query->whereDate('date', '<=', $this->filters['end_date']);
            });

        $attendances = $query->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        // Debug: verificar quantos registros estão sendo retornados
        \Log::info('Attendance query results', [
            'total_count' => $attendances->total(),
            'per_page' => $this->perPage,
            'current_page' => $attendances->currentPage(),
            'search' => $this->search,
            'filters' => $this->filters,
            'sort_field' => $this->sortField,
            'sort_direction' => $this->sortDirection
        ]);

        $employees = Employee::where('employment_status', 'active')->get();
        $departments = Department::where('is_active', true)->get();
        
        // Dados do calendário - sempre calcular fresh
        $calendarData = $this->getCalendarData();
        
        // Garantir que sempre retorna uma collection válida
        if (!$calendarData || !($calendarData instanceof \Illuminate\Support\Collection)) {
            $calendarData = collect();
        }
        
        $currentMonthName = Carbon::createFromDate($this->currentYear, $this->currentMonth, 1)->format('F Y');
        $daysInMonth = Carbon::createFromDate($this->currentYear, $this->currentMonth, 1)->daysInMonth;
        $startOfMonth = Carbon::createFromDate($this->currentYear, $this->currentMonth, 1);
        $startDayOfWeek = $startOfMonth->dayOfWeek; // 0 = Sunday, 1 = Monday, etc.

        return view('livewire.hr.attendance.attendance', [
            'attendances' => $attendances,
            'employees' => $employees,
            'departments' => $departments,
            'calendarData' => $calendarData,
            'currentMonthName' => $currentMonthName,
            'daysInMonth' => $daysInMonth,
            'startDayOfWeek' => $startDayOfWeek,
        ])->layout('layouts.livewire', [
            'title' => __('attendance.attendance_management')
        ]);
    }
}
