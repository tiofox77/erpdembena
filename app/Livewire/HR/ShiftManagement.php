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
    public $rotation_type;
    public $rotation_frequency;
    public $rotation_start_date;

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
    public $showDeleteShiftModal = false;
    public $showDeleteAssignmentModal = false;
    public $isEditing = false;
    
    // Delete properties
    public $shift_to_delete_id;
    public $shift_to_delete_name;
    public $assignment_to_delete_id;
    public $assignment_to_delete_name;

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
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
        
        // Validação condicional para shifts
        if ($this->has_rotation) {
            $rules['selected_shifts'] = ['required', 'array', 'min:2'];
            $rules['selected_shifts.*'] = ['exists:shifts,id'];
            $rules['shift_id_assignment'] = ['nullable']; // Opcional quando há rotação
        } else {
            $rules['shift_id_assignment'] = ['required', 'exists:shifts,id'];
            $rules['selected_shifts'] = ['nullable']; // Opcional quando não há rotação
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

    public function updatedSearchShift()
    {
        $this->resetPage();
    }

    public function updatedSearchAssignment()
    {
        $this->resetPage();
    }

    public function updatedFilters()
    {
        $this->resetPage();
    }
    
    public function updatedPerPage()
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
        // Limpar dados primeiro
        $this->assignment_id = null;
        $this->employee_id = null;
        $this->shift_id_assignment = null;
        $this->selected_shifts = [];
        $this->has_rotation = 0;
        $this->start_date = null;
        $this->end_date = null;
        $this->notes = null;
        $this->rotation_type = null;
        $this->rotation_frequency = null;
        $this->rotation_start_date = null;
        $this->isEditing = false;
        
        // Fechar outros modais primeiro
        $this->showDeleteModal = false;
        $this->showShiftModal = false;
        
        \Log::info('=== EDITANDO ASSIGNMENT ===', [
            'assignment_id' => $assignment->id,
            'employee_id' => $assignment->employee_id,
            'shift_id' => $assignment->shift_id,
            'rotation_pattern' => $assignment->rotation_pattern
        ]);
        
        $this->assignment_id = $assignment->id;
        $this->employee_id = (string) $assignment->employee_id; // Forçar como string
        $this->shift_id_assignment = (string) $assignment->shift_id; // Forçar como string  
        $this->start_date = $assignment->start_date->format('Y-m-d');
        $this->end_date = $assignment->end_date ? $assignment->end_date->format('Y-m-d') : null;
        $this->notes = $assignment->notes;
        
        \Log::info('Dados setados:', [
            'employee_id' => $this->employee_id,
            'employee_id_type' => gettype($this->employee_id),
            'assignment_employee_id' => $assignment->employee_id,
            'assignment_employee_id_type' => gettype($assignment->employee_id)
        ]);
        
        // Processar dados de rotação se existirem
        if ($assignment->rotation_pattern) {
            $rotationData = json_decode($assignment->rotation_pattern, true);
            \Log::info('Rotation data decoded:', $rotationData);
            
            if ($rotationData && isset($rotationData['shifts']) && count($rotationData['shifts']) > 1) {
                $this->has_rotation = 1; // Forçar como inteiro
                $this->selected_shifts = array_map('strval', $rotationData['shifts']); // Converter para strings
                $this->rotation_type = $rotationData['type'] ?? 'weekly';
                $this->rotation_frequency = $rotationData['frequency'] ?? 7;
                $this->rotation_start_date = $this->start_date;
                
                \Log::info('Has rotation - dados setados:', [
                    'has_rotation' => $this->has_rotation,
                    'selected_shifts' => $this->selected_shifts,
                    'rotation_type' => $this->rotation_type
                ]);
            } else {
                $this->has_rotation = 0;
                $this->selected_shifts = [];
                \Log::info('No rotation detected');
            }
        } else {
            $this->has_rotation = 0;
            $this->selected_shifts = [];
            \Log::info('No rotation pattern found');
        }

        $this->isEditing = true;
        $this->showAssignmentModal = true;
        
        \Log::info('Modal aberta - dados finais:', [
            'employee_id' => $this->employee_id,
            'shift_id_assignment' => $this->shift_id_assignment,
            'has_rotation' => $this->has_rotation,
            'selected_shifts' => $this->selected_shifts
        ]);
        
        // Forçar atualização do componente
        $this->dispatch('$refresh');
    }

    // Este método foi removido por ser duplicado

    public function saveAssignment()
    {
        \Log::info('=== INÍCIO saveAssignment ===', [
            'assignment_id' => $this->assignment_id,
            'employee_id' => $this->employee_id,
            'shift_id_assignment' => $this->shift_id_assignment,
            'selected_shifts' => $this->selected_shifts,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'is_permanent' => $this->is_permanent,
            'has_rotation' => $this->has_rotation,
            'isEditing' => $this->isEditing
        ]);
        
        try {
            $rules = $this->assignmentRules();
            \Log::info('Regras de validação:', $rules);
            
            $this->validate($rules);
            \Log::info('Validação passou com sucesso');
        
            // Determinar quais shifts usar
            if ($this->has_rotation && !empty($this->selected_shifts)) {
                $shiftsToAssign = $this->selected_shifts;
            } elseif (!$this->has_rotation && $this->shift_id_assignment) {
                $shiftsToAssign = [$this->shift_id_assignment];
            } else {
                \Log::error('Nenhum shift selecionado', [
                    'has_rotation' => $this->has_rotation,
                    'selected_shifts' => $this->selected_shifts,
                    'shift_id_assignment' => $this->shift_id_assignment
                ]);
                $this->addError('shift_id_assignment', __('shifts.shifts_required'));
                return;
            }
            
            \Log::info('Shifts a serem atribuídos:', $shiftsToAssign);
            
            // Validar que shifts válidos foram selecionados
            $shiftsToAssign = array_filter($shiftsToAssign, function($shiftId) {
                return !empty($shiftId) && is_numeric($shiftId);
            });
            
            if (empty($shiftsToAssign)) {
                \Log::error('Nenhum shift válido após filtragem');
                $this->addError('shift_id_assignment', __('shifts.shifts_required'));
                return;
            }
        
        $successCount = 0;
        $errors = [];
        
            if ($this->isEditing) {
                // Para edição, atualizar o assignment existente
                \Log::info('Editando assignment existente', ['assignment_id' => $this->assignment_id]);
                
                $assignment = ShiftAssignment::find($this->assignment_id);
                if (!$assignment) {
                    \Log::error('Assignment não encontrado para edição', ['assignment_id' => $this->assignment_id]);
                    session()->flash('error', 'Atribuição não encontrada.');
                    return;
                }
                
                $validatedData = [
                    'employee_id' => $this->employee_id,
                    'shift_id' => $this->has_rotation ? $shiftsToAssign[0] : $this->shift_id_assignment,
                    'start_date' => $this->start_date,
                    'end_date' => $this->end_date,
                    'rotation_pattern' => $this->has_rotation ? json_encode(['shifts' => $shiftsToAssign, 'type' => $this->rotation_type, 'frequency' => $this->rotation_frequency]) : null,
                    'notes' => $this->notes,
                    'assigned_by' => auth()->id(),
                ];
                
                \Log::info('Dados para update:', $validatedData);
                
                $assignment->update($validatedData);
                \Log::info('Assignment atualizado com sucesso', ['assignment_id' => $assignment->id]);
                
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
                            'end_date' => $this->end_date,
                            'rotation_pattern' => $this->has_rotation ? json_encode(['shifts' => $shiftsToAssign, 'type' => $this->rotation_type, 'frequency' => $this->rotation_frequency]) : null,
                            'notes' => $this->notes,
                            'assigned_by' => auth()->id(),
                        ];
                        
                        \Log::info('Criando novo assignment', ['dados' => $validatedData]);
                        
                        $newAssignment = ShiftAssignment::create($validatedData);
                        \Log::info('Assignment criado com sucesso', ['assignment_id' => $newAssignment->id]);
                        
                        $successCount++;
                    } else {
                        $shift = Shift::find($shiftId);
                        $errors[] = __('shifts.employee_already_assigned') . ' (' . $shift->name . ')';
                    }
                } catch (\Exception $e) {
                    \Log::error('Erro ao processar shift', [
                        'shift_id' => $shiftId,
                        'error' => $e->getMessage(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                        'trace' => $e->getTraceAsString()
                    ]);
                    
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

            \Log::info('=== FIM saveAssignment - Sucesso ===');
            
        } catch (\Exception $e) {
            \Log::error('=== ERRO GERAL saveAssignment ===', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            session()->flash('error', 'Erro ao salvar atribuição: ' . $e->getMessage());
        }

        $this->showAssignmentModal = false;
        $this->reset([
            'assignment_id', 'employee_id', 'shift_id_assignment', 'selected_shifts', 'start_date',
            'end_date', 'is_permanent', 'rotation_pattern', 'notes', 'has_rotation'
        ]);
    }

    public function deleteShift($shiftId)
    {
        $shift = Shift::find($shiftId);
        
        if (!$shift) {
            session()->flash('error', 'Turno não encontrado.');
            return;
        }
        
        // Store shift data for modal
        $this->shift_to_delete_id = $shiftId;
        $this->shift_to_delete_name = $shift->name;
        
        // Show confirmation modal
        $this->showDeleteShiftModal = true;
    }
    
    public function confirmDeleteShift()
    {
        try {
            $shift = Shift::find($this->shift_to_delete_id);
            
            if (!$shift) {
                session()->flash('error', 'Turno não encontrado.');
                $this->cancelDeleteShift();
                return;
            }
            
            // Verificar se há atribuições relacionadas
            $hasAssignments = ShiftAssignment::whereJsonContains('shifts', function ($query) {
                $query->where('id', $this->shift_to_delete_id);
            })->exists();
            
            if ($hasAssignments) {
                session()->flash('error', 'Não é possível excluir este turno pois existem atribuições relacionadas.');
                $this->cancelDeleteShift();
                return;
            }
            
            $shift->delete();
            session()->flash('success', 'Turno "' . $this->shift_to_delete_name . '" excluído com sucesso!');
            
            $this->cancelDeleteShift();
            
        } catch (\Exception $e) {
            \Log::error('Erro ao excluir turno: ' . $e->getMessage());
            session()->flash('error', 'Erro ao excluir turno.');
            $this->cancelDeleteShift();
        }
    }
    
    public function cancelDeleteShift()
    {
        $this->showDeleteShiftModal = false;
        $this->shift_to_delete_id = null;
        $this->shift_to_delete_name = null;
    }

    public function deleteAssignment($id)
    {
        $assignment = ShiftAssignment::find($id);
        
        if (!$assignment) {
            session()->flash('error', 'Atribuição não encontrada.');
            return;
        }
        
        // Store assignment data for modal
        $this->assignment_to_delete_id = $id;
        $this->assignment_to_delete_name = $assignment->employee->full_name ?? 'Funcionário';
        
        // Show confirmation modal
        $this->showDeleteAssignmentModal = true;
    }
    
    public function confirmDeleteAssignment()
    {
        try {
            $assignment = ShiftAssignment::find($this->assignment_to_delete_id);
            
            if (!$assignment) {
                session()->flash('error', 'Atribuição não encontrada.');
                $this->cancelDeleteAssignment();
                return;
            }
            
            $assignment->delete();
            session()->flash('success', 'Atribuição de "' . $this->assignment_to_delete_name . '" excluída com sucesso!');
            
            $this->cancelDeleteAssignment();
            $this->resetPage();
            
        } catch (\Exception $e) {
            \Log::error('Erro ao excluir atribuição: ' . $e->getMessage());
            session()->flash('error', 'Erro ao excluir atribuição.');
            $this->cancelDeleteAssignment();
        }
    }
    
    public function cancelDeleteAssignment()
    {
        $this->showDeleteAssignmentModal = false;
        $this->assignment_to_delete_id = null;
        $this->assignment_to_delete_name = null;
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
        
        // Se estamos editando, excluir o assignment atual da lista de "já atribuídos"
        if ($this->isEditing && $this->assignment_id) {
            $currentAssignment = ShiftAssignment::find($this->assignment_id);
            if ($currentAssignment) {
                $employeesWithAssignments = $employeesWithAssignments->reject($currentAssignment->employee_id);
            }
        }
        
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
