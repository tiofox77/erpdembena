<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\HR\Employee;
use App\Models\HR\PayrollPeriod;
use App\Models\HR\Attendance;
use App\Models\HR\OvertimeRecord;
use App\Models\HR\SalaryAdvance;
use App\Models\HR\SalaryDiscount;
use App\Models\HR\Leave;
use App\Models\HR\LeaveType;
use App\Models\HR\HRSetting;
use App\Models\HR\IRTTaxBracket;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * Centralized Payroll Calculation Service
 * 
 * This service contains ALL payroll calculation logic used by BOTH:
 * - Individual payroll processing (Livewire component)
 * - Batch payroll processing (Job)
 * 
 * This ensures calculations are ALWAYS consistent!
 */
class PayrollCalculationService
{
    protected Employee $employee;
    protected PayrollPeriod $period;
    protected Carbon $startDate;
    protected Carbon $endDate;
    
    // Settings
    protected int $workingDaysPerMonth = 22;
    protected float $workingHoursPerDay = 8.0;
    protected float $hourlyRate = 0.0;
    protected float $dailyRate = 0.0;
    
    // Attendance data
    protected int $presentDays = 0;
    protected int $absentDays = 0;
    protected int $lateDays = 0;
    protected float $totalHours = 0.0;
    
    // Leave data
    protected int $totalLeaveDays = 0;
    protected int $paidLeaveDays = 0;
    protected int $unpaidLeaveDays = 0;
    protected float $leaveDeduction = 0.0;
    
    // Earnings components
    protected float $basicSalary = 0.0;
    protected float $christmasSubsidy = 0.0;
    protected float $vacationSubsidy = 0.0;
    protected float $transportAllowance = 0.0;
    protected float $foodBenefit = 0.0;
    protected float $profileBonus = 0.0;
    protected float $additionalBonus = 0.0;
    protected float $overtimeAmount = 0.0;
    
    // Deductions
    protected float $irtAmount = 0.0;
    protected float $inss3Percent = 0.0;
    protected float $inss8Percent = 0.0;
    protected float $absenceDeduction = 0.0;
    protected float $lateDeduction = 0.0;
    protected float $advanceDeduction = 0.0;
    protected float $discountDeduction = 0.0;
    
    // Totals
    protected float $grossSalary = 0.0;
    protected float $netSalary = 0.0;
    protected float $totalDeductions = 0.0;
    protected float $baseIrtTaxableAmount = 0.0;
    
    // Subsidy flags
    protected bool $applyChristmasSubsidy = false;
    protected bool $applyVacationSubsidy = false;
    
    // Collections
    protected $attendanceRecords;
    protected $overtimeRecords;
    protected $salaryAdvances;
    protected $salaryDiscounts;
    protected $leaveRecords;

    public function __construct(Employee $employee, PayrollPeriod $period)
    {
        $this->employee = $employee;
        $this->period = $period;
        $this->startDate = Carbon::parse($period->start_date);
        $this->endDate = Carbon::parse($period->end_date);
        
        $this->loadSettings();
        $this->calculateBasicRates();
    }

    /**
     * Load HR settings
     */
    protected function loadSettings(): void
    {
        $this->workingDaysPerMonth = (int) HRSetting::get('monthly_working_days', 22);
        $this->workingHoursPerDay = (float) HRSetting::get('working_hours_per_day', 8.0);
    }

    /**
     * Calculate basic rates
     */
    protected function calculateBasicRates(): void
    {
        $this->basicSalary = (float) $this->employee->base_salary;
        $totalMonthlyHours = $this->workingDaysPerMonth * $this->workingHoursPerDay;
        $this->hourlyRate = $totalMonthlyHours > 0 ? $this->basicSalary / $totalMonthlyHours : 0.0;
        $this->dailyRate = $this->basicSalary / $this->workingDaysPerMonth;
    }

    /**
     * Set subsidy flags
     */
    public function setSubsidies(bool $christmas = false, bool $vacation = false): self
    {
        $this->applyChristmasSubsidy = $christmas;
        $this->applyVacationSubsidy = $vacation;
        return $this;
    }

    /**
     * Set additional bonus
     */
    public function setAdditionalBonus(float $amount): self
    {
        $this->additionalBonus = $amount;
        return $this;
    }

