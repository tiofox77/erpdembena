<?php

namespace App\Livewire\HR;

use App\Models\HR\Equipment;
use App\Models\HR\EmployeeEquipment;
use App\Models\HR\Employee;
use App\Models\HR\WorkEquipmentCategory;
use Livewire\Component;
use Livewire\WithPagination;

class WorkEquipment extends Component
{
    use WithPagination;
    
    // Tabs
    public $activeTab = 'equipment';
    
    // Equipment Properties
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
    public $status = 'available';
    public $notes;

    // Assignment Properties
    public $assignment_id;
    public $employee_id;
    public $equipment_id_assignment;
    public $issue_date;
    public $return_date;
    public $condition_on_issue;
    public $assignment_status = 'issued';
    public $assignment_notes;

    // Search & Filters
    public $searchEquipment = '';
    public $searchAssignment = '';
    public $sortFieldEquipment = 'name';
    public $sortDirectionEquipment = 'asc';
    public $perPage = 10;
    public $filters = [
        'equipment_type' => '',
        'status' => '',
    ];

    // Modal Flags
    public $showEquipmentModal = false;
    public $showAssignmentModal = false;
    public $showDeleteModal = false;
    public $deleteType = '';
    public $deleteId = null;
    public $isEditing = false;

    // Collections
    public $workEquipmentCategories = [];
    public $employees = [];
    public $availableEquipment = [];
    public $allEquipment = [];

    protected $rules = [
        'name' => 'required|string|max:255',
        'equipment_type' => 'required|exists:work_equipment_categories,id',
        'status' => 'required|in:available,assigned,maintenance,damaged,disposed',
        'serial_number' => 'nullable|string|max:255',
        'asset_code' => 'nullable|string|max:255',
        'brand' => 'nullable|string|max:255',
        'model' => 'nullable|string|max:255',
        'purchase_date' => 'nullable|date',
        'purchase_cost' => 'nullable|numeric|min:0',
        'warranty_expiry' => 'nullable|date',
        'condition' => 'nullable|string|max:255',
        'notes' => 'nullable|string',
    ];

    protected $assignmentRules = [
        'employee_id' => 'required|exists:employees,id',
        'equipment_id_assignment' => 'required|exists:equipment,id',
        'issue_date' => 'required|date',
        'return_date' => 'nullable|date|after_or_equal:issue_date',
        'condition_on_issue' => 'nullable|string|max:255',
        'assignment_status' => 'required|in:issued,returned,damaged,lost',
        'assignment_notes' => 'nullable|string',
    ];

    public function mount()
    {
        $this->loadData();
    }

    public function loadData()
    {
        $this->workEquipmentCategories = WorkEquipmentCategory::where('is_active', true)
            ->orderBy('name')
            ->get();
        
        $this->employees = Employee::where('employment_status', 'active')
            ->orderBy('full_name')
            ->get();

        $this->availableEquipment = Equipment::where('status', 'available')
            ->orderBy('name')
            ->get();

        $this->allEquipment = Equipment::orderBy('name')->get();
    }

    public function setActiveTab($tab)
    {
        $this->activeTab = $tab;
        $this->resetPage();
    }

    // Equipment Methods
    public function createEquipment()
    {
        $this->resetEquipmentForm();
        $this->isEditing = false;
        $this->showEquipmentModal = true;
    }

    public function editEquipment($id)
    {
        $equipment = Equipment::findOrFail($id);
        
        $this->equipment_id = $equipment->id;
        $this->name = $equipment->name;
        $this->equipment_type = $equipment->work_equipment_category_id;
        $this->serial_number = $equipment->serial_number;
        $this->asset_code = $equipment->asset_code;
        $this->brand = $equipment->brand;
        $this->model = $equipment->model;
        $this->purchase_date = $equipment->purchase_date?->format('Y-m-d');
        $this->purchase_cost = $equipment->purchase_cost;
        $this->warranty_expiry = $equipment->warranty_expiry?->format('Y-m-d');
        $this->condition = $equipment->condition;
        $this->status = $equipment->status;
        $this->notes = $equipment->notes;
        
        $this->isEditing = true;
        $this->showEquipmentModal = true;
    }

