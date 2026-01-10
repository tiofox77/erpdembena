<?php

declare(strict_types=1);

namespace App\Livewire\HR\Reports;

use App\Models\HR\SalaryDiscount;
use App\Models\HR\Employee;
use App\Models\HR\Department;
use Livewire\Component;
use Livewire\WithPagination;
use Carbon\Carbon;

class DiscountsReport extends Component
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

    public function getDiscountsProperty()
    {
        $query = SalaryDiscount::with(['employee.department'])
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

        if ($this->typeFilter) {
            $query->where('discount_type', $this->typeFilter);
        }

        return $query->orderBy('request_date', 'desc')->paginate(20);
    }

    public function getSummaryProperty()
    {
        $query = SalaryDiscount::whereBetween('request_date', [$this->startDate->format('Y-m-d'), $this->endDate->format('Y-m-d')]);

        if ($this->departmentFilter) {
            $query->whereHas('employee', function($q) {
                $q->where('department_id', $this->departmentFilter);
            });
        }

        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        if ($this->typeFilter) {
            $query->where('discount_type', $this->typeFilter);
        }

        $discounts = $query->get();
        
        return [
            'total_discounts' => $discounts->count(),
            'total_amount' => $discounts->sum('amount'),
            'total_remaining' => $discounts->sum(function($discount) {
                return $discount->remaining_installments * $discount->installment_amount;
            }),
            'pending_count' => $discounts->where('status', 'pending')->count(),
            'approved_count' => $discounts->where('status', 'approved')->count(),
            'completed_count' => $discounts->where('status', 'completed')->count(),
            'avg_amount' => $discounts->avg('amount'),
            'avg_installments' => $discounts->avg('installments'),
        ];
    }

    public function getDepartmentsProperty()
    {
        return Department::orderBy('name')->get();
    }

    public function getDiscountTypesProperty()
    {
        return SalaryDiscount::select('discount_type')
            ->distinct()
            ->whereNotNull('discount_type')
            ->pluck('discount_type');
    }

    public function render()
    {
        return view('livewire.hr.reports.discounts-report', [
            'discounts' => $this->discounts,
            'summary' => $this->summary,
            'departments' => $this->departments,
            'discountTypes' => $this->discountTypes,
        ]);
    }
}
