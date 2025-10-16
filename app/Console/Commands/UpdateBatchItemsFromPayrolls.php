<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\HR\PayrollBatch;

class UpdateBatchItemsFromPayrolls extends Command
{
    protected $signature = 'payroll:update-batch-items-from-payrolls';
    protected $description = 'Atualiza os batch items com dados dos payrolls';

    public function handle()
    {
        $this->info('Atualizando batch items com dados dos payrolls...');
        
        $batches = PayrollBatch::whereIn('status', ['completed', 'approved', 'paid'])
            ->with('batchItems.payroll')
            ->get();
        
        $updatedItems = 0;
        
        foreach ($batches as $batch) {
            $this->line("Processando Batch #{$batch->id} - {$batch->name}");
            
            foreach ($batch->batchItems as $item) {
                if ($item->payroll) {
                    $payroll = $item->payroll;
                    
                    $changed = false;
                    
                    // Atualizar gross_salary
                    if ($item->gross_salary != $payroll->gross_salary) {
                        $item->gross_salary = $payroll->gross_salary;
                        $changed = true;
                    }
                    
                    // Atualizar net_salary
                    if ($item->net_salary != $payroll->net_salary) {
                        $item->net_salary = $payroll->net_salary;
                        $changed = true;
                    }
                    
                    // Atualizar total_deductions
                    if ($item->total_deductions != $payroll->deductions) {
                        $item->total_deductions = $payroll->deductions;
                        $changed = true;
                    }
                    
                    if ($changed) {
                        $item->save();
                        $employeeName = $payroll->employee ? $payroll->employee->full_name : 'N/A';
                        $this->line("  Item #{$item->id} - {$employeeName}");
                        $this->line("    Gross: {$item->gross_salary}");
                        $this->line("    Net: {$item->net_salary}");
                        $this->line("    Deductions: {$item->total_deductions}");
                        $updatedItems++;
                    }
                }
            }
            
            $this->line('');
        }
        
        $this->info("Processo concluído! {$updatedItems} items atualizados.");
        
        return 0;
    }
}
