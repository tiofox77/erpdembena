<?php

namespace App\Livewire\HR;

use App\Models\HR\Leave;
use App\Models\HR\LeaveType;
use App\Models\HR\Department;
use App\Models\HR\Employee;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;

class LeaveManagement extends Component
{
    use WithPagination, WithFileUploads;

    public $search = '';
    public $perPage = 10;
    public $sortField = 'start_date';
    public $sortDirection = 'desc';
    public $filters = [
        'department_id' => '',
        'leave_type_id' => '',
        'status' => '',
        'start_date' => '',
        'end_date' => '',
    ];

    // Form properties
    public $leave_id;
    public $employee_id;
    public $leave_type_id;
    public $start_date;
    public $end_date;
    public $total_days;
    public $reason;
    public $status;
    public $attachment;
    public $existing_attachment;
    public $rejection_reason;

    // Modal flags
    public $showModal = false;
    public $showLeaveModal = false;
    public $showApproveModal = false;
    public $showRejectModal = false;
    public $showDeleteModal = false;
    public $isEditing = false;
    public $isEditingLeave = false;

    // Listeners
    protected $listeners = ['refreshLeaves' => '$refresh'];

    // Rules
    protected function rules()
    {
        return [
            'employee_id' => 'required|exists:employees,id',
            'leave_type_id' => 'required|exists:leave_types,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'total_days' => 'required|numeric|min:0.5',
            'reason' => 'nullable',
            'attachment' => $this->isEditing ? 'nullable|file|max:5120' : 'nullable|file|max:5120',
            'status' => 'required|in:pending,approved,rejected,cancelled',
            'rejection_reason' => 'nullable|required_if:status,rejected',
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

    public function resetFilters()
    {
        $this->reset('filters');
        $this->search = '';
        $this->resetPage();
    }

    public function updatedStartDate()
    {
        $this->calculateTotalDays();
    }

    public function updatedEndDate()
    {
        $this->calculateTotalDays();
    }

    private function calculateTotalDays()
    {
        if ($this->start_date && $this->end_date) {
            $start = Carbon::parse($this->start_date);
            $end = Carbon::parse($this->end_date);
            
            // Calculate number of days excluding weekends
            $totalDays = 0;
            for ($date = $start; $date->lte($end); $date->addDay()) {
                if (!$date->isWeekend()) {
                    $totalDays++;
                }
            }
            
            $this->total_days = $totalDays;
        }
    }

    public function create()
    {
        $this->reset([
            'leave_id', 'employee_id', 'leave_type_id', 'start_date', 'end_date',
            'total_days', 'reason', 'status', 'attachment', 'existing_attachment',
            'rejection_reason'
        ]);
        $this->status = 'pending';
        $this->isEditing = false;
        $this->showModal = true;
    }

    public function edit(Leave $leave)
    {
        $this->leave_id = $leave->id;
        $this->employee_id = $leave->employee_id;
        $this->leave_type_id = $leave->leave_type_id;
        $this->start_date = $leave->start_date->format('Y-m-d');
        $this->end_date = $leave->end_date->format('Y-m-d');
        $this->total_days = $leave->total_days;
        $this->reason = $leave->reason;
        $this->status = $leave->status;
        $this->existing_attachment = $leave->attachment;
        $this->rejection_reason = $leave->rejection_reason;

        $this->isEditing = true;
        $this->showModal = true;
    }

    public function showApprove(Leave $leave)
    {
        $this->leave_id = $leave->id;
        $this->showApproveModal = true;
    }

    public function showReject(Leave $leave)
    {
        $this->leave_id = $leave->id;
        $this->rejection_reason = '';
        $this->showRejectModal = true;
    }

    public function confirmDelete(Leave $leave)
    {
        $this->leave_id = $leave->id;
        $this->showDeleteModal = true;
    }

    public function approve()
    {
        $leave = Leave::find($this->leave_id);
        $leave->status = 'approved';
        $leave->approved_by = auth()->id();
        $leave->approved_date = now();
        $leave->save();

        $this->showApproveModal = false;
        session()->flash('message', 'Leave request approved successfully.');
    }

    public function reject()
    {
        $this->validate([
            'rejection_reason' => 'required',
        ]);

        $leave = Leave::find($this->leave_id);
        $leave->status = 'rejected';
        $leave->rejection_reason = $this->rejection_reason;
        $leave->save();

        $this->showRejectModal = false;
        session()->flash('message', 'Leave request rejected successfully.');
    }

    public function save()
    {
        $validatedData = $this->validate();

        // Handle file upload
        if ($this->attachment && is_object($this->attachment)) {
            $validatedData['attachment'] = $this->attachment->store('leave-attachments', 'public');
        } elseif ($this->isEditing) {
            // Keep the existing attachment if no new one is provided
            $validatedData['attachment'] = $this->existing_attachment;
        }

        if ($this->isEditing) {
            $leave = Leave::find($this->leave_id);
            $leave->update($validatedData);
            session()->flash('message', 'Leave request updated successfully.');
        } else {
            $validatedData['created_by'] = Auth::id();
            Leave::create($validatedData);
            session()->flash('message', 'Leave request created successfully.');
        }

        $this->showModal = false;
        $this->reset([
            'leave_id', 'employee_id', 'leave_type_id', 'start_date', 'end_date',
            'total_days', 'reason', 'status', 'attachment', 'existing_attachment',
            'rejection_reason'
        ]);
    }

    public function delete()
    {
        $leave = Leave::find($this->leave_id);
        $leave->delete();
        $this->showDeleteModal = false;
        session()->flash('message', 'Leave request deleted successfully.');
    }

    public function view($id)
    {
        $this->currentLeave = Leave::with(['employee', 'leaveType', 'approver', 'creator'])
            ->findOrFail($id);
            
        $this->showViewModal = true;
    }

    public function closeViewModal()
    {
        $this->showViewModal = false;
        $this->currentLeave = null;
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

    public function approveLeave($id)
    {
        $leave = Leave::findOrFail($id);
        $leave->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_date' => now()
        ]);
        
        if ($this->showViewModal) {
            $this->closeViewModal();
        }
        
        session()->flash('message', 'Leave request approved successfully.');
    }
    
    public function rejectLeave($id)
    {
        $leave = Leave::findOrFail($id);
        
        // In a real application, you would show a form to collect rejection reason
        $this->rejection_reason = 'Request rejected';
        
        $leave->update([
            'status' => 'rejected',
            'approved_by' => auth()->id(),
            'approved_date' => now(),
            'rejection_reason' => $this->rejection_reason
        ]);
        
        if ($this->showViewModal) {
            $this->closeViewModal();
        }
        
        session()->flash('message', 'Leave request rejected successfully.');
    }
    
    public function confirmCancelLeave($id)
    {
        $this->leave_id = $id;
        $this->showDeleteModal = true;
    }
    
    public function cancelLeave()
    {
        $leave = Leave::findOrFail($this->leave_id);
        $leave->update([
            'status' => 'cancelled'
        ]);
        
        $this->closeDeleteModal();
        session()->flash('message', 'Leave request cancelled successfully.');
    }

    public function openLeaveModal()
    {
        $this->reset([
            'leave_id', 'employee_id', 'leave_type_id', 'start_date',
            'end_date', 'total_days', 'reason', 'status', 'attachment',
            'existing_attachment', 'rejection_reason'
        ]);
        $this->isEditingLeave = false;
        $this->showLeaveModal = true;
    }

    public function closeLeaveModal()
    {
        $this->showLeaveModal = false;
        $this->resetValidation();
    }

    public function editLeave($leaveId)
    {
        $leave = Leave::findOrFail($leaveId);
        
        $this->leave_id = $leave->id;
        $this->employee_id = $leave->employee_id;
        $this->leave_type_id = $leave->leave_type_id;
        $this->start_date = $leave->start_date->format('Y-m-d');
        $this->end_date = $leave->end_date->format('Y-m-d');
        $this->total_days = $leave->days;
        $this->reason = $leave->reason;
        $this->status = $leave->status;
        $this->existing_attachment = $leave->attachment;
        
        $this->isEditingLeave = true;
        $this->showLeaveModal = true;
    }

    public function saveLeave()
    {
        $validatedData = $this->validate([
            'employee_id' => 'required',
            'leave_type_id' => 'required',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string',
            'status' => $this->isEditingLeave ? 'required|in:pending,approved,rejected,cancelled' : 'nullable',
        ]);
        
        // Calculate days
        $startDate = Carbon::parse($this->start_date);
        $endDate = Carbon::parse($this->end_date);
        $days = $startDate->diffInDays($endDate) + 1;
        
        if ($this->isEditingLeave) {
            $leave = Leave::find($this->leave_id);
            
            // Handle attachment
            if ($this->attachment) {
                // Delete old attachment if exists
                if ($leave->attachment && file_exists(storage_path('app/public/' . $leave->attachment))) {
                    unlink(storage_path('app/public/' . $leave->attachment));
                }
                $validatedData['attachment'] = $this->attachment->store('leave-attachments', 'public');
            }
            
            $validatedData['days'] = $days;
            $leave->update($validatedData);
            session()->flash('message', 'Leave request updated successfully.');
        } else {
            // New leave request
            $validatedData['days'] = $days;
            $validatedData['status'] = 'pending';
            
            if ($this->attachment) {
                $validatedData['attachment'] = $this->attachment->store('leave-attachments', 'public');
            }
            
            Leave::create($validatedData);
            session()->flash('message', 'Leave request submitted successfully.');
        }
        
        $this->showLeaveModal = false;
        $this->resetValidation();
        $this->reset([
            'leave_id', 'employee_id', 'leave_type_id', 'start_date',
            'end_date', 'total_days', 'reason', 'status', 'attachment',
            'existing_attachment', 'rejection_reason'
        ]);
    }

    public function render()
    {
        $query = Leave::query()
            ->with(['employee', 'leaveType'])
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
            ->when($this->filters['leave_type_id'], function ($query) {
                return $query->where('leave_type_id', $this->filters['leave_type_id']);
            })
            ->when($this->filters['status'], function ($query) {
                return $query->where('status', $this->filters['status']);
            })
            ->when($this->filters['start_date'], function ($query) {
                return $query->whereDate('start_date', '>=', $this->filters['start_date']);
            })
            ->when($this->filters['end_date'], function ($query) {
                return $query->whereDate('end_date', '<=', $this->filters['end_date']);
            });

        $leaves = $query->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        $employees = Employee::where('employment_status', 'active')->get();
        $leaveTypes = LeaveType::where('is_active', true)->get();
        $departments = Department::where('is_active', true)->get();

        return view('livewire.hr.leave-management', [
            'leaves' => $leaves,
            'employees' => $employees,
            'leaveTypes' => $leaveTypes,
            'departments' => $departments,
        ]);
    }
}
