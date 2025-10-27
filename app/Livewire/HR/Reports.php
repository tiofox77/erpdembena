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

class Reports extends Component
{
    public $selectedPeriod = 'current_period';
    public $selectedPayrollPeriodId;
    public $startDate;
    public $endDate;
    public $selectedDepartment = '';
    public $availablePeriods = [];
    
    // Dashboard Metrics
    public $totalEmployees = 0;
    public $activeEmployees = 0;
    public $newHires = 0;
    public $attendanceRate = 0;
    public $avgOvertimeHours = 0;
    public $totalSalaryAdvances = 0;
    public $totalSalaryDiscounts = 0;
    public $pendingLeaves = 0;
    public $avgDelayMinutes = 0;
    public $totalPayroll = 0;
    public $overtimeCost = 0;
    public $daysToPayroll = 0;
    public $upcomingEvaluations = 0;
    
    // Growth indicators
    public $employeeGrowth = 0;
    public $attendanceGrowth = 0;
    public $overtimeGrowth = 0;
    public $advancesGrowth = 0;
    public $payrollGrowth = 0;
    
    // Chart data
    public $departmentData = [];
    public $attendanceChartData = [];
    public $overtimeChartData = [];
    public $salaryTrendsData = [];
    public $leavesChartData = [];
    public $delayTrendsData = [];
    public $overtimeByDepartmentData = [];
    public $monthlyPayrollData = [];
    public $advancesVsDiscountsData = [];
    public $payrollTimelineData = [];
    
    public function mount(): void
    {
        $this->loadAvailablePeriods();
        $this->initializeDateRange();
        $this->calculateMetrics();
        $this->generateChartData();
    }
    
    public function updatedSelectedPeriod(): void
    {
        $this->initializeDateRange();
        $this->calculateMetrics();
        $this->generateChartData();
        
        // Force refresh of charts
        $this->dispatch('refresh-charts', [
            'leavesChartData' => $this->leavesChartData,
            'attendanceChartData' => $this->attendanceChartData,
            'overtimeChartData' => $this->overtimeChartData,
            'departmentData' => $this->departmentData,
            'salaryTrendsData' => $this->salaryTrendsData,
            'delayTrendsData' => $this->delayTrendsData,
            'overtimeByDepartmentData' => $this->overtimeByDepartmentData,
            'monthlyPayrollData' => $this->monthlyPayrollData,
            'advancesVsDiscountsData' => $this->advancesVsDiscountsData,
            'payrollTimelineData' => $this->payrollTimelineData
        ]);
    }
    
    public function updatedSelectedPayrollPeriodId(): void
    {
        $this->initializeDateRange();
        $this->calculateMetrics();
        $this->generateChartData();
        
        // Force refresh of charts
        $this->dispatch('refresh-charts', [
            'leavesChartData' => $this->leavesChartData,
            'attendanceChartData' => $this->attendanceChartData,
            'overtimeChartData' => $this->overtimeChartData,
            'departmentData' => $this->departmentData,
            'salaryTrendsData' => $this->salaryTrendsData,
            'delayTrendsData' => $this->delayTrendsData,
            'overtimeByDepartmentData' => $this->overtimeByDepartmentData,
            'monthlyPayrollData' => $this->monthlyPayrollData,
            'advancesVsDiscountsData' => $this->advancesVsDiscountsData,
            'payrollTimelineData' => $this->payrollTimelineData
        ]);
    }
    
    public function updatedSelectedDepartment(): void
    {
        $this->calculateMetrics();
        $this->generateChartData();
    }
    
    private function loadAvailablePeriods(): void
    {
        $this->availablePeriods = PayrollPeriod::orderBy('start_date', 'desc')->get();
        
        // Set default to current period if available
        if (!$this->selectedPayrollPeriodId && $this->availablePeriods->isNotEmpty()) {
            $currentPeriod = $this->availablePeriods->where('status', 'open')->first() 
                ?? $this->availablePeriods->first();
            $this->selectedPayrollPeriodId = $currentPeriod->id;
        }
    }
    
