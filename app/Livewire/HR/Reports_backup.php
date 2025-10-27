<?php

declare(strict_types=1);

namespace App\Livewire\HR;

use App\Models\HR\Employee;
use App\Models\HR\Attendance;
use App\Models\HR\Leave;
use App\Models\HR\Department;
use App\Models\HR\Payroll;
use App\Models\HR\SalaryAdvance;
use App\Models\HR\SalaryDiscount;
use App\Models\HR\OvertimeRecord;
use Carbon\Carbon;
use Livewire\Component;
use Illuminate\Support\Facades\DB;

class Reports extends Component
{
    public $selectedPeriod = 'current_month';
    public $startDate;
    public $endDate;
    public $selectedDepartment = '';
    
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
    
    // Growth indicators
    public $employeeGrowth = 0;
    public $attendanceGrowth = 0;
    public $overtimeGrowth = 0;
    public $advancesGrowth = 0;
    
    // Chart data
    public $departmentData = [];
    public $attendanceChartData = [];
    public $overtimeChartData = [];
    public $salaryTrendsData = [];
    public $leavesChartData = [];
    public $delayTrendsData = [];
    
    public function mount(): void
    {
        $this->initializeDateRange();
        $this->calculateMetrics();
        $this->generateChartData();
    }
    
    public function updatedSelectedPeriod(): void
    {
        $this->initializeDateRange();
        $this->calculateMetrics();
        $this->generateChartData();
    }
    
    public function updatedSelectedDepartment(): void
    {
        $this->calculateMetrics();
        $this->generateChartData();
    }
    
    private function initializeDateRange(): void
    {
        match ($this->selectedPeriod) {
            'current_month' => [
                $this->startDate = Carbon::now()->startOfMonth()->format('Y-m-d'),
                $this->endDate = Carbon::now()->endOfMonth()->format('Y-m-d')
            ],
            'last_month' => [
                $this->startDate = Carbon::now()->subMonth()->startOfMonth()->format('Y-m-d'),
                $this->endDate = Carbon::now()->subMonth()->endOfMonth()->format('Y-m-d')
            ],
            'current_quarter' => [
                $this->startDate = Carbon::now()->startOfQuarter()->format('Y-m-d'),
                $this->endDate = Carbon::now()->endOfQuarter()->format('Y-m-d')
            ],
            'current_year' => [
                $this->startDate = Carbon::now()->startOfYear()->format('Y-m-d'),
                $this->endDate = Carbon::now()->endOfYear()->format('Y-m-d')
            ],
            default => [
                $this->startDate = Carbon::now()->startOfMonth()->format('Y-m-d'),
                $this->endDate = Carbon::now()->endOfMonth()->format('Y-m-d')
            ]
        };
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
    }
    
    private function calculateEmployeeMetrics(Carbon $startDate, Carbon $endDate): void
    {
        $query = Employee::query();
        
        if ($this->selectedDepartment) {
            $query->where('department_id', $this->selectedDepartment);
        }
        
        $this->totalEmployees = $query->count();
        $this->activeEmployees = $query->where('status', 'active')->count();
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
        
        $this->avgOvertimeHours = round($query->avg('hours') ?? 0, 1);
        
        // Previous period for growth
        $previousStart = $startDate->copy()->subMonth();
        $previousEnd = $endDate->copy()->subMonth();
        $previousAvg = OvertimeRecord::whereBetween('date', [$previousStart, $previousEnd])
            ->avg('hours') ?? 0;
        
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
        $previousAdvances = SalaryAdvance::whereBetween('request_date', [$previousStart, $previousEnd])
            ->sum('amount');
        
        $this->advancesGrowth = $previousAdvances > 0 ? 
            round((($this->totalSalaryAdvances - $previousAdvances) / $previousAdvances) * 100, 1) : 0;
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
            ->whereNotNull('check_in_time');
        
        if ($this->selectedDepartment) {
            $query->whereHas('employee', function($q) {
                $q->where('department_id', $this->selectedDepartment);
            });
        }
        
        // Calculate average delay in minutes
        $delays = $query->get()->map(function($attendance) {
            if ($attendance->check_in_time && $attendance->employee && $attendance->employee->shift) {
                $expectedTime = Carbon::parse($attendance->employee->shift->start_time);
                $actualTime = Carbon::parse($attendance->check_in_time);
                return $actualTime->diffInMinutes($expectedTime);
            }
            return 0;
        })->filter(function($delay) {
            return $delay > 0;
        });
        
        $this->avgDelayMinutes = $delays->count() > 0 ? round($delays->avg(), 1) : 0;
    }
    
