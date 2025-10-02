<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\HR\PayrollBatch;
use App\Models\HR\PayrollBatchItem;
use App\Models\HR\Payroll;
use App\Models\HR\Employee;
use App\Models\HR\Attendance;
use App\Models\HR\OvertimeRecord;
use App\Models\HR\SalaryAdvance;
use App\Models\HR\SalaryDiscount;
use App\Models\HR\HRSetting;
use App\Models\HR\IRTTaxBracket;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class ProcessPayrollBatch implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected PayrollBatch $batch;
    
    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The maximum number of unhandled exceptions to allow before failing.
     */
    public int $maxExceptions = 3;

    /**
     * Create a new job instance.
     */
    public function __construct(PayrollBatch $batch)
    {
        $this->batch = $batch;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('Starting payroll batch processing', [
            'batch_id' => $this->batch->id,
            'batch_name' => $this->batch->name,
            'total_employees' => $this->batch->total_employees,
        ]);

        try {
            DB::transaction(function () {
                // Update batch status
                $this->batch->update([
                    'status' => PayrollBatch::STATUS_PROCESSING,
                    'processing_started_at' => now(),
                ]);

                // Process each employee in the batch
                $batchItems = $this->batch->batchItems()
                    ->with(['employee'])
                    ->orderBy('processing_order')
                    ->get();

                $processedCount = 0;
                $totalGross = 0;
                $totalNet = 0;
                $totalDeductions = 0;

                foreach ($batchItems as $item) {
                    try {
                        // Update item status to processing
                        $item->update(['status' => PayrollBatchItem::STATUS_PROCESSING]);

                        // Process individual payroll
                        $payrollData = $this->processEmployeePayroll($item->employee, $this->batch);

                        if ($payrollData) {
                            // Create payroll record
                            $payroll = Payroll::create($payrollData);

                            // Update batch item
                            $item->update([
                                'status' => PayrollBatchItem::STATUS_COMPLETED,
                                'payroll_id' => $payroll->id,
                                'gross_salary' => $payroll->gross_salary,
                                'net_salary' => $payroll->net_salary,
                                'total_deductions' => $payroll->total_deductions,
                                'processed_at' => now(),
                            ]);

                            // Accumulate totals
                            $totalGross += $payroll->gross_salary;
                            $totalNet += $payroll->net_salary;
                            $totalDeductions += $payroll->total_deductions;
                            $processedCount++;

                            Log::info('Employee payroll processed successfully', [
                                'batch_id' => $this->batch->id,
                                'employee_id' => $item->employee_id,
                                'payroll_id' => $payroll->id,
                            ]);
                        } else {
                            // Mark as failed
                            $item->update([
                                'status' => PayrollBatchItem::STATUS_FAILED,
                                'error_message' => 'Falha ao calcular folha de pagamento',
                                'processed_at' => now(),
                            ]);
                        }
                    } catch (Exception $e) {
                        // Mark item as failed
                        $item->update([
                            'status' => PayrollBatchItem::STATUS_FAILED,
                            'error_message' => $e->getMessage(),
                            'processed_at' => now(),
                        ]);

                        Log::error('Error processing employee payroll', [
                            'batch_id' => $this->batch->id,
                            'employee_id' => $item->employee_id,
                            'error' => $e->getMessage(),
                        ]);
                    }

                    // Update batch progress
                    $this->batch->update(['processed_employees' => $processedCount]);
                }

                // Update batch totals and status
                $this->batch->update([
                    'status' => PayrollBatch::STATUS_COMPLETED,
                    'processed_employees' => $processedCount,
                    'total_gross_amount' => $totalGross,
                    'total_net_amount' => $totalNet,
                    'total_deductions' => $totalDeductions,
                    'processing_completed_at' => now(),
                ]);

                Log::info('Payroll batch processing completed successfully', [
                    'batch_id' => $this->batch->id,
                    'processed_employees' => $processedCount,
                    'total_gross' => $totalGross,
                    'total_net' => $totalNet,
                ]);
            });

            // Dispatch event to notify UI
            event('payroll-batch-processed', $this->batch->id);
            
        } catch (Exception $e) {
            // Mark batch as failed
            $this->batch->update([
                'status' => PayrollBatch::STATUS_FAILED,
                'processing_completed_at' => now(),
            ]);

            Log::error('Payroll batch processing failed', [
                'batch_id' => $this->batch->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    /**
     * Process payroll for individual employee
     */
    protected function processEmployeePayroll(Employee $employee, PayrollBatch $batch): ?array
    {
        try {
            $period = $batch->payrollPeriod;
            $startDate = Carbon::parse($period->start_date);
            $endDate = Carbon::parse($period->end_date);

            // Load HR settings
            $hrSettings = $this->loadHRSettings();

            // Calculate basic salary components
            $basicSalary = (float) $employee->base_salary;
            $workingDaysPerMonth = (int) $hrSettings['working_days_per_month'];
            $workingHoursPerDay = (float) $hrSettings['working_hours_per_day'];
            $hourlyRate = $workingDaysPerMonth > 0 ? $basicSalary / ($workingDaysPerMonth * $workingHoursPerDay) : 0;

            // Load attendance data
            $attendanceData = $this->loadAttendanceData($employee, $startDate, $endDate, $workingDaysPerMonth);
            
            // Load overtime data
            $overtimeData = $this->loadOvertimeData($employee, $startDate, $endDate);
            
            // Load salary advances and discounts
            $advancesData = $this->loadSalaryAdvances($employee);
            $discountsData = $this->loadSalaryDiscounts($employee);

            // Calculate allowances
            $allowances = $this->calculateAllowances($employee, $attendanceData);

            // Calculate gross salary
            $grossSalary = $basicSalary + $allowances['total'] + $overtimeData['total_amount'];

            // Calculate taxes and deductions
            $taxData = $this->calculateTaxes($grossSalary, $hrSettings);
            
            // Calculate total deductions
            $totalDeductions = $taxData['income_tax'] + 
                              $taxData['social_security'] + 
                              $advancesData['current_deduction'] + 
                              $discountsData['current_deduction'] + 
                              $attendanceData['attendance_deductions'];

            // Calculate net salary
            $netSalary = $grossSalary - $totalDeductions;

            // Build payroll data array
            return [
                'employee_id' => $employee->id,
                'payroll_period_id' => $batch->payroll_period_id,
                'payroll_batch_id' => $batch->id,
                'basic_salary' => $basicSalary,
                'allowances' => $allowances['total'],
                'overtime' => $overtimeData['total_amount'],
                'gross_salary' => $grossSalary,
                'income_tax' => $taxData['income_tax'],
                'social_security' => $taxData['social_security'],
                'other_deductions' => 0,
                'total_deductions' => $totalDeductions,
                'net_salary' => $netSalary,
                'payment_method' => $batch->payment_method,
                'payment_date' => $batch->batch_date,
                'status' => 'approved',
                'remarks' => "Processado via lote: {$batch->name}",
                // Additional fields
                'attendance_hours' => $attendanceData['total_hours'],
                'overtime_hours' => $overtimeData['total_hours'],
                'advance_deduction' => $advancesData['current_deduction'],
                'total_salary_discounts' => $discountsData['current_deduction'],
                'late_deduction' => $attendanceData['late_deduction'],
                'absence_deduction' => $attendanceData['absence_deduction'],
                'transport_allowance' => $allowances['transport'],
                'meal_allowance' => $allowances['meal'],
                'housing_allowance' => $allowances['housing'],
                'performance_bonus' => $allowances['bonus'],
            ];
            
        } catch (Exception $e) {
            Log::error('Error calculating employee payroll', [
                'employee_id' => $employee->id,
                'batch_id' => $batch->id,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Load HR settings
     */
    protected function loadHRSettings(): array
    {
        return [
            'working_hours_per_day' => (float) HRSetting::get('working_hours_per_day', 8),
            'working_days_per_month' => (int) HRSetting::get('working_days_per_month', 22),
            'irt_rate' => (float) HRSetting::get('irt_rate', 6.5),
            'inss_rate' => (float) HRSetting::get('inss_rate', 3.0),
            'irt_min_salary' => (float) HRSetting::get('irt_min_salary', 70000),
        ];
    }

    /**
     * Load attendance data for employee
     */
    protected function loadAttendanceData(Employee $employee, Carbon $startDate, Carbon $endDate, int $workingDays): array
    {
        $attendances = Attendance::where('employee_id', $employee->id)
            ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->get();

        $presentDays = $attendances->whereIn('status', ['present', 'late', 'half_day'])->count();
        $absentDays = max(0, $workingDays - $presentDays);
        $lateDays = $attendances->where('status', 'late')->count();

        // Calculate total hours
        $totalHours = 0;
        $standardWorkDay = 8;
        foreach ($attendances as $attendance) {
            if (in_array($attendance->status, ['present', 'late', 'half_day'])) {
                if ($attendance->time_in && $attendance->time_out) {
                    $timeIn = Carbon::parse($attendance->time_in);
                    $timeOut = Carbon::parse($attendance->time_out);
                    $hours = $timeIn->diffInHours($timeOut);
                    if ($attendance->status === 'half_day') {
                        $hours = min($hours / 2, 4);
                    }
                } else {
                    $hours = $attendance->status === 'half_day' ? 4 : $standardWorkDay;
                }
                $totalHours += $hours;
            }
        }

        // Calculate deductions
        $hourlyRate = $employee->base_salary / ($workingDays * 8);
        $lateDeduction = $lateDays * $hourlyRate; // 1 hour deduction per late day
        $absenceDeduction = $absentDays * ($employee->base_salary / $workingDays);

        return [
            'total_hours' => $totalHours,
            'present_days' => $presentDays,
            'absent_days' => $absentDays,
            'late_days' => $lateDays,
            'late_deduction' => $lateDeduction,
            'absence_deduction' => $absenceDeduction,
            'attendance_deductions' => $lateDeduction + $absenceDeduction,
        ];
    }

    /**
     * Load overtime data for employee
     */
    protected function loadOvertimeData(Employee $employee, Carbon $startDate, Carbon $endDate): array
    {
        $overtimeRecords = OvertimeRecord::where('employee_id', $employee->id)
            ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->where('status', 'approved')
            ->get();

        return [
            'total_hours' => $overtimeRecords->sum('hours'),
            'total_amount' => $overtimeRecords->sum('amount'),
        ];
    }

    /**
     * Load salary advances for employee
     */
    protected function loadSalaryAdvances(Employee $employee): array
    {
        $advances = SalaryAdvance::where('employee_id', $employee->id)
            ->where('status', 'approved')
            ->where('remaining_installments', '>', 0)
            ->get();

        return [
            'total_amount' => $advances->sum('amount'),
            'current_deduction' => $advances->sum('installment_amount'),
        ];
    }

    /**
     * Load salary discounts for employee
     */
    protected function loadSalaryDiscounts(Employee $employee): array
    {
        $discounts = SalaryDiscount::where('employee_id', $employee->id)
            ->where('status', 'approved')
            ->where('remaining_installments', '>', 0)
            ->get();

        return [
            'current_deduction' => $discounts->sum('installment_amount'),
        ];
    }

    /**
     * Calculate allowances for employee
     */
    protected function calculateAllowances(Employee $employee, array $attendanceData): array
    {
        $transportAllowance = (float) ($employee->transport_allowance ?? 0);
        $mealAllowance = (float) ($employee->food_benefit ?? 0);
        $housingAllowance = (float) ($employee->housing_allowance ?? 0);
        $bonusAmount = (float) ($employee->bonus_amount ?? 0);

        // Proportional transport allowance based on attendance
        $workingDaysPerMonth = 22; // From HR settings
        if ($attendanceData['present_days'] < $workingDaysPerMonth) {
            $attendanceRatio = $attendanceData['present_days'] / $workingDaysPerMonth;
            $transportAllowance = $transportAllowance * $attendanceRatio;
        }

        return [
            'transport' => $transportAllowance,
            'meal' => $mealAllowance,
            'housing' => $housingAllowance,
            'bonus' => $bonusAmount,
            'total' => $transportAllowance + $mealAllowance + $housingAllowance + $bonusAmount,
        ];
    }

    /**
     * Calculate taxes and social security
     */
    protected function calculateTaxes(float $grossSalary, array $hrSettings): array
    {
        // Calculate IRT (Income Tax)
        $incomeTax = 0;
        if ($grossSalary > $hrSettings['irt_min_salary']) {
            $taxableAmount = $grossSalary - $hrSettings['irt_min_salary'];
            $incomeTax = $taxableAmount * ($hrSettings['irt_rate'] / 100);
        }

        // Calculate INSS (Social Security)
        $socialSecurity = $grossSalary * ($hrSettings['inss_rate'] / 100);

        return [
            'income_tax' => $incomeTax,
            'social_security' => $socialSecurity,
        ];
    }

    /**
     * Handle job failure
     */
    public function failed(Exception $exception): void
    {
        Log::error('Payroll batch job failed', [
            'batch_id' => $this->batch->id,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);

        // Update batch status to failed
        $this->batch->update([
            'status' => PayrollBatch::STATUS_FAILED,
            'processing_completed_at' => now(),
        ]);
    }
}
