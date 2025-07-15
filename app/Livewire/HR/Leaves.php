<?php

declare(strict_types=1);

namespace App\Livewire\HR;

use App\Models\HR\Employee;
use App\Models\HR\Department;
use App\Models\HR\Leave;
use App\Models\HR\LeaveType;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;

class Leaves extends Component
{
    use WithPagination, WithFileUploads;

    // Filters
    public $filters = [
        'employee_id' => '',
        'department_id' => '',
        'leave_type_id' => '',
        'status' => '',
        'date_range' => '',
    ];

    // Leave properties
    public $leave_id;
    public $leave_employee_id;
    public $leave_type_id;
    public $start_date;
    public $end_date;
    public $total_days;
    public $reason;
    public $status;
    public $approved_by;
    public $approved_date;
    public $rejection_reason;
    public $attachment;
    public $temp_attachment;
    
    // Campos específicos para género
    public $is_gender_specific = false;
    public $gender_leave_type;
    public $medical_certificate_details;
    
    // Campos de pagamento
    public $is_paid_leave = true;
    public $payment_percentage = 100.00;
    public $payment_notes;
    public $affects_payroll = true;

    // UI States
    public $showLeaveModal = false;
    public $showDeleteModal = false;
    public $showApprovalModal = false;
    public $isEditing = false;
    public $searchQuery = '';
    
    // Sorting
    public $sortField = 'id';
    public $sortDirection = 'desc';

    protected $listeners = ['refreshLeaves' => '$refresh'];

    protected $queryString = [
        'searchQuery' => ['except' => ''],
        'sortField' => ['except' => 'id'],
        'sortDirection' => ['except' => 'desc'],
    ];

    protected $rules = [
        'leave_employee_id' => 'required',
        'leave_type_id' => 'required',
        'start_date' => 'required|date',
        'end_date' => 'required|date|after_or_equal:start_date',
        'reason' => 'required|string|max:500',
        'status' => 'nullable|string|in:pending,approved,rejected,cancelled',
        'rejection_reason' => 'nullable|string|max:500',
        'temp_attachment' => 'nullable|file|max:10240', // 10MB max
        
        // Campos específicos para género
        'is_gender_specific' => 'boolean',
        'gender_leave_type' => 'nullable|string|required_if:is_gender_specific,true',
        'medical_certificate_details' => 'nullable|string|max:1000',
        
        // Campos de pagamento
        'is_paid_leave' => 'boolean',
        'payment_percentage' => 'required|numeric|min:0|max:100',
        'payment_notes' => 'nullable|string|max:500',
        'affects_payroll' => 'boolean',
    ];

    protected $validationAttributes = [
        'leave_employee_id' => 'employee',
        'leave_type_id' => 'leave type',
        'start_date' => 'start date',
        'end_date' => 'end date',
        'rejection_reason' => 'rejection reason',
        'temp_attachment' => 'attachment',
        'is_gender_specific' => 'gender specific leave',
        'gender_leave_type' => 'gender leave type',
        'medical_certificate_details' => 'medical certificate details',
        'is_paid_leave' => 'paid leave',
        'payment_percentage' => 'payment percentage',
        'payment_notes' => 'payment notes',
        'affects_payroll' => 'affects payroll',
    ];

    public function mount()
    {
        $this->resetData();
    }

    public function render()
    {
        $employees = Employee::orderBy('full_name')->get();
        $departments = Department::orderBy('name')->get();
        $leaveTypes = LeaveType::where('is_active', true)->orderBy('name')->get();

        $query = Leave::query()
            ->with(['employee', 'employee.department', 'leaveType'])
            ->when($this->searchQuery, function ($query) {
                $query->whereHas('employee', function ($q) {
                    $q->where('full_name', 'like', '%' . $this->searchQuery . '%');
                });
            })
            ->when($this->filters['employee_id'], function ($query) {
                $query->where('employee_id', $this->filters['employee_id']);
            })
            ->when($this->filters['department_id'], function ($query) {
                $query->whereHas('employee', function ($q) {
                    $q->where('department_id', $this->filters['department_id']);
                });
            })
            ->when($this->filters['leave_type_id'], function ($query) {
                $query->where('leave_type_id', $this->filters['leave_type_id']);
            })
            ->when($this->filters['status'], function ($query) {
                $query->where('status', $this->filters['status']);
            })
            ->when($this->filters['date_range'], function ($query) {
                $dates = explode(' to ', $this->filters['date_range']);
                if(count($dates) == 2) {
                    $start = Carbon::parse($dates[0]);
                    $end = Carbon::parse($dates[1]);
                    $query->whereBetween('start_date', [$start, $end])
                        ->orWhereBetween('end_date', [$start, $end]);
                }
            })
            ->orderBy($this->sortField, $this->sortDirection);

        $leaves = $query->paginate(10);

        return view('livewire.hr.leaves', [
            'leaves' => $leaves,
            'employees' => $employees,
            'departments' => $departments,
            'leaveTypes' => $leaveTypes,
        ]);
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortDirection = 'asc';
        }
        
