<?php

declare(strict_types=1);

namespace App\Livewire\HR;

use App\Models\HR\Employee;
use App\Models\HR\Attendance;
use App\Models\HR\Leave;
use App\Models\HR\Department;
use App\Models\HR\Payroll;
use App\Models\HR\PayrollPeriod;
use App\Models\HR\SalaryAdvance;
use App\Models\HR\SalaryDiscount;
use App\Models\HR\OvertimeRecord;
use Carbon\Carbon;
use Livewire\Component;
use Illuminate\Support\Facades\DB;

class HRDashboard extends Component
{
    // Filters
    public string $period = 'current_month';
    public ?int $departmentId = null;
    public ?int $payrollPeriodId = null;
    
    // Date range
    public string $startDate;
    public string $endDate;
    
    // KPIs
    public array $kpis = [];
    
    // Chart data
    public array $charts = [];
    
    public function mount(): void
    {
        $this->initializeDates();
        $this->loadData();
    }
    
    public function updatedPeriod(): void
    {
        $this->initializeDates();
        $this->loadData();
    }
    
    public function updatedDepartmentId(): void
    {
        $this->loadData();
    }
    
    public function updatedPayrollPeriodId(): void
    {
        if ($this->payrollPeriodId) {
            $period = PayrollPeriod::find($this->payrollPeriodId);
            if ($period) {
                $this->startDate = $period->start_date->format('Y-m-d');
                $this->endDate = $period->end_date->format('Y-m-d');
            }
        }
        $this->loadData();
    }
    
    private function initializeDates(): void
    {
        $now = Carbon::now();
        
        switch ($this->period) {
            case 'current_month':
                $this->startDate = $now->copy()->startOfMonth()->format('Y-m-d');
                $this->endDate = $now->copy()->endOfMonth()->format('Y-m-d');
                break;
            case 'last_month':
                $this->startDate = $now->copy()->subMonth()->startOfMonth()->format('Y-m-d');
                $this->endDate = $now->copy()->subMonth()->endOfMonth()->format('Y-m-d');
                break;
            case 'current_quarter':
                $this->startDate = $now->copy()->startOfQuarter()->format('Y-m-d');
                $this->endDate = $now->copy()->endOfQuarter()->format('Y-m-d');
                break;
            case 'current_year':
                $this->startDate = $now->copy()->startOfYear()->format('Y-m-d');
                $this->endDate = $now->copy()->endOfYear()->format('Y-m-d');
                break;
            default:
                $this->startDate = $now->copy()->startOfMonth()->format('Y-m-d');
                $this->endDate = $now->copy()->endOfMonth()->format('Y-m-d');
        }
    }
    
    private function loadData(): void
    {
        $this->loadKPIs();
        $this->loadCharts();
        
        $this->dispatch('charts-updated', charts: $this->charts);
    }
    
    private function loadKPIs(): void
    {
        $start = Carbon::parse($this->startDate);
        $end = Carbon::parse($this->endDate);
        
        // Base queries with department filter
        $employeeQuery = Employee::query();
        if ($this->departmentId) {
            $employeeQuery->where('department_id', $this->departmentId);
        }
        
        // Employee counts
        $totalEmployees = (clone $employeeQuery)->count();
        $activeEmployees = (clone $employeeQuery)->where('employment_status', 'active')->count();
        $newHires = (clone $employeeQuery)->whereBetween('hire_date', [$start, $end])->count();
        
        // Attendance
        $attendanceQuery = Attendance::whereBetween('date', [$start, $end]);
        if ($this->departmentId) {
            $attendanceQuery->whereHas('employee', fn($q) => $q->where('department_id', $this->departmentId));
        }
        $totalAttendance = (clone $attendanceQuery)->count();
        $presentCount = (clone $attendanceQuery)->where('status', 'present')->count();
        $lateCount = (clone $attendanceQuery)->where('status', 'late')->count();
        $absentCount = (clone $attendanceQuery)->where('status', 'absent')->count();
        $attendanceRate = $totalAttendance > 0 ? round(($presentCount / $totalAttendance) * 100, 1) : 0;
        
        // Overtime (approved only)
        $overtimeQuery = OvertimeRecord::whereBetween('date', [$start, $end])->where('status', 'approved');
        if ($this->departmentId) {
            $overtimeQuery->whereHas('employee', fn($q) => $q->where('department_id', $this->departmentId));
        }
        $totalOvertimeHours = (float) (clone $overtimeQuery)->sum('hours');
        $totalOvertimeCost = (float) (clone $overtimeQuery)->sum('amount');
        
        // Leaves
        $leaveQuery = Leave::whereBetween('start_date', [$start, $end]);
        if ($this->departmentId) {
            $leaveQuery->whereHas('employee', fn($q) => $q->where('department_id', $this->departmentId));
        }
        $pendingLeaves = (clone $leaveQuery)->where('status', 'pending')->count();
        $approvedLeaves = (clone $leaveQuery)->where('status', 'approved')->count();
        
        // Salary Advances & Discounts (approved/completed)
        $advanceQuery = SalaryAdvance::whereBetween('request_date', [$start, $end])
            ->whereIn('status', ['approved', 'completed']);
        $discountQuery = SalaryDiscount::whereBetween('request_date', [$start, $end])
            ->whereIn('status', ['approved', 'completed']);
        if ($this->departmentId) {
            $advanceQuery->whereHas('employee', fn($q) => $q->where('department_id', $this->departmentId));
            $discountQuery->whereHas('employee', fn($q) => $q->where('department_id', $this->departmentId));
        }
        $totalAdvances = (float) $advanceQuery->sum('amount');
        $totalDiscounts = (float) $discountQuery->sum('amount');
        
        // Payroll
        $payrollQuery = Payroll::query();
        if ($this->payrollPeriodId) {
            $payrollQuery->where('payroll_period_id', $this->payrollPeriodId);
        } else {
            $payrollQuery->whereHas('payrollPeriod', fn($q) => $q->whereBetween('start_date', [$start, $end]));
        }
        if ($this->departmentId) {
            $payrollQuery->whereHas('employee', fn($q) => $q->where('department_id', $this->departmentId));
        }
        $totalPayroll = (float) $payrollQuery->sum('net_salary');
        $payrollCount = $payrollQuery->count();
        
        $this->kpis = [
            'totalEmployees' => $totalEmployees,
            'activeEmployees' => $activeEmployees,
            'newHires' => $newHires,
            'attendanceRate' => $attendanceRate,
            'presentCount' => $presentCount,
            'lateCount' => $lateCount,
            'absentCount' => $absentCount,
            'totalOvertimeHours' => round($totalOvertimeHours, 1),
            'totalOvertimeCost' => $totalOvertimeCost,
            'pendingLeaves' => $pendingLeaves,
            'approvedLeaves' => $approvedLeaves,
            'totalAdvances' => $totalAdvances,
            'totalDiscounts' => $totalDiscounts,
            'totalPayroll' => $totalPayroll,
            'payrollCount' => $payrollCount,
        ];
    }
    