    /**
     * Load ALL data and calculate payroll
     */
    public function calculate(): array
    {
        Log::info('PayrollCalculationService: Starting calculation', [
            'employee_id' => $this->employee->id,
            'employee_name' => $this->employee->full_name,
            'period' => $this->period->name
        ]);

        // Step 1: Load data
        $this->loadAttendanceData();
        $this->loadLeaveData();
        $this->loadOvertimeData();
        $this->loadSalaryAdvances();
        $this->loadSalaryDiscounts();
        
        // Step 2: Calculate earnings
        $this->calculateEarnings();
        
        // Step 3: Calculate deductions
        $this->calculateDeductions();
        
        // Step 4: Calculate net salary
        $this->calculateNetSalary();
        
        Log::info('PayrollCalculationService: Calculation completed', [
            'gross_salary' => $this->grossSalary,
            'total_deductions' => $this->totalDeductions,
            'net_salary' => $this->netSalary
        ]);

        return $this->getResult();
    }

    /**
     * Load attendance data
     */
    protected function loadAttendanceData(): void
    {
        $this->attendanceRecords = Attendance::where('employee_id', $this->employee->id)
            ->whereBetween('date', [$this->startDate->format('Y-m-d'), $this->endDate->format('Y-m-d')])
            ->get();

        $this->presentDays = $this->attendanceRecords->whereIn('status', ['present', 'late', 'half_day'])->count();
        $this->absentDays = max(0, $this->workingDaysPerMonth - $this->presentDays);
        $this->lateDays = $this->attendanceRecords->where('status', 'late')->count();

        // Calculate total hours
        $this->totalHours = 0.0;
        foreach ($this->attendanceRecords as $attendance) {
            if (in_array($attendance->status, ['present', 'late', 'half_day'])) {
                if ($attendance->time_in && $attendance->time_out) {
                    $timeIn = Carbon::parse($attendance->time_in);
                    $timeOut = Carbon::parse($attendance->time_out);
                    $hours = $timeIn->diffInHours($timeOut);
                    if ($attendance->status === 'half_day') {
                        $hours = min($hours / 2, 4);
                    }
                } else {
                    $hours = $attendance->status === 'half_day' ? 4 : $this->workingHoursPerDay;
                }
                $this->totalHours += $hours;
            }
        }

        // Calculate deductions for absences and lates
        $this->absenceDeduction = $this->absentDays * $this->dailyRate;
        $this->lateDeduction = $this->lateDays * $this->hourlyRate; // 1 hour per late
    }

    /**
     * Load leave data for the period
     */
    protected function loadLeaveData(): void
    {
        $this->leaveRecords = Leave::where('employee_id', $this->employee->id)
            ->where('status', Leave::STATUS_APPROVED)
            ->where('affects_payroll', true)
            ->where(function ($query) {
                $query->whereBetween('start_date', [$this->startDate->format('Y-m-d'), $this->endDate->format('Y-m-d')])
                      ->orWhereBetween('end_date', [$this->startDate->format('Y-m-d'), $this->endDate->format('Y-m-d')])
                      ->orWhere(function ($q) {
                          $q->where('start_date', '<=', $this->startDate->format('Y-m-d'))
                            ->where('end_date', '>=', $this->endDate->format('Y-m-d'));
                      });
            })
            ->with('leaveType')
            ->get();

        $this->totalLeaveDays = 0;
        $this->paidLeaveDays = 0;
        $this->unpaidLeaveDays = 0;
        $this->leaveDeduction = 0.0;

        foreach ($this->leaveRecords as $leave) {
            // Calculate days within the period
            $leaveStart = Carbon::parse($leave->start_date);
            $leaveEnd = Carbon::parse($leave->end_date);
            
            // Adjust dates if leave extends beyond period
            $effectiveStart = $leaveStart->lt($this->startDate) ? $this->startDate : $leaveStart;
            $effectiveEnd = $leaveEnd->gt($this->endDate) ? $this->endDate : $leaveEnd;
            
            $daysInPeriod = $effectiveStart->diffInDays($effectiveEnd) + 1;
            $this->totalLeaveDays += $daysInPeriod;
            
            // Check if leave is paid or unpaid
            if ($leave->is_paid_leave) {
                $this->paidLeaveDays += $daysInPeriod;
                
                // If payment percentage is less than 100%, calculate partial deduction
                if ($leave->payment_percentage < 100) {
                    $deductionPercentage = (100 - $leave->payment_percentage) / 100;
                    $this->leaveDeduction += ($this->dailyRate * $daysInPeriod * $deductionPercentage);
                }
            } else {
                // Unpaid leave - full deduction
                $this->unpaidLeaveDays += $daysInPeriod;
                $this->leaveDeduction += ($this->dailyRate * $daysInPeriod);
            }
        }
        
        // Adjust present days to account for paid leaves
        // Paid leaves should count as present days
        $this->presentDays += $this->paidLeaveDays;
        $this->absentDays = max(0, $this->workingDaysPerMonth - $this->presentDays);
        
        // Recalculate absence deduction to exclude paid leave days
        $this->absenceDeduction = $this->absentDays * $this->dailyRate;

        Log::info('Leave data loaded', [
            'total_leave_days' => $this->totalLeaveDays,
            'paid_leave_days' => $this->paidLeaveDays,
            'unpaid_leave_days' => $this->unpaidLeaveDays,
            'leave_deduction' => $this->leaveDeduction,
            'adjusted_present_days' => $this->presentDays,
            'adjusted_absent_days' => $this->absentDays
        ]);
    }