        $this->sortField = $field;
    }

    public function resetFilters()
    {
        $this->filters = [
            'employee_id' => '',
            'department_id' => '',
            'leave_type_id' => '',
            'status' => '',
            'date_range' => '',
        ];
    }

    public function openLeaveModal()
    {
        $this->resetData();
        $this->status = Leave::STATUS_PENDING;
        $this->showLeaveModal = true;
    }

    public function editLeave($id)
    {
        $this->isEditing = true;
        $this->leave_id = $id;
        
        $leave = Leave::findOrFail($id);
        
        $this->leave_employee_id = $leave->employee_id;
        $this->leave_type_id = $leave->leave_type_id;
        $this->start_date = $leave->start_date->format('Y-m-d');
        $this->end_date = $leave->end_date->format('Y-m-d');
        $this->total_days = $leave->total_days;
        $this->reason = $leave->reason;
        $this->status = $leave->status;
        $this->attachment = $leave->attachment;
        $this->approved_by = $leave->approved_by;
        $this->approved_date = $leave->approved_date ? $leave->approved_date->format('Y-m-d') : null;
        $this->rejection_reason = $leave->rejection_reason;
        
        // Campos específicos para género
        $this->is_gender_specific = $leave->is_gender_specific;
        $this->gender_leave_type = $leave->gender_leave_type;
        $this->medical_certificate_details = $leave->medical_certificate_details;
        
        // Campos de pagamento
        $this->is_paid_leave = $leave->is_paid_leave;
        $this->payment_percentage = $leave->payment_percentage;
        $this->payment_notes = $leave->payment_notes;
        $this->affects_payroll = $leave->affects_payroll;
        
        $this->showLeaveModal = true;
    }

    public function closeModal()
    {
        $this->showLeaveModal = false;
        $this->showDeleteModal = false;
        $this->showApprovalModal = false;
    }

    public function confirmDelete($id)
    {
        $this->leave_id = $id;
        $this->showDeleteModal = true;
    }

    public function deleteLeave()
    {
        $leave = Leave::findOrFail($this->leave_id);
        $leave->delete();
        
        session()->flash('message', 'Leave request deleted successfully.');
        $this->closeModal();
    }

    public function openApprovalModal($id)
    {
        $this->leave_id = $id;
        $leave = Leave::findOrFail($id);
        
        $this->status = $leave->status;
        $this->rejection_reason = $leave->rejection_reason;
        
        $this->showApprovalModal = true;
    }

    public function updateLeaveStatus()
    {
        if($this->status === Leave::STATUS_REJECTED && empty($this->rejection_reason)) {
            $this->addError('rejection_reason', 'Please provide a reason for rejection.');
            return;
        }
        
        $leave = Leave::findOrFail($this->leave_id);
        $leave->status = $this->status;
        $leave->rejection_reason = $this->rejection_reason;
        
        if(in_array($this->status, [Leave::STATUS_APPROVED, Leave::STATUS_REJECTED])) {
            $leave->approved_by = Auth::id();
            $leave->approved_date = now();
        }
        
        $leave->save();
        
        session()->flash('message', 'Leave request status updated successfully.');
        $this->closeModal();
    }

    public function saveLeave()
    {
        $this->validate();
        
        // Calculate total days
        $startDate = Carbon::parse($this->start_date);
        $endDate = Carbon::parse($this->end_date);
        $this->total_days = $endDate->diffInDays($startDate) + 1;
        
        $data = [
            'employee_id' => $this->leave_employee_id,
            'leave_type_id' => $this->leave_type_id,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'total_days' => $this->total_days,
            'reason' => $this->reason,
            'status' => $this->status ?? Leave::STATUS_PENDING,
            
            // Campos específicos para género
            'is_gender_specific' => $this->is_gender_specific,
            'gender_leave_type' => $this->is_gender_specific ? $this->gender_leave_type : null,
            'medical_certificate_details' => $this->is_gender_specific ? $this->medical_certificate_details : null,
            
            // Campos de pagamento
            'is_paid_leave' => $this->is_paid_leave,
            'payment_percentage' => $this->payment_percentage,
            'payment_notes' => $this->payment_notes,
            'affects_payroll' => $this->affects_payroll,
        ];
        
        // Handle attachment upload
        if ($this->temp_attachment) {
            $path = $this->temp_attachment->store('leave-attachments', 'public');
            $data['attachment'] = $path;
        }
        
        // Handle approval/rejection
        if (in_array($this->status, [Leave::STATUS_APPROVED, Leave::STATUS_REJECTED])) {
            $data['approved_by'] = Auth::id();
            $data['approved_date'] = now();
            
            if ($this->status === Leave::STATUS_REJECTED) {
                $data['rejection_reason'] = $this->rejection_reason;
            }
        }
        
        // Verificar se é licença específica para mulher e validar
        if ($this->is_gender_specific) {
            // Obter o funcionário
            $employee = Employee::find($this->leave_employee_id);
            
            if ($employee && $employee->gender !== 'female') {
                $this->addError('is_gender_specific', 'Licença específica de género só pode ser atribuída a funcionárias mulher.');
                return;
            }
        }
        
        if ($this->isEditing) {
            $leave = Leave::findOrFail($this->leave_id);
            $leave->update($data);
            session()->flash('message', 'Pedido de licença atualizado com sucesso.');
        } else {
            Leave::create($data);
            session()->flash('message', 'Pedido de licença criado com sucesso.');
        }
        
        $this->closeModal();
        $this->resetData();
    }

    private function resetData()
    {
        $this->leave_id = null;
        $this->leave_employee_id = '';
        $this->leave_type_id = '';
        $this->start_date = '';
        $this->end_date = '';
        $this->total_days = '';
        $this->reason = '';
        $this->status = '';
        $this->approved_by = null;
        $this->approved_date = null;
        $this->rejection_reason = '';
        $this->attachment = '';
        $this->temp_attachment = null;
        
        // Campos específicos para género
        $this->is_gender_specific = false;
        $this->gender_leave_type = null;
        $this->medical_certificate_details = null;
        
        // Campos de pagamento
        $this->is_paid_leave = true;
        $this->payment_percentage = 100.00;
        $this->payment_notes = null;
        $this->affects_payroll = true;
        
        $this->isEditing = false;
    }
}