    private function loadCharts(): void
    {
        $start = Carbon::parse($this->startDate);
        $end = Carbon::parse($this->endDate);
        
        // 1. Department Distribution (Doughnut)
        $departmentData = Employee::select('departments.name', DB::raw('COUNT(*) as total'))
            ->join('departments', 'employees.department_id', '=', 'departments.id')
            ->where('employees.employment_status', 'active')
            ->groupBy('departments.id', 'departments.name')
            ->get();
        
        $this->charts['department'] = [
            'labels' => $departmentData->pluck('name')->toArray(),
            'data' => $departmentData->pluck('total')->toArray(),
        ];
        
        // 2. Attendance Summary (Doughnut)
        $this->charts['attendance'] = [
            'labels' => ['Presente', 'Atrasado', 'Ausente'],
            'data' => [
                $this->kpis['presentCount'] ?? 0,
                $this->kpis['lateCount'] ?? 0,
                $this->kpis['absentCount'] ?? 0,
            ],
        ];
        
        // 3. Leave Status (Pie)
        $leaveStatusData = Leave::select('status', DB::raw('COUNT(*) as total'))
            ->whereBetween('start_date', [$start, $end])
            ->when($this->departmentId, fn($q) => $q->whereHas('employee', fn($e) => $e->where('department_id', $this->departmentId)))
            ->groupBy('status')
            ->get()
            ->pluck('total', 'status')
            ->toArray();
        
        $this->charts['leaves'] = [
            'labels' => ['Pendente', 'Aprovado', 'Rejeitado'],
            'data' => [
                $leaveStatusData['pending'] ?? 0,
                $leaveStatusData['approved'] ?? 0,
                $leaveStatusData['rejected'] ?? 0,
            ],
        ];
        
        // 4. Overtime by Department (Bar)
        $overtimeByDept = OvertimeRecord::select('departments.name', DB::raw('SUM(overtime_records.hours) as total_hours'))
            ->join('employees', 'overtime_records.employee_id', '=', 'employees.id')
            ->join('departments', 'employees.department_id', '=', 'departments.id')
            ->whereBetween('overtime_records.date', [$start, $end])
            ->where('overtime_records.status', 'approved')
            ->groupBy('departments.id', 'departments.name')
            ->get();
        
        $this->charts['overtimeByDept'] = [
            'labels' => $overtimeByDept->pluck('name')->toArray(),
            'data' => $overtimeByDept->pluck('total_hours')->map(fn($v) => round((float)$v, 1))->toArray(),
        ];
        
        // 5. Monthly Payroll Trend (Line) - Last 6 periods
        $payrollTrend = PayrollPeriod::with(['payrolls' => function($q) {
                if ($this->departmentId) {
                    $q->whereHas('employee', fn($e) => $e->where('department_id', $this->departmentId));
                }
            }])
            ->orderBy('start_date', 'desc')
            ->limit(6)
            ->get()
            ->reverse();
        
        $this->charts['payrollTrend'] = [
            'labels' => $payrollTrend->pluck('name')->toArray(),
            'data' => $payrollTrend->map(fn($p) => $p->payrolls->sum('net_salary'))->toArray(),
        ];
        
        // 6. Advances vs Discounts (Doughnut)
        $this->charts['advancesDiscounts'] = [
            'labels' => ['Adiantamentos', 'Descontos'],
            'data' => [
                $this->kpis['totalAdvances'] ?? 0,
                $this->kpis['totalDiscounts'] ?? 0,
            ],
        ];
    }
    
    public function render()
    {
        $departments = Department::where('is_active', true)->orderBy('name')->get();
        $payrollPeriods = PayrollPeriod::orderBy('start_date', 'desc')->limit(12)->get();
        
        return view('livewire.hr.hr-dashboard', [
            'departments' => $departments,
            'payrollPeriods' => $payrollPeriods,
        ])->layout('layouts.livewire', ['title' => 'Dashboard RH']);
    }
}
