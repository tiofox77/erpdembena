<?php

namespace App\Livewire\HR;

use App\Models\HR\Shift;
use App\Models\HR\ShiftAssignment;
use App\Models\HR\Department;
use App\Models\HR\Employee;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\WithPagination;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Storage;

class ShiftManagement extends Component
{
    use WithPagination;

    // Tabs
    public $activeTab = 'shifts';

    // Shifts
    public $shift_id;
    public $name;
    public $start_time;
    public $end_time;
    public $break_duration;
    public $description;
    public $is_night_shift = false;
    public $is_active = true;

    // Shift Assignments
    public $assignment_id;
    public $employee_id;
    public $shift_id_assignment;
    public $start_date;
    public $end_date;
    public $is_permanent = false;
    public $rotation_pattern;
    public $notes;

    // Filters
    public $searchShift = '';
    public $searchAssignment = '';
    public $perPage = 10;
    public $sortFieldShift = 'name';
    public $sortDirectionShift = 'asc';
    public $sortFieldAssignment = 'start_date';
    public $sortDirectionAssignment = 'desc';
    public $filters = [
        'department_id' => '',
        'shift_id' => '',
        'is_active' => '',
    ];

    // Modal flags
    public $showShiftModal = false;
    public $showAssignmentModal = false;
    public $showDeleteModal = false;
    public $isEditing = false;

    // Listeners
    protected $listeners = ['refreshShifts' => '$refresh'];

