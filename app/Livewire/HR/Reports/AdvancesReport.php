<?php

declare(strict_types=1);

namespace App\Livewire\HR\Reports;

use App\Models\HR\SalaryAdvance;
use App\Models\HR\Employee;
use App\Models\HR\Department;
use Livewire\Component;
use Livewire\WithPagination;
use Carbon\Carbon;

class AdvancesReport extends Component
{
    use WithPagination;

    public string $period = 'current_month';
    public $startDate;
    public $endDate;
    public string $departmentFilter = '';
    public string $employeeFilter = '';
    public string $statusFilter = '';

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

    public function getAdvancesProperty()
    {
        $query = SalaryAdvance::with(['employee.department'])
            ->whereBetween('request_date', [$this->startDate->format('Y-m-d'), $this->endDate->format('Y-m-d')]);

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

        return $query->orderBy('request_date', 'desc')->paginate(20);
    }

    public function getSummaryProperty()
    {
        $query = SalaryAdvance::whereBetween('request_date', [$this->startDate->format('Y-m-d'), $this->endDate->format('Y-m-d')]);

        if ($this->departmentFilter) {
            $query->whereHas('employee', function($q) {
                $q->where('department_id', $this->departmentFilter);
            });
        }

        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        $advances = $query->get();
        
        return [
            'total_advances' => $advances->count(),
            'total_amount' => $advances->sum('amount'),
            'total_remaining' => $advances->sum(function($advance) {
                return $advance->remaining_installments * $advance->installment_amount;
            }),
            'pending_count' => $advances->where('status', 'pending')->count(),
            'approved_count' => $advances->where('status', 'approved')->count(),
            'paid_count' => $advances->where('status', 'paid')->count(),
            'completed_count' => $advances->where('status', 'completed')->count(),
            'avg_amount' => $advances->avg('amount'),
            'avg_installments' => $advances->avg('installments'),
        ];
    }

    public function getDepartmentsProperty()
    {
        return Department::orderBy('name')->get();
    }

    public function render()
    {
        return view('livewire.hr.reports.advances-report', [
            'advances' => $this->advances,
            'summary' => $this->summary,
            'departments' => $this->departments,
        ]);
    }
}