    public function saveEquipment()
    {
        $this->validate($this->rules);

        try {
            // Gerar asset_code aleatório se não for fornecido
            if (empty($this->asset_code)) {
                $this->asset_code = 'EQ-' . strtoupper(substr(uniqid(), -8));
            }

            $data = [
                'name' => $this->name,
                'work_equipment_category_id' => $this->equipment_type,
                'serial_number' => $this->serial_number,
                'asset_code' => $this->asset_code,
                'brand' => $this->brand,
                'model' => $this->model,
                'purchase_date' => $this->purchase_date,
                'purchase_cost' => $this->purchase_cost,
                'warranty_expiry' => $this->warranty_expiry,
                'condition' => $this->condition,
                'status' => $this->status,
                'notes' => $this->notes,
            ];

            if ($this->isEditing) {
                Equipment::findOrFail($this->equipment_id)->update($data);
                $this->dispatch('success', __('messages.updated_successfully'));
            } else {
                Equipment::create($data);
                $this->dispatch('success', __('messages.created_successfully'));
            }

            $this->closeEquipmentModal();
            $this->loadData();
        } catch (\Exception $e) {
            $this->dispatch('error', __('messages.error_occurred') . ': ' . $e->getMessage());
        }
    }

    public function closeEquipmentModal()
    {
        $this->showEquipmentModal = false;
        $this->resetEquipmentForm();
    }

    public function resetEquipmentForm()
    {
        $this->reset([
            'equipment_id',
            'name',
            'equipment_type',
            'serial_number',
            'asset_code',
            'brand',
            'model',
            'purchase_date',
            'purchase_cost',
            'warranty_expiry',
            'condition',
            'notes',
        ]);
        $this->status = 'available';
        $this->isEditing = false;
    }

    // Assignment Methods
    public function createAssignment()
    {
        $this->resetAssignmentForm();
        $this->isEditing = false;
        $this->showAssignmentModal = true;
        $this->loadData();
    }

    public function editAssignment($id)
    {
        $assignment = EmployeeEquipment::findOrFail($id);
        
        $this->assignment_id = $assignment->id;
        $this->employee_id = $assignment->employee_id;
        $this->equipment_id_assignment = $assignment->equipment_id;
        $this->issue_date = $assignment->assigned_date?->format('Y-m-d');
        $this->return_date = $assignment->return_date?->format('Y-m-d');
        $this->condition_on_issue = $assignment->condition_on_issue;
        $this->assignment_status = $assignment->status;
        $this->assignment_notes = $assignment->notes;
        
        $this->isEditing = true;
        $this->showAssignmentModal = true;
        $this->loadData();
    }

    public function saveAssignment()
    {
        $this->validate($this->assignmentRules);

        try {
            // Definir valor padrão para condition_on_issue se vazio
            if (empty($this->condition_on_issue)) {
                $this->condition_on_issue = 'Good';
            }

            $data = [
                'employee_id' => $this->employee_id,
                'equipment_id' => $this->equipment_id_assignment,
                'issue_date' => $this->issue_date,
                'return_date' => $this->return_date,
                'condition_on_issue' => $this->condition_on_issue,
                'status' => $this->assignment_status,
                'notes' => $this->assignment_notes,
                'issued_by' => auth()->id(),
            ];

            if ($this->isEditing) {
                EmployeeEquipment::findOrFail($this->assignment_id)->update($data);
                $this->dispatch('success', __('messages.updated_successfully'));
            } else {
                EmployeeEquipment::create($data);
                
                // Update equipment status
                Equipment::findOrFail($this->equipment_id_assignment)->update(['status' => 'assigned']);
                
                $this->dispatch('success', __('messages.created_successfully'));
            }

            $this->closeAssignmentModal();
            $this->loadData();
        } catch (\Exception $e) {
            $this->dispatch('error', __('messages.error_occurred') . ': ' . $e->getMessage());
        }
    }

