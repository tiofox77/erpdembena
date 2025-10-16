<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\HR\Payroll;

class RecalculatePayrollNetSalary extends Command
{
    protected $signature = 'payroll:recalculate-net-salary';
    protected $description = 'Recalcula o salário líquido dos payrolls';

    public function handle()
    {
        $this->info('Recalculando salários líquidos...');
        
        $payrolls = Payroll::whereIn('status', ['paid', 'approved'])->get();
        
        $updated = 0;
        foreach ($payrolls as $payroll) {
            $oldNet = $payroll->net_salary;
            
            // Recalcular: net = gross - deductions
            $calculatedNet = $payroll->gross_salary - $payroll->deductions;
            
            // Só atualizar se estiver zerado ou diferente
            if ($payroll->net_salary == 0 || abs($payroll->net_salary - $calculatedNet) > 0.01) {
                $payroll->net_salary = $calculatedNet;
                $payroll->save();
                
                $employeeName = $payroll->employee ? $payroll->employee->full_name : 'N/A';
                $this->line("Payroll #{$payroll->id} - {$employeeName}");
                $this->line("  Gross: {$payroll->gross_salary}");
                $this->line("  Deductions: {$payroll->deductions}");
                $this->line("  Old Net: {$oldNet}");
                $this->line("  New Net: {$calculatedNet}");
                $this->line('');
                
                $updated++;
            }
        }
        
        $this->info("Processo concluído! {$updated} payrolls atualizados de {$payrolls->count()} total.");
        
        return 0;
    }
}