    private function initializeDateRange(): void
    {
        if ($this->selectedPeriod === 'payroll_period' && $this->selectedPayrollPeriodId) {
            $period = PayrollPeriod::find($this->selectedPayrollPeriodId);
            if ($period) {
                $this->startDate = $period->start_date->format('Y-m-d');
                $this->endDate = $period->end_date->format('Y-m-d');
                logger("Payroll Period Set: {$this->startDate} to {$this->endDate}");
                return;
            }
        }
        
        switch ($this->selectedPeriod) {
            case 'current_period':
                $currentPeriod = PayrollPeriod::where('status', 'open')->first() 
                    ?? PayrollPeriod::orderBy('start_date', 'desc')->first();
                
                if ($currentPeriod) {
                    $this->startDate = $currentPeriod->start_date->format('Y-m-d');
                    $this->endDate = $currentPeriod->end_date->format('Y-m-d');
                    $this->selectedPayrollPeriodId = $currentPeriod->id;
                } else {
                    $this->startDate = Carbon::now()->startOfMonth()->format('Y-m-d');
                    $this->endDate = Carbon::now()->endOfMonth()->format('Y-m-d');
                }
                logger("Current Period Set: {$this->startDate} to {$this->endDate}");
                break;
                
            case 'current_month':
                $this->startDate = Carbon::now()->startOfMonth()->format('Y-m-d');
                $this->endDate = Carbon::now()->endOfMonth()->format('Y-m-d');
                logger("Current Month Set: {$this->startDate} to {$this->endDate}");
                break;
                
            case 'last_month':
                $this->startDate = Carbon::now()->subMonth()->startOfMonth()->format('Y-m-d');
                $this->endDate = Carbon::now()->subMonth()->endOfMonth()->format('Y-m-d');
                logger("Last Month Set: {$this->startDate} to {$this->endDate}");
                break;
                
            case 'current_quarter':
                $this->startDate = Carbon::now()->startOfQuarter()->format('Y-m-d');
                $this->endDate = Carbon::now()->endOfQuarter()->format('Y-m-d');
                logger("Current Quarter Set: {$this->startDate} to {$this->endDate}");
                break;
                
            case 'current_year':
                $this->startDate = Carbon::now()->startOfYear()->format('Y-m-d');
                $this->endDate = Carbon::now()->endOfYear()->format('Y-m-d');
                logger("Current Year Set: {$this->startDate} to {$this->endDate}");
                break;
                
            default:
                $this->startDate = Carbon::now()->startOfMonth()->format('Y-m-d');
                $this->endDate = Carbon::now()->endOfMonth()->format('Y-m-d');
                logger("Default Period Set: {$this->startDate} to {$this->endDate}");
                break;
        }
        
        logger("Selected Period: {$this->selectedPeriod}, Date Range: {$this->startDate} to {$this->endDate}");
    }
    
    private function calculateMetrics(): void
    {
        $startDate = Carbon::parse($this->startDate);
        $endDate = Carbon::parse($this->endDate);
        
        // Employee Metrics
        $this->calculateEmployeeMetrics($startDate, $endDate);
        
        // Attendance Metrics
        $this->calculateAttendanceMetrics($startDate, $endDate);
        
        // Overtime Metrics
        $this->calculateOvertimeMetrics($startDate, $endDate);
        
        // Salary Advances & Discounts
        $this->calculateSalaryMetrics($startDate, $endDate);
        
        // Leave Metrics
        $this->calculateLeaveMetrics($startDate, $endDate);
        
        // Delay Metrics
        $this->calculateDelayMetrics($startDate, $endDate);
        
        // Payroll Metrics
        $this->calculatePayrollMetrics($startDate, $endDate);
        
        // Additional Metrics
        $this->calculateAdditionalMetrics($startDate, $endDate);
    }
    
    private function calculateEmployeeMetrics(Carbon $startDate, Carbon $endDate): void
    {
        $query = Employee::query();
        
        if ($this->selectedDepartment) {
            $query->where('department_id', $this->selectedDepartment);
        }
        
        $this->totalEmployees = $query->count();
        $this->activeEmployees = $query->where('employment_status', 'active')->count();
        $this->newHires = $query->whereBetween('hire_date', [$startDate, $endDate])->count();
        
        // Calculate growth
        $previousPeriodStart = $startDate->copy()->subMonth();
        $previousPeriodEnd = $endDate->copy()->subMonth();
        $previousCount = Employee::whereBetween('hire_date', [$previousPeriodStart, $previousPeriodEnd])->count();
        
        $this->employeeGrowth = $previousCount > 0 ? 
            round((($this->newHires - $previousCount) / $previousCount) * 100, 1) : 0;
    }
    
