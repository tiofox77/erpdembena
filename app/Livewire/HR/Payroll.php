<?php

declare(strict_types=1);

namespace App\Livewire\HR;

use App\Models\HR\Payroll as PayrollModel;
use App\Models\HR\PayrollPeriod;
use App\Models\HR\PayrollItem;
use App\Models\HR\Department;
use App\Models\HR\Employee;
use App\Models\HR\Attendance;
use App\Models\HR\OvertimeRecord;
use App\Models\HR\SalaryAdvance;
use App\Models\HR\SalaryDiscount;
use App\Models\HR\HRSetting;
use App\Models\HR\IRTTaxBracket;
use App\Models\HR\ShiftAssignment;
use App\Models\HR\Leave;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use App\Models\Setting;

class Payroll extends Component
{
    use WithPagination;

    public string $search = '';
    public int $perPage = 10;
    public string $sortField = 'created_at';
    public string $sortDirection = 'desc';
    public array $filters = [
        'department_id' => '',
        'period_id' => '',
        'status' => '',
        'month' => '',
        'year' => '',
    ];

    // Additional Search Properties
    public int $selectedMonth;
    public int $selectedYear;
    
    /**
     * Initialize properties with default values
     */
    public function initializeProperties(): void
    {
        if (!isset($this->selectedMonth) || $this->selectedMonth === 0) {
            $this->selectedMonth = (int) now()->month;
        }
        if (!isset($this->selectedYear) || $this->selectedYear === 0) {
            $this->selectedYear = (int) now()->year;
        }
    }
    
    /**
     * Get selected month with fallback
     */
    public function getSelectedMonthProperty(): int
    {
        if (!isset($this->selectedMonth) || $this->selectedMonth === 0) {
            $this->selectedMonth = (int) now()->month;
        }
        return $this->selectedMonth;
    }
    
    /**
     * Get selected year with fallback
     */
    public function getSelectedYearProperty(): int
    {
        if (!isset($this->selectedYear) || $this->selectedYear === 0) {
            $this->selectedYear = (int) now()->year;
        }
        return $this->selectedYear;
    }

    // Form properties - Employee Selection
    public ?int $payroll_id = null;
    public ?int $employee_id = null;
    public ?int $payroll_period_id = null;
    public ?PayrollPeriod $selectedPayrollPeriod = null;
    public bool $showPeriodSelection = false;
    
    // Basic Salary Components
    public float $basic_salary = 0.0;
    public float $hourly_rate = 0.0;
    public float $monthly_hours = 0.0;
    
    // Attendance & Hours Data
    public float $total_attendance_hours = 0.0;
    public float $regular_hours_pay = 0.0;
    public int $total_present_days = 0;
    public int $total_absent_days = 0;
    public int $total_late_days = 0;
    public array $attendanceData = [];
    
    // Properties used in modal (matching template variables)
    public int $total_working_days = 0;
    public int $present_days = 0;
    public int $absent_days = 0;
    public int $late_arrivals = 0;
    
    // Deductions for attendance issues
    public float $late_deduction = 0.0;
    public float $absence_deduction = 0.0;
    
    // Overtime Data
    public float $total_overtime_hours = 0.0;
    public float $total_overtime_amount = 0.0;
    public array $overtime_details = [];
    public float $night_shift_allowance = 0.0;
    public float $night_shift_bonus = 0.0;
    public array $night_shift_details = [];
    public int $night_shift_days = 0;
    
    // Leave Data
    public int $total_leave_days = 0;
    public float $leave_deduction = 0.0;
    public int $unpaid_leave_days = 0;
    public array $leaveRecords = [];
    
    // Salary Advances
    public float $total_salary_advances = 0.0;
    public float $advance_deduction = 0.0;
    public array $salaryAdvances = [];
    
    // Salary Discounts
    public float $total_salary_discounts = 0.0;
    public array $salaryDiscounts = [];
    
    // Additional Components
    public float $meal_allowance = 0.0;
    public float $transport_allowance = 0.0;
    public float $other_allowances = 0.0;
    public bool $is_food_in_kind = false;
    public float $housing_allowance = 0.0;
    public float $performance_bonus = 0.0;
    public float $profile_bonus = 0.0;
    public float $custom_bonus = 0.0;
    public string $custom_bonus_description = '';
    public float $bonus_amount = 0.0;
    public float $additional_bonus_amount = 0.0; // Separate from employee record bonus
    
    /**
     * Updated hook to ensure bonus fields are never null
     */
    public function updated($propertyName): void
    {
        // Ensure bonus fields are never null
        $bonusFields = [
            'bonus_amount',
            'additional_bonus_amount',
            'profile_bonus',
            'custom_bonus',
            'performance_bonus',
            'transport_allowance',
            'meal_allowance',
            'housing_allowance',
            'total_overtime_amount',
        ];
        
        if (in_array($propertyName, $bonusFields)) {
            $this->$propertyName = (float) ($this->$propertyName ?? 0);
        }
        
        // Recalculate when any financial field changes
        if ($this->selectedEmployee) {
            $this->calculatePayrollComponents();
        }
    }
    
    // Holiday Subsidies (Checkboxes)
    public bool $christmas_subsidy = false;
    public bool $vacation_subsidy = false;
    public bool $include_vacation_subsidy = false;
    public bool $include_christmas_subsidy = false;
    
    // Holiday Subsidy Amounts
    public float $christmas_subsidy_amount = 0.0;
    public float $vacation_subsidy_amount = 0.0;
    
    // Deductions
    public float $income_tax = 0.0;
    public float $social_security = 0.0;
    public float $other_deductions = 0.0;
    public string $other_deductions_description = '';
    public float $union_deduction = 0.0;
    public float $u_fund_ded = 0.0;
    public float $loan_installments = 0.0;
    public float $deductions_irt = 0.0;
    public float $inss_3_percent = 0.0;
    public float $inss_8_percent = 0.0;
    public float $absence_deduction_amount = 0.0;
    
    // Calculated Totals
    public float $gross_salary = 0.0;
    public float $total_deductions = 0.0;
    public float $net_salary = 0.0;
    
    // New calculated properties for consistent payroll calculations
    public float $main_salary = 0.0;
    public float $gross_for_tax = 0.0;
    public float $calculated_inss = 0.0;
    public float $calculated_irt = 0.0;
    public float $calculated_net_salary = 0.0;
    public float $base_irt_taxable_amount = 0.0;
    public float $total_deductions_calculated = 0.0;
    
    // Payment Information
    public string $payment_method = 'bank_transfer';
    public ?string $bank_account = null;
    public ?string $payment_date = null;
    public string $status = 'draft';
    public ?string $remarks = null;
    
    // Period Selection
    public ?string $selected_month = null;
    public ?string $selected_year = null;
    
    // HR Settings Cache
    public array $hrSettings = [];
    
    // Payroll items
    public array $payrollItems = [];

    // Modal states
    public bool $showModal = false;
    public bool $showViewModal = false;
    public bool $showPayModal = false;
    public bool $showDeleteModal = false;
    public bool $showApproveModal = false;
    public bool $showProcessModal = false;
    public bool $isEditing = false;
    public bool $showEmployeeSearch = false;
    
    // Current payroll for operations
    public ?PayrollModel $currentPayroll = null;
    public ?PayrollModel $payrollToDelete = null;
    public ?Employee $selectedEmployee = null;
    
    // Employee search and selection
    public string $employeeSearch = '';
    public array $searchResults = [];
    public array $allEmployees = [];
    public bool $showAllEmployees = true;
    public bool $dataLoaded = false;
    
    // Advanced search filters
    public string $departmentFilter = '';
    public string $statusFilter = 'active';
    public string $employeeSortField = 'full_name';
    public string $employeeSortDirection = 'asc';
    public int $resultsPerPage = 20;
    public int $currentPage = 1;
    public int $totalResults = 0;
    public int $totalPages = 0;

    // Listeners
    protected $listeners = [
        'refreshPayrolls' => '$refresh',
        'employeeSelected' => 'selectEmployee',
        'recalculatePayroll' => 'calculatePayrollComponents'
    ];

    /**
     * Initialize component and load HR settings
     */
    public function mount(): void
    {
        // Initialize properties first
        $this->initializeProperties();
        
        // Initialize current month and year for payroll processing
        $this->selectedMonth = (int) now()->month;
        $this->selectedYear = (int) now()->year;
        $this->selected_month = now()->format('m');
        $this->selected_year = now()->format('Y');

        // Initialize filters
        $this->filters['month'] = $this->selected_month;
        $this->filters['year'] = $this->selected_year;
        $this->createDefaultTaxSettings();
        $this->loadHRSettings();
    }

    /**
     * Check if user can view salary details
     */
    public function canViewSalaryDetails(): bool
    {
        return auth()->user()->can('hr.employees.salary.view') || auth()->user()->can('hr.payroll.view');
    }
    
    /**
     * Check if user can process payroll
     */
    public function canProcessPayroll(): bool
    {
        return auth()->user()->can('hr.payroll.process') || auth()->user()->can('hr.payroll.view');
    }

    /**
     * Load HR Settings for payroll calculations
     */
    public function loadHRSettings(): void
    {
        $this->hrSettings = [
            'working_hours_per_day' => (float) \App\Models\HR\HRSetting::get('working_hours_per_day', 8),
            'working_days_per_month' => (int) \App\Models\HR\HRSetting::get('working_days_per_month', 22),
            'vacation_subsidy_percentage' => (float) \App\Models\HR\HRSetting::get('vacation_subsidy_percentage', 50),
            'christmas_subsidy_percentage' => (float) \App\Models\HR\HRSetting::get('christmas_subsidy_percentage', 100),
            'income_tax_percentage' => (float) \App\Models\HR\HRSetting::get('income_tax_percentage', 15),
            'social_security_percentage' => (float) \App\Models\HR\HRSetting::get('social_security_percentage', 3),
        ];
    }



    /**
     * Calculate hourly rate based on monthly salary
     */
    private function calculateHourlyRate(): float
    {
        $workingDays = $this->hrSettings['working_days_per_month'] ?? 22;
        $workingHours = $this->hrSettings['working_hours_per_day'] ?? 8;
        $totalMonthlyHours = $workingDays * $workingHours;
        
        return $totalMonthlyHours > 0 ? $this->basic_salary / $totalMonthlyHours : 0;
    }