    /**
     * Load overtime data
     */
    protected function loadOvertimeData(): void
    {
        $this->overtimeRecords = OvertimeRecord::where('employee_id', $this->employee->id)
            ->whereBetween('date', [$this->startDate->format('Y-m-d'), $this->endDate->format('Y-m-d')])
            ->where('status', 'approved')
            ->get();

        $this->overtimeAmount = $this->overtimeRecords->sum('amount');
    }

    /**
     * Load salary advances
     */
    protected function loadSalaryAdvances(): void
    {
        $this->salaryAdvances = SalaryAdvance::where('employee_id', $this->employee->id)
            ->where('status', 'approved')
            ->where('remaining_installments', '>', 0)
            ->get();

        $this->advanceDeduction = $this->salaryAdvances->sum('installment_amount');
    }

    /**
     * Load salary discounts
     */
    protected function loadSalaryDiscounts(): void
    {
        $this->salaryDiscounts = SalaryDiscount::where('employee_id', $this->employee->id)
            ->where('status', 'approved')
            ->where('remaining_installments', '>', 0)
            ->get();

        $this->discountDeduction = $this->salaryDiscounts->sum('installment_amount');
    }

    /**
     * Calculate earnings (Gross Salary)
     */
    protected function calculateEarnings(): void
    {
        $gross = $this->basicSalary;

        // Add subsidies (50% each)
        if ($this->applyChristmasSubsidy) {
            $this->christmasSubsidy = $this->basicSalary * 0.5;
            $gross += $this->christmasSubsidy;
        }

        if ($this->applyVacationSubsidy) {
            $this->vacationSubsidy = $this->basicSalary * 0.5;
            $gross += $this->vacationSubsidy;
        }

        // Add profile bonus from employee record (ensure never null)
        $this->profileBonus = (float) ($this->employee->bonus_amount ?? 0.0);
        $gross += $this->profileBonus;

        // Add additional bonus (ensure never null)
        $this->additionalBonus = (float) ($this->additionalBonus ?? 0.0);
        $gross += $this->additionalBonus;

        // Add transport allowance (proportional to days worked)
        $fullTransport = (float) ($this->employee->transport_benefit ?? 0);
        $this->transportAllowance = ($fullTransport / $this->workingDaysPerMonth) * $this->presentDays;
        
        // Only taxable portion above 30k
        $exemptLimit = 30000.0;
        $taxableTransport = max(0, $this->transportAllowance - $exemptLimit);
        $gross += $taxableTransport;

        // Food benefit (excluded from gross, up to 30k non-taxable)
        $this->foodBenefit = (float) ($this->employee->food_benefit ?? 0);
        if ($this->foodBenefit > 30000) {
            $gross += ($this->foodBenefit - 30000); // Only excess is taxable
        }

        // Add overtime
        $gross += $this->overtimeAmount;

        $this->grossSalary = $gross;
    }

    /**
     * Calculate deductions
     */
    protected function calculateDeductions(): void
    {
        $deductions = 0.0;

        // Calculate INSS (base includes all taxable components)
        $inssBase = $this->basicSalary + $this->foodBenefit + $this->transportAllowance + 
                    $this->profileBonus + $this->additionalBonus + 
                    $this->christmasSubsidy + $this->vacationSubsidy;

        // INSS 3% (employee contribution)
        $this->inss3Percent = $inssBase * 0.03;
        $deductions += $this->inss3Percent;

        // INSS 8% (employer contribution - illustrative, not deducted)
        $this->inss8Percent = $inssBase * 0.08;

        // Calculate IRT (MC = Gross Salary - INSS 3%)
        $this->baseIrtTaxableAmount = max(0, $this->grossSalary - $this->inss3Percent);
        $this->irtAmount = IRTTaxBracket::calculateIRT($this->baseIrtTaxableAmount);
        $deductions += $this->irtAmount;

        // Attendance deductions
        $deductions += $this->absenceDeduction;
        $deductions += $this->lateDeduction;
        
        // Leave deductions (unpaid or partial payment)
        $deductions += $this->leaveDeduction;

        // Salary advances
        $deductions += $this->advanceDeduction;

        // Salary discounts
        $deductions += $this->discountDeduction;

        $this->totalDeductions = $deductions;
    }

