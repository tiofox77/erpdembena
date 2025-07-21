<?php

declare(strict_types=1);

namespace App\Livewire\HR;

use App\Models\HR\Attendance as AttendanceModel;
use App\Models\HR\Employee;
use App\Models\HR\Department;
use App\Models\HR\Shift;
use App\Models\HR\ShiftAssignment;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\WithPagination;

class Attendance extends Component
{
    use WithPagination;

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
    public $is_approved = false;
    
    // Campos para cálculo de pagamento
    public $hourly_rate;
    public $overtime_hours;
    public $overtime_rate;
    public $is_maternity_related = false;
    public $maternity_type;
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
    public $batchRemarks = '';
    public $shiftEmployees = [];
    public $shiftFilter = ''; // Para filtrar por shift
    public $selectAllEmployees = false; // Para marcar/desmarcar todos
    public $selectedShift = null; // Shift obrigatório selecionado
    public $availableShifts = []; // Shifts disponíveis
    
    // Listeners
    protected $listeners = ['refreshAttendance' => '$refresh'];

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
            'is_approved' => 'boolean',
            
            // Campos para cálculo de pagamento
            'hourly_rate' => 'nullable|numeric|min:0',
            'overtime_hours' => 'nullable|numeric|min:0',
            'overtime_rate' => 'nullable|numeric|min:0',
            'is_maternity_related' => 'boolean',
            'maternity_type' => 'nullable|string|required_if:is_maternity_related,true',
            'affects_payroll' => 'boolean',
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
        $this->filters['start_date'] = Carbon::today()->subDays(30)->format('Y-m-d');
        $this->filters['end_date'] = Carbon::today()->format('Y-m-d');
        $this->currentMonth = Carbon::now()->month;
        $this->currentYear = Carbon::now()->year;
    }

    public function create()
    {
        // Reset form fields
        $this->reset([
            'attendance_id', 'employee_id', 'date', 'time_in', 'time_out',
            'status', 'remarks', 'is_approved', 'hourly_rate', 'overtime_hours',
            'overtime_rate', 'is_maternity_related', 'maternity_type', 'affects_payroll'
        ]);
        
        // Set defaults for new attendance records
        $this->date = Carbon::today()->format('Y-m-d');
        $this->status = 'present'; // Default to present
        $this->is_approved = true; // Auto-approve new records
        $this->affects_payroll = true;
        $this->is_maternity_related = false;
        
        $this->showModal = true;
    }
    
    public function openCalendar()
    {
        // Abrir modal de batch attendance ligada ao calendário
        $this->selectedDate = Carbon::today()->format('Y-m-d');
        $this->loadShiftEmployees($this->selectedDate);
        $this->showCalendarModal = true;
        
        // Definir configurações padrão para batch attendance
        $this->batchStatus = 'present';
        $this->batchTimeIn = '09:00';
        $this->batchTimeOut = '17:00';
        $this->batchObservations = '';
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
        $this->is_approved = $attendance->is_approved;
        
        // Campos de pagamento
        $this->hourly_rate = $attendance->hourly_rate;
        $this->overtime_hours = $attendance->overtime_hours;
        $this->overtime_rate = $attendance->overtime_rate;
        $this->is_maternity_related = $attendance->is_maternity_related;
        $this->maternity_type = $attendance->maternity_type;
        $this->affects_payroll = $attendance->affects_payroll;

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

        // All new records are auto-approved by default
        if (!$this->isEditing) {
            // Force approve all new records
            $validatedData['is_approved'] = true;
            $validatedData['approved_by'] = auth()->id();
            $validatedData['status'] = $this->status ?: 'present'; // Ensure status is set
        } else {
            // For editing, keep the current approval status
            if ($this->is_approved) {
                $validatedData['approved_by'] = auth()->id();
            } else {
                $validatedData['approved_by'] = null;
            }
        }
        
        // Gerenciar campos específicos de maternidade
        if ($this->is_maternity_related) {
            $employee = Employee::find($this->employee_id);
            if ($employee && $employee->gender !== 'female') {
                $this->addError('is_maternity_related', 'Registos relacionados com maternidade só podem ser atribuídos a funcionárias mulher.');
                return;
            }
        } else {
            $validatedData['maternity_type'] = null;
        }

        if ($this->isEditing) {
            $attendance = AttendanceModel::find($this->attendance_id);
            $attendance->update($validatedData);
            session()->flash('message', 'Registo de presença atualizado com sucesso.');
        } else {
            // Check if there's already an attendance record for this employee on this date
            $exists = AttendanceModel::where('employee_id', $this->employee_id)
                ->where('date', $this->date)
                ->exists();

            if ($exists) {
                session()->flash('error', 'Já existe um registo de presença para este funcionário nesta data.');
                return;
            }
            
            // Para novos registos, se não for especificado, usar o valor base do funcionário
            if (empty($this->hourly_rate) && $this->employee_id) {
                $employee = Employee::find($this->employee_id);
                if ($employee) {
                    // Converter o salário base para valor hora (assumindo 8 horas/dia, 22 dias/mês)
                    $validatedData['hourly_rate'] = $employee->base_salary / (8 * 22);
                    // Valor de hora extra é 1.5x o valor normal
                    $validatedData['overtime_rate'] = $validatedData['hourly_rate'] * 1.5;
                }
            }

            AttendanceModel::create($validatedData);
            session()->flash('message', 'Registo de presença criado com sucesso.');
        }

        $this->showModal = false;
        $this->reset([
            'attendance_id', 'employee_id', 'date', 'time_in', 'time_out',
            'status', 'remarks', 'is_approved', 'hourly_rate', 'overtime_hours',
            'overtime_rate', 'is_maternity_related', 'maternity_type', 'affects_payroll'
        ]);
    }

    public function delete()
    {
        $attendance = AttendanceModel::find($this->attendance_id);
        $attendance->delete();
        $this->showDeleteModal = false;
        session()->flash('message', 'Attendance deleted successfully.');
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetValidation();
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
    
    public function selectDate($date)
    {
        $this->selectedDate = $date;
        $this->loadAvailableShifts($date);
        $this->showCalendarModal = true;
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
    
    /**
     * Load employees for selected shift considering rotation
     */
    public function loadEmployeesForSelectedShift()
    {
        if (!$this->selectedShift || !$this->selectedDate) {
            $this->shiftEmployees = [];
            return;
        }
        
        $employeesWithRotation = $this->getEmployeesWithRotationInfo($this->selectedShift, $this->selectedDate);
        
        $this->shiftEmployees = collect($employeesWithRotation)->map(function($employee) {
            // Check if attendance already exists for this date
            $existingAttendance = AttendanceModel::where('employee_id', $employee['id'])
                ->whereDate('date', $this->selectedDate)
                ->first();
            
            return array_merge($employee, [
                'already_marked' => $existingAttendance ? true : false,
                'existing_status' => $existingAttendance ? $existingAttendance->status : null,
            ]);
        })->toArray();
    }
    
    public function selectShift($shiftId)
    {
        $this->selectedShift = $shiftId;
        
        // Carregar dados do shift selecionado
        $shift = \App\Models\HR\Shift::find($shiftId);
        
        if ($shift) {
            // Preencher automaticamente os horários baseado no shift
            $this->batchTimeIn = $shift->start_time->format('H:i');
            $this->batchTimeOut = $shift->end_time->format('H:i');
        }
        
        // Carregar funcionários do shift selecionado
        $this->loadShiftEmployees($this->selectedDate);
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
    
    public function updatedSelectedShift()
    {
        if ($this->selectedShift) {
            $this->loadEmployeesForSelectedShift();
        }
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
            ->with([
                'department:id,name',
                'shiftAssignments' => function($query) use ($date) {
                    $query->where('shift_id', $this->selectedShift)
                        ->whereDate('start_date', '<=', $date)
                        ->where(function($q) use ($date) {
                            $q->whereNull('end_date')
                              ->orWhereDate('end_date', '>=', $date);
                        })
                        ->with('shift:id,name,start_time,end_time');
                }
            ])
            ->whereHas('shiftAssignments', function($query) use ($date) {
                $query->where('shift_id', $this->selectedShift)
                    ->whereDate('start_date', '<=', $date)
                    ->where(function($q) use ($date) {
                        $q->whereNull('end_date')
                          ->orWhereDate('end_date', '>=', $date);
                    });
            })
            ->get();
            
        // Se não há funcionários com assignments, carregar todos os funcionários ativos para debug
        if ($employees->isEmpty()) {
            $employees = Employee::where('employment_status', 'active')
                ->select('id', 'full_name', 'department_id')
                ->with('department:id,name')
                ->get();
        }
            
        // Verificar quais funcionários já têm presença registada neste dia
        $existingAttendances = AttendanceModel::whereDate('date', $date)
            ->pluck('employee_id')
            ->toArray();
            
        // Processar dados dos funcionários
        $this->shiftEmployees = $employees->map(function($employee) use ($existingAttendances) {
            $currentShift = $employee->shiftAssignments->first();
            
            // Obter dados do shift selecionado
            $selectedShiftData = \App\Models\HR\Shift::find($this->selectedShift);
            
            return [
                'id' => $employee->id,
                'full_name' => $employee->full_name,
                'department_id' => $employee->department_id,
                'department' => $employee->department,
                'shift_id' => $this->selectedShift,
                'shift_name' => $selectedShiftData ? $selectedShiftData->name : __('attendance.no_shift'),
                'shift_time' => $selectedShiftData 
                    ? $selectedShiftData->start_time->format('H:i') . ' - ' . $selectedShiftData->end_time->format('H:i')
                    : '',
                'has_attendance' => in_array($employee->id, $existingAttendances),
            ];
        })->toArray();
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
            session()->flash('error', __('attendance.select_shift_first'));
            return;
        }
        
        if (empty($this->selectedEmployees)) {
            session()->flash('error', __('attendance.no_employees_selected'));
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
                $hourlyRate = $baseSalary > 0 ? $baseSalary / (8 * 22) : 0; // 8 horas/dia, 22 dias/mês
                $overtimeRate = $hourlyRate * 1.5;
                
                \Log::info('Dados do funcionário obtidos', [
                    'employee' => $employee->full_name,
                    'baseSalary' => $baseSalary,
                    'hourlyRate' => $hourlyRate,
                    'overtimeRate' => $overtimeRate
                ]);
                
                // Criar dados para salvar
                $attendanceData = [
                    'employee_id' => $employeeId,
                    'date' => Carbon::parse($this->selectedDate)->format('Y-m-d'),
                    'status' => $this->batchStatus,
                    'remarks' => $this->batchRemarks,
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
            session()->flash('message', "Registadas {$successCount} presenças com sucesso.");
            
            // Fechar a modal após salvar com sucesso
            $this->closeCalendarModal();
            
            // Forçar atualização dos dados do calendário
            $this->calendarData = null; // Resetar cache se existir
            
            // Emitir evento para atualizar a interface
            $this->dispatch('attendanceUpdated');
            \Log::info('Presenças registadas com sucesso', ['count' => $successCount]);
        }
        
        if (!empty($errors)) {
            session()->flash('errors', $errors);
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
    
    public function getCalendarData()
    {
        $startOfMonth = Carbon::createFromDate($this->currentYear, $this->currentMonth, 1);
        $endOfMonth = $startOfMonth->copy()->endOfMonth();
        
        // Debug: log para verificar o período sendo consultado
        \Log::info('Carregando dados do calendário', [
            'startOfMonth' => $startOfMonth->format('Y-m-d'),
            'endOfMonth' => $endOfMonth->format('Y-m-d')
        ]);
        
        // Obter dados de presença para o mês usando DB::table para evitar conversão automática
        $attendances = \DB::table('attendances')
            ->whereBetween('date', [$startOfMonth->format('Y-m-d'), $endOfMonth->format('Y-m-d')])
            ->selectRaw('DATE(date) as date, COUNT(*) as count, status')
            ->groupBy('date', 'status')
            ->get();
            
        // Debug: log dos dados encontrados
        \Log::info('Dados de presença encontrados', [
            'total_records' => $attendances->count(),
            'records' => $attendances->toArray()
        ]);
        
        // Converter para collection e agrupar por data
        $groupedAttendances = $attendances->groupBy('date');
        
        // Calcular estatísticas agregadas por dia
        $calendarData = collect();
        
        foreach ($groupedAttendances as $date => $dayAttendances) {
            $stats = [
                'date' => $date,
                'total_attendances' => $dayAttendances->sum('count'),
                'present' => $dayAttendances->where('status', 'present')->sum('count'),
                'absent' => $dayAttendances->where('status', 'absent')->sum('count'),
                'late' => $dayAttendances->where('status', 'late')->sum('count'),
                'half_day' => $dayAttendances->where('status', 'half_day')->sum('count'),
                'leave' => $dayAttendances->where('status', 'leave')->sum('count'),
                'sick_leave' => $dayAttendances->where('status', 'sick_leave')->sum('count'),
                'vacation' => $dayAttendances->where('status', 'vacation')->sum('count'),
                'overtime' => $dayAttendances->where('status', 'overtime')->sum('count'),
            ];
            
            // Calcular percentagem de presença
            $stats['attendance_rate'] = $stats['total_attendances'] > 0 
                ? round(($stats['present'] / $stats['total_attendances']) * 100, 1) 
                : 0;
                
            $calendarData->put($date, $stats);
        }
        
        // Debug: log dos dados do calendário calculados
        \Log::info('Dados do calendário calculados', [
            'calendar_data_count' => $calendarData->count(),
            'calendar_data' => $calendarData->toArray()
        ]);
        
        return $calendarData;
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

        $employees = Employee::where('employment_status', 'active')->get();
        $departments = Department::where('is_active', true)->get();
        
        // Dados do calendário
        $calendarData = $this->getCalendarData();
        $currentMonthName = Carbon::createFromDate($this->currentYear, $this->currentMonth, 1)->format('F Y');
        $daysInMonth = Carbon::createFromDate($this->currentYear, $this->currentMonth, 1)->daysInMonth;
        $startOfMonth = Carbon::createFromDate($this->currentYear, $this->currentMonth, 1);
        $startDayOfWeek = $startOfMonth->dayOfWeek; // 0 = Sunday, 1 = Monday, etc.

        return view('livewire.hr.attendance', [
            'attendances' => $attendances,
            'employees' => $employees,
            'departments' => $departments,
            'calendarData' => $calendarData,
            'currentMonthName' => $currentMonthName,
            'daysInMonth' => $daysInMonth,
            'startDayOfWeek' => $startDayOfWeek,
        ]);
    }
}
