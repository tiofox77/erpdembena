<?php

namespace App\Livewire\HR;

use App\Models\HR\Employee;
use App\Models\HR\Attendance;
use App\Models\HR\Leave;
use App\Models\HR\Department;
use App\Models\HR\Payroll;
use Carbon\Carbon;
use Livewire\Component;

class Reports extends Component
{
    public $reportType = 'employees';
    public $dateRange = 'month';
    public $customStartDate;
    public $customEndDate;
    public $departmentFilter;
    
    // Chart data
    public $employeesByDepartmentLabels = [];
    public $employeesByDepartmentData = [];
    public $attendanceLabels = [];
    public $attendanceData = [];
    public $leaveLabels = [];
    public $leaveData = [];
    public $payrollLabels = [];
    public $payrollData = [];
    
    public function mount()
    {
        $this->customStartDate = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->customEndDate = Carbon::now()->endOfMonth()->format('Y-m-d');
        $this->loadReportData();
    }
    
    public function updatedReportType()
    {
        $this->loadReportData();
        $this->dispatchBrowserEvent('refreshCharts', [
            'departmentChartData' => $this->employeesByDepartmentLabels ? [
                'labels' => $this->employeesByDepartmentLabels,
                'data' => $this->employeesByDepartmentData
            ] : [],
            'attendanceChartData' => [
                'labels' => $this->attendanceLabels,
                'datasets' => [
                    [
                        'label' => 'Presente',
                        'data' => $this->attendanceData['present'] ?? [],
                        'backgroundColor' => 'rgba(75, 192, 192, 0.2)',
                        'borderColor' => 'rgba(75, 192, 192, 1)',
                    ],
                    [
                        'label' => 'Ausente',
                        'data' => $this->attendanceData['absent'] ?? [],
                        'backgroundColor' => 'rgba(255, 99, 132, 0.2)',
                        'borderColor' => 'rgba(255, 99, 132, 1)',
                    ],
                    [
                        'label' => 'Atrasado',
                        'data' => $this->attendanceData['late'] ?? [],
                        'backgroundColor' => 'rgba(255, 206, 86, 0.2)',
                        'borderColor' => 'rgba(255, 206, 86, 1)',
                    ]
                ]
            ],
            'leaveChartData' => [
                'labels' => $this->leaveLabels,
                'data' => $this->leaveData
            ],
            'payrollChartData' => [
                'labels' => $this->payrollLabels,
                'data' => $this->payrollData
            ]
        ]);
    }
    
    public function updatedDateRange()
    {
        if ($this->dateRange === 'custom') {
            return;
        }
        
        $this->setDateRange();
        $this->loadReportData();
        $this->dispatchBrowserEvent('refreshCharts', [
            'departmentChartData' => $this->employeesByDepartmentLabels ? [
                'labels' => $this->employeesByDepartmentLabels,
                'data' => $this->employeesByDepartmentData
            ] : [],
            'attendanceChartData' => [
                'labels' => $this->attendanceLabels,
                'datasets' => [
                    [
                        'label' => 'Presente',
                        'data' => $this->attendanceData['present'] ?? [],
                        'backgroundColor' => 'rgba(75, 192, 192, 0.2)',
                        'borderColor' => 'rgba(75, 192, 192, 1)',
                    ],
                    [
                        'label' => 'Ausente',
                        'data' => $this->attendanceData['absent'] ?? [],
                        'backgroundColor' => 'rgba(255, 99, 132, 0.2)',
                        'borderColor' => 'rgba(255, 99, 132, 1)',
                    ],
                    [
                        'label' => 'Atrasado',
                        'data' => $this->attendanceData['late'] ?? [],
                        'backgroundColor' => 'rgba(255, 206, 86, 0.2)',
                        'borderColor' => 'rgba(255, 206, 86, 1)',
                    ]
                ]
            ],
            'leaveChartData' => [
                'labels' => $this->leaveLabels,
                'data' => $this->leaveData
            ],
            'payrollChartData' => [
                'labels' => $this->payrollLabels,
                'data' => $this->payrollData
            ]
        ]);
    }
    
