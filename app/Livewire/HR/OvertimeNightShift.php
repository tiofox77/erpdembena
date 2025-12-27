<?php

declare(strict_types=1);

namespace App\Livewire\HR;

use App\Models\HR\Employee;
use App\Models\HR\OvertimeRecord;
use App\Models\HR\HRSetting;
use App\Models\HR\ShiftAssignment;
use App\Models\HR\Shift;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class OvertimeNightShift extends Component
{
    use WithPagination;
    
    // Propriedades do formulário
    public ?int $overtime_id = null;
    public ?int $employee_id = null;
    public ?int $employee_shift_id = null;
    public ?string $employee_shift_name = null;
    public ?string $date = null;
    public ?string $start_time = null;
    public ?string $end_time = null;
    public ?float $rate = null;
    public ?float $hourly_rate = null;
    public ?float $daily_rate = null;
    public ?float $days = null;
    public ?float $hours = null;
    public ?float $amount = null;
    public ?string $description = null;
    public string $status = 'pending';
    public ?float $direct_hours = null;
    public string $input_type = 'days'; // FIXO: days
    public string $period_type = 'day';
    public float $additionalHoursMultiplier = 1.375;
    public bool $is_night_shift = true; // SEMPRE TRUE para night shift
    public ?float $night_shift_multiplier = null;
    public ?string $created_at = null;
    public ?string $updated_at = null;
    public ?string $approver_name = null;
    public ?string $creator_name = null;
    
    // Limites para horas extras
    public float $dailyLimit = 2.0;
    public float $monthlyLimit = 48.0;
    public float $yearlyLimit = 200.0;
    
    // Estados da modal
    public bool $showModal = false;
    public bool $showViewModal = false;
    public bool $showDeleteModal = false;
    public bool $isEditing = false;
    public float $employee_salary = 0.0;
    public string $employee_name = '';
    
    // Filtros
    public array $filters = [
        'search' => '',
        'status' => '',
        'date_from' => '',
        'date_to' => '',
        'employee_id' => '',
    ];
    
    // Ordenação
    public string $sortField = 'date';
    public string $sortDirection = 'desc';
    
    protected $listeners = [
        'refreshOvertimeRecords' => '$refresh',
        'approveOvertime' => 'approve',
        'rejectOvertime' => 'reject'
    ];

    public function approve(int $id): void
    {
        try {
            $record = OvertimeRecord::findOrFail($id);
            
            if ($record->status !== 'pending') {
                session()->flash('error', __('messages.overtime_already_processed'));
                return;
            }
            
            $record->update([
                'status' => 'approved',
                'approved_by' => Auth::id(),
                'approved_at' => now(),
            ]);
            
            session()->flash('message', __('messages.overtime_approved_successfully'));
            
        } catch (\Exception $e) {
            session()->flash('error', __('messages.error_approving_overtime'));
        }
    }

    public function reject(int $id): void
    {
        try {
            $record = OvertimeRecord::findOrFail($id);
            
            if ($record->status !== 'pending') {
                session()->flash('error', __('messages.overtime_already_processed'));
                return;
            }
            
            $record->update([
                'status' => 'rejected',
                'approved_by' => Auth::id(),
                'approved_at' => now(),
            ]);
            
            session()->flash('message', __('messages.overtime_rejected_successfully'));
            
        } catch (\Exception $e) {
            session()->flash('error', __('messages.error_rejecting_overtime'));
        }
    }
    
    public function mount(): void
    {
        $this->date = now()->format('Y-m-d');
        $this->is_night_shift = true; // Força sempre TRUE
        $this->loadHRSettings();
    }
    
    private function loadHRSettings(): void
    {
        $this->dailyLimit = (float) HRSetting::get('overtime_daily_limit', 2.0);
        $this->monthlyLimit = (float) HRSetting::get('overtime_monthly_limit', 48.0);
        $this->yearlyLimit = (float) HRSetting::get('overtime_yearly_limit', 200.0);
        $this->night_shift_multiplier = (float) HRSetting::get('night_shift_multiplier', 1.25);
    }
    
    protected function rules(): array
    {
        $rules = [
            'employee_id' => 'required|exists:employees,id',
            'date' => 'required|date',
            'rate' => 'required|numeric|min:0',
            'description' => 'nullable|string|max:255',
            'status' => 'required|in:pending,approved,rejected',
            'input_type' => 'required|in:days',
            'days' => 'required|numeric|min:0.5|max:31',
        ];
        
        // Removido - agora só usa dias
        
        return $rules;
    }
    
    protected function validationAttributes(): array
    {
        return [
            'employee_id' => __('messages.employee'),
            'date' => __('messages.date'),
            'start_time' => __('messages.start_time'),
            'end_time' => __('messages.end_time'),
            'rate' => __('messages.hourly_rate'),
            'description' => __('messages.description'),
            'status' => __('messages.status'),
        ];
    }
    
    public function getOvertimeSummaryProperty()
    {
        $query = OvertimeRecord::query()
            ->where('is_night_shift', true) // FILTRO FIXO para night shift
            ->when($this->filters['search'] ?? false, function ($query, $search) {
                return $query->whereHas('employee', function ($subquery) use ($search) {
                    $subquery->where('full_name', 'like', '%' . $search . '%');
                });
            })
            ->when($this->filters['employee_id'] ?? false, function ($query, $employeeId) {
                return $query->where('employee_id', $employeeId);
            })
            ->when($this->filters['status'] ?? false, function ($query, $status) {
                return $query->where('status', $status);
            })
            ->when($this->filters['date_from'] ?? false, function ($query, $dateFrom) {
                return $query->where('date', '>=', $dateFrom);
            })
            ->when($this->filters['date_to'] ?? false, function ($query, $dateTo) {
                return $query->where('date', '<=', $dateTo);
            });

        return [
            'total_records' => $query->count(),
            'total_hours' => $query->sum('hours'),
            'total_amount' => $query->sum('amount'),
            'approved_hours' => $query->where('status', 'approved')->sum('hours'),
            'approved_amount' => $query->where('status', 'approved')->sum('amount'),
            'pending_hours' => $query->where('status', 'pending')->sum('hours'),
            'pending_amount' => $query->where('status', 'pending')->sum('amount'),
        ];
    }

    public function render()
    {
        $employees = Employee::orderBy('full_name')->get();
        
        $overtimeRecordsQuery = OvertimeRecord::with(['employee', 'approver'])
            ->where('is_night_shift', true) // FILTRO FIXO para night shift apenas
            ->when($this->filters['search'] ?? false, function ($query, $search) {
                return $query->whereHas('employee', function ($subquery) use ($search) {
                    $subquery->where('full_name', 'like', '%' . $search . '%');
                });
            })
            ->when($this->filters['employee_id'] ?? false, function ($query, $employeeId) {
                return $query->where('employee_id', $employeeId);
            })
            ->when($this->filters['status'] ?? false, function ($query, $status) {
                return $query->where('status', $status);
            })
            ->when($this->filters['date_from'] ?? false, function ($query, $dateFrom) {
                return $query->where('date', '>=', $dateFrom);
            })
            ->when($this->filters['date_to'] ?? false, function ($query, $dateTo) {
                return $query->where('date', '<=', $dateTo);
            })
            ->orderBy($this->sortField, $this->sortDirection);
        
        $perPage = !empty($this->filters['employee_id']) ? 25 : 10;
        $overtimeRecords = $overtimeRecordsQuery->paginate($perPage);
        
        return view('livewire.hr.overtime-night-shift.overtime-night-shift', [
            'overtimeRecords' => $overtimeRecords,
            'employees' => $employees,
            'summary' => $this->overtimeSummary,
        ]);
    }
    
    public function sortBy($field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }
    
    public function create(): void
    {
        $this->reset(['overtime_id', 'employee_id', 'date', 'start_time', 'end_time', 'rate', 'hours', 'amount', 'description', 'status', 'days', 'daily_rate']);
        $this->status = 'pending';
        $this->date = date('Y-m-d');
        $this->is_night_shift = true;
        $this->input_type = 'days';
        $this->isEditing = false;
        $this->showModal = true;
    }
    
    public function edit(int $id): void
    {
        $overtime = OvertimeRecord::findOrFail($id);
        
        // Verifica se é realmente night shift
        if (!$overtime->is_night_shift) {
            session()->flash('error', 'Este registro não é de turno noturno.');
            return;
        }
        
        $this->overtime_id = $id;
        $this->isEditing = true;
        $this->employee_id = (int) $overtime->employee_id;
        $this->date = $overtime->date->format('Y-m-d');
        $this->days = (float) ($overtime->direct_hours ?? 0);
        $this->daily_rate = (float) $overtime->rate;
        $this->hours = (float) $overtime->hours;
        $this->amount = (float) $overtime->amount;
        $this->description = $overtime->description;
        $this->status = $overtime->status;
        $this->input_type = 'days';
        $this->is_night_shift = true;
        
        if ($this->employee_id) {
            $this->loadEmployeeInfo();
        }
        
        $this->showModal = true;
    }
    
    private function loadEmployeeInfo(): void
    {
        $employee = Employee::with(['shiftAssignments.shift'])->find($this->employee_id);
        
        if ($employee) {
            $this->employee_name = $employee->full_name ?? '';
            $this->employee_salary = (float) ($employee->base_salary ?? 0.0);
            
            $currentShift = $employee->shiftAssignments()
                ->with('shift')
                ->where(function($query) {
                    $today = now()->toDateString();
                    $query->where('start_date', '<=', $today)
                          ->where(function($q) use ($today) {
                              $q->where('end_date', '>=', $today)
                                ->orWhereNull('end_date');
                          });
                })
                ->first();
                
            if ($currentShift && $currentShift->shift) {
                $this->employee_shift_id = $currentShift->shift->id;
                $this->employee_shift_name = $currentShift->shift->name ?? '';
            } else {
                $this->employee_shift_id = 0;
                $this->employee_shift_name = 'Sem turno atribuído';
            }
            
            $this->loadHourlyRate();
        }
    }
    
    private function loadHourlyRate(): void
    {
        if (!$this->employee_id) {
            return;
        }
        
        try {
            $employee = Employee::find($this->employee_id);
            
            if ($employee) {
                $monthlyWorkingDays = (int) HRSetting::get('monthly_working_days', 22);
                $dailyWorkHours = (float) HRSetting::get('daily_work_hours', 8);
                $monthlyHours = $monthlyWorkingDays * $dailyWorkHours;
                
                if (isset($employee->hourly_rate) && $employee->hourly_rate > 0) {
                    $this->hourly_rate = (float) $employee->hourly_rate;
                } else {
                    if (isset($employee->base_salary) && $employee->base_salary > 0) {
                        $this->hourly_rate = round($employee->base_salary / $monthlyHours, 2);
                    } else {
                        $this->hourly_rate = (float) HRSetting::get('default_hourly_rate', 10.00);
                    }
                }
                
                // Para night shift, calcula daily rate
                $monthlyWorkingDays = (int) HRSetting::get('monthly_working_days', 22);
                $this->daily_rate = round($employee->base_salary / $monthlyWorkingDays, 2);
                $this->rate = $this->daily_rate;
            }
        } catch (\Exception $e) {
            $monthlyWorkingDays = (int) HRSetting::get('monthly_working_days', 22);
            $this->daily_rate = round(10000 / $monthlyWorkingDays, 2);
            $this->rate = $this->daily_rate;
        }
    }
    
    public function calculateHoursAndAmount(): void
    {
        try {
            $this->resetErrorBag(['days', 'calculation']);
            
            if (!$this->employee_id || !$this->date) {
                return;
            }
            
            if (!$this->daily_rate) {
                $this->loadHourlyRate();
            }
            
            if (!$this->days || $this->days <= 0) {
                $this->hours = 0;
                $this->amount = 0;
                return;
            }
            
            // Calcular valor total: dias × daily_rate
            $totalValue = $this->days * $this->daily_rate;
            
            // Night shift overtime = 20% do valor total
            $this->amount = round($totalValue * 0.20, 2);
            
            // Hours não é usado mas mantemos para compatibilidade
            $this->hours = $this->days * 8; // Assumindo 8h por dia
            
        } catch (\Exception $e) {
            $this->addError('calculation', __('messages.overtime_calculation_error') . ': ' . $e->getMessage());
        }
    }
    
    public function updatedEmployeeId(): void
    {
        if ($this->employee_id) {
            $this->loadEmployeeInfo();
        }
        $this->calculateHoursAndAmount();
    }
    
    public function updatedDate(): void
    {
        $this->loadHourlyRate();
        $this->calculateHoursAndAmount();
    }
    
    public function updatedStartTime(): void
    {
        $this->calculateHoursAndAmount();
    }
    
    public function updatedEndTime(): void
    {
        $this->calculateHoursAndAmount();
    }
    
    public function updatedDays(): void
    {
        $this->calculateHoursAndAmount();
    }
    
    public function updatedDirectHours(): void
    {
        $this->calculateHoursAndAmount();
    }
    
    public function updatedRate(): void
    {
        $this->calculateHoursAndAmount();
    }
    
    public function save(): void
    {
        $this->is_night_shift = true; // Garante que sempre é TRUE
        $this->validate();
        
        if ($this->overtime_id) {
            $overtime = OvertimeRecord::findOrFail($this->overtime_id);
        } else {
            $overtime = new OvertimeRecord();
            $overtime->created_by = Auth::id();
        }
        
        $overtime->employee_id = $this->employee_id;
        $overtime->date = $this->date;
        $overtime->hours = $this->hours;
        $overtime->rate = $this->daily_rate;
        $overtime->amount = $this->amount;
        $overtime->description = $this->description;
        $overtime->status = $this->status;
        $overtime->input_type = 'days';
        $overtime->is_night_shift = true;
        $overtime->direct_hours = $this->days; // Armazena dias em direct_hours
        $overtime->start_time = null;
        $overtime->end_time = null;
        $overtime->period_type = 'day';
        
        if ($this->status === 'approved' && $overtime->approved_by === null) {
            $overtime->approved_by = Auth::id();
            $overtime->approved_at = now();
        }
        
        $overtime->save();
        
        $this->showModal = false;
        $this->resetErrorBag();
        $this->reset(['overtime_id', 'employee_id', 'date', 'start_time', 'end_time', 'rate', 'hours', 'amount', 'description', 'status', 'days', 'daily_rate']);
        $this->is_night_shift = true;
        $this->input_type = 'days';
        
        if ($this->isEditing) {
            session()->flash('message', __('messages.overtime_updated'));
        } else {
            session()->flash('message', __('messages.overtime_created'));
        }
    }
    
    public function view(int $id): void
    {
        $overtime = OvertimeRecord::with(['employee', 'approver', 'creator'])->findOrFail($id);
        
        if (!$overtime->is_night_shift) {
            session()->flash('error', 'Este registro não é de turno noturno.');
            return;
        }
        
        $this->overtime_id = $id;
        $this->employee_id = (int) $overtime->employee_id;
        $this->employee_name = $overtime->employee->full_name ?? '';
        $this->date = $overtime->date->format('Y-m-d');
        $this->days = (float) ($overtime->direct_hours ?? 0);
        $this->direct_hours = (float) ($overtime->direct_hours ?? 0); // Para o view modal
        $this->daily_rate = (float) $overtime->rate;
        $this->rate = (float) $overtime->rate; // Para o view modal
        $this->hours = (float) $overtime->hours;
        $this->amount = (float) $overtime->amount;
        $this->description = $overtime->description;
        $this->status = $overtime->status;
        $this->input_type = 'days';
        $this->is_night_shift = true;
        
        $this->approver_name = $overtime->approver->name ?? null;
        $this->creator_name = $overtime->creator->name ?? null;
        $this->created_at = $overtime->created_at ? $overtime->created_at->format('Y-m-d H:i:s') : null;
        $this->updated_at = $overtime->updated_at ? $overtime->updated_at->format('Y-m-d H:i:s') : null;
        
        $this->showViewModal = true;
    }
    
    public function closeModal(): void
    {
        $this->showModal = false;
        $this->isEditing = false;
        $this->resetErrorBag();
        $this->reset(['overtime_id', 'employee_id', 'employee_shift_id', 'employee_shift_name', 'date', 'rate', 'hours', 'amount', 'description', 'status', 'days', 'daily_rate']);
        $this->is_night_shift = true;
        $this->input_type = 'days';
    }
    
    public function closeViewModal(): void
    {
        $this->showViewModal = false;
    }
    
    public function confirmDelete(int $id): void
    {
        $this->overtime_id = $id;
        $this->showDeleteModal = true;
    }
    
    public function delete(): void
    {
        OvertimeRecord::destroy($this->overtime_id);
        $this->showDeleteModal = false;
        $this->overtime_id = null;
        session()->flash('message', __('messages.overtime_deleted'));
    }
}