    private function generateChartData(): void
    {
        $this->generateDepartmentChart();
        $this->generateAttendanceChart();
        $this->generateOvertimeChart();
        $this->generateSalaryTrendsChart();
        $this->generateLeavesChart();
        $this->generateDelayTrendsChart();
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
            
            $attendanceQuery = Attendance::whereDate('date', $dateStr);
            
            if ($this->selectedDepartment) {
                $attendanceQuery->whereHas('employee', function($q) {
                    $q->where('department_id', $this->selectedDepartment);
                });
            }
            
            $presentData[] = $attendanceQuery->where('status', 'present')->count();
            $absentData[] = $attendanceQuery->where('status', 'absent')->count();
            $lateData[] = $attendanceQuery->where('status', 'late')->count();
            
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
                    'label' => 'Adiantamentos',
                    'data' => $advancesData,
                    'backgroundColor' => '#3B82F6',
                    'borderColor' => '#3B82F6'
                ],
                [
                    'label' => 'Descontos',
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
        
        $query = Leave::selectRaw('status, COUNT(*) as count')
            ->whereBetween('start_date', [$startDate, $endDate])
            ->when($this->selectedDepartment, function($query) {
                $query->whereHas('employee', function($q) {
                    $q->where('department_id', $this->selectedDepartment);
                });
            })
            ->groupBy('status');
        
        $data = $query->get();
        
        $this->leavesChartData = [
            'labels' => $data->pluck('status')->map(function($status) {
                return match($status) {
                    'pending' => 'Pendente',
                    'approved' => 'Aprovada',
                    'rejected' => 'Rejeitada',
                    'cancelled' => 'Cancelada',
                    default => $status
                };
            })->toArray(),
            'data' => $data->pluck('count')->toArray(),
            'backgroundColor' => ['#F59E0B', '#10B981', '#EF4444', '#6B7280']
        ];
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
    
    public function render()
    {
        $departments = Department::all();
        
        return view('livewire.hr.reports', [
            'departments' => $departments
        ])->layout('layouts.livewire', ['title' => 'HR Dashboard']);
    }
            ->sum('net_salary');
            
        // Calcular crescimento na folha de pagamento (comparação com mês anterior)
        $previousPayrollTotal = Payroll::whereBetween('payment_date', [$previousPeriodStart, $previousPeriodEnd])
            ->when($this->departmentFilter, function($query) {
                return $query->whereHas('employee', function($q) {
                    $q->where('department_id', $this->departmentFilter);
                });
            })
            ->sum('net_salary');
            
        $payrollGrowth = 0;
        if ($previousPayrollTotal > 0) {
            $payrollGrowth = round((($totalPayroll - $previousPayrollTotal) / $previousPayrollTotal) * 100, 1);
        }
        
        // Preparar dados para os gráficos
        // 1. Gráfico de distribuição de funcionários por departamento
        $departmentData = Department::where('is_active', true)
            ->withCount(['employees' => function($query) {
                $query->where('employment_status', 'active');
            }])
            ->get();
            
        $departmentChartData = [
            'labels' => $departmentData->pluck('name')->toArray(),
            'data' => $departmentData->pluck('employees_count')->toArray()
        ];
        
        // 2. Gráfico de assiduidade (dados para o último mês)
        $attendanceData = [];
        $attendanceLabels = [];
        
        // Preparar dados para os últimos 7 dias
        for ($i = 6; $i >= 0; $i--) {
            $day = Carbon::now()->subDays($i);
            $attendanceLabels[] = $day->format('d/m');
            
            $present = Attendance::whereDate('date', $day->format('Y-m-d'))
                ->where('status', 'present')
                ->when($this->departmentFilter, function($query) {
                    return $query->whereHas('employee', function($q) {
                        $q->where('department_id', $this->departmentFilter);
                    });
                })
                ->count();
                
            $absent = Attendance::whereDate('date', $day->format('Y-m-d'))
                ->where('status', 'absent')
                ->when($this->departmentFilter, function($query) {
                    return $query->whereHas('employee', function($q) {
                        $q->where('department_id', $this->departmentFilter);
                    });
                })
                ->count();
                
            $late = Attendance::whereDate('date', $day->format('Y-m-d'))
                ->where('status', 'late')
                ->when($this->departmentFilter, function($query) {
                    return $query->whereHas('employee', function($q) {
                        $q->where('department_id', $this->departmentFilter);
                    });
                })
                ->count();
                
            $attendanceData['present'][] = $present;
            $attendanceData['absent'][] = $absent;
            $attendanceData['late'][] = $late;
        }
        
        $attendanceChartData = [
            'labels' => $attendanceLabels,
            'datasets' => [
                [
                    'label' => 'Presente',
                    'data' => $attendanceData['present'] ?? [],
                    'backgroundColor' => 'rgba(75, 192, 192, 0.2)',
                    'borderColor' => 'rgba(75, 192, 192, 1)',
                ],
                [
                    'label' => 'Ausente',
                    'data' => $attendanceData['absent'] ?? [],
                    'backgroundColor' => 'rgba(255, 99, 132, 0.2)',
                    'borderColor' => 'rgba(255, 99, 132, 1)',
                ],
                [
                    'label' => 'Atrasado',
                    'data' => $attendanceData['late'] ?? [],
                    'backgroundColor' => 'rgba(255, 206, 86, 0.2)',
                    'borderColor' => 'rgba(255, 206, 86, 1)',
                ]
            ]
        ];
        
        // 3. Gráfico de licenças por tipo
        $leaveData = Leave::whereBetween('start_date', [$startDate, $endDate])
            ->when($this->departmentFilter, function($query) {
                return $query->whereHas('employee', function($q) {
                    $q->where('department_id', $this->departmentFilter);
                });
            })
            ->selectRaw('leave_type_id, COUNT(*) as count')
            ->groupBy('leave_type_id')
            ->with('leaveType')
            ->get();
            
        $leaveChartData = [
            'labels' => $leaveData->pluck('leaveType.name')->toArray(),
            'data' => $leaveData->pluck('count')->toArray()
        ];
        
        // 4. Gráfico da folha de pagamento mensal
        $payrollMonths = [];
        $payrollAmounts = [];
        
        // Últimos 6 meses
        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $monthStart = $month->copy()->startOfMonth();
            $monthEnd = $month->copy()->endOfMonth();
            
            $payrollMonths[] = $month->format('M Y');
            
            $amount = Payroll::whereBetween('payment_date', [$monthStart, $monthEnd])
                ->when($this->departmentFilter, function($query) {
                    return $query->whereHas('employee', function($q) {
                        $q->where('department_id', $this->departmentFilter);
                    });
                })
                ->sum('net_salary');
                
            $payrollAmounts[] = $amount;
        }
        