    public function updatedDepartmentFilter()
    {
        $this->loadReportData();
        $this->dispatchBrowserEvent('refreshCharts', [
            'departmentChartData' => $this->employeesByDepartmentLabels ? [
                'labels' => $this->employeesByDepartmentLabels,
                'data' => $this->employeesByDepartmentData
            ] : [],
            'attendanceChartData' => [
                'labels' => $this->attendanceLabels,
                'datasets' => [
                    [
                        'label' => 'Presente',
                        'data' => $this->attendanceData['present'] ?? [],
                        'backgroundColor' => 'rgba(75, 192, 192, 0.2)',
                        'borderColor' => 'rgba(75, 192, 192, 1)',
                    ],
                    [
                        'label' => 'Ausente',
                        'data' => $this->attendanceData['absent'] ?? [],
                        'backgroundColor' => 'rgba(255, 99, 132, 0.2)',
                        'borderColor' => 'rgba(255, 99, 132, 1)',
                    ],
                    [
                        'label' => 'Atrasado',
                        'data' => $this->attendanceData['late'] ?? [],
                        'backgroundColor' => 'rgba(255, 206, 86, 0.2)',
                        'borderColor' => 'rgba(255, 206, 86, 1)',
                    ]
                ]
            ],
            'leaveChartData' => [
                'labels' => $this->leaveLabels,
                'data' => $this->leaveData
            ],
            'payrollChartData' => [
                'labels' => $this->payrollLabels,
                'data' => $this->payrollData
            ]
        ]);
    }
    
    public function setDateRange()
    {
        $now = Carbon::now();
        
        switch($this->dateRange) {
            case 'week':
                $this->customStartDate = $now->copy()->startOfWeek()->format('Y-m-d');
                $this->customEndDate = $now->copy()->endOfWeek()->format('Y-m-d');
                break;
            case 'month':
                $this->customStartDate = $now->copy()->startOfMonth()->format('Y-m-d');
                $this->customEndDate = $now->copy()->endOfMonth()->format('Y-m-d');
                break;
            case 'quarter':
                $this->customStartDate = $now->copy()->startOfQuarter()->format('Y-m-d');
                $this->customEndDate = $now->copy()->endOfQuarter()->format('Y-m-d');
                break;
            case 'year':
                $this->customStartDate = $now->copy()->startOfYear()->format('Y-m-d');
                $this->customEndDate = $now->copy()->endOfYear()->format('Y-m-d');
                break;
        }
    }
    
    public function applyCustomDateRange()
    {
        $this->validate([
            'customStartDate' => 'required|date',
            'customEndDate' => 'required|date|after_or_equal:customStartDate',
        ]);
        
        $this->loadReportData();
        $this->dispatchBrowserEvent('refreshCharts', [
            'departmentChartData' => $this->employeesByDepartmentLabels ? [
                'labels' => $this->employeesByDepartmentLabels,
                'data' => $this->employeesByDepartmentData
            ] : [],
            'attendanceChartData' => [
                'labels' => $this->attendanceLabels,
                'datasets' => [
                    [
                        'label' => 'Presente',
                        'data' => $this->attendanceData['present'] ?? [],
                        'backgroundColor' => 'rgba(75, 192, 192, 0.2)',
                        'borderColor' => 'rgba(75, 192, 192, 1)',
                    ],
                    [
                        'label' => 'Ausente',
                        'data' => $this->attendanceData['absent'] ?? [],
                        'backgroundColor' => 'rgba(255, 99, 132, 0.2)',
                        'borderColor' => 'rgba(255, 99, 132, 1)',
                    ],
                    [
                        'label' => 'Atrasado',
                        'data' => $this->attendanceData['late'] ?? [],
                        'backgroundColor' => 'rgba(255, 206, 86, 0.2)',
                        'borderColor' => 'rgba(255, 206, 86, 1)',
                    ]
                ]
            ],
            'leaveChartData' => [
                'labels' => $this->leaveLabels,
                'data' => $this->leaveData
            ],
            'payrollChartData' => [
                'labels' => $this->payrollLabels,
                'data' => $this->payrollData
            ]
        ]);
    }
    
    private function loadReportData()
    {
        switch($this->reportType) {
            case 'employees':
                $this->loadEmployeesByDepartmentChart();
                break;
            case 'attendance':
                $this->loadAttendanceStats();
                break;
            case 'leave':
                $this->loadLeaveStats();
                break;
            case 'payroll':
                $this->loadPayrollStats();
                break;
        }
    }
    