    /**
     * Calculate net salary
     */
    protected function calculateNetSalary(): void
    {
        // Net = Gross - Deductions - Food Benefit (if up to 30k, paid separately)
        $foodToSubtract = min($this->foodBenefit, 30000); // Non-taxable food paid separately
        $this->netSalary = max(0, $this->grossSalary - $this->totalDeductions - $foodToSubtract);
    }

    /**
     * Get calculation result
     */
    public function getResult(): array
    {
        return [
            // Basic info
            'employee_id' => $this->employee->id,
            'payroll_period_id' => $this->period->id,
            'basic_salary' => $this->basicSalary,
            
            // Attendance
            'attendance_hours' => $this->totalHours,
            'present_days' => $this->presentDays,
            'absent_days' => $this->absentDays,
            'late_days' => $this->lateDays,
            
            // Leave
            'total_leave_days' => $this->totalLeaveDays,
            'paid_leave_days' => $this->paidLeaveDays,
            'unpaid_leave_days' => $this->unpaidLeaveDays,
            'leave_deduction' => $this->leaveDeduction,
            
            // Earnings
            'christmas_subsidy' => $this->christmasSubsidy,
            'vacation_subsidy' => $this->vacationSubsidy,
            'transport_allowance' => $this->transportAllowance,
            'food_benefit' => $this->foodBenefit,
            'profile_bonus' => $this->profileBonus,
            'additional_bonus' => $this->additionalBonus,
            'overtime_amount' => $this->overtimeAmount,
            'allowances' => $this->transportAllowance + $this->foodBenefit,
            'bonuses' => $this->profileBonus + $this->additionalBonus + $this->christmasSubsidy + $this->vacationSubsidy,
            'overtime' => $this->overtimeAmount,
            'gross_salary' => $this->grossSalary,
            'main_salary' => $this->basicSalary + $this->foodBenefit + $this->transportAllowance + $this->overtimeAmount,
            
            // Deductions
            'tax' => $this->irtAmount,
            'deductions_irt' => $this->irtAmount,
            'social_security' => $this->inss3Percent,
            'inss_3_percent' => $this->inss3Percent,
            'inss_8_percent' => $this->inss8Percent,
            'absence_deduction_amount' => $this->absenceDeduction,
            'late_deduction' => $this->lateDeduction,
            'advance_deduction' => $this->advanceDeduction,
            'total_salary_discounts' => $this->discountDeduction,
            'deductions' => $this->totalDeductions,
            'total_deductions_calculated' => $this->totalDeductions,
            'base_irt_taxable_amount' => $this->baseIrtTaxableAmount,
            
            // Totals
            'net_salary' => $this->netSalary,
            
            // Metadata
            'hourly_rate' => $this->hourlyRate,
            'daily_rate' => $this->dailyRate,
            'monthly_working_days' => $this->workingDaysPerMonth,
        ];
    }

    /**
     * Get detailed breakdown for display
     */
    public function getDetailedBreakdown(): array
    {
        $result = $this->getResult();
        
        $result['earnings_breakdown'] = [
            'Basic Salary' => $this->basicSalary,
            'Christmas Subsidy' => $this->christmasSubsidy,
            'Vacation Subsidy' => $this->vacationSubsidy,
            'Transport Allowance' => $this->transportAllowance,
            'Food Benefit' => $this->foodBenefit,
            'Profile Bonus' => $this->profileBonus,
            'Additional Bonus' => $this->additionalBonus,
            'Overtime' => $this->overtimeAmount,
        ];

        $result['deductions_breakdown'] = [
            'IRT (Income Tax)' => $this->irtAmount,
            'INSS 3%' => $this->inss3Percent,
            'Absence Deduction' => $this->absenceDeduction,
            'Late Deduction' => $this->lateDeduction,
            'Leave Deduction' => $this->leaveDeduction,
            'Salary Advances' => $this->advanceDeduction,
            'Salary Discounts' => $this->discountDeduction,
        ];

        return $result;
    }
}