        $payrollChartData = [
            'labels' => $payrollMonths,
            'data' => $payrollAmounts
        ];
        
        // Calcular os melhores funcionários com base na assiduidade e desempenho
        $topEmployees = Employee::with(['department', 'position'])
            ->where('employment_status', 'active')
            ->when($this->departmentFilter, function($query) {
                return $query->where('department_id', $this->departmentFilter);
            })
            ->get()
            ->map(function($employee) use ($startDate, $endDate) {
                // Calcular a taxa de presença
                $totalDays = Attendance::where('employee_id', $employee->id)
                    ->whereBetween('date', [$startDate, $endDate])
                    ->count();
                    
                $presentDays = Attendance::where('employee_id', $employee->id)
                    ->whereBetween('date', [$startDate, $endDate])
                    ->where('status', 'present')
                    ->count();
                    
                $attendanceRate = $totalDays > 0 ? round(($presentDays / $totalDays) * 100, 1) : 0;
                
                // Atribuir um valor de desempenho aleatório (em produção, isso seria baseado em avaliações reais)
                $performanceScore = rand(60, 100);
                
                // Adicionar estas métricas ao objeto employee
                $employee->attendance_rate = $attendanceRate;
                $employee->performance_score = $performanceScore;
                $employee->overall_score = ($attendanceRate * 0.4) + ($performanceScore * 0.6); // 40% presença, 60% desempenho
                
                return $employee;
            })
            ->sortByDesc('overall_score')
            ->take(5); // Pegar os 5 melhores
        
        return view('livewire.hr.reports', [
            'departments' => $departments,
            'totalEmployees' => $totalEmployees,
            'employeeGrowth' => $employeeGrowth,
            'attendanceRate' => $attendanceRate,
            'attendanceGrowth' => $attendanceGrowth,
            'leaveRate' => $leaveRate,
            'leaveGrowth' => $leaveGrowth,
            'totalPayroll' => $totalPayroll,
            'payrollGrowth' => $payrollGrowth,
            'topEmployees' => $topEmployees,
            'departmentChartData' => $departmentChartData,
            'attendanceChartData' => $attendanceChartData,
            'leaveChartData' => $leaveChartData,
            'payrollChartData' => $payrollChartData
        ]);
    }
}