    private function loadEmployeesByDepartmentChart()
    {
        $departments = Department::with(['employees' => function($query) {
            $query->where('employment_status', 'active');
            if ($this->departmentFilter) {
                $query->where('department_id', $this->departmentFilter);
            }
        }])->get();
        
        $this->employeesByDepartmentLabels = $departments->pluck('name')->toArray();
        $this->employeesByDepartmentData = $departments->map(function($department) {
            return $department->employees->count();
        })->toArray();
    }
    
    private function loadAttendanceStats()
    {
        $startDate = Carbon::parse($this->customStartDate);
        $endDate = Carbon::parse($this->customEndDate);
        
        $dateRange = [];
        $present = [];
        $absent = [];
        $late = [];
        
        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $dateString = $date->format('Y-m-d');
            $dateRange[] = $date->format('M d');
            
            $query = Attendance::whereDate('date', $dateString);
            
            if ($this->departmentFilter) {
                $query->whereHas('employee', function($q) {
                    $q->where('department_id', $this->departmentFilter);
                });
            }
            
            $presentCount = (clone $query)->where('status', 'present')->count();
            $absentCount = (clone $query)->where('status', 'absent')->count();
            $lateCount = (clone $query)->where('status', 'late')->count();
            
            $present[] = $presentCount;
            $absent[] = $absentCount;
            $late[] = $lateCount;
        }
        