    /**
     * Load all employee payroll data for the selected period
     */
    public function loadEmployeePayrollData(): void
    {
        if (!$this->selectedEmployee || !$this->selected_month || !$this->selected_year) {
            return;
        }

        $startDate = Carbon::createFromDate($this->selected_year, $this->selected_month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        $this->loadAttendanceData($startDate, $endDate);
        $this->loadOvertimeData($startDate, $endDate);
        $this->loadSalaryAdvances($startDate, $endDate);
        $this->loadSalaryDiscounts($startDate, $endDate);
        $this->loadLeaveData($startDate, $endDate);
        
        // Load benefits and bonus from employee record
        $this->loadEmployeeBenefits();
    }

    /**
     * Load employee payroll data for a specific payroll period
     * This method uses the selected payroll period dates instead of month/year
     */
    public function loadEmployeePayrollDataForPeriod(): void
    {
        if (!$this->selectedEmployee || !$this->selectedPayrollPeriod) {
            return;
        }

        $startDate = Carbon::parse($this->selectedPayrollPeriod->start_date);
        $endDate = Carbon::parse($this->selectedPayrollPeriod->end_date);

        $this->loadAttendanceData($startDate, $endDate);
        $this->loadOvertimeData($startDate, $endDate);
        $this->loadSalaryAdvances($startDate, $endDate);
        $this->loadSalaryDiscounts($startDate, $endDate);
        $this->loadLeaveData($startDate, $endDate);
        
        // Load benefits and bonus from employee record
        $this->loadEmployeeBenefits();
        
        // Log for debugging
        \Log::info('Employee payroll data loaded for period', [
            'employee_id' => $this->selectedEmployee['id'],
            'period_id' => $this->selectedPayrollPeriod->id,
            'period_name' => $this->selectedPayrollPeriod->name,
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d')
        ]);
    }

    /**
     * Load employee benefits and bonus from employee record
     */
    private function loadEmployeeBenefits(): void
    {
        if (!$this->selectedEmployee) {
            return;
        }

        // Load benefits from employee record (food benefit is excluded from salary calculation)
        $this->transport_allowance = $this->calculateProportionalTransportAllowance();
        $this->meal_allowance = (float) ($this->selectedEmployee->food_benefit ?? 0); // Loaded but excluded from gross salary
        
        // Load bonus amount from employee record
        $this->bonus_amount = (float) ($this->selectedEmployee->bonus_amount ?? 0);
        
        // Initialize other allowances (these might come from other sources)
        $this->housing_allowance = $this->housing_allowance ?? 0.0;
        $this->performance_bonus = $this->performance_bonus ?? 0.0;
        $this->custom_bonus = $this->custom_bonus ?? 0.0;
    }

    /**
     * Load attendance data for the period
     */
    private function loadAttendanceData(Carbon $startDate, Carbon $endDate): void
    {
        // Load ALL attendance records for the period (not just certain statuses)
        $attendances = \App\Models\HR\Attendance::where('employee_id', $this->employee_id)
            ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->orderBy('date', 'asc')
            ->get();

        // Get working days from HR settings or calculate from period
        $monthlyWorkingDays = (int) \App\Models\HR\HRSetting::get('monthly_working_days', 22);
        
        // Use HR setting if available, otherwise calculate from period
        if ($monthlyWorkingDays > 0) {
            $this->total_working_days = $monthlyWorkingDays;
        } else {
            // Fallback: Calculate working days in the period (excluding weekends)
            $this->total_working_days = 0;
            $current = $startDate->copy();
            while ($current <= $endDate) {
                if ($current->isWeekday()) {
                    $this->total_working_days++;
                }
                $current->addDay();
            }
        }

        // Count different attendance types
        $this->present_days = $attendances->whereIn('status', ['present', 'late', 'half_day'])->count();
        $this->absent_days = $this->total_working_days - $this->present_days; // Calculate actual absences
        $this->late_arrivals = $attendances->where('status', 'late')->count();
        
        // Calculate total attendance hours
        $this->total_attendance_hours = 0;
        $standardWorkDay = 8; // Standard work day hours
        
        foreach ($attendances as $attendance) {
            if (in_array($attendance->status, ['present', 'late', 'half_day'])) {
                $hours = 0;
                
                // If we have time_in and time_out, calculate actual hours
                if ($attendance->time_in && $attendance->time_out) {
                    $timeIn = Carbon::parse($attendance->time_in);
                    $timeOut = Carbon::parse($attendance->time_out);
                    $hours = $timeIn->diffInHours($timeOut);
                    
                    // For half_day, ensure maximum is 4 hours
                    if ($attendance->status === 'half_day') {
                        $hours = min($hours / 2, 4);
                    }
                } else {
                    // If no times recorded, use standard hours based on status
                    switch ($attendance->status) {
                        case 'present':
                            $hours = $standardWorkDay;
                            break;
                        case 'late':
                            $hours = $standardWorkDay; // Assume full day but note as late
                            break;
                        case 'half_day':
                            $hours = $standardWorkDay / 2;
                            break;
                    }
                }
                
                $this->total_attendance_hours += $hours;
            }
        }

        $this->regular_hours_pay = $this->total_attendance_hours * $this->hourly_rate;
        $this->attendanceData = $attendances->toArray();
        
        // Calculate deductions for late arrivals and absences
        $this->calculateAttendanceDeductions($attendances);
        
        // Debug log for troubleshooting
        \Log::info('Attendance data loaded', [
            'employee_id' => $this->employee_id,
            'period' => $startDate->format('Y-m-d') . ' to ' . $endDate->format('Y-m-d'),
            'total_records' => $attendances->count(),
            'present_days' => $this->present_days,
            'absent_days' => $this->absent_days,
            'total_working_days' => $this->total_working_days,
            'total_hours' => $this->total_attendance_hours,
            'late_deduction' => $this->late_deduction,
            'absence_deduction' => $this->absence_deduction
        ]);
    }

    /**
     * Calculate deductions for late arrivals and absences
     */
    private function calculateAttendanceDeductions($attendances): void
    {
        // Reset deductions
        $this->late_deduction = 0.0;
        $this->absence_deduction = 0.0;
        
        // Get working days from HR settings for consistent calculation
        $monthlyWorkingDays = (int) \App\Models\HR\HRSetting::get('monthly_working_days', 22);
        $workingDaysForCalculation = $monthlyWorkingDays > 0 ? $monthlyWorkingDays : $this->total_working_days;
        
        // Calculate daily rate (monthly salary / working days)
        $dailyRate = $workingDaysForCalculation > 0 ? $this->basic_salary / $workingDaysForCalculation : 0;
        
        foreach ($attendances as $attendance) {
            switch ($attendance->status) {
                case 'late':
                    // Deduct 1 hour for each late arrival
                    $this->late_deduction += $this->hourly_rate;
                    break;
                    
                case 'absent':
                    // Deduct full daily rate for absence
                    $this->absence_deduction += $dailyRate;
                    break;
                    
                case 'half_day':
                    // Deduct half daily rate for half day
                    $this->absence_deduction += ($dailyRate / 2);
                    break;
            }
        }
    }

    /**
     * Get computed total deductions including attendance deductions
     */
    public function getTotalDeductionsProperty(): float
    {
        $absenceDeduction = $this->getAbsenceDeductionAmountProperty();
        
        $totalDeductions = ($this->income_tax ?? 0) +
                          ($this->social_security ?? 0) +
                          ($this->other_deductions ?? 0) +
                          ($this->advance_deduction ?? 0) +
                          ($this->total_salary_discounts ?? 0) +
                          ($this->late_deduction ?? 0) +
                          ($this->absence_deduction ?? 0) +
                          $absenceDeduction;
        
        // Debug log
        \Log::info('Total Deductions Calculation', [
            'income_tax' => $this->income_tax ?? 0,
            'social_security' => $this->social_security ?? 0,
            'other_deductions' => $this->other_deductions ?? 0,
            'advance_deduction' => $this->advance_deduction ?? 0,
            'total_salary_discounts' => $this->total_salary_discounts ?? 0,
            'late_deduction' => $this->late_deduction ?? 0,
            'absence_deduction_old' => $this->absence_deduction ?? 0,
            'absence_deduction_new' => $absenceDeduction,
            'absent_days' => $this->absent_days,
            'total_working_days' => $this->total_working_days,
            'basic_salary' => $this->basic_salary,
            'total_calculated' => $totalDeductions
        ]);
        
        return $totalDeductions;
    }

    /**
     * Get computed net salary
     */
    public function getNetSalaryProperty(): float
    {
        $grossSalary = $this->gross_salary ?? 0;
        $totalDeductions = $this->getTotalDeductionsProperty();
        
        // Subtrair subsídio de alimentação do net salary
        $foodBenefit = (float) ($this->selectedEmployee->food_benefit ?? $this->meal_allowance ?? 0);
        
        $netSalary = $grossSalary - $totalDeductions - $foodBenefit;
        
        // Debug log
        \Log::info('Net Salary Calculation', [
            'gross_salary' => $grossSalary,
            'total_deductions' => $totalDeductions,
            'food_benefit_deducted' => $foodBenefit,
            'net_salary_calculated' => $netSalary,
            'net_salary_property' => $this->net_salary ?? 0
        ]);
        
        // Return actual calculated value (can be negative)
        return $netSalary;
    }

    /**
     * Get deduction for absent days
     */
    public function getAbsenceDeductionAmountProperty(): float
    {
        if ($this->absent_days > 0 && $this->basic_salary > 0) {
            // Get working days from HR settings for consistent calculation
            $monthlyWorkingDays = (int) \App\Models\HR\HRSetting::get('monthly_working_days', 22);
            $workingDaysForCalculation = $monthlyWorkingDays > 0 ? $monthlyWorkingDays : $this->total_working_days;
            
            if ($workingDaysForCalculation > 0) {
                // Calcular dedução baseada nos dias ausentes
                $dailyRate = $this->basic_salary / $workingDaysForCalculation;
                return $dailyRate * $this->absent_days;
            }
        }
        return 0;
    }

    /**
     * Get attendance hours (alias for total_attendance_hours)
     */
    public function getAttendanceHoursProperty(): float
    {
        return $this->total_attendance_hours ?? 0;
    }


    /**
     * Load overtime records for the period
     */
    private function loadOvertimeData(Carbon $startDate, Carbon $endDate): void
    {
        $overtimeRecords = \App\Models\HR\OvertimeRecord::where('employee_id', $this->employee_id)
            ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->where('status', 'approved')
            ->get();

        $this->total_overtime_hours = $overtimeRecords->sum('hours');
        $this->total_overtime_amount = $overtimeRecords->sum('amount');
        $this->overtimeRecords = $overtimeRecords->toArray();
    }

    /**
     * Load salary advances for the period
     */
    private function loadSalaryAdvances(Carbon $startDate, Carbon $endDate): void
    {
        // Load all active advances for this employee (not just for this period)
        $advances = \App\Models\HR\SalaryAdvance::where('employee_id', $this->employee_id)
            ->where('status', 'approved')
            ->where('remaining_installments', '>', 0)
            ->get();

        $this->total_salary_advances = $advances->sum('amount');
        $this->advance_deduction = $advances->sum('installment_amount'); // Current month deduction
        
        // Map advances with installment details
        $this->salaryAdvances = $advances->map(function($advance) {
            return [
                'id' => $advance->id,
                'request_date' => $advance->request_date->format('d/m/Y'),
                'amount' => $advance->amount,
                'installments' => $advance->installments,
                'installment_amount' => $advance->installment_amount,
                'remaining_installments' => $advance->remaining_installments,
                'reason' => $advance->reason,
            ];
        })->toArray();
    }

    /**
     * Load salary discounts for the period
     */
    private function loadSalaryDiscounts(Carbon $startDate, Carbon $endDate): void
    {
        // Load all approved discounts for this employee with remaining installments
        $discounts = \App\Models\HR\SalaryDiscount::where('employee_id', $this->employee_id)
            ->where('status', 'approved')
            ->where('remaining_installments', '>', 0)
            ->get();

        $this->total_salary_discounts = $discounts->sum('installment_amount'); // Current month deduction
        
        // Map discounts with installment details
        $this->salaryDiscounts = $discounts->map(function($discount) {
            return [
                'id' => $discount->id,
                'request_date' => $discount->request_date->format('d/m/Y'),
                'amount' => $discount->amount,
                'installments' => $discount->installments,
                'installment_amount' => $discount->installment_amount,
                'remaining_installments' => $discount->remaining_installments,
                'reason' => $discount->reason,
                'discount_type' => $discount->discount_type,
            ];
        })->toArray();
    }

    /**
     * Load leave data for the period
     */
    private function loadLeaveData(Carbon $startDate, Carbon $endDate): void
    {
        $leaves = \App\Models\HR\Leave::where('employee_id', $this->employee_id)
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('start_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                      ->orWhereBetween('end_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')]);
            })
            ->where('status', 'approved')
            ->get();

        $this->total_leave_days = 0;
        $this->unpaid_leave_days = 0;

        foreach ($leaves as $leave) {
            $leaveDays = Carbon::parse($leave->start_date)->diffInDays(Carbon::parse($leave->end_date)) + 1;
            $this->total_leave_days += $leaveDays;
            
            // Check if leave type is unpaid
            $leaveType = \App\Models\HR\LeaveType::find($leave->leave_type_id);
            if ($leaveType && !$leaveType->is_paid) {
                $this->unpaid_leave_days += $leaveDays;
            }
        }

        // Calculate leave deduction based on unpaid days
        $dailyRate = $this->basic_salary / ($this->hrSettings['working_days_per_month'] ?? 22);
        $this->leave_deduction = $this->unpaid_leave_days * $dailyRate;
        $this->leaveRecords = $leaves->toArray();
    }



    /**
     * Create or update HR settings for subsidies
     */
    private function createOrUpdateSubsidySetting(string $type, float $amount): void
    {
        if ($amount > 0) {
            \App\Models\HR\HRSetting::updateOrCreate(
                [
                    'employee_id' => $this->employee_id,
                    'setting_type' => $type,
                    'period' => $this->selected_year . '-' . str_pad($this->selected_month, 2, '0', STR_PAD_LEFT)
                ],
                [
                    'setting_value' => (string) $amount,
                    'is_active' => true,
                    'created_by' => Auth::id()
                ]
            );
        }
    }

    /**
     * Load HR tax and discount settings
     */
    private function loadTaxSettings(): array
    {
        // Get tax settings from HR settings with defaults
        $taxSettings = \App\Models\HR\HRSetting::where('group', 'tax')
            ->pluck('value', 'key')
            ->toArray();

        // Set default values if not found in settings
        $defaults = [
            'irt_rate' => 6.5,          // IRT - 6.5%
            'inss_rate' => 3.0,         // INSS - 3.0%  
            'irt_min_salary' => 70000,  // Mínimo isento de IRT
            'inss_max_salary' => 0,     // Máximo para INSS (0 = sem limite)
            'tax_calculation_base' => 'gross',  // Base de cálculo: 'gross' ou 'base'
        ];

        // Merge with defaults and convert numeric values
        $settings = array_merge($defaults, $taxSettings);
        
        // Convert string values to appropriate types
        $settings['irt_rate'] = (float) $settings['irt_rate'];
        $settings['inss_rate'] = (float) $settings['inss_rate'];
        $settings['irt_min_salary'] = (float) $settings['irt_min_salary'];
        $settings['inss_max_salary'] = (float) $settings['inss_max_salary'];

        return $settings;
    }

    /**
     * Create default tax settings if they don't exist
     */
    private function createDefaultTaxSettings(): void
    {
        $defaultSettings = [
            [
                'key' => 'irt_rate',
                'value' => '6.5',
                'group' => 'tax',
                'description' => 'Taxa de IRT (Imposto sobre Rendimento do Trabalho) em percentagem',
                'is_system' => true
            ],
            [
                'key' => 'inss_rate', 
                'value' => '3.0',
                'group' => 'tax',
                'description' => 'Taxa de INSS (Instituto Nacional de Segurança Social) em percentagem',
                'is_system' => true
            ],
            [
                'key' => 'irt_min_salary',
                'value' => '70000',
                'group' => 'tax',
                'description' => 'Salário mínimo isento de IRT em AOA',
                'is_system' => true
            ],
            [
                'key' => 'inss_max_salary',
                'value' => '0',
                'group' => 'tax', 
                'description' => 'Salário máximo para cálculo de INSS (0 = sem limite)',
                'is_system' => true
            ],
            [
                'key' => 'tax_calculation_base',
                'value' => 'gross',
                'group' => 'tax',
                'description' => 'Base de cálculo de impostos: gross (salário bruto) ou base (salário base)',
                'is_system' => true
            ]
        ];

        foreach ($defaultSettings as $setting) {
            \App\Models\HR\HRSetting::firstOrCreate(
                ['key' => $setting['key'], 'group' => $setting['group']],
                $setting
            );
        }
    }