    // Rules for shifts
    protected function shiftRules()
    {
        return [
            'name' => 'required|string|max:255',
            'start_time' => 'required',
            'end_time' => 'required',
            'break_duration' => 'nullable|integer|min:0',
            'description' => 'nullable|string',
            'is_night_shift' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    // Rules for shift assignments
    protected function assignmentRules()
    {
        return [
            'employee_id' => 'required|exists:employees,id',
            'shift_id_assignment' => 'required|exists:shifts,id',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'is_permanent' => 'boolean',
            'rotation_pattern' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ];
    }

    public function setActiveTab($tab)
    {
        $this->activeTab = $tab;
    }

    public function sortByShift($field)
    {
        if ($this->sortFieldShift === $field) {
            $this->sortDirectionShift = $this->sortDirectionShift === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortFieldShift = $field;
            $this->sortDirectionShift = 'asc';
        }
    }

    public function sortByAssignment($field)
    {
        if ($this->sortFieldAssignment === $field) {
            $this->sortDirectionAssignment = $this->sortDirectionAssignment === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortFieldAssignment = $field;
            $this->sortDirectionAssignment = 'asc';
        }
    }

    public function updatingSearchShift()
    {
        $this->resetPage();
    }

    public function updatingSearchAssignment()
    {
        $this->resetPage();
    }

    public function updatingFilters()
    {
        $this->resetPage();
    }

    // Shift methods
    public function openShiftModal()
    {
        $this->reset([
            'shift_id', 'name', 'start_time', 'end_time', 'break_duration',
            'description', 'is_night_shift'
        ]);
        $this->is_active = true;
        $this->isEditing = false;
        $this->showShiftModal = true;
    }

    public function createShift()
    {
        $this->openShiftModal();
    }

    public function editShift(Shift $shift)
    {
        $this->shift_id = $shift->id;
        $this->name = $shift->name;
        $this->start_time = $shift->start_time->format('H:i');
        $this->end_time = $shift->end_time->format('H:i');
        $this->break_duration = $shift->break_duration;
        $this->description = $shift->description;
        $this->is_night_shift = $shift->is_night_shift;
        $this->is_active = $shift->is_active;

        $this->isEditing = true;
        $this->showShiftModal = true;
    }

    public function saveShift()
    {
        $validatedData = $this->validate($this->shiftRules());

        if ($this->isEditing) {
            $shift = Shift::find($this->shift_id);
            $shift->update($validatedData);
            session()->flash('message', 'Shift updated successfully.');
        } else {
            Shift::create($validatedData);
            session()->flash('message', 'Shift created successfully.');
        }

        $this->showShiftModal = false;
        $this->reset([
            'shift_id', 'name', 'start_time', 'end_time', 'break_duration',
            'description', 'is_night_shift'
        ]);
        $this->is_active = true;
    }

    // Shift Assignment methods
    public function openAssignmentModal()
    {
        $this->reset([
            'assignment_id', 'employee_id', 'shift_id_assignment', 'start_date',
            'end_date', 'is_permanent', 'rotation_pattern', 'notes'
        ]);
        $this->start_date = Carbon::today()->format('Y-m-d');
        $this->is_permanent = false;
        $this->isEditing = false;
        $this->showAssignmentModal = true;
    }

    public function createAssignment()
    {
        $this->openAssignmentModal();
    }

    public function editAssignment(ShiftAssignment $assignment)
    {
        $this->assignment_id = $assignment->id;
        $this->employee_id = $assignment->employee_id;
        $this->shift_id_assignment = $assignment->shift_id;
        $this->start_date = $assignment->start_date->format('Y-m-d');
        $this->end_date = $assignment->end_date ? $assignment->end_date->format('Y-m-d') : null;
        $this->is_permanent = $assignment->is_permanent;
        $this->rotation_pattern = $assignment->rotation_pattern;
        $this->notes = $assignment->notes;

        $this->isEditing = true;
        $this->showAssignmentModal = true;
    }

    // Este método foi removido por ser duplicado

    public function saveAssignment()
    {
        // Corrigir o problema com o shift_id_assignment
        $this->validate($this->assignmentRules());
        
        // Preparar dados validados para salvar
        $validatedData = [
            'employee_id' => $this->employee_id,
            'shift_id' => $this->shift_id_assignment,
            'start_date' => $this->start_date,
            'end_date' => $this->is_permanent ? null : $this->end_date,
            'is_permanent' => $this->is_permanent,
            'rotation_pattern' => $this->rotation_pattern,
            'notes' => $this->notes,
        ];
        
        // Add the current user as assigned_by
        $validatedData['assigned_by'] = auth()->id();

        if ($this->isEditing) {
            $assignment = ShiftAssignment::find($this->assignment_id);
            $assignment->update($validatedData);
            session()->flash('message', 'Shift assignment updated successfully.');
        } else {
            ShiftAssignment::create($validatedData);
            session()->flash('message', 'Shift assignment created successfully.');
        }

        $this->showAssignmentModal = false;
        $this->reset([
            'assignment_id', 'employee_id', 'shift_id_assignment', 'shift_id', 'start_date',
            'end_date', 'is_permanent', 'rotation_pattern', 'notes'
        ]);
    }

    public function confirmDeleteShift($id)
    {
        $this->confirmDelete($id, 'shift');
    }

    public function confirmDeleteAssignment($id)
    {
        $this->confirmDelete($id, 'assignment');
    }

    public function confirmDelete($id, $type)
    {
        $this->showDeleteModal = true;
        if ($type === 'shift') {
            $this->shift_id = $id;
            $this->assignment_id = null;
        } else {
            $this->assignment_id = $id;
            $this->shift_id = null;
        }
    }

    public function delete()
    {
        if ($this->shift_id) {
            $shift = Shift::find($this->shift_id);
            $shift->delete();
            session()->flash('message', 'Shift deleted successfully.');
        } elseif ($this->assignment_id) {
            $assignment = ShiftAssignment::find($this->assignment_id);
            $assignment->delete();
            session()->flash('message', 'Shift assignment deleted successfully.');
        }
        
        $this->showDeleteModal = false;
    }

    public function closeModal()
    {
        $this->showShiftModal = false;
        $this->showAssignmentModal = false;
        $this->resetErrorBag();
    }

    public function closeShiftModal()
    {
        $this->showShiftModal = false;
        $this->resetErrorBag();
    }

    public function closeAssignmentModal()
    {
        $this->showAssignmentModal = false;
        $this->resetErrorBag();
    }

    public function closeDeleteModal()
    {
        $this->showDeleteModal = false;
    }
    
    public function resetFilters()
    {
        $this->reset('filters');
        $this->searchShift = '';
        $this->searchAssignment = '';
        $this->resetPage();
    }

    public function exportShiftsPDF()
    {
        // Mostrar notificação antes de iniciar o download
        $this->dispatch('notify', type: 'success', message: __('livewire/hr/shifts.pdf_generating'));
        
        $shifts = Shift::when($this->searchShift, function ($query) {
                return $query->where('name', 'like', "%{$this->searchShift}%");
            })
            ->when($this->filters['is_active'] !== '', function ($query) {
                return $query->where('is_active', $this->filters['is_active']);
            })
            ->orderBy($this->sortFieldShift, $this->sortDirectionShift)
            ->get();

        $companyName = \App\Models\Setting::get('company_name', 'ERP DEMBENA');
        
        // Corrigindo para obter o logo dos settings do sistema
        $companyLogo = \App\Models\Setting::get('company_logo');
        $logoPath = $companyLogo ? public_path('storage/' . $companyLogo) : null;
        $hasLogo = $logoPath && file_exists($logoPath);

        $data = [
            'shifts' => $shifts,
            'companyName' => $companyName,
            'hasLogo' => $hasLogo,
            'logoPath' => $logoPath,
            'date' => Carbon::now()->format('d/m/Y H:i'),
            'title' => 'Shifts Report'
        ];

        $pdf = PDF::loadView('pdf.shifts-report', $data);

        return response()->streamDownload(
            fn () => print($pdf->output()),
            'shifts_report_' . Carbon::now()->format('Y-m-d') . '.pdf'
        );
    }

    public function exportAssignmentsPDF()
    {
        // Mostrar notificação antes de iniciar o download
        $this->dispatch('notify', type: 'success', message: __('livewire/hr/shifts.assignments_pdf_generating'));
        
        $shiftAssignments = ShiftAssignment::with(['employee', 'shift'])
            ->when($this->searchAssignment, function ($query) {
                return $query->whereHas('employee', function ($query) {
                    $query->where('full_name', 'like', "%{$this->searchAssignment}%");
                });
            })
            ->when($this->filters['department_id'], function ($query) {
                return $query->whereHas('employee', function ($query) {
                    $query->where('department_id', $this->filters['department_id']);
                });
            })
            ->when($this->filters['shift_id'], function ($query) {
                return $query->where('shift_id', $this->filters['shift_id']);
            })
            ->orderBy($this->sortFieldAssignment, $this->sortDirectionAssignment)
            ->get();

        $companyName = \App\Models\Setting::get('company_name', 'ERP DEMBENA');
        
        // Corrigindo para obter o logo dos settings do sistema
        $companyLogo = \App\Models\Setting::get('company_logo');
        $logoPath = $companyLogo ? public_path('storage/' . $companyLogo) : null;
        $hasLogo = $logoPath && file_exists($logoPath);

        $data = [
            'assignments' => $shiftAssignments,
            'companyName' => $companyName,
            'hasLogo' => $hasLogo,
            'logoPath' => $logoPath,
            'date' => Carbon::now()->format('d/m/Y H:i'),
            'title' => 'Shift Assignments Report'
        ];

        $pdf = PDF::loadView('pdf.shift-assignments-report', $data);

        return response()->streamDownload(
            fn () => print($pdf->output()),
            'shift_assignments_report_' . Carbon::now()->format('Y-m-d') . '.pdf'
        );
    }

    public function render()
    {
        $shifts = Shift::when($this->searchShift, function ($query) {
                return $query->where('name', 'like', "%{$this->searchShift}%");
            })
            ->when($this->filters['is_active'] !== '', function ($query) {
                return $query->where('is_active', $this->filters['is_active']);
            })
            ->orderBy($this->sortFieldShift, $this->sortDirectionShift)
            ->paginate($this->perPage);

        $shiftAssignments = ShiftAssignment::with(['employee', 'shift'])
            ->when($this->searchAssignment, function ($query) {
                return $query->whereHas('employee', function ($query) {
                    $query->where('full_name', 'like', "%{$this->searchAssignment}%");
                });
            })
            ->when($this->filters['department_id'], function ($query) {
                return $query->whereHas('employee', function ($query) {
                    $query->where('department_id', $this->filters['department_id']);
                });
            })
            ->when($this->filters['shift_id'], function ($query) {
                return $query->where('shift_id', $this->filters['shift_id']);
            })
            ->orderBy($this->sortFieldAssignment, $this->sortDirectionAssignment)
            ->paginate($this->perPage);

        $employees = Employee::where('employment_status', 'active')->get();
        $departments = Department::where('is_active', true)->get();
        $shiftsForSelect = Shift::where('is_active', true)->get();

        return view('livewire.hr.shift-management', [
            'shifts' => $shifts,
            'shiftAssignments' => $shiftAssignments,
            'employees' => $employees,
            'departments' => $departments,
            'shiftsForSelect' => $shiftsForSelect,
        ]);
    }
}
