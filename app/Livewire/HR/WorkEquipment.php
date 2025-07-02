<?php

namespace App\Livewire\HR;

use App\Models\HR\Equipment;
use App\Models\HR\EmployeeEquipment;
use App\Models\HR\EquipmentMaintenance;
use App\Models\HR\Department;
use App\Models\HR\Employee;
use App\Models\HR\WorkEquipmentCategory;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;

class WorkEquipment extends Component
{
    use WithPagination, WithFileUploads;
    
    // WorkEquipmentCategories
    public $workEquipmentCategories = [];

    // Tabs
    public $activeTab = 'equipment';
    
    /**
     * Inicialização do componente
     */
    public function mount()
    {
        // Carregar categorias de equipamento de trabalho
        $this->workEquipmentCategories = WorkEquipmentCategory::where('is_active', true)->orderBy('name')->get();
    }

    // Equipment
    public $equipment_id;
    public $name;
    public $equipment_type;
    public $serial_number;
    public $asset_code;
    public $brand;
    public $model;
    public $purchase_date;
    public $purchase_cost;
    public $warranty_expiry;
    public $condition;
    public $status;
    public $notes;

    // Equipment Assignment
    public $assignment_id;
    public $employee_id;
    public $equipment_id_assignment;
    public $issue_date;
    public $return_date;
    public $condition_on_issue;
    public $condition_on_return;
    public $assignment_status;
    public $assignment_notes;

    // Equipment Maintenance
    public $maintenance_id;
    public $equipment_id_maintenance;
    public $maintenance_type;
    public $maintenance_date;
    public $cost;
    public $performed_by;
    public $maintenance_status;
    public $description;
    public $next_maintenance_date;

    // Filters
    public $searchEquipment = '';
    public $searchAssignment = '';
    public $searchMaintenance = '';
    public $perPage = 10;
    public $sortFieldEquipment = 'name';
    public $sortDirectionEquipment = 'asc';
    public $sortFieldAssignment = 'issue_date';
    public $sortDirectionAssignment = 'desc';
    public $sortFieldMaintenance = 'maintenance_date';
    public $sortDirectionMaintenance = 'desc';
    public $filters = [
        'department_id' => '',
        'equipment_type' => '',
        'status' => '',
    ];

    // Modal flags
    public $showEquipmentModal = false;
    public $showAssignmentModal = false;
    public $showMaintenanceModal = false;
    public $showDeleteModal = false;
    public $deleteType = '';
    public $isEditing = false;

    // Listeners
    protected $listeners = ['refreshEquipment' => '$refresh'];

    // Rules for equipment
    protected function equipmentRules()
    {
        return [
            'name' => 'required|string|max:255',
            'equipment_type' => 'required|exists:work_equipment_categories,id',
            'serial_number' => 'nullable|string|max:255',
            'asset_code' => 'required|string|max:255|unique:equipment,asset_code,' . $this->equipment_id,
            'brand' => 'nullable|string|max:255',
            'model' => 'nullable|string|max:255',
            'purchase_date' => 'nullable|date',
            'purchase_cost' => 'nullable|numeric|min:0',
            'warranty_expiry' => 'nullable|date',
            'condition' => 'nullable|string|max:255',
            'status' => 'required|in:available,assigned,maintenance,damaged,disposed',
            'notes' => 'nullable|string',
        ];
    }

    // Rules for equipment assignment
    protected function assignmentRules()
    {
        return [
            'employee_id' => 'required|exists:employees,id',
            'equipment_id_assignment' => 'required|exists:equipment,id',
            'issue_date' => 'required|date',
            'return_date' => 'nullable|date|after_or_equal:issue_date',
            'condition_on_issue' => 'required|string|max:255',
            'condition_on_return' => 'nullable|string|max:255',
            'assignment_status' => 'required|in:issued,returned,damaged,lost',
            'assignment_notes' => 'nullable|string',
        ];
    }