    /**
     * Search employees for payroll processing with advanced filters
     */
    public function searchEmployees(): void
    {
        $query = Employee::query()
            ->with(['department', 'position'])
            ->select('id', 'full_name', 'id_card', 'email', 'phone', 'department_id', 'position_id', 'employment_status', 'base_salary', 'hire_date');

        // Apply status filter
        if ($this->statusFilter) {
            $query->where('employment_status', $this->statusFilter);
        } else {
            // Default to active employees only
            $query->where('employment_status', 'active');
        }

        // Apply department filter
        if ($this->departmentFilter) {
            $query->where('department_id', $this->departmentFilter);
        }

        // Excluir funcionários já pagos no período selecionado (usar período da primeira modal)
        if ($this->payroll_period_id) {
            $query->whereDoesntHave('payrolls', function ($payrollQuery) {
                $payrollQuery->where('payroll_period_id', $this->payroll_period_id);
            });
        }

        // Apply search term if provided
        if (strlen($this->employeeSearch) >= 2) {
            $query->where(function ($q) {
                $q->where('full_name', 'like', '%' . $this->employeeSearch . '%')
                  ->orWhere('id_card', 'like', '%' . $this->employeeSearch . '%')
                  ->orWhere('email', 'like', '%' . $this->employeeSearch . '%')
                  ->orWhere('tax_number', 'like', '%' . $this->employeeSearch . '%')
                  ->orWhere('phone', 'like', '%' . $this->employeeSearch . '%');
            });
        }

        // Apply sorting
        $query->orderBy($this->employeeSortField, $this->employeeSortDirection);
        
        // Get total count for pagination
        $totalResults = $query->count();
        
        // Apply pagination
        $offset = ($this->currentPage - 1) * $this->resultsPerPage;
        $employees = $query->skip($offset)
            ->take($this->resultsPerPage)
            ->get();

        // Transform results
        $this->searchResults = $employees->map(function ($employee) {
            return [
                'id' => $employee->id,
                'full_name' => $employee->full_name,
                'id_card' => $employee->id_card,
                'email' => $employee->email,
                'phone' => $employee->phone ?? 'N/A',
                'department_name' => $employee->department?->name ?? 'N/A',
                'position_name' => $employee->position?->name ?? 'N/A',
                'employment_status' => $employee->employment_status,
                'base_salary' => $employee->base_salary ?? 0,
                'hire_date' => $employee->hire_date?->format('d/m/Y') ?? 'N/A'
            ];
        })->toArray();
        
        // Store pagination info
        $this->totalResults = $totalResults;
        $this->totalPages = (int) ceil($totalResults / $this->resultsPerPage);
    }

    /**
     * Load all employees when modal opens
     */
    public function loadAllEmployees(): void
    {
        if ($this->showAllEmployees) {
            $this->searchEmployees();
        }
    }

    /**
     * Reset employee search filters
     */
    public function resetEmployeeSearchFilters(): void
    {
        $this->employeeSearch = '';
        $this->departmentFilter = '';
        $this->statusFilter = 'active';
        $this->employeeSortField = 'full_name';
        $this->employeeSortDirection = 'asc';
        $this->currentPage = 1;
        $this->searchEmployees();
    }



    /**
     * Go to specific page
     */
    public function goToPage(int $page): void
    {
        if ($page >= 1 && $page <= $this->totalPages) {
            $this->currentPage = $page;
            $this->searchEmployees();
        }
    }

    /**
     * Go to previous page
     */
    public function previousPage(): void
    {
        if ($this->currentPage > 1) {
            $this->currentPage--;
            $this->searchEmployees();
        }
    }

    /**
     * Go to next page
     */
    public function nextPage(): void
    {
        if ($this->currentPage < $this->totalPages) {
            $this->currentPage++;
            $this->searchEmployees();
        }
    }

    /**
     * Update filters and trigger search
     */
    public function updatedEmployeeSearch(): void
    {
        $this->currentPage = 1;
        $this->searchEmployees();
    }

    public function updatedDepartmentFilter(): void
    {
        $this->currentPage = 1;
        $this->searchEmployees();
    }

    public function updatedStatusFilter(): void
    {
        $this->currentPage = 1;
        $this->searchEmployees();
    }

    public function updatedResultsPerPage(): void
    {
        $this->currentPage = 1;
        $this->searchEmployees();
    }

    public function updatedEmployeeSortField(): void
    {
        $this->currentPage = 1;
        $this->searchEmployees();
    }

    public function updatedEmployeeSortDirection(): void
    {
        $this->currentPage = 1;
        $this->searchEmployees();
    }

    /**
     * Show delete confirmation modal
     */
    public function delete(int $payrollId): void
    {
        $this->payrollToDelete = PayrollModel::with(['employee', 'payrollPeriod'])->findOrFail($payrollId);
        
        // Check if payroll can be deleted (only draft/rejected for regular users, admin can delete any)
        $isAdmin = auth()->user()->hasRole('admin') || auth()->user()->hasRole('super-admin');
        
        if (!$isAdmin && !in_array($this->payrollToDelete->status, ['draft', 'rejected'])) {
            session()->flash('error', __('messages.cannot_delete_payroll_status'));
            return;
        }
        
        $this->showDeleteModal = true;
    }

    /**
     * Confirm and execute delete
     */
    public function confirmDelete(): void
    {
        try {
            if (!$this->payrollToDelete) {
                session()->flash('error', __('messages.payroll_not_found'));
                return;
            }
            
            // Delete related payroll items first
            $this->payrollToDelete->payrollItems()->delete();
            
            // Delete the payroll record
            $this->payrollToDelete->delete();
            
            session()->flash('message', __('messages.payroll_deleted_successfully'));
            
            $this->cancelDelete();
            
        } catch (\Exception $e) {
            session()->flash('error', __('messages.error_deleting_payroll'));
        }
    }

    /**
     * Cancel delete operation
     */
    public function cancelDelete(): void
    {
        $this->showDeleteModal = false;
        $this->payrollToDelete = null;
    }

    /**
     * Calculate payroll components dynamically
     */
    public function calculatePayrollComponents(): void
    {
        if (!$this->selectedEmployee || !$this->basic_salary) {
            return;
        }

        // Reset calculations
        $this->gross_salary = 0.0;
        $this->total_deductions = 0.0;
        $this->net_salary = 0.0;

        // Base salary original (sem proporcionalidade)
        $grossAmount = $this->basic_salary;

        // Add Christmas subsidy (50% of base salary)
        if ($this->christmas_subsidy) {
            $grossAmount += ($this->basic_salary * 0.5);
        }

        // Add Vacation subsidy (50% of base salary)
        if ($this->vacation_subsidy) {
            $grossAmount += ($this->basic_salary * 0.5);
        }

        // Add additional bonus (from payroll form, separate from employee record)
        if ($this->additional_bonus_amount > 0) {
            $grossAmount += $this->additional_bonus_amount;
        }
        
        // Add employee record bonus (from employee profile) - fully taxable, not affected by absences
        if ($this->bonus_amount > 0) {
            $grossAmount += $this->bonus_amount;
        }

        // Add allowances with tax-exempt limits (Angola tax rules)
        $grossAmount += $this->getTaxableTransportAllowance(); // Transport only (proportional by days worked)
        // $grossAmount += $this->meal_allowance; // Food benefit excluded from gross salary (fully exempt)
        $grossAmount += $this->getTaxableHousingAllowance();
        $grossAmount += $this->performance_bonus; // Fully taxable
        $grossAmount += $this->custom_bonus; // Fully taxable

        // Add overtime amount if available
        if ($this->total_overtime_amount > 0) {
            $grossAmount += $this->total_overtime_amount;
        }

        // Calculate night shift allowance (20% additional)
        $nightShiftAmount = $this->calculateNightShiftAmount();
        if ($nightShiftAmount > 0) {
            $grossAmount += $nightShiftAmount;
        }

        $this->gross_salary = $grossAmount;

        // Load tax settings
        $taxSettings = $this->loadTaxSettings();
        
        // Calculate deductions
        $deductions = 0.0;
        
        // INSS (Social Security) - based on gross salary with maximum limit
        $inssBase = $this->gross_salary;
        if ($taxSettings['inss_max_salary'] > 0 && $inssBase > $taxSettings['inss_max_salary']) {
            $inssBase = $taxSettings['inss_max_salary'];
        }
        $this->social_security = $inssBase * ($taxSettings['inss_rate'] / 100);
        $deductions += $this->social_security;
        
        // Calculate IRT using progressive tax brackets based on MC (Matéria Coletável)
        // MC = Gross Salary - INSS
        $materiaColetavel = max(0, $this->gross_salary - $this->social_security);
        $this->income_tax = IRTTaxBracket::calculateIRT($materiaColetavel);
        $deductions += $this->income_tax;

        // Salary advances deduction
        if ($this->advance_deduction > 0) {
            $deductions += $this->advance_deduction;
        }

        // Salary discounts
        if ($this->total_salary_discounts > 0) {
            $deductions += $this->total_salary_discounts;
        }
        
        // Attendance deductions
        if ($this->late_deduction > 0) {
            $deductions += $this->late_deduction;
        }
        if ($this->absence_deduction > 0) {
            $deductions += $this->absence_deduction;
        }

        // Other deductions
        $deductions += $this->other_deductions;

        $this->total_deductions = $deductions;
        
        // Calcular e atribuir valores às novas propriedades
        $this->main_salary = $this->getMainSalaryProperty();
        $this->base_irt_taxable_amount = $materiaColetavel;
        $this->deductions_irt = $this->income_tax;
        
        // Calcular INSS 3% (empregador) e 8% (empregado)
        $inssEmployeeRate = 8.0; // 8% para empregado
        $inssEmployerRate = 3.0; // 3% para empregador
        $this->inss_3_percent = $inssBase * ($inssEmployerRate / 100);
        $this->inss_8_percent = $inssBase * ($inssEmployeeRate / 100);
        
        // Garantir que absence_deduction_amount seja preenchido
        $this->absence_deduction_amount = (float)($this->absence_deduction ?? 0);
        $this->total_deductions_calculated = $deductions;
        
        // Subtrair subsídio de alimentação do net salary
        $foodBenefit = (float) ($this->selectedEmployee->food_benefit ?? $this->meal_allowance ?? 0);
        
        $this->net_salary = max(0, $this->gross_salary - $this->total_deductions - $foodBenefit);
    }

    /**
     * Get Christmas subsidy amount
     */
    public function getChristmasSubsidyAmountProperty(): float
    {
        return $this->christmas_subsidy ? ($this->basic_salary * 0.5) : 0.0;
    }

    /**
     * Get Vacation subsidy amount  
     */
    public function getVacationSubsidyAmountProperty(): float
    {
        return $this->vacation_subsidy ? ($this->basic_salary * 0.5) : 0.0;
    }

    /**
     * Get total subsidies amount
     */
    public function getTotalSubsidiesProperty(): float
    {
        return $this->christmasSubsidyAmount + $this->vacationSubsidyAmount;
    }
    
    /**
     * Get IRT tax bracket information
     */
    public function getIrtTaxBracketProperty(): ?object
    {
        if ($this->gross_salary <= 0) {
            return null;
        }
        
        return \App\Models\HR\IRTTaxBracket::getBracketForIncome($this->gross_salary);
    }
    
    /**
     * Get formatted IRT bracket description
     */
    public function getIrtBracketDescriptionProperty(): string
    {
        // Use the same MC calculation as in irtCalculationDetails
        $details = $this->irtCalculationDetails;
        $bracket = $details['bracket'];
        $mc = $details['mc'];
        $totalIrt = $details['total_irt'];
        
        if (!$bracket || $mc <= 0) {
            return 'Isento - Escalão 1';
        }
        
        // Create detailed breakdown description
        return $this->getIrtBreakdownDescription($bracket, $mc, $totalIrt);
    }
    
    /**
     * Get detailed IRT breakdown description showing cumulative total
     */
    private function getIrtBreakdownDescription($bracket, $mc, $totalIrt): string
    {
        if ($bracket->bracket_number == 1) {
            return "Escalão 1 - Isento";
        }
        
        // Show the current bracket with cumulative total
        $details = $this->irtCalculationDetails;
        $fixedAmount = $details['fixed_amount'] ?? 0;
        $taxOnExcess = $details['tax_on_excess'] ?? 0;
        
        if ($bracket->bracket_number == 2) {
            // Only current bracket tax for bracket 2
            return "Escalão 2 - {$bracket->tax_rate}% | Total: " . number_format($totalIrt, 0) . " AOA";
        } else {
            // For higher brackets, show breakdown: fixed part (from previous brackets) + current bracket tax
            $currentBracketTax = $taxOnExcess;
            return "Escalões 1-{$bracket->bracket_number} | Fixo: " . number_format($fixedAmount, 0) . " + Atual: " . number_format($currentBracketTax, 0) . " = " . number_format($totalIrt, 0) . " AOA";
        }
    }
    
    /**
     * Calculate proportional transport allowance based on attendance
     */
    private function calculateProportionalTransportAllowance(): float
    {
        if (!$this->selectedEmployee || $this->total_working_days <= 0) {
            return 0.0;
        }

        $fullTransportAllowance = (float) ($this->selectedEmployee->transport_benefit ?? 0);
        
        // Calculate proportional allowance based on days worked
        $daysWorked = $this->present_days;
        $proportionalAllowance = ($fullTransportAllowance / $this->total_working_days) * $daysWorked;
        
        return $proportionalAllowance;
    }

    /**
     * Get full transport benefit amount from employee record
     */
    public function getFullTransportBenefit(): float
    {
        return (float) ($this->selectedEmployee->transport_benefit ?? 0);
    }

    /**
     * Get bonus amount from employee record
     */
    public function getBonusAmount(): float
    {
        return (float) ($this->selectedEmployee->bonus_amount ?? 0);
    }

    /**
     * Get proportional transport allowance only (without bonus)
     */
    public function getProportionalTransportOnly(): float
    {
        if (!$this->selectedEmployee) {
            return 0.0;
        }

        $fullTransportBenefit = $this->getFullTransportBenefit();
        $monthlyWorkingDays = (int) \App\Models\HR\HRSetting::get('monthly_working_days', 22);
        
        return ($fullTransportBenefit / $monthlyWorkingDays) * $this->present_days;
    }

    /**
     * Get transport allowance discount amount
     */
    public function getTransportDiscountAmount(): float
    {
        return $this->getFullTransportBenefit() - $this->getProportionalTransportOnly();
    }