        $this->attendanceLabels = $dateRange;
        $this->attendanceData = [
            'present' => $present,
            'absent' => $absent,
            'late' => $late,
        ];
    }
    
    private function loadLeaveStats()
    {
        $startDate = Carbon::parse($this->customStartDate);
        $endDate = Carbon::parse($this->customEndDate);
        
        $leaveTypes = Leave::selectRaw('leave_type_id, count(*) as count')
            ->whereBetween('start_date', [$startDate, $endDate])
            ->when($this->departmentFilter, function($query) {
                return $query->whereHas('employee', function($q) {
                    $q->where('department_id', $this->departmentFilter);
                });
            })
            ->groupBy('leave_type_id')
            ->with('leaveType')
            ->get();
        
        $this->leaveLabels = $leaveTypes->pluck('leaveType.name')->toArray();
        $this->leaveData = $leaveTypes->pluck('count')->toArray();
    }
    
    private function loadPayrollStats()
    {
        $startDate = Carbon::parse($this->customStartDate);
        $endDate = Carbon::parse($this->customEndDate);
        
        $payrollData = Payroll::whereBetween('payment_date', [$startDate, $endDate])
            ->when($this->departmentFilter, function($query) {
                return $query->whereHas('employee', function($q) {
                    $q->where('department_id', $this->departmentFilter);
                });
            })
            ->selectRaw('DATE_FORMAT(payment_date, "%b %Y") as month, SUM(net_salary) as total')
            ->groupBy('month')
            ->orderBy('payment_date')
            ->get();
        
        $this->payrollLabels = $payrollData->pluck('month')->toArray();
        $this->payrollData = $payrollData->pluck('total')->toArray();
    }
    
    public function render()
    {
        $departments = Department::where('is_active', true)->get();
        
        // Inicializar variáveis de gráficos para evitar erros
        $departmentChartData = [
            'labels' => [],
            'data' => []
        ];
        
        $attendanceChartData = [
            'labels' => [],
            'datasets' => []
        ];
        
        $leaveChartData = [
            'labels' => [],
            'data' => []
        ];
        
        $payrollChartData = [
            'labels' => [],
            'data' => []
        ];
        
        // Calcular o total de funcionários atuais
        $totalEmployees = Employee::where('employment_status', 'active')
            ->when($this->departmentFilter, function($query) {
                return $query->where('department_id', $this->departmentFilter);
            })
            ->count();
            
        // Calcular crescimento de funcionários (comparação com mês anterior)
        $currentDate = Carbon::now();
        $previousMonthStart = $currentDate->copy()->subMonth()->startOfMonth();
        $previousMonthEnd = $currentDate->copy()->subMonth()->endOfMonth();
        
        $previousMonthEmployees = Employee::where('employment_status', 'active')
            ->where('hire_date', '<=', $previousMonthEnd)
            ->when($this->departmentFilter, function($query) {
                return $query->where('department_id', $this->departmentFilter);
            })
            ->count();
            
        $employeeGrowth = 0;
        if ($previousMonthEmployees > 0) {
            $employeeGrowth = round((($totalEmployees - $previousMonthEmployees) / $previousMonthEmployees) * 100, 1);
        }
        
        // Calcular taxa de presença
        $startDate = Carbon::parse($this->customStartDate);
        $endDate = Carbon::parse($this->customEndDate);
        
        $totalAttendance = Attendance::whereBetween('date', [$startDate, $endDate])
            ->when($this->departmentFilter, function($query) {
                return $query->whereHas('employee', function($q) {
                    $q->where('department_id', $this->departmentFilter);
                });
            })
            ->count();
            
        $presentAttendance = Attendance::whereBetween('date', [$startDate, $endDate])
            ->where('status', 'present')
            ->when($this->departmentFilter, function($query) {
                return $query->whereHas('employee', function($q) {
                    $q->where('department_id', $this->departmentFilter);
                });
            })
            ->count();
            
        $attendanceRate = $totalAttendance > 0 ? round(($presentAttendance / $totalAttendance) * 100, 1) : 0;
        
        // Calcular crescimento na taxa de presença (comparação com mês anterior)
        $previousPeriodStart = $startDate->copy()->subMonth();
        $previousPeriodEnd = $endDate->copy()->subMonth();
        
        $previousTotalAttendance = Attendance::whereBetween('date', [$previousPeriodStart, $previousPeriodEnd])
            ->when($this->departmentFilter, function($query) {
                return $query->whereHas('employee', function($q) {
                    $q->where('department_id', $this->departmentFilter);
                });
            })
            ->count();
            
        $previousPresentAttendance = Attendance::whereBetween('date', [$previousPeriodStart, $previousPeriodEnd])
            ->where('status', 'present')
            ->when($this->departmentFilter, function($query) {
                return $query->whereHas('employee', function($q) {
                    $q->where('department_id', $this->departmentFilter);
                });
            })
            ->count();
            
        $previousAttendanceRate = $previousTotalAttendance > 0 ? round(($previousPresentAttendance / $previousTotalAttendance) * 100, 1) : 0;
        $attendanceGrowth = $previousAttendanceRate > 0 ? round(($attendanceRate - $previousAttendanceRate), 1) : 0;
        
        // Calcular utilização de licenças
        $totalLeaves = Leave::whereBetween('start_date', [$startDate, $endDate])
            ->when($this->departmentFilter, function($query) {
                return $query->whereHas('employee', function($q) {
                    $q->where('department_id', $this->departmentFilter);
                });
            })
            ->count();
            
        $approvedLeaves = Leave::whereBetween('start_date', [$startDate, $endDate])
            ->where('status', 'approved')
            ->when($this->departmentFilter, function($query) {
                return $query->whereHas('employee', function($q) {
                    $q->where('department_id', $this->departmentFilter);
                });
            })
            ->count();
            
        $leaveRate = $totalLeaves > 0 ? round(($approvedLeaves / $totalLeaves) * 100, 1) : 0;
        
        // Calcular crescimento na utilização de licenças (comparação com mês anterior)
        $previousLeaveTotal = Leave::whereBetween('start_date', [$previousPeriodStart, $previousPeriodEnd])
            ->when($this->departmentFilter, function($query) {
                return $query->whereHas('employee', function($q) {
                    $q->where('department_id', $this->departmentFilter);
                });
            })
            ->count();
            
        $previousApprovedLeaves = Leave::whereBetween('start_date', [$previousPeriodStart, $previousPeriodEnd])
            ->where('status', 'approved')
            ->when($this->departmentFilter, function($query) {
                return $query->whereHas('employee', function($q) {
                    $q->where('department_id', $this->departmentFilter);
                });
            })
            ->count();
            
        $previousLeaveRate = $previousLeaveTotal > 0 ? round(($previousApprovedLeaves / $previousLeaveTotal) * 100, 1) : 0;
        $leaveGrowth = $previousLeaveRate > 0 ? round(($leaveRate - $previousLeaveRate), 1) : 0;
        
        // Calcular total da folha de pagamento
        $totalPayroll = Payroll::whereBetween('payment_date', [$startDate, $endDate])
            ->when($this->departmentFilter, function($query) {
                return $query->whereHas('employee', function($q) {
                    $q->where('department_id', $this->departmentFilter);
                });
            })
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