    // Rules for equipment maintenance
    protected function maintenanceRules()
    {
        return [
            'equipment_id_maintenance' => 'required|exists:equipment,id',
            'maintenance_type' => 'required|in:preventive,corrective,upgrade,inspection',
            'maintenance_date' => 'required|date',
            'cost' => 'nullable|numeric|min:0',
            'performed_by' => 'nullable|exists:employees,id',
            'maintenance_status' => 'required|in:planned,in_progress,completed,cancelled',
            'description' => 'nullable|string',
            'next_maintenance_date' => 'nullable|date|after_or_equal:maintenance_date',
        ];
    }

    public function setActiveTab($tab)
    {
        $this->activeTab = $tab;
    }

    public function sortByEquipment($field)
    {
        if ($this->sortFieldEquipment === $field) {
            $this->sortDirectionEquipment = $this->sortDirectionEquipment === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortFieldEquipment = $field;
            $this->sortDirectionEquipment = 'asc';
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

    public function sortByMaintenance($field)
    {
        if ($this->sortFieldMaintenance === $field) {
            $this->sortDirectionMaintenance = $this->sortDirectionMaintenance === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortFieldMaintenance = $field;
            $this->sortDirectionMaintenance = 'asc';
        }
    }

    public function updatingSearchEquipment()
    {
        $this->resetPage();
    }

    public function updatingSearchAssignment()
    {
        $this->resetPage();
    }

    public function updatingSearchMaintenance()
    {
        $this->resetPage();
    }

    public function updatingFilters()
    {
        $this->resetPage();
    }

    // Equipment methods
    public function createEquipment()
    {
        $this->reset([
            'equipment_id', 'name', 'equipment_type', 'serial_number', 'asset_code',
            'brand', 'model', 'purchase_date', 'purchase_cost', 'warranty_expiry',
            'condition', 'status', 'notes'
        ]);
        $this->status = 'available';
        $this->isEditing = false;
        $this->showEquipmentModal = true;
    }

    public function editEquipment(Equipment $equipment)
    {
        $this->equipment_id = $equipment->id;
        $this->name = $equipment->name;
        $this->equipment_type = $equipment->equipment_type;
        $this->serial_number = $equipment->serial_number;
        $this->asset_code = $equipment->asset_code;
        $this->brand = $equipment->brand;
        $this->model = $equipment->model;
        $this->purchase_date = $equipment->purchase_date ? $equipment->purchase_date->format('Y-m-d') : null;
        $this->purchase_cost = $equipment->purchase_cost;
        $this->warranty_expiry = $equipment->warranty_expiry ? $equipment->warranty_expiry->format('Y-m-d') : null;
        $this->condition = $equipment->condition;
        $this->status = $equipment->status;
        $this->notes = $equipment->notes;

        $this->isEditing = true;
        $this->showEquipmentModal = true;
    }

    public function saveEquipment()
    {
        $validatedData = $this->validate($this->equipmentRules());

        if ($this->isEditing) {
            $equipment = Equipment::find($this->equipment_id);
            $equipment->update($validatedData);
            $this->dispatch('notify', 
                type: 'warning',
                title: __('messages.success'),
                message: __('messages.equipment_updated')
            );
        } else {
            Equipment::create($validatedData);
            $this->dispatch('notify', 
                type: 'success',
                title: __('messages.success'),
                message: __('messages.equipment_created')
            );
        }

        $this->showEquipmentModal = false;
        $this->reset([
            'equipment_id', 'name', 'equipment_type', 'serial_number', 'asset_code',
            'brand', 'model', 'purchase_date', 'purchase_cost', 'warranty_expiry',
            'condition', 'status', 'notes'
        ]);
    }

    // Equipment Assignment methods
    public function createAssignment()
    {
        $this->reset([
            'assignment_id', 'employee_id', 'equipment_id_assignment', 'issue_date',
            'return_date', 'condition_on_issue', 'condition_on_return',
            'assignment_status', 'assignment_notes'
        ]);
        $this->issue_date = Carbon::today()->format('Y-m-d');
        $this->assignment_status = 'issued';
        $this->isEditing = false;
        $this->showAssignmentModal = true;
    }

    public function editAssignment(EmployeeEquipment $assignment)
    {
        $this->assignment_id = $assignment->id;
        $this->employee_id = $assignment->employee_id;
        $this->equipment_id_assignment = $assignment->equipment_id;
        $this->issue_date = $assignment->issue_date->format('Y-m-d');
        $this->return_date = $assignment->return_date ? $assignment->return_date->format('Y-m-d') : null;
        $this->condition_on_issue = $assignment->condition_on_issue;
        $this->condition_on_return = $assignment->condition_on_return;
        $this->assignment_status = $assignment->status;
        $this->assignment_notes = $assignment->notes;

        $this->isEditing = true;
        $this->showAssignmentModal = true;
    }

    public function saveAssignment()
    {
        // For validation, map the fields correctly
        $this->validate([
            'employee_id' => 'required|exists:employees,id',
            'equipment_id_assignment' => 'required|exists:equipment,id',
            'issue_date' => 'required|date',
            'return_date' => 'nullable|date|after_or_equal:issue_date',
            'condition_on_issue' => 'required|string|max:255',
            'condition_on_return' => 'nullable|string|max:255',
            'assignment_status' => 'required|in:issued,returned,damaged,lost',
            'assignment_notes' => 'nullable|string',
        ]);
        
        $data = [
            'employee_id' => $this->employee_id,
            'equipment_id' => $this->equipment_id_assignment,
            'issue_date' => $this->issue_date,
            'return_date' => $this->return_date,
            'condition_on_issue' => $this->condition_on_issue,
            'condition_on_return' => $this->condition_on_return,
            'status' => $this->assignment_status,
            'notes' => $this->assignment_notes,
            'issued_by' => auth()->id(),
        ];
        
        // Update equipment status
        $equipment = Equipment::find($this->equipment_id_assignment);
        if ($this->assignment_status === 'issued') {
            $equipment->status = 'assigned';
        } elseif ($this->assignment_status === 'returned') {
            $equipment->status = 'available';
            // Add received_by
            $data['received_by'] = auth()->id();
        } elseif ($this->assignment_status === 'damaged') {
            $equipment->status = 'damaged';
        }
        $equipment->save();

        if ($this->isEditing) {
            $assignment = EmployeeEquipment::find($this->assignment_id);
            $assignment->update($data);
            session()->flash('message', 'Equipment assignment updated successfully.');
        } else {
            EmployeeEquipment::create($data);
            session()->flash('message', 'Equipment assigned successfully.');
        }

        $this->showAssignmentModal = false;
        $this->reset([
            'assignment_id', 'employee_id', 'equipment_id_assignment', 'issue_date',
            'return_date', 'condition_on_issue', 'condition_on_return',
            'assignment_status', 'assignment_notes'
        ]);
    }

    // Equipment Maintenance methods
    public function createMaintenance()
    {
        $this->reset([
            'maintenance_id', 'equipment_id_maintenance', 'maintenance_type',
            'maintenance_date', 'cost', 'performed_by', 'maintenance_status',
            'description', 'next_maintenance_date'
        ]);
        $this->maintenance_date = Carbon::today()->format('Y-m-d');
        $this->maintenance_status = 'planned';
        $this->isEditing = false;
        $this->showMaintenanceModal = true;
    }

    public function editMaintenance(EquipmentMaintenance $maintenance)
    {
        $this->maintenance_id = $maintenance->id;
        $this->equipment_id_maintenance = $maintenance->equipment_id;
        $this->maintenance_type = $maintenance->maintenance_type;
        $this->maintenance_date = $maintenance->maintenance_date->format('Y-m-d');
        $this->cost = $maintenance->cost;
        $this->performed_by = $maintenance->performed_by;
        $this->maintenance_status = $maintenance->status;
        $this->description = $maintenance->description;
        $this->next_maintenance_date = $maintenance->next_maintenance_date ? $maintenance->next_maintenance_date->format('Y-m-d') : null;

        $this->isEditing = true;
        $this->showMaintenanceModal = true;
    }

    public function saveMaintenance()
    {
        // For validation, map the fields correctly
        $this->validate([
            'equipment_id_maintenance' => 'required|exists:equipment,id',
            'maintenance_type' => 'required|in:preventive,corrective,upgrade,inspection',
            'maintenance_date' => 'required|date',
            'cost' => 'nullable|numeric|min:0',
            'performed_by' => 'nullable|exists:employees,id',
            'maintenance_status' => 'required|in:planned,in_progress,completed,cancelled',
            'description' => 'nullable|string',
            'next_maintenance_date' => 'nullable|date|after_or_equal:maintenance_date',
        ]);
        
        $data = [
            'equipment_id' => $this->equipment_id_maintenance,
            'maintenance_type' => $this->maintenance_type,
            'maintenance_date' => $this->maintenance_date,
            'cost' => $this->cost,
            'performed_by' => $this->performed_by,
            'status' => $this->maintenance_status,
            'description' => $this->description,
            'next_maintenance_date' => $this->next_maintenance_date,
        ];
        
        // Update equipment status if in maintenance
        $equipment = Equipment::find($this->equipment_id_maintenance);
        if (in_array($this->maintenance_status, ['planned', 'in_progress'])) {
            $equipment->status = 'maintenance';
        } elseif ($this->maintenance_status === 'completed') {
            $equipment->status = 'available';
        }
        $equipment->save();

        if ($this->isEditing) {
            $maintenance = EquipmentMaintenance::find($this->maintenance_id);
            $maintenance->update($data);
            $this->dispatch('notify', 
                type: 'warning',
                title: __('messages.success'),
                message: __('messages.equipment_maintenance_updated')
            );
        } else {
            EquipmentMaintenance::create($data);
            $this->dispatch('notify', 
                type: 'success',
                title: __('messages.success'),
                message: __('messages.equipment_maintenance_created')
            );
        }

        $this->showMaintenanceModal = false;
        $this->reset([
            'maintenance_id', 'equipment_id_maintenance', 'maintenance_type',
            'maintenance_date', 'cost', 'performed_by', 'maintenance_status',
            'description', 'next_maintenance_date'
        ]);
    }

    public function confirmDelete($id, $type)
    {
        $this->showDeleteModal = true;
        $this->deleteType = $type;
        
        if ($type === 'equipment') {
            $this->equipment_id = $id;
        } elseif ($type === 'assignment') {
            $this->assignment_id = $id;
        } elseif ($type === 'maintenance') {
            $this->maintenance_id = $id;
        }
    }

    public function delete()
    {
        if ($this->deleteType === 'equipment') {
            $equipment = Equipment::find($this->equipment_id);
            if ($equipment) {
                $equipment->delete();
                $this->dispatch('notify', 
                    type: 'error',
                    title: __('messages.success'),
                    message: __('messages.equipment_deleted')
                );
            }
        } elseif ($this->deleteType === 'assignment') {
            $assignment = EmployeeEquipment::find($this->assignment_id);
            if ($assignment) {
                // Reset equipment status if it was assigned
                if ($assignment->status === 'issued') {
                    $equipment = Equipment::find($assignment->equipment_id);
                    if ($equipment) {
                        $equipment->status = 'available';
                        $equipment->save();
                    }
                }
                
                $assignment->delete();
                $this->dispatch('notify', 
                    type: 'error',
                    title: __('messages.success'),
                    message: __('messages.equipment_assignment_deleted')
                );
            }
        } elseif ($this->deleteType === 'maintenance') {
            $maintenance = EquipmentMaintenance::find($this->maintenance_id);
            if ($maintenance) {
                // Reset equipment status if it was in maintenance
                if (in_array($maintenance->status, ['planned', 'in_progress'])) {
                    $equipment = Equipment::find($maintenance->equipment_id);
                    if ($equipment) {
                        $equipment->status = 'available';
                        $equipment->save();
                    }
                }
                
                $maintenance->delete();
                $this->dispatch('notify', 
                    type: 'error',
                    title: __('messages.success'),
                    message: __('messages.equipment_maintenance_deleted')
                );
            }
        }
        
        $this->showDeleteModal = false;
        $this->deleteType = '';
        $this->reset(['equipment_id', 'assignment_id', 'maintenance_id']);
    }

    public function closeEquipmentModal()
    {
        $this->showEquipmentModal = false;
        $this->resetValidation();
    }
    
    public function closeAssignmentModal()
    {
        $this->showAssignmentModal = false;
        $this->resetValidation();
    }
    
    public function closeMaintenanceModal()
    {
        $this->showMaintenanceModal = false;
        $this->resetValidation();
    }
    
    public function closeDeleteModal()
    {
        $this->showDeleteModal = false;
    }
    
    public function resetFilters()
    {
        $this->reset('filters');
        $this->searchEquipment = '';
        $this->searchAssignment = '';
        $this->searchMaintenance = '';
        $this->resetPage();
    }

    public function render()
    {
        $equipment = Equipment::when($this->searchEquipment, function ($query) {
                return $query->where(function($q) {
                    $q->where('name', 'like', "%{$this->searchEquipment}%")
                      ->orWhere('serial_number', 'like', "%{$this->searchEquipment}%")
                      ->orWhere('asset_code', 'like', "%{$this->searchEquipment}%");
                });
            })
            ->when($this->filters['equipment_type'], function ($query) {
                return $query->where('equipment_type', $this->filters['equipment_type']);
            })
            ->when($this->filters['status'], function ($query) {
                return $query->where('status', $this->filters['status']);
            })
            ->orderBy($this->sortFieldEquipment, $this->sortDirectionEquipment)
            ->paginate($this->perPage);

        $assignments = EmployeeEquipment::with(['employee', 'equipment'])
            ->when($this->searchAssignment, function ($query) {
                return $query->whereHas('employee', function ($query) {
                    $query->where('full_name', 'like', "%{$this->searchAssignment}%");
                })->orWhereHas('equipment', function ($query) {
                    $query->where('name', 'like', "%{$this->searchAssignment}%");
                });
            })
            ->when($this->filters['department_id'], function ($query) {
                return $query->whereHas('employee', function ($query) {
                    $query->where('department_id', $this->filters['department_id']);
                });
            })
            ->orderBy($this->sortFieldAssignment, $this->sortDirectionAssignment)
            ->paginate($this->perPage);

        // Comentando temporariamente o trecho que usa a tabela equipment_maintenances
        // que ainda não existe
        /*
        $maintenanceRecords = EquipmentMaintenance::with(['equipment', 'performer'])
            ->when($this->searchMaintenance, function ($query) {
                return $query->whereHas('equipment', function ($query) {
                    $query->where('name', 'like', "%{$this->searchMaintenance}%");
                });
            })
            ->orderBy($this->sortFieldMaintenance, $this->sortDirectionMaintenance)
            ->paginate($this->perPage);
        */
        
        // Substituindo por uma coleção vazia até que a tabela seja criada
        $maintenanceRecords = collect([]);

        $employees = Employee::where('employment_status', 'active')->get();
        $departments = Department::where('is_active', true)->get();
        $availableEquipment = Equipment::where('status', 'available')->get();
        
        // All equipment for maintenance
        $allEquipment = Equipment::all();

        return view('livewire.hr.work-equipment', [
            'equipment' => $equipment,
            'assignments' => $assignments,
            'maintenanceRecords' => $maintenanceRecords,
            'employees' => $employees,
            'departments' => $departments,
            'availableEquipment' => $availableEquipment,
            'allEquipment' => $allEquipment,
        ]);
    }
}
