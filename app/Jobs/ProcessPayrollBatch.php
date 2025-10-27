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
     * Process payroll for individual employee using centralized calculation service
     */
    protected function processEmployeePayroll(Employee $employee, PayrollBatch $batch): ?array
    {
        try {
            Log::info('Processing employee payroll', [
                'batch_id' => $batch->id,
                'employee_id' => $employee->id,
                'employee_name' => $employee->full_name,
            ]);

            // Use centralized PayrollCalculationService
            $calculator = new PayrollCalculationService($employee, $batch->payrollPeriod);
            
            // Set subsidies if needed (could be configured per batch or employee)
            // For now, not applying subsidies automatically in batch processing
            // $calculator->setSubsidies(christmas: false, vacation: false);
            
            // Calculate all payroll components
            $result = $calculator->calculate();
            
            // Build payroll data array with ALL fields from calculation service
            $payrollData = [
                'employee_id' => $employee->id,
                'payroll_period_id' => $batch->payroll_period_id,
                'payroll_batch_id' => $batch->id,
                
                // Basic salary
                'basic_salary' => $result['basic_salary'],
                
                // Earnings
                'allowances' => $result['allowances'],
                'overtime' => $result['overtime'],
                'bonuses' => $result['bonuses'],
                'profile_bonus' => $result['profile_bonus'],
                'overtime_amount' => $result['overtime_amount'],
                'gross_salary' => $result['gross_salary'],
                'main_salary' => $result['main_salary'],
                
                // Tax base
                'base_irt_taxable_amount' => $result['base_irt_taxable_amount'],
                
                // Deductions
                'tax' => $result['tax'],
                'deductions_irt' => $result['deductions_irt'],
                'social_security' => $result['social_security'],
                'inss_3_percent' => $result['inss_3_percent'],
                'inss_8_percent' => $result['inss_8_percent'],
                'absence_deduction_amount' => $result['absence_deduction_amount'],
                'deductions' => $result['deductions'],
                'total_deductions_calculated' => $result['total_deductions_calculated'],
                
                // Net salary
                'net_salary' => $result['net_salary'],
                
                // Attendance
                'attendance_hours' => $result['attendance_hours'],
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
            ];

            Log::info('Employee payroll calculated successfully', [
                'employee_id' => $employee->id,
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
