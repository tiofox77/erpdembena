<?php

declare(strict_types=1);

namespace App\Livewire\HR;

use App\Models\HR\Attendance as AttendanceModel;
use App\Models\HR\Department;
use App\Models\HR\Employee;
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

    public function create()
    {
        $this->reset([
            'attendance_id', 'employee_id', 'date', 'time_in', 'time_out',
            'status', 'remarks', 'hourly_rate', 'overtime_hours', 'overtime_rate',
            'is_maternity_related', 'maternity_type', 'affects_payroll'
        ]);
        $this->date = Carbon::today()->format('Y-m-d');
        $this->status = 'present';
        $this->is_approved = false;
        $this->affects_payroll = true;
        $this->is_maternity_related = false;
        $this->isEditing = false;
        $this->showModal = true;
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

        // Add the current user as approver if is_approved is true
        if ($this->is_approved) {
            $validatedData['approved_by'] = auth()->id();
        } else {
            $validatedData['approved_by'] = null;
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

        return view('livewire.hr.attendance', [
            'attendances' => $attendances,
            'employees' => $employees,
            'departments' => $departments,
        ]);
    }
}
