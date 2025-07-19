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
    public $selected_shifts = []; // Para múltiplos shifts
    public $start_date;
    public $end_date;
    public $is_permanent = false;
    public $rotation_pattern;
    public $notes;
    public $has_rotation = false; // Para controlar se permite rotação

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
        $rules = [
            'employee_id' => ['required', 'exists:employees,id'],
            'start_date' => ['required', 'date'],
            'end_date' => $this->is_permanent ? ['nullable'] : ['required', 'date', 'after_or_equal:start_date'],
            'rotation_pattern' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
        
        // Validação condicional para shifts
        if ($this->has_rotation) {
            $rules['selected_shifts'] = ['required', 'array', 'min:2'];
            $rules['selected_shifts.*'] = ['exists:shifts,id'];
        } else {
            $rules['shift_id_assignment'] = ['required', 'exists:shifts,id'];
        }
        
        return $rules;
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
            'assignment_id', 'employee_id', 'shift_id_assignment', 'selected_shifts', 'start_date',
            'end_date', 'is_permanent', 'rotation_pattern', 'notes', 'has_rotation'
        ]);
        $this->start_date = Carbon::today()->format('Y-m-d');
        $this->is_permanent = false;
        $this->has_rotation = false;
        $this->selected_shifts = [];
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
        $this->validate($this->assignmentRules());
        
        // Determinar quais shifts usar
        $shiftsToAssign = $this->has_rotation && !empty($this->selected_shifts) 
            ? $this->selected_shifts 
            : [$this->shift_id_assignment];
        
        // Validar que pelo menos um shift foi selecionado
        if (empty($shiftsToAssign) || (count($shiftsToAssign) === 1 && !$shiftsToAssign[0])) {
            $this->addError('shifts', __('shifts.shifts_required'));
            return;
        }
        
        $successCount = 0;
        $errors = [];
        
        if ($this->isEditing) {
            // Para edição, atualizar o assignment existente
            $assignment = ShiftAssignment::find($this->assignment_id);
            $validatedData = [
                'employee_id' => $this->employee_id,
                'shift_id' => $this->has_rotation ? $shiftsToAssign[0] : $this->shift_id_assignment,
                'start_date' => $this->start_date,
                'end_date' => $this->is_permanent ? null : $this->end_date,
                'is_permanent' => $this->is_permanent,
                'rotation_pattern' => $this->has_rotation ? json_encode(['shifts' => $shiftsToAssign, 'pattern' => $this->rotation_pattern]) : $this->rotation_pattern,
                'notes' => $this->notes,
                'assigned_by' => auth()->id(),
            ];
            $assignment->update($validatedData);
            session()->flash('message', __('shifts.assignment_updated'));
        } else {
            // Para criação nova, criar um assignment para cada shift se houver rotação
            foreach ($shiftsToAssign as $shiftId) {
                try {
                    // Verificar se já existe assignment para este funcionário e shift no período
                    $existingAssignment = ShiftAssignment::where('employee_id', $this->employee_id)
                        ->where('shift_id', $shiftId)
                        ->where(function($query) {
                            $query->where(function($q) {
                                $q->where('start_date', '<=', $this->start_date)
                                  ->where(function($q2) {
                                      $q2->whereNull('end_date')
                                         ->orWhere('end_date', '>=', $this->start_date);
                                  });
                            });
                            if (!$this->is_permanent && $this->end_date) {
                                $query->orWhere(function($q) {
                                    $q->where('start_date', '<=', $this->end_date)
                                      ->where(function($q2) {
                                          $q2->whereNull('end_date')
                                             ->orWhere('end_date', '>=', $this->end_date);
                                      });
                                });
                            }
                        })
                        ->exists();
                        
                    if (!$existingAssignment) {
                        $validatedData = [
                            'employee_id' => $this->employee_id,
                            'shift_id' => $shiftId,
                            'start_date' => $this->start_date,
                            'end_date' => $this->is_permanent ? null : $this->end_date,
                            'is_permanent' => $this->is_permanent,
                            'rotation_pattern' => $this->has_rotation ? json_encode(['shifts' => $shiftsToAssign, 'pattern' => $this->rotation_pattern]) : $this->rotation_pattern,
                            'notes' => $this->notes,
                            'assigned_by' => auth()->id(),
                        ];
                        
                        ShiftAssignment::create($validatedData);
                        $successCount++;
                    } else {
                        $shift = Shift::find($shiftId);
                        $errors[] = __('shifts.employee_already_assigned') . ' (' . $shift->name . ')';
                    }
                } catch (\Exception $e) {
                    $shift = Shift::find($shiftId);
                    $errors[] = 'Erro ao atribuir turno ' . $shift->name . ': ' . $e->getMessage();
                }
            }
            
            if ($successCount > 0) {
                $message = $successCount === 1 
                    ? __('shifts.assignment_created')
                    : __('shifts.bulk_assignments_created') . ' (' . $successCount . ')';
                session()->flash('message', $message);
            }
            
            if (!empty($errors)) {
                session()->flash('errors', $errors);
            }
        }

        $this->showAssignmentModal = false;
        $this->reset([
            'assignment_id', 'employee_id', 'shift_id_assignment', 'selected_shifts', 'start_date',
            'end_date', 'is_permanent', 'rotation_pattern', 'notes', 'has_rotation'
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

        // Buscar assignments com relações
        $rawAssignments = ShiftAssignment::with(['employee.department', 'shift'])
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
        
        // Agrupar assignments por funcionário
        $groupedAssignments = $rawAssignments->groupBy('employee_id')->map(function ($assignments) {
            $firstAssignment = $assignments->first();
            $shifts = $assignments->map(function ($assignment) {
                return [
                    'id' => $assignment->shift->id,
                    'name' => $assignment->shift->name,
                    'start_time' => $assignment->shift->start_time->format('H:i'),
                    'end_time' => $assignment->shift->end_time->format('H:i'),
                    'assignment_id' => $assignment->id,
                ];
            });
            
            return (object) [
                'id' => $firstAssignment->id,
                'employee' => $firstAssignment->employee,
                'shifts' => $shifts,
                'start_date' => $firstAssignment->start_date,
                'end_date' => $firstAssignment->end_date,
                'is_permanent' => $firstAssignment->is_permanent,
                'rotation_pattern' => $firstAssignment->rotation_pattern,
                'notes' => $firstAssignment->notes,
                'created_at' => $firstAssignment->created_at,
                'updated_at' => $firstAssignment->updated_at,
            ];
        });
        
        // Convert to paginated collection (simplified for demo)
        $currentPage = request()->get('page', 1);
        $perPage = $this->perPage;
        $total = $groupedAssignments->count();
        $items = $groupedAssignments->slice(($currentPage - 1) * $perPage, $perPage)->values();
        
        $shiftAssignments = new \Illuminate\Pagination\LengthAwarePaginator(
            $items,
            $total,
            $perPage,
            $currentPage,
            ['path' => request()->url(), 'pageName' => 'page']
        );

        // Obter IDs de funcionários que já têm turnos atribuídos
    $employeesWithAssignments = ShiftAssignment::pluck('employee_id')->unique();
    
    // Carregar apenas funcionários ativos que NÃO têm turnos atribuídos
    $employees = Employee::where('employment_status', 'active')
        ->whereNotIn('id', $employeesWithAssignments)
        ->get();
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