    /**
     * Get total transport display amount (proportional transport + full bonus)
     */
    public function getTotalTransportDisplay(): float
    {
        return $this->getProportionalTransportOnly() + $this->getBonusAmount();
    }

    /**
     * Get taxable transport allowance only (amount above 30k exemption)
     * Applied proportionally based on days worked - EXCLUDES bonus
     */
    public function getTaxableTransportAllowance(): float
    {
        // Get proportional transport allowance only (no bonus)
        $proportionalTransport = $this->getProportionalTransportOnly();
        
        // Apply tax exemption limit only to transport
        $exemptLimit = 30000.0; // 30,000 AKZ per month exempt
        return max(0, $proportionalTransport - $exemptLimit);
    }
    
    /**
     * Get exempt transport allowance amount (proportional, transport only)
     */
    public function getExemptTransportAllowance(): float
    {
        // Use only proportional transport for exemption calculation (no bonus)
        $proportionalTransport = $this->getProportionalTransportOnly();
        $exemptLimit = 30000.0;
        return min($proportionalTransport, $exemptLimit);
    }
    
    /**
     * Get taxable housing allowance (assume 50% exempt for now, can be configured)
     */
    public function getTaxableHousingAllowance(): float
    {
        $exempt_limit = 30000; // AOA tax-exempt limit for housing
        return max(0, $this->housing_allowance - $exempt_limit);
    }

    /**
     * Calculate night shift allowance amount
     */
    private function calculateNightShiftAmount(): float
    {
        // Calculate 20% additional allowance for night shift days
        if ($this->night_shift_days > 0 && $this->basic_salary > 0) {
            // Get working days from HR settings for consistent calculation
            $monthlyWorkingDays = (int) \App\Models\HR\HRSetting::get('monthly_working_days', 22);
            $workingDaysForCalculation = $monthlyWorkingDays > 0 ? $monthlyWorkingDays : 30;
            
            $daily_salary = $this->basic_salary / $workingDaysForCalculation;
            $night_shift_allowance = ($daily_salary * $this->night_shift_days) * 0.20; // 20% additional
            $this->night_shift_allowance = $night_shift_allowance;
            $this->night_shift_bonus = $night_shift_allowance; // Sync both properties
            return $night_shift_allowance;
        }
        
        $this->night_shift_allowance = 0.0;
        $this->night_shift_bonus = 0.0;
        return 0.0;
    }
    
    /**
     * Calculate INSS base (after deducting INSS from gross salary)
     */
    public function getMateriaColevavelProperty(): float
    {
        // MC = Gross Salary - INSS
        return max(0, $this->gross_salary - $this->social_security);
    }
    
    /**
     * Calculate Main Salary (base calculation before taxes)
     */
    public function getMainSalaryProperty(): float
    {
        $basic = (float)($this->basic_salary ?? 0);
        $transportCash = (float)($this->transport_allowance ?? 0);
        
        // Food benefit handling
        $isFoodInKind = (bool)($this->is_food_in_kind ?? false);
        $foodBenefit = (float)($this->selectedEmployee->food_benefit ?? 0);
        $foodCash = $isFoodInKind ? 0.0 : $foodBenefit;
        
        $overtime = (float)($this->total_overtime_amount ?? 0);
        $nightShift = (float)($this->night_shift_allowance ?? 0);
        $otherAllow = (float)($this->other_allowances ?? 0);
        
        // Absence deduction
        $absence = (float)max(($this->absence_deduction ?? 0), ($this->absenceDeductionAmount ?? 0));
        
        return max(0.0, $basic + $transportCash + $foodCash + $overtime + $nightShift + $otherAllow - $absence);
    }
    
    /**
     * Calculate Gross For Tax (includes additional benefits)
     */
    public function getGrossForTaxProperty(): float
    {
        $mainSalary = $this->getMainSalaryProperty();
        
        // Additional benefits
        $vacationAllow = !empty($this->vacation_subsidy) ? 0.5 * ($this->basic_salary ?? 0) : 0.0;
        $christmasOffer = !empty($this->christmas_subsidy) ? 0.5 * ($this->basic_salary ?? 0) : 0.0;
        $bonusTotal = (float)($this->bonus_amount ?? 0) + (float)($this->additional_bonus_amount ?? 0);
        
        return $mainSalary + $vacationAllow + $christmasOffer + $bonusTotal;
    }
    
    /**
     * Calculate INSS (3% on main salary components)
     */
    public function getCalculatedInssProperty(): float
    {
        $basic = (float)($this->basic_salary ?? 0);
        $transport = (float)($this->transport_allowance ?? 0);
        $meal = (float)($this->meal_allowance ?? 0);
        $overtime = (float)($this->total_overtime_amount ?? 0);
        
        return round(($basic + $transport + $meal + $overtime) * 0.03, 2);
    }
    
    /**
     * Calculate IRT base with exemptions
     */
    public function getIrtBaseProperty(): float
    {
        $grossForTax = $this->getGrossForTaxProperty();
        $inssDeduction = $this->getCalculatedInssProperty();
        
        // Transport and food exemptions (30k each)
        $transportCash = (float)($this->transport_allowance ?? 0);
        $foodBenefit = (float)($this->selectedEmployee->food_benefit ?? 0);
        $isFoodInKind = (bool)($this->is_food_in_kind ?? false);
        $foodCash = $isFoodInKind ? 0.0 : $foodBenefit;
        
        $exemptTransport = min(30000.0, $transportCash);
        $exemptFood = min(30000.0, $foodCash);
        
        return max(0.0, $grossForTax - $inssDeduction - $exemptTransport - $exemptFood);
    }
    
    /**
     * Calculate IRT amount
     */
    public function getCalculatedIrtProperty(): float
    {
        $irtBase = $this->getIrtBaseProperty();
        return \App\Models\HR\IRTTaxBracket::calculateIRT($irtBase);
    }
    
    /**
     * Calculate total deductions
     */
    public function getTotalDeductionsCalculatedProperty(): float
    {
        $inss = $this->getCalculatedInssProperty();
        $irt = $this->getCalculatedIrtProperty();
        $advance = (float)($this->advance_deduction ?? 0);
        $otherDiscounts = (float)($this->total_salary_discounts ?? 0);
        $union = (float)($this->union_deduction ?? 0);
        $uFund = (float)($this->u_fund_ded ?? 0);
        $loans = (float)($this->loan_installments ?? 0);
        
        // Food handling
        $isFoodInKind = (bool)($this->is_food_in_kind ?? false);
        $foodBenefit = (float)($this->selectedEmployee->food_benefit ?? 0);
        $foodInKind = $isFoodInKind ? $foodBenefit : 0.0;
        $foodCash = $isFoodInKind ? 0.0 : $foodBenefit;
        
        return $inss + $irt + $advance + $otherDiscounts + $union + $uFund + $loans + $foodInKind + $foodCash;
    }
    
    /**
     * Calculate final net salary
     */
    public function getCalculatedNetSalaryProperty(): float
    {
        $grossForTax = $this->getGrossForTaxProperty();
        $deductions = $this->getTotalDeductionsCalculatedProperty();
        
        return round(max(0.0, $grossForTax - $deductions), 2);
    }

    /**
     * Get detailed IRT calculation breakdown
     */
    public function getIrtCalculationDetailsProperty(): array
    {
        $mc = $this->getIrtBaseProperty();
        
        // Get the appropriate bracket for this MC value
        $bracket = IRTTaxBracket::getBracketForIncome($mc);
        
        if (!$bracket || $mc <= 0) {
            return [
                'mc' => $mc,
                'bracket' => null,
                'excess' => 0,
                'fixed_amount' => 0,
                'tax_on_excess' => 0,
                'total_irt' => 0
            ];
        }
        
        $excess = max(0, $mc - $bracket->min);
        $taxOnExcess = $excess * ($bracket->tax_rate / 100);
        
        return [
            'mc' => $mc,
            'bracket' => $bracket,
            'excess' => $excess,
            'fixed_amount' => (float) $bracket->fixed_amount,
            'tax_on_excess' => $taxOnExcess,
            'total_irt' => (float) $bracket->fixed_amount + $taxOnExcess
        ];
    }

    /**
     * Open period selection modal first (required before employee search)
     */
    public function openEmployeeSearch(): void
    {
        // First check if period is already selected
        if ($this->selectedPayrollPeriod) {
            // Period already selected, proceed to employee search
            $this->proceedToEmployeeSearch();
        } else {
            // Show period selection first
            $this->showPeriodSelection = true;
        }
    }

    /**
     * Show period selection modal
     */
    public function openPeriodSelection(): void
    {
        $this->showPeriodSelection = true;
        $this->selectedPayrollPeriod = null;
        $this->payroll_period_id = null;
    }

    /**
     * Select payroll period and proceed to employee search
     */
    public function selectPayrollPeriod(int $periodId): void
    {
        $this->selectedPayrollPeriod = PayrollPeriod::find($periodId);
        $this->payroll_period_id = $periodId;
        
        if ($this->selectedPayrollPeriod) {
            // Close period selection and proceed to employee search
            $this->showPeriodSelection = false;
            $this->proceedToEmployeeSearch();
            
            // Show success message
            session()->flash('success', __('messages.payroll_period_selected', ['period' => $this->selectedPayrollPeriod->name]));
        }
    }

    /**
     * Proceed to employee search after period is selected
     */
    private function proceedToEmployeeSearch(): void
    {
        // Ensure properties are initialized before opening modal
        $this->initializeProperties();
        
        $this->showEmployeeSearch = true;
        $this->resetEmployeeSearchFilters();
        $this->loadAllEmployees();
    }

    /**
     * Close period selection modal
     */
    public function closePeriodSelection(): void
    {
        $this->showPeriodSelection = false;
    }

    /**
     * Close employee search modal
     */
    public function closeEmployeeSearch(): void
    {
        $this->showEmployeeSearch = false;
        $this->employeeSearch = '';
        $this->searchResults = [];
    }

    /**
     * Select employee and open process modal
     */
    public function selectEmployee(int $employeeId): void
    {
        // Ensure period is selected before proceeding
        if (!$this->selectedPayrollPeriod) {
            session()->flash('error', __('messages.please_select_payroll_period_first'));
            return;
        }

        $this->selectedEmployee = Employee::with(['department', 'position'])->find($employeeId);
        $this->employee_id = $employeeId;
        
        if ($this->selectedEmployee) {
            // Initialize basic salary from employee
            $this->basic_salary = (float) ($this->selectedEmployee->base_salary ?? 0);
            
            // Use selected period dates for calculations
            $this->selected_month = (string) $this->selectedPayrollPeriod->start_date->month;
            $this->selected_year = (string) $this->selectedPayrollPeriod->start_date->year;
            
            // Load HR settings
            $this->loadHRSettings();
            
            // Calculate hourly rate
            $this->hourly_rate = $this->calculateHourlyRate();
            
            // Load employee payroll data based on selected period
            $this->loadEmployeePayrollDataForPeriod();
            $this->calculatePayrollComponents();
            
            // Close search modal and open process modal
            $this->showEmployeeSearch = false;
            $this->showProcessModal = true;
        }
    }

    /**
     * Open payroll processing modal
     */
    public function openProcessModal(): void
    {
        $this->showProcessModal = true;
        $this->showEmployeeSearch = false;
    }

    /**
     * Go back to employee selection from payroll processing
     */
    public function goBackToEmployeeSelection(): void
    {
        // Ensure properties are initialized
        $this->initializeProperties();
        
        $this->showProcessModal = false;
        $this->showEmployeeSearch = true;
        // Keep employee data for quick re-selection if needed
        // Don't reset payroll data in case user wants to come back
    }

    /**
     * Reset payroll data
     */
    private function resetPayrollData(): void
    {
        $this->selectedEmployee = null;
        $this->employee_id = null;
        $this->basic_salary = 0.0;
        $this->gross_salary = 0.0;
        $this->total_deductions = 0.0;
        $this->net_salary = 0.0;
    }

    // Updated properties for search functionality

    public function updatedSelectedMonth(): void
    {
        // Campos selectedMonth/selectedYear são da modal de busca - não afetam filtro de período
        if ($this->selectedEmployee) {
            $this->loadEmployeePayrollData();
            $this->calculatePayrollComponents();
        }
    }

    public function updatedSelectedYear(): void
    {
        // Campos selectedMonth/selectedYear são da modal de busca - não afetam filtro de período  
        if ($this->selectedEmployee) {
            $this->loadEmployeePayrollData();
            $this->calculatePayrollComponents();
        }
    }

    // Auto-recalculate when bonus values change
    public function updatedCustomBonus(): void
    {
        $this->calculatePayrollComponents();
    }

    public function updatedTransportAllowance(): void
    {
        $this->calculatePayrollComponents();
    }

    public function updatedMealAllowance(): void
    {
        $this->calculatePayrollComponents();
    }

    public function updatedHousingAllowance(): void
    {
        $this->calculatePayrollComponents();
    }

    public function updatedPerformanceBonus(): void
    {
        $this->calculatePayrollComponents();
    }

    public function updatedOtherDeductions(): void
    {
        $this->calculatePayrollComponents();
    }

    public function updatedIncludeVacationSubsidy(): void
    {
        $this->calculatePayrollComponents();
    }

    public function updatedIncludeChristmasSubsidy(): void
    {
        $this->calculatePayrollComponents();
    }

    public function updatedChristmasSubsidy(): void
    {
        $this->calculatePayrollComponents();
    }

    public function updatedVacationSubsidy(): void
    {
        $this->calculatePayrollComponents();
    }

    public function updatedBonusAmount(): void
    {
        $this->calculatePayrollComponents();
    }