    public function markAsReturned($id)
    {
        try {
            $assignment = EmployeeEquipment::findOrFail($id);
            $assignment->update([
                'status' => 'returned',
                'return_date' => now(),
            ]);

            // Update equipment status back to available
            Equipment::findOrFail($assignment->equipment_id)->update(['status' => 'available']);

            $this->dispatch('success', __('messages.equipment_returned_successfully'));
            $this->loadData();
        } catch (\Exception $e) {
            $this->dispatch('error', __('messages.error_occurred') . ': ' . $e->getMessage());
        }
    }

    public function closeAssignmentModal()
    {
        $this->showAssignmentModal = false;
        $this->resetAssignmentForm();
    }

    public function resetAssignmentForm()
    {
        $this->reset([
            'assignment_id',
            'employee_id',
            'equipment_id_assignment',
            'issue_date',
            'return_date',
            'condition_on_issue',
            'assignment_notes',
        ]);
        $this->assignment_status = 'issued';
        $this->isEditing = false;
    }

    // Delete Methods
    public function confirmDelete($id, $type)
    {
        $this->deleteId = $id;
        $this->deleteType = $type;
        $this->showDeleteModal = true;
    }

    public function confirmDeleteAssignment($id)
    {
        $this->deleteId = $id;
        $this->deleteType = 'assignment';
        $this->showDeleteModal = true;
    }

    public function delete()
    {
        try {
            if ($this->deleteType === 'equipment') {
                Equipment::findOrFail($this->deleteId)->delete();
            } elseif ($this->deleteType === 'assignment') {
                $assignment = EmployeeEquipment::findOrFail($this->deleteId);
                
                // Update equipment status if assignment was active
                if ($assignment->status === 'issued') {
                    Equipment::findOrFail($assignment->equipment_id)->update(['status' => 'available']);
                }
                
                $assignment->delete();
            }

            $this->dispatch('success', __('messages.deleted_successfully'));
            $this->closeDeleteModal();
            $this->loadData();
        } catch (\Exception $e) {
            $this->dispatch('error', __('messages.error_occurred') . ': ' . $e->getMessage());
        }
    }

    public function closeDeleteModal()
    {
        $this->showDeleteModal = false;
        $this->deleteId = null;
        $this->deleteType = '';
    }

    // Sorting & Filtering
    public function sortByEquipment($field)
    {
        if ($this->sortFieldEquipment === $field) {
            $this->sortDirectionEquipment = $this->sortDirectionEquipment === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortFieldEquipment = $field;
            $this->sortDirectionEquipment = 'asc';
        }
    }

    public function resetFilters()
    {
        $this->searchEquipment = '';
        $this->searchAssignment = '';
        $this->resetPage();
    }

    public function render()
    {
        $equipment = Equipment::query()
            ->with('category')
            ->when($this->searchEquipment, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->searchEquipment . '%')
                      ->orWhere('serial_number', 'like', '%' . $this->searchEquipment . '%')
                      ->orWhere('asset_code', 'like', '%' . $this->searchEquipment . '%');
                });
            })
            ->when($this->filters['equipment_type'], function ($query) {
                $query->where('work_equipment_category_id', $this->filters['equipment_type']);
            })
            ->when($this->filters['status'], function ($query) {
                $query->where('status', $this->filters['status']);
            })
            ->orderBy($this->sortFieldEquipment, $this->sortDirectionEquipment)
            ->paginate($this->perPage);

        $assignments = EmployeeEquipment::query()
            ->with(['employee', 'equipment'])
            ->when($this->searchAssignment, function ($query) {
                $query->whereHas('employee', function ($q) {
                    $q->where('full_name', 'like', '%' . $this->searchAssignment . '%');
                })->orWhereHas('equipment', function ($q) {
                    $q->where('name', 'like', '%' . $this->searchAssignment . '%');
                });
            })
            ->orderBy('issue_date', 'desc')
            ->paginate($this->perPage);

        return view('livewire.hr.work-equipment', [
            'equipment' => $equipment,
            'assignments' => $assignments,
        ]);
    }
}
