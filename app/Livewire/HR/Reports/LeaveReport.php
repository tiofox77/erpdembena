<?php

declare(strict_types=1);

namespace App\Livewire\HR\Reports;

use App\Models\HR\Leave;
use App\Models\HR\Employee;
use App\Models\HR\Department;
use App\Models\HR\LeaveType;
use Livewire\Component;
use Livewire\WithPagination;
use Carbon\Carbon;

class LeaveReport extends Component
{
    use WithPagination;

    public string $period = 'current_month';
    public $startDate;
    public $endDate;
    public string $departmentFilter = '';
    public string $employeeFilter = '';
    public string $statusFilter = '';
    public string $typeFilter = '';

    public function mount()
    {
        $this->updateDateRange();
    }

    public function updatedPeriod()
    {
        $this->updateDateRange();
        $this->resetPage();
    }

    public function updatedDepartmentFilter()
    {
        $this->resetPage();
    }

    public function updatedEmployeeFilter()
    {
        $this->resetPage();
    }

    public function updatedStatusFilter()
    {
        $this->resetPage();
    }

    public function updatedTypeFilter()
    {
        $this->resetPage();
    }

    private function updateDateRange()
    {
        $now = Carbon::now();
        
        switch ($this->period) {
            case 'current_month':
                $this->startDate = $now->copy()->startOfMonth();
                $this->endDate = $now->copy()->endOfMonth();
                break;
            case 'last_month':
                $this->startDate = $now->copy()->subMonth()->startOfMonth();
                $this->endDate = $now->copy()->subMonth()->endOfMonth();
                break;
            case 'current_quarter':
                $this->startDate = $now->copy()->startOfQuarter();
                $this->endDate = $now->copy()->endOfQuarter();
                break;
            case 'current_year':
                $this->startDate = $now->copy()->startOfYear();
                $this->endDate = $now->copy()->endOfYear();
                break;
            default:
                $this->startDate = $now->copy()->startOfMonth();
                $this->endDate = $now->copy()->endOfMonth();
        }
    }

    public function getLeavesProperty()
    {
        $query = Leave::with(['employee.department', 'leaveType'])
            ->whereBetween('start_date', [$this->startDate->format('Y-m-d'), $this->endDate->format('Y-m-d')]);

        if ($this->departmentFilter) {
            $query->whereHas('employee', function($q) {
                $q->where('department_id', $this->departmentFilter);
            });
        }

        if ($this->employeeFilter) {
            $query->whereHas('employee', function($q) {
                $q->where('full_name', 'like', '%' . $this->employeeFilter . '%');
            });
        }

        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        if ($this->typeFilter) {
            $query->where('leave_type_id', $this->typeFilter);
        }

        return $query->orderBy('start_date', 'desc')->paginate(20);
    }

    public function getSummaryProperty()
    {
        $query = Leave::whereBetween('start_date', [$this->startDate->format('Y-m-d'), $this->endDate->format('Y-m-d')]);

        if ($this->departmentFilter) {
            $query->whereHas('employee', function($q) {
                $q->where('department_id', $this->departmentFilter);
            });
        }

        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        if ($this->typeFilter) {
            $query->where('leave_type_id', $this->typeFilter);
        }

        return [
            'total_leaves' => $query->count(),
            'total_days' => $query->sum('total_days'),
            'pending_count' => (clone $query)->where('status', 'pending')->count(),
            'approved_count' => (clone $query)->where('status', 'approved')->count(),
            'rejected_count' => (clone $query)->where('status', 'rejected')->count(),
            'avg_days' => $query->avg('total_days'),
        ];
    }

    public function getDepartmentsProperty()
    {
        return Department::orderBy('name')->get();
    }

    public function getLeaveTypesProperty()
    {
        return LeaveType::orderBy('name')->get();
    }

    public function render()
    {
        return view('livewire.hr.reports.leave-report', [
            'leaves' => $this->leaves,
            'summary' => $this->summary,
            'departments' => $this->departments,
            'leaveTypes' => $this->leaveTypes,
        ]);
    }
}