    // Validation Rules
    protected function rules(): array
    {
        return [
            'employee_id' => 'required|exists:employees,id',
            'payroll_period_id' => 'nullable|exists:payroll_periods,id',
            'selected_month' => 'required|string',
            'selected_year' => 'required|string',
            'basic_salary' => 'required|numeric|min:0',
            'hourly_rate' => 'nullable|numeric|min:0',
            'transport_allowance' => 'nullable|numeric|min:0',
            'meal_allowance' => 'nullable|numeric|min:0',
            'housing_allowance' => 'nullable|numeric|min:0',
            'performance_bonus' => 'nullable|numeric|min:0',
            'custom_bonus' => 'nullable|numeric|min:0',
            'custom_bonus_description' => 'nullable|string|max:255',
            'vacation_subsidy' => 'nullable|numeric|min:0',
            'christmas_subsidy' => 'nullable|numeric|min:0',
            'income_tax' => 'nullable|numeric|min:0',
            'social_security' => 'nullable|numeric|min:0',
            'other_deductions' => 'nullable|numeric|min:0',
            'other_deductions_description' => 'nullable|string|max:255',
            'payment_method' => 'required|in:bank_transfer,cash,check',
            'bank_account' => 'nullable|required_if:payment_method,bank_transfer|string|max:50',
            'payment_date' => 'nullable|date',
            'status' => 'required|in:draft,approved,paid,cancelled',
            'remarks' => 'nullable|string|max:500',
        ];
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilters()
    {
        $this->resetPage();
    }

    public function updatedEmployeeId()
    {
        if ($this->employee_id) {
            $employee = Employee::find($this->employee_id);
            $this->employee = $employee; // Guardar objeto completo do funcionário
            
            if ($employee) {
                // Verificar se este funcionário já tem uma folha de pagamento com salário customizado
                $lastPayroll = PayrollModel::where('employee_id', $this->employee_id)
                    ->orderBy('created_at', 'desc')
                    ->first();
                
                if ($lastPayroll) {
                    // Usar o último salário registrado para continuidade
                    $this->basic_salary = $lastPayroll->basic_salary;
                    
                    // Recuperar valores de presença e licença se disponíveis
                    $this->attendance_hours = $lastPayroll->attendance_hours ?? 0;
                    $this->leave_days = $lastPayroll->leave_days ?? 0;
                    $this->maternity_days = $lastPayroll->maternity_days ?? 0;
                    $this->special_leave_days = $lastPayroll->special_leave_days ?? 0;
                } else {
                    // Se existe posição, obter o salário padrão da posição
                    if ($employee->position) {
                        // Usar o ponto médio da faixa salarial como padrão em vez do mínimo
                        $minSalary = $employee->position->salary_range_min;
                        $maxSalary = $employee->position->salary_range_max;
                        
                        // Se o salário máximo está definido, usar o ponto médio, caso contrário usar mínimo
                        if ($maxSalary > $minSalary) {
                            $this->basic_salary = ($minSalary + $maxSalary) / 2;
                        } else {
                            $this->basic_salary = $minSalary;
                        }
                    } else {
                        // Valor padrão zero se não há posição
                        $this->basic_salary = 0;
                    }
                    
                    // Calcular taxa horária base com base no salário mensal (considera 22 dias e 8 horas por dia)
                    if ($this->basic_salary > 0) {
                        $this->base_hourly_rate = round($this->basic_salary / (8 * 22), 2);
                    }
                }

                // Definir conta bancária se disponível
                if ($employee->bank_account) {
                    $this->bank_account = $employee->bank_account;
                }

                // Calcular os valores com base nos novos dados
                $this->calculateAttendanceAndLeavePay();
                $this->calculatePayroll();
            }
        } else {
            // Redefinir valores se nenhum funcionário for selecionado
            $this->reset([
                'basic_salary',
                'bank_account',
                'tax',
                'social_security',
                'net_salary',
                'attendance_hours',
                'base_hourly_rate',
                'total_hours_pay',
                'leave_days',
                'leave_deduction',
                'maternity_days',
                'special_leave_days',
                'employee'
            ]);
        }
    }

    public function updatedBasicSalary()
    {
        $this->calculatePayroll();
    }

    public function updatedAllowances()
    {
        $this->calculatePayroll();
    }

    public function updatedOvertime()
    {
        $this->calculatePayroll();
    }

    public function updatedBonuses()
    {
        $this->calculatePayroll();
    }

    public function updatedDeductions()
    {
        $this->calculatePayroll();
    }

    /**
     * Calcula pagamentos baseados em horas de presença e dias de licença
     */
    public function calculateAttendanceAndLeavePay()
    {
        // Calcular o pagamento com base nas horas trabalhadas
        if ($this->attendance_hours > 0 && $this->base_hourly_rate > 0) {
            $this->total_hours_pay = round($this->attendance_hours * $this->base_hourly_rate, 2);
        } else {
            $this->total_hours_pay = 0;
        }
        
        // Calcular deduções baseadas em dias de licença
        // Considerar 22 dias úteis por mês como base para cálculo
        $workingDaysInMonth = 22;
        
        if ($this->leave_days > 0 && $this->basic_salary > 0) {
            // Cálculo da dedução por dias de licença (excluindo maternidade e licença especial)
            // Note que licença maternidade não gera dedução, conforme a legislação angolana
            $dailyRate = $this->basic_salary / $workingDaysInMonth;
            $this->leave_deduction = round($dailyRate * $this->leave_days, 2);
        } else {
            $this->leave_deduction = 0;
        }
    }
    
    /**
     * Método para atualizar cálculos quando há alteração nas horas de presença
     */
    public function updatedAttendanceHours()
    {
        $this->calculateAttendanceAndLeavePay();
        $this->calculatePayroll();
    }
    
    /**
     * Método para atualizar cálculos quando há alteração na taxa horária
     */
    public function updatedBaseHourlyRate()
    {
        $this->calculateAttendanceAndLeavePay();
        $this->calculatePayroll();
    }
    
    /**
     * Método para atualizar cálculos quando há alteração nos dias de licença
     */
    public function updatedLeaveDays()
    {
        $this->calculateAttendanceAndLeavePay();
        $this->calculatePayroll();
    }
    
    /**
     * Método para atualizar cálculos quando há alteração nos dias de maternidade
     */
    public function updatedMaternityDays()
    {
        // Apenas mulheres podem ter dias de maternidade
        if ($this->employee && $this->employee->gender !== 'female') {
            $this->maternity_days = 0;
            session()->flash('error', 'Licença maternidade só pode ser atribuída a funcionárias mulheres.');
        }
        
        $this->calculateAttendanceAndLeavePay();
        $this->calculatePayroll();
    }
    
    /**
     * Método para atualizar cálculos quando há alteração nos dias de licença especial
     */
    public function updatedSpecialLeaveDays()
    {
        $this->calculateAttendanceAndLeavePay();
        $this->calculatePayroll();
    }
    
    public function calculatePayroll()
    {
        // Calcular o salário base proporcional aos dias trabalhados
        $baseEarnings = 0;
        
        if ($this->attendance_hours > 0) {
            // Se há horas registradas, usar pagamento por horas
            $baseEarnings = $this->total_hours_pay;
        } elseif ($this->present_days > 0 && $this->total_working_days > 0) {
            // Se há dias presentes, calcular salário proporcional aos dias trabalhados
            $baseEarnings = ($this->basic_salary / $this->total_working_days) * $this->present_days;
        } else {
            // Se não trabalhou nenhum dia, não recebe salário base
            $baseEarnings = 0;
        }
        
        // Subtrair deduções por licenças não-especiais
        $baseEarnings = $baseEarnings - $this->leave_deduction;
        
        // Garantir que o salário base não seja negativo
        if ($baseEarnings < 0) {
            $baseEarnings = 0;
        }
        
        // Calcular o total de ganhos incluindo adicionais
        $totalEarnings = $baseEarnings + $this->allowances + $this->overtime + $this->bonuses;
        
        // Calcular imposto (15% flat rate, ajuste conforme necessário)
        $this->tax = $totalEarnings * 0.15;
        
        // Calcular seguro social (4% flat rate, ajuste conforme necessário)
        $this->social_security = $totalEarnings * 0.04;
        
        // Calcular salário líquido
        $totalDeductions = $this->tax + $this->social_security + $this->deductions;
        $this->net_salary = $totalEarnings - $totalDeductions;
        
        // Garantir que o salário líquido não seja negativo
        if ($this->net_salary < 0) {
            $this->net_salary = 0;
        }
    }

    private function calculateIncomeTax()
    {
        // Calculate taxable income (after INSS deduction)
        $taxableBase = $this->basic_salary + $this->allowances;
        $inss = $taxableBase * 0.03;
        $taxableIncome = $taxableBase - $inss;
        
        // Updated IRT calculation based on current Angolan tax brackets
        if ($taxableIncome <= 100000) {
            return 0; // Exempt
        } elseif ($taxableIncome <= 110000) {
            return 870.87;
        } elseif ($taxableIncome <= 120000) {
            return 2131.87;
        } elseif ($taxableIncome <= 150000) {
            return 5914.87;
        } elseif ($taxableIncome <= 175000) {
            return 15659.84;
        } elseif ($taxableIncome <= 200000) {
            return 19539.84;
        } elseif ($taxableIncome <= 250000) {
            return 38899.82;
        } elseif ($taxableIncome <= 350000) {
            return 56754.81;
        } else {
            return ($taxableIncome - 350000) * 0.19 + 56754.81;
        }
    }

    public function create()
    {
        $this->reset([
            'payroll_id', 'employee_id', 'payroll_period_id', 'basic_salary',
            'allowances', 'overtime', 'bonuses', 'deductions', 'tax',
            'social_security', 'net_salary', 'payment_method', 'bank_account',
            'payment_date', 'status', 'remarks', 'payrollItems',
            // Resetar campos de presença e licença
            'attendance_hours', 'base_hourly_rate', 'total_hours_pay',
            'leave_days', 'leave_deduction', 'maternity_days', 'special_leave_days',
            'employee'
        ]);
        
        // Inicialização dos campos financeiros
        $this->allowances = 0;
        $this->overtime = 0;
        $this->bonuses = 0;
        $this->deductions = 0;
        $this->tax = 0;
        $this->social_security = 0;
        $this->net_salary = 0;
        $this->payment_method = 'bank_transfer';
        $this->status = 'draft';
        
        // Inicialização dos campos de presença e licença
        $this->attendance_hours = 0;
        $this->base_hourly_rate = 0;
        $this->total_hours_pay = 0;
        $this->leave_days = 0;
        $this->leave_deduction = 0;
        $this->maternity_days = 0;
        $this->special_leave_days = 0;
        
        $this->isEditing = false;
        $this->showModal = true;
    }

    public function edit(PayrollModel $payroll)
    {
        // Carregar dados básicos da folha de pagamento
        $this->payroll_id = $payroll->id;
        $this->employee_id = $payroll->employee_id;
        $this->payroll_period_id = $payroll->payroll_period_id;
        $this->basic_salary = (float) $payroll->basic_salary;
        $this->allowances = (float) $payroll->allowances;
        $this->overtime = (float) $payroll->overtime;
        $this->bonuses = (float) $payroll->bonuses;
        $this->deductions = (float) $payroll->deductions;
        $this->tax = (float) $payroll->tax;
        $this->social_security = (float) $payroll->social_security;
        $this->net_salary = (float) $payroll->net_salary;
        $this->payment_method = $payroll->payment_method;
        $this->bank_account = $payroll->bank_account;
        $this->payment_date = $payroll->payment_date ? $payroll->payment_date->format('Y-m-d') : null;
        $this->status = $payroll->status;
        $this->remarks = $payroll->remarks;
        
        // Carregar campos de presença e licença
        $this->attendance_hours = (float) ($payroll->attendance_hours ?? 0);
        $this->base_hourly_rate = (float) ($payroll->base_hourly_rate ?? 0);
        $this->total_hours_pay = (float) ($payroll->total_hours_pay ?? 0);
        $this->leave_days = (int) ($payroll->leave_days ?? 0);
        $this->leave_deduction = (float) ($payroll->leave_deduction ?? 0);
        $this->maternity_days = (int) ($payroll->maternity_days ?? 0);
        $this->special_leave_days = (int) ($payroll->special_leave_days ?? 0);
        
        // Carregar dados completos do funcionário com relacionamentos
        $this->employee = Employee::with(['department', 'position'])->find($this->employee_id);
        $this->selectedEmployee = $this->employee;

        // Carregar itens da folha
        $this->payrollItems = $payroll->payrollItems->toArray();
        
        // Carregar subsídios dos itens da folha
        foreach ($payroll->payrollItems as $item) {
            if ($item->type === 'earning') {
                if (str_contains(strtolower($item->description), 'christmas') || str_contains(strtolower($item->description), 'natal')) {
                    $this->christmas_subsidy = true;
                }
                if (str_contains(strtolower($item->description), 'vacation') || str_contains(strtolower($item->description), 'férias')) {
                    $this->vacation_subsidy = true;
                }
            }
        }

        $this->isEditing = true;
        
        // Definir período selecionado
        if ($payroll->payrollPeriod) {
            $this->selected_month = $payroll->payrollPeriod->start_date->format('m');
            $this->selected_year = $payroll->payrollPeriod->start_date->format('Y');
        }
        
        // Carregar configurações de RH
        $this->loadHRSettings();
        
        // Calcular taxa horária
        $this->hourly_rate = $this->calculateHourlyRate();
        
        // Carregar todos os dados do funcionário para o período (igual ao create)
        $this->loadEmployeePayrollData();
        
        // Recalcular componentes do payroll com dados atualizados
        $this->calculatePayrollComponents();
        
        // Abrir modal de processamento diretamente
        $this->showProcessModal = true;
    }
    
    public function closeProcessModal()
    {
        $this->showProcessModal = false;
        $this->showEmployeeSearch = false;
        $this->selectedEmployee = null;
        $this->isEditing = false;
        $this->reset([
            'payroll_id', 'employee_id', 'payroll_period_id', 'basic_salary',
            'transport_allowance', 'meal_allowance', 'housing_allowance',
            'total_overtime_amount', 'performance_bonus', 'custom_bonus', 'bonus_amount', 'additional_bonus_amount',
            'income_tax', 'social_security', 'total_deductions', 'net_salary', 'gross_salary',
            'payment_method', 'bank_account', 'payment_date', 'status', 'remarks', 'payrollItems',
            'total_attendance_hours', 'regular_hours_pay', 'hourly_rate',
            'total_leave_days', 'leave_deduction', 'unpaid_leave_days',
            'total_salary_advances', 'advance_deduction', 'total_salary_discounts',
            'late_deduction', 'absence_deduction', 'other_deductions',
            'christmas_subsidy', 'vacation_subsidy', 'christmas_subsidy_amount', 'vacation_subsidy_amount'
        ]);
    }

    public function showApprove(PayrollModel $payroll)
    {
        $this->payroll_id = $payroll->id;
        $this->showApproveModal = true;
    }

    public function showPay(PayrollModel $payroll)
    {
        $this->payroll_id = $payroll->id;
        $this->payment_date = now()->format('Y-m-d');
        $this->showPayModal = true;
    }

    public function showGenerate()
    {
        $this->reset(['payroll_period_id']);
        $this->showGenerateModal = true;
    }

    public function showDeleteConfirmation(PayrollModel $payroll)
    {
        $this->payroll_id = $payroll->id;
        $this->showDeleteModal = true;
    }

    public function approve($payrollId = null)
    {
        // Verificar permissão para aprovar pagamentos
        if (!auth()->user()->can('hr.payroll.approve')) {
            session()->flash('error', 'Não tem permissão para aprovar pagamentos.');
            return;
        }
        
        // Se foi passado ID como parâmetro, usar ele
        if ($payrollId) {
            $this->payroll_id = $payrollId;
        }
        
        \Log::info('=== INÍCIO approve() ===', [
            'payroll_id' => $this->payroll_id,
            'payroll_id_parameter' => $payrollId,
            'user_id' => auth()->id()
        ]);
        
        $payroll = PayrollModel::find($this->payroll_id);
        
        if (!$payroll) {
            \Log::error('Payroll not found', ['payroll_id' => $this->payroll_id]);
            session()->flash('error', 'Payroll not found.');
            $this->showApproveModal = false;
            return;
        }
        
        \Log::info('Payroll encontrada', [
            'id' => $payroll->id,
            'status_atual' => $payroll->status,
            'employee_id' => $payroll->employee_id
        ]);
        
        DB::beginTransaction();
        
        try {
            // Aprovar folha de pagamento
            $payroll->status = 'approved';
            $payroll->approved_by = auth()->id();
            $payroll->save();
            
            // Processar automaticamente salary advances e discounts
            \Log::info('Chamando processInstallmentPayments', ['payroll_id' => $payroll->id]);
            $this->processInstallmentPayments($payroll);
            \Log::info('processInstallmentPayments executado');
            
            DB::commit();
            
            $this->showApproveModal = false;
            \Log::info('Payroll aprovada com sucesso', [
                'payroll_id' => $payroll->id,
                'novo_status' => $payroll->status
            ]);
            
            session()->flash('success', 'Payroll approved successfully.');
            $this->showApproveModal = false;
            
            \Log::info('=== FIM approve() - SUCESSO ===');
            
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('ERRO ao aprovar payroll', [
                'payroll_id' => $this->payroll_id,
                'erro' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            session()->flash('error', 'Error approving payroll: ' . $e->getMessage());
        }
    }

    /**
     * Processa automaticamente prestações de salary advances e discounts
     */
    private function processInstallmentPayments(PayrollModel $payroll): void
    {
        \Log::info('=== INÍCIO processInstallmentPayments() ===', [
            'payroll_id' => $payroll->id,
            'employee_id' => $payroll->employee_id,
            'payroll_period_id' => $payroll->payroll_period_id
        ]);
        
        $employee = $payroll->employee;
        $payrollPeriod = $payroll->payrollPeriod;
        $periodStart = $payrollPeriod?->start_date ?? now()->startOfMonth();
        $periodEnd = $payrollPeriod?->end_date ?? now()->endOfMonth();
        $paymentDate = now(); // Usar data atual para pagamento
        $currentDate = now()->format('Y-m-d'); // Data atual para filtro
        
        \Log::info('Datas para processamento', [
            'period_start' => $periodStart->format('Y-m-d'),
            'period_end' => $periodEnd->format('Y-m-d'),
            'current_date' => $currentDate,
            'payment_date' => $paymentDate->format('Y-m-d')
        ]);
        
        // Processar Salary Advances - usar data atual em vez do período da folha
        $advancesToProcess = SalaryAdvance::where('employee_id', $employee->id)
            ->where('status', 'approved')
            ->where('remaining_installments', '>', 0)
            ->where('first_deduction_date', '<=', $currentDate)
            ->get();
        
        \Log::info('Salary Advances encontrados', [
            'employee_id' => $employee->id,
            'total_advances' => $advancesToProcess->count(),
            'periodo_start' => $periodStart->format('Y-m-d'),
            'periodo_end' => $periodEnd->format('Y-m-d')
        ]);
        
        foreach ($advancesToProcess as $advance) {
            // Verificar se já foi processado neste período
            $existingPayment = $advance->payments()
                ->whereBetween('payment_date', [$periodStart, $periodEnd])
                ->first();
            
            if (!$existingPayment && $advance->remaining_installments > 0) {
                // Registrar pagamento da prestação
                $advance->registerPayment(
                    (float) $advance->installment_amount,
                    $paymentDate->format('Y-m-d'),
                    ($advance->installments - $advance->remaining_installments + 1), // Número da prestação
                    auth()->id() ?? 1,  // Fallback para usuário ID 1 se não autenticado
                    "Pagamento automático via aprovação da folha de pagamento ID: {$payroll->id}"
                );
                
                \Log::info('Advance payment processado', [
                    'advance_id' => $advance->id,
                    'installment_amount' => $advance->installment_amount,
                    'remaining_before' => $advance->remaining_installments
                ]);
            } else {
                \Log::info('Advance não processado', [
                    'advance_id' => $advance->id,
                    'existing_payment' => $existingPayment ? 'exists' : 'not_exists',
                    'remaining_installments' => $advance->remaining_installments
                ]);
            }
        }
        
        // Processar Salary Discounts - usar data atual em vez do período da folha
        $discountsToProcess = SalaryDiscount::where('employee_id', $employee->id)
            ->where('status', 'approved')
            ->where('remaining_installments', '>', 0)
            ->where('first_deduction_date', '<=', $currentDate)
            ->get();
        
        \Log::info('Salary Discounts encontrados', [
            'employee_id' => $employee->id,
            'total_discounts' => $discountsToProcess->count(),
            'periodo_start' => $periodStart->format('Y-m-d'),
            'periodo_end' => $periodEnd->format('Y-m-d')
        ]);
        
        foreach ($discountsToProcess as $discount) {
            // Verificar se já foi processado neste período
            $existingPayment = $discount->payments()
                ->whereBetween('payment_date', [$periodStart, $periodEnd])
                ->first();
            
            if (!$existingPayment && $discount->remaining_installments > 0) {
                // Registrar pagamento da prestação  
                $discount->registerPayment(
                    (float) $discount->installment_amount,
                    $paymentDate->format('Y-m-d'),
                    ($discount->installments - $discount->remaining_installments + 1), // Número da prestação
                    auth()->id() ?? 1,  // Fallback para usuário ID 1 se não autenticado
                    "Pagamento automático via aprovação da folha de pagamento ID: {$payroll->id}"
                );
                
                \Log::info('Discount payment processado', [
                    'discount_id' => $discount->id,
                    'installment_amount' => $discount->installment_amount,
                    'remaining_before' => $discount->remaining_installments
                ]);
            } else {
                \Log::info('Discount não processado', [
                    'discount_id' => $discount->id,
                    'existing_payment' => $existingPayment ? 'exists' : 'not_exists',
                    'remaining_installments' => $discount->remaining_installments
                ]);
            }
        }
        
        \Log::info('=== FIM processInstallmentPayments() ===', [
            'payroll_id' => $payroll->id
        ]);
    }

    public function pay()
    {
        $this->validate([
            'payment_date' => 'required|date',
        ]);

        $payroll = PayrollModel::find($this->payroll_id);
        
        if (!$payroll) {
            session()->flash('error', 'Payroll not found.');
            $this->showPayModal = false;
            return;
        }
        
        $payroll->status = 'paid';
        $payroll->payment_date = $this->payment_date;
        $payroll->save();

        $this->showPayModal = false;
        session()->flash('message', 'Payroll marked as paid successfully.');
    }

    public function generatePayrolls()
    {
        $this->validate([
            'payroll_period_id' => 'required|exists:payroll_periods,id',
        ]);

        $period = PayrollPeriod::find($this->payroll_period_id);
        $employees = Employee::where('employment_status', 'active')->get();
        $count = 0;

        foreach ($employees as $employee) {
            // Skip if payroll already exists for this employee and period
            $exists = PayrollModel::where('employee_id', $employee->id)
                ->where('payroll_period_id', $this->payroll_period_id)
                ->exists();

            if (!$exists) {
                // Get basic salary from position or use latest payroll
                $lastPayroll = PayrollModel::where('employee_id', $employee->id)
                    ->orderBy('created_at', 'desc')
                    ->first();
                
                if ($lastPayroll) {
                    $basicSalary = $lastPayroll->basic_salary;
                } else {
                    if ($employee->position) {
                        $minSalary = $employee->position->salary_range_min;
                        $maxSalary = $employee->position->salary_range_max;
                        
                        if ($maxSalary > $minSalary) {
                            $basicSalary = ($minSalary + $maxSalary) / 2;
                        } else {
                            $basicSalary = $minSalary;
                        }
                    } else {
                        $basicSalary = 0;
                    }
                }
                
                // Calculate INSS (3% of basic salary)
                $inssBase = $basicSalary;
                $socialSecurity = $inssBase * 0.03;
                
                // Calculate IRT (After INSS deduction)
                $taxableIncome = $basicSalary - $socialSecurity;
                
                // Updated IRT calculation
                if ($taxableIncome <= 100000) {
                    $tax = 0; // Exempt
                } elseif ($taxableIncome <= 110000) {
                    $tax = 870.87;
                } elseif ($taxableIncome <= 120000) {
                    $tax = 2131.87;
                } elseif ($taxableIncome <= 150000) {
                    $tax = 5914.87;
                } elseif ($taxableIncome <= 175000) {
                    $tax = 15659.84;
                } elseif ($taxableIncome <= 200000) {
                    $tax = 19539.84;
                } elseif ($taxableIncome <= 250000) {
                    $tax = 38899.82;
                } elseif ($taxableIncome <= 350000) {
                    $tax = 56754.81;
                } else {
                    $tax = ($taxableIncome - 350000) * 0.19 + 56754.81;
                }
                
                // Calcular salário líquido
                $netSalary = $basicSalary - ($tax + $socialSecurity);

                // Criar folha de pagamento com valores calculados
                PayrollModel::create([
                    'employee_id' => $employee->id,
                    'payroll_period_id' => $this->payroll_period_id,
                    'basic_salary' => $basicSalary,
                    'allowances' => 0,
                    'overtime' => 0,
                    'bonuses' => 0,
                    'deductions' => 0,
                    'tax' => $tax,
                    'social_security' => $socialSecurity,
                    'net_salary' => $netSalary,
                    'payment_method' => 'bank_transfer',
                    'bank_account' => $employee->bank_account,
                    'status' => 'draft',
                    'generated_by' => auth()->id(),
                ]);

                $count++;
            }
        }

        $this->showGenerateModal = false;
        session()->flash('message', "Geradas {$count} folhas de pagamento com sucesso.");
    }

    /**
     * Save payroll data to database
     */
    public function save(): void
    {
        \Log::info('=== PAYROLL SAVE INICIADO ===', [
            'employee_id' => $this->employee_id,
            'selectedEmployee' => $this->selectedEmployee ? $this->selectedEmployee->full_name : null,
            'isEditing' => $this->isEditing,
            'payroll_id' => $this->payroll_id,
        ]);
        
        try {
            // Validate required data
            if (!$this->selectedEmployee || !$this->employee_id) {
                \Log::error('PAYROLL SAVE: Funcionário não selecionado', [
                    'selectedEmployee' => $this->selectedEmployee,
                    'employee_id' => $this->employee_id
                ]);
                session()->flash('error', 'Funcionário não selecionado.');
                return;
            }

            // Get or create payroll period for selected month/year
            $payrollPeriod = $this->getOrCreatePayrollPeriod();
            
            if (!$payrollPeriod) {
                session()->flash('error', 'Erro ao criar período de folha de pagamento.');
                return;
            }

            // Check if we're editing an existing payroll
            if ($this->isEditing && $this->payroll_id) {
                // Editing mode - update existing payroll
                $existingPayroll = PayrollModel::find($this->payroll_id);
                if (!$existingPayroll) {
                    session()->flash('error', 'Folha de pagamento não encontrada para edição.');
                    return;
                }
            } else {
                // Creating mode - check for duplicates
                $existingPayroll = PayrollModel::where('employee_id', $this->employee_id)
                    ->where('payroll_period_id', $payrollPeriod->id)
                    ->first();

                if ($existingPayroll) {
                    session()->flash('error', 'Já existe uma folha de pagamento para este funcionário neste período.');
                    return;
                }
            }

            // Ensure all calculations are up to date
            $this->calculatePayrollComponents();
            
            // Sanitize all numeric fields to prevent null values
            $this->sanitizeNumericFields();

            // Prepare comprehensive payroll data
            $payrollData = [
                'employee_id' => $this->employee_id,
                'payroll_period_id' => $payrollPeriod->id,
                
                // Basic salary components
                'basic_salary' => $this->basic_salary,
                
                // Allowances and benefits
                'allowances' => $this->transport_allowance + $this->meal_allowance + $this->housing_allowance,
                
                // Overtime and bonuses
                'overtime' => $this->total_overtime_amount,
                'bonuses' => $this->performance_bonus + $this->custom_bonus + $this->bonus_amount + 
                           $this->additional_bonus_amount +
                           ($this->christmas_subsidy ? ($this->basic_salary * 0.5) : 0) +
                           ($this->vacation_subsidy ? ($this->basic_salary * 0.5) : 0),
                
                // New columns - Bónus e componentes
                'profile_bonus' => $this->profile_bonus,
                'overtime_amount' => $this->total_overtime_amount,
                'gross_salary' => $this->gross_salary,
                
                // New columns - Base tributável e deduções
                'base_irt_taxable_amount' => $this->base_irt_taxable_amount,
                'deductions_irt' => $this->deductions_irt,
                'inss_3_percent' => $this->inss_3_percent,
                'inss_8_percent' => $this->inss_8_percent,
                
                // New columns - Deduções por faltas
                'absence_deduction_amount' => $this->absence_deduction_amount,
                
                // New columns - Totais calculados
                'main_salary' => $this->main_salary,
                'total_deductions_calculated' => $this->total_deductions_calculated,
                
                // Tax deductions
                'tax' => $this->income_tax, // IRT
                'social_security' => $this->social_security, // INSS
                
                // Other deductions
                'deductions' => $this->total_deductions,
                
                // Final amounts
                'net_salary' => $this->net_salary,
                
                // Attendance data
                'attendance_hours' => $this->total_attendance_hours,
                
                // Leave data
                'leave_days' => $this->total_leave_days,
                'maternity_days' => $this->maternity_leave_days ?? 0,
                'special_leave_days' => $this->special_leave_days ?? 0,
                
                // Payment information
                'payment_method' => 'bank_transfer',
                'bank_account' => $this->selectedEmployee->bank_account,
                'payment_date' => null, // Will be set when approved
                
                // Status and metadata
                'status' => PayrollModel::STATUS_DRAFT,
                'remarks' => $this->generatePayrollRemarks(),
                'generated_by' => null, // Campo aponta para employees, não users
            ];

            \Log::info('PAYROLL SAVE: Dados preparados', [
                'payroll_period_id' => $payrollPeriod->id,
                'employee_id' => $this->employee_id,
                'basic_salary' => $this->basic_salary,
                'gross_salary' => $this->gross_salary,
                'net_salary' => $this->net_salary,
                'isEditing' => $this->isEditing
            ]);

            // Create or update the payroll record
            if ($this->isEditing && $this->payroll_id) {
                \Log::info('PAYROLL SAVE: Atualizando payroll existente', ['payroll_id' => $this->payroll_id]);
                
                // Update existing payroll
                $payroll = PayrollModel::find($this->payroll_id);
                $payroll->update($payrollData);
                
                // Update payroll items
                $payroll->payrollItems()->delete(); // Remove old items
                $this->createPayrollItems($payroll); // Create new items
                
                $message = 'Folha de pagamento atualizada com sucesso para ' . $this->selectedEmployee->full_name . '.';
            } else {
                \Log::info('PAYROLL SAVE: Criando novo payroll', ['employee_name' => $this->selectedEmployee->full_name]);
                
                // Create new payroll
                $payroll = PayrollModel::create($payrollData);
                
                \Log::info('PAYROLL SAVE: Payroll criado', ['payroll_id' => $payroll->id]);
                
                // Create detailed payroll items for transparency
                $this->createPayrollItems($payroll);
                
                $message = 'Folha de pagamento criada com sucesso para ' . $this->selectedEmployee->full_name . '.';
            }

            \Log::info('PAYROLL SAVE: Sucesso!', [
                'payroll_id' => $payroll->id,
                'message' => $message
            ]);

            // Success message and close modal
            session()->flash('message', $message);
            
            // Close modal and reset
            $this->showProcessModal = false;
            $this->reset([
                'selectedEmployee',
                'employee_id',
                'basic_salary',
                'transport_allowance',
                'meal_allowance',
                'housing_allowance',
                'bonus_amount',
                'total_overtime_amount',
                'income_tax',
                'social_security',
                'total_deductions',
                'net_salary',
                'attendanceRecords',
            ]);
            
            // Refresh the payroll list
            $this->dispatch('refreshPayrolls');
            
        } catch (\Exception $e) {
            \Log::error('Error saving payroll: ' . $e->getMessage(), [
                'employee_id' => $this->employee_id,
                'selected_month' => $this->selected_month,
                'selected_year' => $this->selected_year,
                'trace' => $e->getTraceAsString()
            ]);
            
            session()->flash('error', 'Erro ao salvar folha de pagamento: ' . $e->getMessage());
        }
    }
    
    /**
     * Sanitize all numeric fields to prevent null values
     */
    private function sanitizeNumericFields(): void
    {
        $numericFields = [
            'basic_salary',
            'bonus_amount',
            'additional_bonus_amount',
            'profile_bonus',
            'custom_bonus',
            'performance_bonus',
            'transport_allowance',
            'meal_allowance',
            'housing_allowance',
            'total_overtime_amount',
            'income_tax',
            'social_security',
            'total_deductions',
            'net_salary',
            'gross_salary',
            'advance_deduction',
            'total_salary_discounts',
            'late_deduction',
            'absence_deduction',
            'other_deductions',
            'main_salary',
            'base_irt_taxable_amount',
            'deductions_irt',
            'inss_3_percent',
            'inss_8_percent',
            'absence_deduction_amount',
            'total_deductions_calculated',
        ];
        
        foreach ($numericFields as $field) {
            if (property_exists($this, $field)) {
                $this->$field = (float) ($this->$field ?? 0.0);
            }
        }
    }
    
    /**
     * Get or create payroll period for selected month/year
     */
    private function getOrCreatePayrollPeriod(): ?PayrollPeriod
    {
        $startDate = Carbon::create((int)$this->selected_year, (int)$this->selected_month, 1);
        $endDate = $startDate->copy()->endOfMonth();
        
        // Create period name in Portuguese
        $monthNames = [
            1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril',
            5 => 'Maio', 6 => 'Junho', 7 => 'Julho', 8 => 'Agosto',
            9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro'
        ];
        $periodName = $monthNames[(int)$this->selected_month] . ' ' . $this->selected_year;
        
        // Try to find existing period
        $period = PayrollPeriod::where('start_date', $startDate->format('Y-m-d'))
            ->where('end_date', $endDate->format('Y-m-d'))
            ->first();
            
        if (!$period) {
            // Create new period
            $period = PayrollPeriod::create([
                'name' => $periodName,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'payment_date' => $endDate->copy()->addDays(5), // Payment 5 days after month end
                'status' => PayrollPeriod::STATUS_OPEN,
                'remarks' => 'Auto-created for payroll processing'
            ]);
        }
        
        return $period;
    }
    
    /**
     * Generate comprehensive payroll remarks
     */
    private function generatePayrollRemarks(): string
    {
        $remarks = [];
        
        // Attendance summary
        $remarks[] = "Presenças: {$this->present_days}/{$this->total_working_days} dias";
        $remarks[] = "Horas trabalhadas: {$this->total_attendance_hours}h";
        
        if ($this->late_arrivals > 0) {
            $remarks[] = "Atrasos: {$this->late_arrivals} dias";
        }
        
        if ($this->absent_days > 0) {
            $remarks[] = "Faltas: {$this->absent_days} dias";
        }
        
        // Overtime summary
        if ($this->total_overtime_hours > 0) {
            $remarks[] = "Horas extras: {$this->total_overtime_hours}h";
        }
        
        // Leave summary
        if ($this->total_leave_days > 0) {
            $remarks[] = "Licenças: {$this->total_leave_days} dias";
        }
        
        // Deductions summary
        if ($this->late_deduction > 0) {
            $remarks[] = "Desconto atrasos: " . number_format($this->late_deduction, 2) . " AOA";
        }
        
        if ($this->absence_deduction > 0) {
            $remarks[] = "Desconto faltas: " . number_format($this->absence_deduction, 2) . " AOA";
        }
        
        return implode('; ', $remarks);
    }
    
    /**
     * Create detailed payroll items for transparency
     */
    private function createPayrollItems(PayrollModel $payroll): void
    {
        $items = [];
        
        // Basic salary
        $items[] = [
            'payroll_id' => $payroll->id,
            'type' => 'earning',
            'name' => 'Salário Base',
            'description' => 'Salário base mensal do funcionário',
            'amount' => $this->basic_salary,
            'is_taxable' => true,
        ];
        
        // Transport allowance (proportional to days worked)
        if ($this->transport_allowance > 0) {
            $fullTransportAllowance = (float) ($this->selectedEmployee->transport_benefit ?? 0);
            $description = "Subsídio de transporte ({$this->present_days}/{$this->total_working_days} dias)";
            if ($this->transport_allowance < $fullTransportAllowance) {
                $description .= " - Descontado por faltas";
            }
            
            $items[] = [
                'payroll_id' => $payroll->id,
                'type' => 'allowance',
                'name' => 'Subsídio de Transporte',
                'description' => $description,
                'amount' => $this->transport_allowance,
                'is_taxable' => true, // Parte tributável
            ];
        }
        
        // Meal allowance
        if ($this->meal_allowance > 0) {
            $items[] = [
                'payroll_id' => $payroll->id,
                'type' => 'allowance',
                'name' => 'Subsídio de Alimentação',
                'description' => 'Subsídio mensal de alimentação',
                'amount' => $this->meal_allowance,
                'is_taxable' => false, // Isento
            ];
        }
        
        // Housing allowance
        if ($this->housing_allowance > 0) {
            $items[] = [
                'payroll_id' => $payroll->id,
                'type' => 'allowance',
                'name' => 'Subsídio de Moradia',
                'description' => 'Subsídio mensal de moradia',
                'amount' => $this->housing_allowance,
                'is_taxable' => true, // Parte tributável
            ];
        }
        
        // Overtime
        if ($this->total_overtime_amount > 0) {
            $items[] = [
                'payroll_id' => $payroll->id,
                'type' => 'earning',
                'name' => 'Horas Extras',
                'description' => "Pagamento de {$this->total_overtime_hours} horas extras",
                'amount' => $this->total_overtime_amount,
                'is_taxable' => true,
            ];
        }
        
        // Bonuses
        if ($this->bonus_amount > 0) {
            $items[] = [
                'payroll_id' => $payroll->id,
                'type' => 'bonus',
                'name' => 'Bónus',
                'description' => 'Bónus de performance e outros',
                'amount' => $this->bonus_amount,
                'is_taxable' => true,
            ];
        }
        
        // Christmas Subsidy
        if ($this->christmas_subsidy && $this->basic_salary > 0) {
            $christmasAmount = $this->basic_salary * 0.5; // 50% do salário base
            $items[] = [
                'payroll_id' => $payroll->id,
                'type' => 'earning',
                'name' => 'Subsídio de Natal',
                'description' => 'Subsídio de Natal (13º salário)',
                'amount' => $christmasAmount,
                'is_taxable' => true,
            ];
        }
        
        // Vacation Subsidy
        if ($this->vacation_subsidy && $this->basic_salary > 0) {
            $vacationAmount = $this->basic_salary * 0.5; // 50% do salário base
            $items[] = [
                'payroll_id' => $payroll->id,
                'type' => 'earning',
                'name' => 'Subsídio de Férias',
                'description' => 'Subsídio de férias (14º salário)',
                'amount' => $vacationAmount,
                'is_taxable' => true,
            ];
        }
        
        // Late deduction
        if ($this->late_deduction > 0) {
            $items[] = [
                'payroll_id' => $payroll->id,
                'type' => 'deduction',
                'name' => 'Desconto por Atrasos',
                'description' => "Desconto por {$this->late_arrivals} dias de atraso",
                'amount' => -$this->late_deduction,
                'is_taxable' => false,
            ];
        }
        
        // Absence deduction
        if ($this->absence_deduction > 0) {
            $items[] = [
                'payroll_id' => $payroll->id,
                'type' => 'deduction',
                'name' => 'Desconto por Faltas',
                'description' => "Desconto por {$this->absent_days} dias de falta",
                'amount' => -$this->absence_deduction,
                'is_taxable' => false,
            ];
        }
        
        // INSS deduction
        if ($this->social_security > 0) {
            $items[] = [
                'payroll_id' => $payroll->id,
                'type' => 'tax',
                'name' => 'INSS',
                'description' => 'Contribuição para Segurança Social (3%)',
                'amount' => -$this->social_security,
                'is_taxable' => false,
            ];
        }
        
        // IRT deduction
        if ($this->income_tax > 0) {
            $items[] = [
                'payroll_id' => $payroll->id,
                'type' => 'tax',
                'name' => 'IRT',
                'description' => 'Imposto sobre Rendimento do Trabalho',
                'amount' => -$this->income_tax,
                'is_taxable' => false,
            ];
        }
        
        // Salary discounts
        if ($this->total_salary_discounts > 0) {
            $items[] = [
                'payroll_id' => $payroll->id,
                'type' => 'deduction',
                'name' => 'Descontos Salariais',
                'description' => 'Descontos diversos aplicados ao salário',
                'amount' => -$this->total_salary_discounts,
                'is_taxable' => false,
            ];
        }
        
        // Salary advances
        if ($this->total_salary_advances > 0) {
            $items[] = [
                'payroll_id' => $payroll->id,
                'type' => 'deduction',
                'name' => 'Adiantamentos Salariais',
                'description' => 'Desconto de adiantamentos salariais concedidos',
                'amount' => -$this->advance_deduction,
                'is_taxable' => false,
            ];
        }
        
        // Create all items
        foreach ($items as $item) {
            PayrollItem::create($item);
        }
    }
    

    
    public function deletePayroll()
    {
        $payroll = PayrollModel::find($this->payroll_id);
        $payroll->delete();
        $this->showDeleteModal = false;
        session()->flash('message', 'Folha de pagamento eliminada com sucesso.');
    }

    public function exportPayroll()
    {
        // TODO: Implementar lógica de export
        session()->flash('message', 'Export functionality coming soon!');
    }

    /**
     * Gera recibos de salário em lote para os funcionários filtrados
     */
    public function generateBulkReceipts(): void
    {
        try {
            $payrolls = $this->getFilteredPayrolls();
            
            if ($payrolls->count() === 0) {
                session()->flash('error', 'Nenhuma folha de pagamento encontrada com os filtros aplicados.');
                return;
            }

            \Log::info('Gerando recibos em lote', [
                'total_payrolls' => $payrolls->count(),
                'filters' => $this->filters,
                'search' => $this->search,
                'user_id' => auth()->id()
            ]);

            // Adicionar parâmetro de busca aos filtros para o controller
            $filtersWithSearch = array_merge($this->filters, ['search' => $this->search]);

            // Usar month/year dos filtros se disponíveis, senão usar selectedMonth/selectedYear
            $urlMonth = !empty($this->filters['month']) ? (int)$this->filters['month'] : ($this->selectedMonth ?? date('n'));
            $urlYear = !empty($this->filters['year']) ? (int)$this->filters['year'] : ($this->selectedYear ?? date('Y'));

            // Redirecionar para rota de geração de PDF
            $this->redirectRoute('payroll.bulk-receipts', [
                'filters' => base64_encode(json_encode($filtersWithSearch)),
                'month' => $urlMonth,
                'year' => $urlYear
            ]);

        } catch (\Exception $e) {
            \Log::error('Erro ao gerar recibos em lote', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'filters' => $this->filters,
                'search' => $this->search
            ]);
            
            session()->flash('error', 'Erro ao gerar recibos: ' . $e->getMessage());
        }
    }

    /**
     * Retorna folhas de pagamento filtradas para geração de recibos
     */
    private function getFilteredPayrolls()
    {
        $query = PayrollModel::with(['employee.department', 'payrollPeriod'])
            ->when(!empty($this->search), function ($q) {
                $q->whereHas('employee', function ($subQuery) {
                    $subQuery->where('full_name', 'like', '%' . $this->search . '%')
                             ->orWhere('employee_id', 'like', '%' . $this->search . '%')
                             ->orWhere('email', 'like', '%' . $this->search . '%');
                });
            })
            ->when(!empty($this->filters['department_id']), function ($q) {
                $q->whereHas('employee', function ($subQuery) {
                    $subQuery->where('department_id', $this->filters['department_id']);
                });
            })
            ->when(!empty($this->filters['period_id']), function ($q) {
                $q->where('payroll_period_id', $this->filters['period_id']);
            })
            ->when(!empty($this->filters['status']), function ($q) {
                $q->where('status', $this->filters['status']);
            })
            ->when(!empty($this->filters['month']), function ($q) {
                $q->whereHas('payrollPeriod', function ($subQuery) {
                    $subQuery->whereMonth('start_date', $this->filters['month']);
                });
            })
            ->when(!empty($this->filters['year']), function ($q) {
                $q->whereHas('payrollPeriod', function ($subQuery) {
                    $subQuery->whereYear('start_date', $this->filters['year']);
                });
            });

        // Log da query para debugging
        \Log::info('getFilteredPayrolls query', [
            'search' => $this->search,
            'filters' => $this->filters,
            'sql' => $query->toSql(),
            'bindings' => $query->getBindings()
        ]);

        $results = $query->get();
        
        \Log::info('getFilteredPayrolls results', [
            'count' => $results->count(),
            'payroll_ids' => $results->pluck('id')->toArray()
        ]);

        return $results;
    }
    
    public function closeViewModal()
    {
        $this->showViewModal = false;
        $this->resetValidation();
        $this->currentPayroll = null;
    }
    
    public function closeDeleteModal()
    {
        $this->showDeleteModal = false;
    }
    
    /**
     * View payroll details
     * 
     * @param int $payrollId
     * @return void
     */
    public function view($payrollId)
    {
        $this->payroll_id = $payrollId;
        $this->currentPayroll = PayrollModel::with(['employee', 'payrollPeriod', 'payrollItems', 'employee.department', 'employee.position'])
            ->findOrFail($payrollId);
        $this->showViewModal = true;
    }
    
    /**
     * Download employee payslip
     * 
     * @param int $payrollId
     * @return mixed
     */
    public function downloadPayslip($payrollId)
    {
        try {
            // Buscar payroll com relacionamentos
            $payroll = PayrollModel::with([
                'employee', 
                'employee.department', 
                'employee.position', 
                'payrollPeriod',
                'payrollItems'
            ])->findOrFail($payrollId);
            
            // Verificar se o usuário tem permissão para baixar este payslip
            if (!$payroll) {
                session()->flash('error', 'Folha de pagamento não encontrada.');
                return null;
            }
            
            // Preparar dados do funcionário
            $employeeData = [
                'name' => $payroll->employee->full_name,
                'id' => $payroll->employee->employee_id,
                'department' => $payroll->employee->department->name ?? 'N/A',
                'position' => $payroll->employee->position->name ?? 'N/A',
                'bank_account' => $payroll->bank_account ?? 'N/A'
            ];
            
            // Preparar dados do período
            $periodData = [
                'name' => $payroll->payrollPeriod->name ?? 'N/A',
                'start_date' => $payroll->payrollPeriod ? $payroll->payrollPeriod->start_date->format('d/m/Y') : 'N/A',
                'end_date' => $payroll->payrollPeriod ? $payroll->payrollPeriod->end_date->format('d/m/Y') : 'N/A'
            ];
            
            // Preparar dados da empresa
            $companyData = [
                'name' => \App\Models\Setting::get('company_name', 'ERP DEMBENA'),
                'address' => \App\Models\Setting::get('company_address', ''),
                'phone' => \App\Models\Setting::get('company_phone', ''),
                'email' => \App\Models\Setting::get('company_email', ''),
                'nif' => \App\Models\Setting::get('company_nif', ''),
                'logo' => \App\Models\Setting::get('company_logo', '')
            ];
            
            // Preparar breakdown dos componentes
            $earnings = [];
            $deductions = [];
            $totalEarnings = 0;
            $totalDeductions = 0;
            
            if ($payroll->payrollItems && $payroll->payrollItems->count() > 0) {
                // Processar rendimentos
                foreach ($payroll->payrollItems->whereIn('type', ['earning', 'allowance', 'bonus']) as $item) {
                    $amount = (float)$item->amount;
                    $totalEarnings += $amount;
                    $earnings[] = [
                        'name' => $item->name,
                        'description' => $item->description,
                        'type' => $item->type,
                        'is_taxable' => $item->is_taxable,
                        'amount' => $amount
                    ];
                }
                
                // Processar deduções
                foreach ($payroll->payrollItems->whereIn('type', ['deduction', 'tax']) as $item) {
                    $amount = abs((float)$item->amount);
                    $totalDeductions += $amount;
                    $deductions[] = [
                        'name' => $item->name,
                        'description' => $item->description,
                        'type' => $item->type,
                        'amount' => $amount
                    ];
                }
            } else {
                // Fallback para payrolls sem items detalhados
                $earnings = [
                    ['name' => 'Salário Base', 'amount' => (float)$payroll->basic_salary, 'type' => 'earning'],
                    ['name' => 'Subsídios', 'amount' => (float)$payroll->allowances, 'type' => 'allowance'],
                    ['name' => 'Horas Extras', 'amount' => (float)$payroll->overtime, 'type' => 'earning'],
                    ['name' => 'Bónus', 'amount' => (float)$payroll->bonuses, 'type' => 'bonus']
                ];
                
                $deductions = [
                    ['name' => 'IRT', 'amount' => (float)$payroll->tax, 'type' => 'tax'],
                    ['name' => 'INSS', 'amount' => (float)$payroll->social_security, 'type' => 'tax'],
                    ['name' => 'Outras Deduções', 'amount' => (float)$payroll->deductions, 'type' => 'deduction']
                ];
                
                $totalEarnings = array_sum(array_column($earnings, 'amount'));
                $totalDeductions = array_sum(array_column($deductions, 'amount'));
            }
            
            // Preparar dados completos para o PDF
            $totals = [
                'earnings' => $totalEarnings,
                'deductions' => $totalDeductions,
                'net_salary' => (float)$payroll->net_salary
            ];
            
            // Generate PDF with new payslip format matching the provided template
            $pdf = PDF::loadView('livewire.hr.payslip-template', [
                'employee' => $employeeData,
                'period' => $periodData,
                'company' => $companyData,
                'earnings' => $earnings,
                'deductions' => $deductions,
                'totals' => $totals,
                'generated_at' => now()->format('d/m/Y H:i'),
                // Additional fields for the new template
                'attendance_days' => $payroll->attendance_days ?? 31,
                'absence_days' => $payroll->absence_days ?? 0,
                'overtime_hours' => $payroll->overtime_hours ?? 0,
                'tax_irt' => $payroll->income_tax ?? 0,
                'social_security' => $payroll->social_security ?? 0,
                'absence_deduction' => $payroll->absence_deduction ?? 0,
                'advance_deduction' => $payroll->advance_deduction ?? 0,
                'food_deduction' => $payroll->food_deduction ?? 0,
                'other_deductions' => $payroll->other_deductions ?? 0,
                'union_fee' => $payroll->union_fee ?? 0,
                'bank_name' => $payroll->employee->bank->name ?? '',
                'bank_account' => $payroll->employee->bank_account ?? ''
            ]);
            
            // Configurações do PDF
            $pdf->setPaper('A4', 'portrait');
            $pdf->setOptions([
                'isHtml5ParserEnabled' => true,
                'isPhpEnabled' => true,
                'defaultFont' => 'DejaVu Sans',
                'fontSubsetting' => false,
                'debugKeepTemp' => false
            ]);
            
            // Nome do arquivo
            $fileName = sprintf(
                'recibo_pagamento_%s_%s_%s.pdf',
                $payroll->employee->employee_id,
                $payroll->payrollPeriod ? $payroll->payrollPeriod->name : 'periodo',
                now()->format('Y-m-d')
            );
            
            // Log da ação
            \Log::info('PDF Payslip gerado', [
                'payroll_id' => $payrollId,
                'employee' => $payroll->employee->full_name,
                'generated_by' => auth()->id(),
                'file_name' => $fileName
            ]);
            
            // Retornar PDF para download
            return response()->streamDownload(function () use ($pdf) {
                echo $pdf->output();
            }, $fileName, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $fileName . '"'
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Erro ao gerar PDF do payslip', [
                'payroll_id' => $payrollId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            session()->flash('error', 'Erro ao gerar contracheque: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Mark payroll as paid
     * 
     * @param int $payrollId
     * @return void
     */
    public function markAsPaid($payrollId)
    {
        $this->payroll_id = $payrollId;
        $this->payment_date = now()->format('Y-m-d');
        $this->showPayModal = true;
    }
    
    /**
     * Reset all filters to their default values
     */
    public function resetFilters(): void
    {
        $this->reset(['filters', 'search']);
        $this->resetPage();
        
        // Log filter reset for debugging
        \Log::info('Payroll filters reset', [
            'user_id' => auth()->id(),
            'timestamp' => now()->toDateTimeString()
        ]);
    }

    public function render()
    {
        \Log::info('PAYROLL RENDER', [
            'filters' => $this->filters,
            'search' => $this->search,
            'perPage' => $this->perPage
        ]);
        
        $query = PayrollModel::query()
            ->with(['employee', 'payrollPeriod'])
            ->when($this->search, function ($query) {
                return $query->whereHas('employee', function ($query) {
                    $query->where('full_name', 'like', "%{$this->search}%")
                          ->orWhere('employee_id', 'like', "%{$this->search}%")
                          ->orWhere('email', 'like', "%{$this->search}%");
                });
            })
            ->when($this->filters['department_id'], function ($query) {
                return $query->whereHas('employee', function ($query) {
                    $query->where('department_id', $this->filters['department_id']);
                });
            })
            ->when($this->filters['period_id'], function ($query) {
                return $query->where('payroll_period_id', $this->filters['period_id']);
            })
            ->when($this->filters['status'], function ($query) {
                return $query->where('status', $this->filters['status']);
            })
            ->when($this->filters['month'], function ($query) {
                return $query->whereHas('payrollPeriod', function ($subQuery) {
                    $subQuery->whereMonth('start_date', $this->filters['month']);
                });
            })
            ->when($this->filters['year'], function ($query) {
                return $query->whereHas('payrollPeriod', function ($subQuery) {
                    $subQuery->whereYear('start_date', $this->filters['year']);
                });
            });

        $payrolls = $query->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);
        
        \Log::info('PAYROLL RENDER RESULT', [
            'total' => $payrolls->total(),
            'count' => $payrolls->count(),
            'sql' => $query->toSql()
        ]);

        // Buscar funcionários ativos que NÃO foram pagos no período selecionado
        $employeesQuery = Employee::where('employment_status', 'active');
        
        // Se há um período selecionado nos filtros, excluir funcionários já pagos nesse período
        if (!empty($this->filters['period_id'])) {
            $employeesQuery->whereDoesntHave('payrolls', function ($query) {
                $query->where('payroll_period_id', $this->filters['period_id']);
            });
        }
        
        $employees = $employeesQuery->get();
        $payrollPeriods = PayrollPeriod::orderBy('start_date', 'desc')->get();
        $departments = Department::where('is_active', true)->get();

        return view('livewire.hr.payroll.payroll', [
            'payrolls' => $payrolls,
            'employees' => $employees,
            'payrollPeriods' => $payrollPeriods,
            'departments' => $departments,
        ])->layout('layouts.livewire', [
            'title' => __('payroll.payroll_management')
        ]);
    }
}