    private function calculateAttendanceMetrics(Carbon $startDate, Carbon $endDate): void
    {
        $query = Attendance::whereBetween('date', [$startDate, $endDate]);
        
        if ($this->selectedDepartment) {
            $query->whereHas('employee', function($q) {
                $q->where('department_id', $this->selectedDepartment);
            });
        }
        
        $totalAttendance = $query->count();
        $presentCount = $query->where('status', 'present')->count();
        
        $this->attendanceRate = $totalAttendance > 0 ? 
            round(($presentCount / $totalAttendance) * 100, 1) : 0;
        
        // Previous period for growth
        $previousStart = $startDate->copy()->subMonth();
        $previousEnd = $endDate->copy()->subMonth();
        $previousTotal = Attendance::whereBetween('date', [$previousStart, $previousEnd])->count();
        $previousPresent = Attendance::whereBetween('date', [$previousStart, $previousEnd])
            ->where('status', 'present')->count();
        $previousRate = $previousTotal > 0 ? ($previousPresent / $previousTotal) * 100 : 0;
        
        $this->attendanceGrowth = $previousRate > 0 ? 
            round(($this->attendanceRate - $previousRate), 1) : 0;
    }
    
    private function calculateOvertimeMetrics(Carbon $startDate, Carbon $endDate): void
    {
        $query = OvertimeRecord::whereBetween('date', [$startDate, $endDate]);
        
        if ($this->selectedDepartment) {
            $query->whereHas('employee', function($q) {
                $q->where('department_id', $this->selectedDepartment);
            });
        }
        
        $this->avgOvertimeHours = round((float)($query->avg('hours') ?? 0), 1);
        
        // Previous period for growth
        $previousStart = $startDate->copy()->subMonth();
        $previousEnd = $endDate->copy()->subMonth();
        $previousAvg = (float)(OvertimeRecord::whereBetween('date', [$previousStart, $previousEnd])
            ->avg('hours') ?? 0);
        
        $this->overtimeGrowth = $previousAvg > 0 ? 
            round((($this->avgOvertimeHours - $previousAvg) / $previousAvg) * 100, 1) : 0;
    }
    
    private function calculateSalaryMetrics(Carbon $startDate, Carbon $endDate): void
    {
        $advancesQuery = SalaryAdvance::whereBetween('request_date', [$startDate, $endDate]);
        $discountsQuery = SalaryDiscount::whereBetween('request_date', [$startDate, $endDate]);
        
        if ($this->selectedDepartment) {
            $advancesQuery->whereHas('employee', function($q) {
                $q->where('department_id', $this->selectedDepartment);
            });
            $discountsQuery->whereHas('employee', function($q) {
                $q->where('department_id', $this->selectedDepartment);
            });
        }
        
        $this->totalSalaryAdvances = $advancesQuery->sum('amount');
        $this->totalSalaryDiscounts = $discountsQuery->sum('amount');
        
        // Previous period for growth
        $previousStart = $startDate->copy()->subMonth();
        $previousEnd = $endDate->copy()->subMonth();
        $previousAdvances = (float)(SalaryAdvance::whereBetween('request_date', [$previousStart, $previousEnd])
            ->sum('amount'));
        
        $this->advancesGrowth = $previousAdvances > 0 ? 
            round((((float)$this->totalSalaryAdvances - $previousAdvances) / $previousAdvances) * 100, 1) : 0;
    }
    
    private function calculateLeaveMetrics(Carbon $startDate, Carbon $endDate): void
    {
        $query = Leave::whereBetween('start_date', [$startDate, $endDate]);
        
        if ($this->selectedDepartment) {
            $query->whereHas('employee', function($q) {
                $q->where('department_id', $this->selectedDepartment);
            });
        }
        
        $this->pendingLeaves = $query->where('status', 'pending')->count();
    }
    
