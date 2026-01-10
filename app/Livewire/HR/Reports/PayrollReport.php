<?php

declare(strict_types=1);

namespace App\Livewire\HR\Reports;

use App\Models\HR\Payroll;
use App\Models\HR\PayrollPeriod;
use App\Models\HR\Department;
use App\Models\HR\Employee;
use Livewire\Component;
use Livewire\WithPagination;
use Carbon\Carbon;

class PayrollReport extends Component
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

    public function getPayrollsProperty()
    {
        $query = Payroll::with(['employee.department', 'payrollPeriod'])
            ->whereBetween('created_at', [$this->startDate, $this->endDate]);

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

        return $query->orderBy('created_at', 'desc')->paginate(20);
    }

    public function getSummaryProperty()
    {
        $query = Payroll::whereBetween('created_at', [$this->startDate, $this->endDate]);

        if ($this->departmentFilter) {
            $query->whereHas('employee', function($q) {
                $q->where('department_id', $this->departmentFilter);
            });
        }

        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        return [
            'total_employees' => $query->distinct('employee_id')->count('employee_id'),
            'total_gross' => $query->sum('gross_salary'),
            'total_net' => $query->sum('net_salary'),
            'total_deductions' => $query->sum('deductions'),
            'total_inss' => $query->sum('inss'),
            'total_irt' => $query->sum('irt'),
            'total_overtime' => $query->sum('overtime_amount'),
            'total_advances' => $query->sum('advance_deduction'),
            'total_discounts' => $query->sum('salary_discount'),
        ];
    }

    public function getDepartmentsProperty()
    {
        return Department::orderBy('name')->get();
    }

    public function render()
    {
        return view('livewire.hr.reports.payroll-report', [
            'payrolls' => $this->payrolls,
            'summary' => $this->summary,
            'departments' => $this->departments,
        ]);
    }
}
