<?php

declare(strict_types=1);

namespace App\Livewire\HR\Reports;

use App\Models\HR\OvertimeRecord;
use App\Models\HR\Employee;
use App\Models\HR\Department;
use Livewire\Component;
use Livewire\WithPagination;
use Carbon\Carbon;

class OvertimeReport extends Component
{
    use WithPagination;

    public string $period = 'current_month';
    public $startDate;
    public $endDate;
    public string $departmentFilter = '';
    public string $employeeFilter = '';
    public string $statusFilter = '';
    public string $typeFilter = ''; // regular or night_shift

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

    public function getOvertimeRecordsProperty()
    {
        $query = OvertimeRecord::with(['employee.department'])
            ->whereBetween('date', [$this->startDate->format('Y-m-d'), $this->endDate->format('Y-m-d')]);

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

        if ($this->typeFilter === 'regular') {
            $query->where(function($q) {
                $q->where('is_night_shift', 0)->orWhereNull('is_night_shift');
            });
        } elseif ($this->typeFilter === 'night_shift') {
            $query->where('is_night_shift', 1);
        }

        return $query->orderBy('date', 'desc')->paginate(20);
    }

    public function getSummaryProperty()
    {
        $query = OvertimeRecord::whereBetween('date', [$this->startDate->format('Y-m-d'), $this->endDate->format('Y-m-d')]);

        if ($this->departmentFilter) {
            $query->whereHas('employee', function($q) {
                $q->where('department_id', $this->departmentFilter);
            });
        }

        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        $regularQuery = clone $query;
        $regularQuery->where(function($q) {
            $q->where('is_night_shift', 0)->orWhereNull('is_night_shift');
        });

        $nightQuery = clone $query;
        $nightQuery->where('is_night_shift', 1);

        return [
            'total_records' => $query->count(),
            'total_hours' => $query->sum('hours'),
            'total_amount' => $query->sum('amount'),
            'regular_hours' => $regularQuery->sum('hours'),
            'regular_amount' => $regularQuery->sum('amount'),
            'night_shift_hours' => $nightQuery->sum('direct_hours'),
            'night_shift_amount' => $nightQuery->sum('amount'),
            'pending_count' => (clone $query)->where('status', 'pending')->count(),
            'approved_count' => (clone $query)->where('status', 'approved')->count(),
            'rejected_count' => (clone $query)->where('status', 'rejected')->count(),
        ];
    }

    public function getDepartmentsProperty()
    {
        return Department::orderBy('name')->get();
    }

    public function render()
    {
        return view('livewire.hr.reports.overtime-report', [
            'overtimeRecords' => $this->overtimeRecords,
            'summary' => $this->summary,
            'departments' => $this->departments,
        ]);
    }
}