    private function calculateDelayMetrics(Carbon $startDate, Carbon $endDate): void
    {
        $query = Attendance::whereBetween('date', [$startDate, $endDate])
            ->where('status', 'late')
            ->whereNotNull('time_in');
        
        if ($this->selectedDepartment) {
            $query->whereHas('employee', function($q) {
                $q->where('department_id', $this->selectedDepartment);
            });
        }
        
        // Calculate average delay in minutes
        $delays = $query->get()->map(function($attendance) {
            if ($attendance->time_in && $attendance->employee) {
                // For simplicity, assume standard work start time of 8:00 AM
                $expectedTime = Carbon::parse($attendance->date)->setTime(8, 0);
                $actualTime = Carbon::parse($attendance->time_in);
                
                if ($actualTime->gt($expectedTime)) {
                    return $actualTime->diffInMinutes($expectedTime);
                }
            }
            return 0;
        })->filter(function($delay) {
            return $delay > 0;
        });
        
        $this->avgDelayMinutes = $delays->count() > 0 ? round((float)$delays->avg(), 1) : 0;
    }
    
    private function calculatePayrollMetrics(Carbon $startDate, Carbon $endDate): void
    {
        // If using payroll periods, filter by the specific period
        if ($this->selectedPayrollPeriodId) {
            $query = Payroll::where('payroll_period_id', $this->selectedPayrollPeriodId);
        } else {
            $query = Payroll::whereBetween('payment_date', [$startDate, $endDate]);
        }
        
        if ($this->selectedDepartment) {
            $query->whereHas('employee', function($q) {
                $q->where('department_id', $this->selectedDepartment);
            });
        }
        
        $this->totalPayroll = $query->sum('net_salary');
        
        // Calculate payroll growth based on previous period
        if ($this->selectedPayrollPeriodId) {
            $currentPeriod = PayrollPeriod::find($this->selectedPayrollPeriodId);
            $previousPeriod = PayrollPeriod::where('end_date', '<', $currentPeriod->start_date)
                ->orderBy('end_date', 'desc')
                ->first();
            
            $previousPayroll = $previousPeriod ? 
                (float)(Payroll::where('payroll_period_id', $previousPeriod->id)->sum('net_salary')) : 0;
        } else {
            $previousStart = $startDate->copy()->subMonth();
            $previousEnd = $endDate->copy()->subMonth();
            $previousPayroll = (float)(Payroll::whereBetween('payment_date', [$previousStart, $previousEnd])
                ->sum('net_salary'));
        }
        
        $this->payrollGrowth = $previousPayroll > 0 ? 
            round((((float)$this->totalPayroll - $previousPayroll) / $previousPayroll) * 100, 1) : 0;
    }
    
    private function calculateAdditionalMetrics(Carbon $startDate, Carbon $endDate): void
    {
        // Calculate overtime cost
        $overtimeQuery = OvertimeRecord::whereBetween('date', [$startDate, $endDate]);
        
        if ($this->selectedDepartment) {
            $overtimeQuery->whereHas('employee', function($q) {
                $q->where('department_id', $this->selectedDepartment);
            });
        }
        
        $this->overtimeCost = $overtimeQuery->sum('amount') ?? 0;
        
        // Calculate days to next payroll (assuming monthly payroll on 25th)
        $today = Carbon::now();
        $nextPayroll = Carbon::now()->day(25);
        if ($today->day > 25) {
            $nextPayroll->addMonth();
        }
        $this->daysToPayroll = $today->diffInDays($nextPayroll);
        
        // For now, set upcoming evaluations to 0 (would need performance evaluation system)
        $this->upcomingEvaluations = 0;
    }
    
    private function generateChartData(): void
    {
        $this->generateDepartmentChart();
        $this->generateAttendanceChart();
        $this->generateOvertimeChart();
        $this->generateSalaryTrendsChart();
        $this->generateLeavesChart();
        $this->generateDelayTrendsChart();
        $this->generateOvertimeByDepartmentChart();
        $this->generateMonthlyPayrollChart();
        $this->generateAdvancesVsDiscountsChart();
        $this->generatePayrollTimelineChart();
    }
    
