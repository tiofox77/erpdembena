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
use App\Services\PayrollCalculationService;
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

                        // Process individual payroll with batch item data
                        $payrollData = $this->processEmployeePayroll($item, $this->batch);

                        if ($payrollData) {
                            try {
                                // Create payroll record
                                $payroll = Payroll::create($payrollData);
                            } catch (\Exception $createError) {
                                Log::error('Error creating payroll record', [
                                    'batch_id' => $this->batch->id,
                                    'employee_id' => $item->employee_id,
                                    'error' => $createError->getMessage(),
                                    'payroll_data_keys' => array_keys($payrollData),
                                    'trace' => $createError->getTraceAsString(),
                                ]);
                                
                                // Mark item as failed
                                $item->update([
                                    'status' => PayrollBatchItem::STATUS_FAILED,
                                    'error_message' => 'Erro ao criar payroll: ' . $createError->getMessage(),
                                ]);
                                
                                continue;
                            }

                            // Update batch item with all calculated values
                            $item->update([
                                'status' => PayrollBatchItem::STATUS_COMPLETED,
                                'payroll_id' => $payroll->id,
                                'basic_salary' => $payroll->basic_salary ?? 0,
                                'gross_salary' => $payroll->gross_salary ?? 0,
                                'net_salary' => $payroll->net_salary ?? 0,
                                'total_deductions' => $payroll->deductions ?? 0,
                                'inss_deduction' => $payroll->inss_3_percent ?? 0,
                                'irt_deduction' => $payroll->tax ?? 0,
                                'advance_deduction' => $payroll->advance_deduction ?? 0,
                                'discount_deduction' => $payroll->total_salary_discounts ?? 0,
                                'late_deduction' => $payroll->late_deduction ?? 0,
                                'absence_deduction' => $payroll->absence_deduction ?? 0,
                                'christmas_subsidy_amount' => $payroll->christmas_subsidy_amount ?? 0,
                                'vacation_subsidy_amount' => $payroll->vacation_subsidy_amount ?? 0,
                                'transport_allowance' => $payroll->transport_allowance ?? 0,
                                'food_allowance' => $payroll->food_allowance ?? 0,
                                'overtime_amount' => $payroll->overtime_amount ?? 0,
                                'processed_at' => now(),
                            ]);

                            // Accumulate totals
                            $totalGross += $payroll->gross_salary ?? 0;
                            $totalNet += $payroll->net_salary ?? 0;
                            $totalDeductions += $payroll->deductions ?? 0;
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
     * Process payroll for individual employee using centralized calculation service
     */
    protected function processEmployeePayroll(PayrollBatchItem $item, PayrollBatch $batch): ?array
    {
        try {
            $employee = $item->employee;
            
            if (!$employee) {
                Log::error('Employee not found for batch item', [
                    'batch_item_id' => $item->id,
                    'employee_id' => $item->employee_id,
                ]);
                return null;
            }
            
            Log::info('Processing employee payroll', [
                'batch_id' => $batch->id,
                'batch_item_id' => $item->id,
                'employee_id' => $employee->id,
                'employee_name' => $employee->full_name,
                'christmas_subsidy' => $item->christmas_subsidy ?? false,
                'vacation_subsidy' => $item->vacation_subsidy ?? false,
                'additional_bonus' => $item->additional_bonus ?? 0,
            ]);

            // Use centralized PayrollCalculationService
            $calculator = new PayrollCalculationService($employee, $batch->payrollPeriod);
            
            // Apply subsidies and bonuses from batch item if configured
            $calculator->setSubsidies(
                christmas: (bool) ($item->christmas_subsidy ?? false),
                vacation: (bool) ($item->vacation_subsidy ?? false)
            );
            
            if ($item->additional_bonus > 0) {
                $calculator->setAdditionalBonus((float) $item->additional_bonus);
            }
            
            // Calculate all payroll components
            $result = $calculator->calculate();
            
            // Validate calculation result
            if (!isset($result['gross_salary']) || !isset($result['net_salary'])) {
                Log::error('Invalid calculation result - missing required fields', [
                    'employee_id' => $employee->id,
                    'batch_item_id' => $item->id,
                    'result_keys' => array_keys($result),
                ]);
                return null;
            }
            
            // Build payroll data array with ALL fields from calculation service
            $payrollData = [
                'employee_id' => $employee->id,
                'payroll_period_id' => $batch->payroll_period_id,
                'payroll_batch_id' => $batch->id,
                
                // Basic salary
                'basic_salary' => $result['basic_salary'] ?? 0,
                
                // Earnings
                'allowances' => $result['allowances'] ?? 0,
                'overtime' => $result['overtime'] ?? 0,
                'bonuses' => $result['bonuses'] ?? 0,
                'profile_bonus' => $result['profile_bonus'] ?? 0,
                'overtime_amount' => $result['overtime_amount'] ?? 0,
                'gross_salary' => $result['gross_salary'] ?? 0,
                'main_salary' => $result['main_salary'] ?? 0,
                
                // Tax base
                'base_irt_taxable_amount' => $result['base_irt_taxable_amount'] ?? 0,
                
                // Deductions
                'tax' => $result['tax'] ?? 0,
                'deductions_irt' => $result['deductions_irt'] ?? 0,
                'social_security' => $result['social_security'] ?? 0,
                'inss_3_percent' => $result['inss_3_percent'] ?? 0,
                'inss_8_percent' => $result['inss_8_percent'] ?? 0,
                'absence_deduction_amount' => $result['absence_deduction_amount'] ?? 0,
                'deductions' => $result['deductions'] ?? 0,
                'total_deductions_calculated' => $result['total_deductions_calculated'] ?? 0,
                
                // Net salary
                'net_salary' => $result['net_salary'] ?? 0,
                
                // Attendance
                'attendance_hours' => $result['attendance_hours'] ?? 0,
                'leave_days' => 0, // Can be added later
                'maternity_days' => 0,
                'special_leave_days' => 0,
                
                // Payment info
                'payment_method' => $batch->payment_method ?? 'bank_transfer',
                'bank_account' => $employee->bank_account,
                'payment_date' => $batch->batch_date,
                
                // Status
                'status' => Payroll::STATUS_APPROVED,
                'remarks' => $this->generateRemarks($employee, $result, $batch),
                'generated_by' => null, // Batch processing
                'approved_by' => null,
                
                // Detailed components for receipts
                'transport_allowance' => $result['transport_allowance'] ?? 0,
                'food_allowance' => $result['food_allowance'] ?? $result['food_benefit'] ?? 0,
                'family_allowance' => $result['family_allowance'] ?? 0,
                'position_subsidy' => $result['position_subsidy'] ?? 0,
                'performance_subsidy' => $result['performance_subsidy'] ?? 0,
                'additional_bonus' => $result['additional_bonus_amount'] ?? 0,
                'christmas_subsidy_amount' => $result['christmas_subsidy_amount'] ?? 0,
                'vacation_subsidy_amount' => $result['vacation_subsidy_amount'] ?? 0,
                'advance_deduction' => $result['advance_deduction'] ?? 0,
                'late_deduction' => $result['late_deduction'] ?? 0,
                'absence_deduction' => $result['absence_deduction'] ?? 0,
                'total_salary_discounts' => $result['total_salary_discounts'] ?? 0,
                'present_days' => $result['present_days'] ?? 0,
                'absent_days' => $result['absent_days'] ?? 0,
                'late_arrivals' => $result['late_arrivals'] ?? $result['late_days'] ?? 0,
                'total_overtime_hours' => $result['total_overtime_hours'] ?? 0,
                // Night shift allowance (Lei Angola Art. 102º - 25%)
                'night_shift_allowance' => $result['night_shift_allowance'] ?? 0,
                'night_shift_days' => $result['night_shift_days'] ?? 0,
                // IRT calculation fields
                'food_exemption' => $result['exempt_food'] ?? min($result['food_allowance'] ?? $result['food_benefit'] ?? 0, 30000),
                'transport_exemption' => $result['exempt_transport'] ?? min($result['transport_allowance'] ?? 0, 30000),
                'food_taxable' => $result['taxable_food'] ?? max(0, ($result['food_allowance'] ?? $result['food_benefit'] ?? 0) - 30000),
                'transport_taxable' => $result['taxable_transport'] ?? max(0, ($result['transport_allowance'] ?? 0) - 30000),
                'irt_base_before_inss' => ($result['base_irt_taxable_amount'] ?? 0) + ($result['inss_3_percent'] ?? 0),
            ];

            Log::info('Employee payroll calculated successfully', [
                'employee_id' => $employee->id,
                'christmas_subsidy_amount' => $result['christmas_subsidy_amount'] ?? 0,
                'vacation_subsidy_amount' => $result['vacation_subsidy_amount'] ?? 0,
                'additional_bonus' => $result['additional_bonus_amount'] ?? 0,
                'gross_salary' => $result['gross_salary'],
                'net_salary' => $result['net_salary'],
                'total_deductions' => $result['deductions'],
            ]);

            return $payrollData;
            
        } catch (Exception $e) {
            Log::error('Error calculating employee payroll', [
                'employee_id' => $employee->id,
                'batch_id' => $batch->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return null;
        }
    }
    
    /**
     * Generate payroll remarks
     */
    protected function generateRemarks(Employee $employee, array $result, PayrollBatch $batch): string
    {
        $remarks = "Processado via lote: {$batch->name}\n";
        $remarks .= "Presenças: {$result['present_days']}/{$result['monthly_working_days']} dias\n";
        $remarks .= "Horas trabalhadas: {$result['attendance_hours']}h\n";
        
        if ($result['absent_days'] > 0) {
            $remarks .= "Faltas: {$result['absent_days']} dias\n";
        }
        
        if ($result['late_days'] > 0) {
            $remarks .= "Atrasos: {$result['late_days']} dias\n";
        }
        
        if ($result['overtime_amount'] > 0) {
            $remarks .= "Horas extras: " . number_format($result['overtime_amount'], 2) . " AOA\n";
        }
        
        return $remarks;
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
