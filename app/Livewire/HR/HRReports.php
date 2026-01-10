<?php

declare(strict_types=1);

namespace App\Livewire\HR;

use App\Models\HR\Employee;
use App\Models\HR\PayrollPeriod;
use App\Models\HR\PayrollBatch;
use App\Models\HR\OvertimeRecord;
use App\Models\HR\SalaryAdvance;
use App\Models\HR\SalaryDiscount;
use App\Models\HR\Leave;
use App\Models\HR\DisciplinaryMeasure;
use Livewire\Component;
use Carbon\Carbon;

class HRReports extends Component
{
    public string $period = 'current_month';
    public $startDate;
    public $endDate;

    public function mount()
    {
        $this->updateDateRange();
    }

    public function updatedPeriod()
    {
        $this->updateDateRange();
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

    public function getReportStatsProperty()
    {
        $start = $this->startDate->format('Y-m-d');
        $end = $this->endDate->format('Y-m-d');

        return [
            'total_employees' => Employee::where('employment_status', 'active')->count(),
            'payroll_periods' => PayrollPeriod::whereBetween('start_date', [$start, $end])->count(),
            'payroll_batches' => PayrollBatch::whereBetween('created_at', [$start, $end])->count(),
            'overtime_records' => OvertimeRecord::whereBetween('date', [$start, $end])->count(),
            'salary_advances' => SalaryAdvance::whereBetween('request_date', [$start, $end])->count(),
            'salary_discounts' => SalaryDiscount::whereBetween('request_date', [$start, $end])->count(),
            'leave_requests' => Leave::whereBetween('start_date', [$start, $end])->count(),
            'disciplinary_measures' => DisciplinaryMeasure::whereBetween('applied_date', [$start, $end])->count(),
        ];
    }

    public function render()
    {
        return view('livewire.hr.hr-reports', [
            'stats' => $this->reportStats,
        ]);
    }
}