    private function generateDepartmentChart(): void
    {
        $query = Employee::select('departments.name', DB::raw('COUNT(*) as count'))
            ->join('departments', 'employees.department_id', '=', 'departments.id')
            ->groupBy('departments.name');
        
        $data = $query->get();
        
        $this->departmentData = [
            'labels' => $data->pluck('name')->toArray(),
            'data' => $data->pluck('count')->toArray(),
            'backgroundColor' => [
                '#10B981', '#3B82F6', '#8B5CF6', '#F59E0B', 
                '#EF4444', '#06B6D4', '#84CC16', '#F97316'
            ]
        ];
    }
    
    private function generateAttendanceChart(): void
    {
        $startDate = Carbon::parse($this->startDate);
        $endDate = Carbon::parse($this->endDate);
        
        $labels = [];
        $presentData = [];
        $absentData = [];
        $lateData = [];
        
        $current = $startDate->copy();
        while ($current <= $endDate) {
            $dateStr = $current->format('Y-m-d');
            $labels[] = $current->format('M d');
            
            $dayQuery = Attendance::whereDate('date', $dateStr);
            
            if ($this->selectedDepartment) {
                $dayQuery->whereHas('employee', function($q) {
                    $q->where('department_id', $this->selectedDepartment);
                });
            }
            
            $presentData[] = (clone $dayQuery)->where('status', 'present')->count();
            $absentData[] = (clone $dayQuery)->where('status', 'absent')->count();
            $lateData[] = (clone $dayQuery)->where('status', 'late')->count();
            
            $current->addDay();
        }
        
        $this->attendanceChartData = [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Presente',
                    'data' => $presentData,
                    'backgroundColor' => '#10B981'
                ],
                [
                    'label' => 'Ausente',
                    'data' => $absentData,
                    'backgroundColor' => '#EF4444'
                ],
                [
                    'label' => 'Atrasado',
                    'data' => $lateData,
                    'backgroundColor' => '#F59E0B'
                ]
            ]
        ];
    }
    
    private function generateOvertimeChart(): void
    {
        $startDate = Carbon::parse($this->startDate);
        $endDate = Carbon::parse($this->endDate);
        
        $data = OvertimeRecord::selectRaw('DATE(date) as date, SUM(hours) as total_hours')
            ->whereBetween('date', [$startDate, $endDate])
            ->when($this->selectedDepartment, function($query) {
                $query->whereHas('employee', function($q) {
                    $q->where('department_id', $this->selectedDepartment);
                });
            })
            ->groupBy('date')
            ->orderBy('date')
            ->get();
        
        $this->overtimeChartData = [
            'labels' => $data->pluck('date')->map(function($date) {
                return Carbon::parse($date)->format('M d');
            })->toArray(),
            'data' => $data->pluck('total_hours')->toArray(),
            'backgroundColor' => '#8B5CF6'
        ];
    }
    
    private function generateSalaryTrendsChart(): void
    {
        $startDate = Carbon::parse($this->startDate);
        $endDate = Carbon::parse($this->endDate);
        
        $advances = SalaryAdvance::selectRaw('DATE(request_date) as date, SUM(amount) as total')
            ->whereBetween('request_date', [$startDate, $endDate])
            ->when($this->selectedDepartment, function($query) {
                $query->whereHas('employee', function($q) {
                    $q->where('department_id', $this->selectedDepartment);
                });
            })
            ->groupBy('date')
            ->get()
            ->keyBy('date');
        
        $discounts = SalaryDiscount::selectRaw('DATE(request_date) as date, SUM(amount) as total')
            ->whereBetween('request_date', [$startDate, $endDate])
            ->when($this->selectedDepartment, function($query) {
                $query->whereHas('employee', function($q) {
                    $q->where('department_id', $this->selectedDepartment);
                });
            })
            ->groupBy('date')
            ->get()
            ->keyBy('date');
        
        $labels = [];
        $advancesData = [];
        $discountsData = [];
        
        $current = $startDate->copy();
        while ($current <= $endDate) {
            $dateStr = $current->format('Y-m-d');
            $labels[] = $current->format('M d');
            
            $advancesData[] = $advances->get($dateStr)?->total ?? 0;
            $discountsData[] = $discounts->get($dateStr)?->total ?? 0;
            
            $current->addDay();
        }
        
        $this->salaryTrendsData = [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => __('dashboard.advances'),
                    'data' => $advancesData,
                    'backgroundColor' => '#3B82F6',
                    'borderColor' => '#3B82F6'
                ],
                [
                    'label' => __('dashboard.discounts'),
                    'data' => $discountsData,
                    'backgroundColor' => '#EF4444',
                    'borderColor' => '#EF4444'
                ]
            ]
        ];
    }
    
    private function generateLeavesChart(): void
    {
        $startDate = Carbon::parse($this->startDate);
        $endDate = Carbon::parse($this->endDate);
        
        try {
            $query = Leave::selectRaw('status, COUNT(*) as count')
                ->whereBetween('start_date', [$startDate, $endDate])
                ->when($this->selectedDepartment, function($query) {
                    $query->whereHas('employee', function($q) {
                        $q->where('department_id', $this->selectedDepartment);
                    });
                })
                ->groupBy('status');
            
            $data = $query->get();
            
            // If no data, create default empty chart
            if ($data->isEmpty()) {
                $this->leavesChartData = [
                    'labels' => [__('dashboard.no_data')],
                    'data' => [0],
                    'backgroundColor' => ['#E5E7EB']
                ];
                return;
            }
            
            $this->leavesChartData = [
                'labels' => $data->pluck('status')->map(function($status) {
                    return match($status) {
                        'pending' => __('dashboard.pending'),
                        'approved' => __('dashboard.approved'),
                        'rejected' => __('dashboard.rejected'),
                        'cancelled' => __('dashboard.cancelled'),
                        default => $status
                    };
                })->toArray(),
                'data' => $data->pluck('count')->toArray(),
                'backgroundColor' => ['#F59E0B', '#10B981', '#EF4444', '#6B7280']
            ];
        } catch (\Exception $e) {
            // Fallback in case of error
            $this->leavesChartData = [
                'labels' => [__('dashboard.error_loading')],
                'data' => [0],
                'backgroundColor' => ['#EF4444']
            ];
        }
    }
    
    private function generateDelayTrendsChart(): void
    {
        $startDate = Carbon::parse($this->startDate);
        $endDate = Carbon::parse($this->endDate);
        
        $data = Attendance::selectRaw('DATE(date) as date, COUNT(*) as count')
            ->whereBetween('date', [$startDate, $endDate])
            ->where('status', 'late')
            ->when($this->selectedDepartment, function($query) {
                $query->whereHas('employee', function($q) {
                    $q->where('department_id', $this->selectedDepartment);
                });
            })
            ->groupBy('date')
            ->orderBy('date')
            ->get();
        
        $this->delayTrendsData = [
            'labels' => $data->pluck('date')->map(function($date) {
                return Carbon::parse($date)->format('M d');
            })->toArray(),
            'data' => $data->pluck('count')->toArray(),
            'backgroundColor' => '#F59E0B'
        ];
    }
    
    private function generateOvertimeByDepartmentChart(): void
    {
        $startDate = Carbon::parse($this->startDate);
        $endDate = Carbon::parse($this->endDate);
        
        $data = OvertimeRecord::selectRaw('departments.name, SUM(hours) as total_hours')
            ->join('employees', 'overtime_records.employee_id', '=', 'employees.id')
            ->join('departments', 'employees.department_id', '=', 'departments.id')
            ->whereBetween('overtime_records.date', [$startDate, $endDate])
            ->groupBy('departments.name')
            ->get();
        
        $this->overtimeByDepartmentData = [
            'labels' => $data->pluck('name')->toArray(),
            'data' => $data->pluck('total_hours')->toArray(),
            'backgroundColor' => ['#8B5CF6', '#3B82F6', '#10B981', '#F59E0B', '#EF4444']
        ];
    }
    
    private function generateMonthlyPayrollChart(): void
    {
        // Get payroll data by periods instead of months
        $query = PayrollPeriod::with(['payrolls' => function($q) {
            if ($this->selectedDepartment) {
                $q->whereHas('employee', function($employeeQuery) {
                    $employeeQuery->where('department_id', $this->selectedDepartment);
                });
            }
        }])
        ->orderBy('start_date', 'asc')
        ->limit(6);
        
        if ($this->selectedPayrollPeriodId) {
            // Show current and previous periods
            $currentPeriod = PayrollPeriod::find($this->selectedPayrollPeriodId);
            $query->where('end_date', '<=', $currentPeriod->end_date);
        }
        
        $periods = $query->get();
        
        $labels = [];
        $data = [];
        
        foreach ($periods as $period) {
            $labels[] = $period->name;
            $data[] = $period->payrolls->sum('net_salary');
        }
        
        $this->monthlyPayrollData = [
            'labels' => $labels,
            'data' => $data,
            'backgroundColor' => '#10B981',
            'borderColor' => '#10B981'
        ];
    }
    
    private function generateAdvancesVsDiscountsChart(): void
    {
        $startDate = Carbon::parse($this->startDate);
        $endDate = Carbon::parse($this->endDate);
        
        $advances = SalaryAdvance::selectRaw('DATE(request_date) as date, SUM(amount) as total')
            ->whereBetween('request_date', [$startDate, $endDate])
            ->when($this->selectedDepartment, function($query) {
                $query->whereHas('employee', function($q) {
                    $q->where('department_id', $this->selectedDepartment);
                });
            })
            ->groupBy('date')
            ->get()
            ->keyBy('date');
        
        $discounts = SalaryDiscount::selectRaw('DATE(request_date) as date, SUM(amount) as total')
            ->whereBetween('request_date', [$startDate, $endDate])
            ->when($this->selectedDepartment, function($query) {
                $query->whereHas('employee', function($q) {
                    $q->where('department_id', $this->selectedDepartment);
                });
            })
            ->groupBy('date')
            ->get()
            ->keyBy('date');
        
        $this->advancesVsDiscountsData = [
            'advances_total' => $advances->sum('total'),
            'discounts_total' => $discounts->sum('total'),
            'labels' => [__('dashboard.advances'), __('dashboard.discounts')],
            'data' => [$advances->sum('total'), $discounts->sum('total')],
            'backgroundColor' => ['#3B82F6', '#EF4444']
        ];
    }
    
    private function generatePayrollTimelineChart(): void
    {
        $today = Carbon::now();
        $labels = [];
        $data = [];
        
        // Generate next 6 months of payroll dates (assuming 25th of each month)
        for ($i = 0; $i < 6; $i++) {
            $payrollDate = $today->copy()->addMonths($i)->day(25);
            $labels[] = $payrollDate->format('M Y');
            
            // Estimated payroll based on current employees
            $estimatedPayroll = Employee::where('employment_status', 'active')
                ->when($this->selectedDepartment, function($query) {
                    $query->where('department_id', $this->selectedDepartment);
                })
                ->sum('base_salary');
            
            $data[] = $estimatedPayroll;
        }
        
        $this->payrollTimelineData = [
            'labels' => $labels,
            'data' => $data,
            'backgroundColor' => '#8B5CF6',
            'borderColor' => '#8B5CF6'
        ];
    }
    
    public function updated($propertyName): void
    {
        if (in_array($propertyName, ['selectedPeriod', 'selectedPayrollPeriodId', 'selectedDepartment'])) {
            // Recalculate all data when period or department changes
            $this->initializeDateRange();
            $this->calculateMetrics();
            $this->generateChartData();
            
            // Dispatch events with data to frontend
            $this->dispatch('charts-update');
            $this->dispatch('periodsUpdated');
            $this->dispatch('refresh-charts', [
                'leavesChartData' => $this->leavesChartData,
                'attendanceChartData' => $this->attendanceChartData,
                'overtimeChartData' => $this->overtimeChartData,
                'departmentData' => $this->departmentData,
                'salaryTrendsData' => $this->salaryTrendsData,
                'delayTrendsData' => $this->delayTrendsData,
                'overtimeByDepartmentData' => $this->overtimeByDepartmentData,
                'monthlyPayrollData' => $this->monthlyPayrollData,
                'advancesVsDiscountsData' => $this->advancesVsDiscountsData,
                'payrollTimelineData' => $this->payrollTimelineData
            ]);
        }
    }
    
    public function render()
    {
        $departments = Department::all();
        
        return view('livewire.hr.reports', [
            'departments' => $departments,
            'availablePeriods' => $this->availablePeriods
        ])->layout('layouts.livewire', ['title' => 'HR Dashboard']);
    }
}
