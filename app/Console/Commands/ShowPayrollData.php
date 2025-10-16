<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\HR\Payroll;

class ShowPayrollData extends Command
{
    protected $signature = 'payroll:show {id}';
    protected $description = 'Mostra todos os dados de um payroll';

    public function handle()
    {
        $id = $this->argument('id');
        $payroll = Payroll::with('employee')->find($id);
        
        if (!$payroll) {
            $this->error("Payroll #{$id} não encontrado");
            return 1;
        }
        
        $this->info("=== Payroll #{$payroll->id} ===");
        $this->line("Employee: " . ($payroll->employee ? $payroll->employee->full_name : 'N/A'));
        $this->line("");
        
        $this->line("basic_salary: " . number_format($payroll->basic_salary ?? 0, 2));
        $this->line("allowances: " . number_format($payroll->allowances ?? 0, 2));
        $this->line("overtime: " . number_format($payroll->overtime ?? 0, 2));
        $this->line("bonuses: " . number_format($payroll->bonuses ?? 0, 2));
        $this->line("profile_bonus: " . number_format($payroll->profile_bonus ?? 0, 2));
        $this->line("overtime_amount: " . number_format($payroll->overtime_amount ?? 0, 2));
        $this->line("gross_salary: " . number_format($payroll->gross_salary ?? 0, 2));
        $this->line("");
        
        $this->line("deductions (coluna geral): " . number_format($payroll->deductions ?? 0, 2));
        $this->line("tax (IRT): " . number_format($payroll->tax ?? 0, 2));
        $this->line("social_security (INSS geral): " . number_format($payroll->social_security ?? 0, 2));
        $this->line("deductions_irt: " . number_format($payroll->deductions_irt ?? 0, 2));
        $this->line("inss_3_percent: " . number_format($payroll->inss_3_percent ?? 0, 2));
        $this->line("inss_8_percent: " . number_format($payroll->inss_8_percent ?? 0, 2));
        $this->line("absence_deduction_amount: " . number_format($payroll->absence_deduction_amount ?? 0, 2));
        $this->line("total_deductions_calculated: " . number_format($payroll->total_deductions_calculated ?? 0, 2));
        $this->line("");
        
        $this->line("net_salary: " . number_format($payroll->net_salary ?? 0, 2));
        $this->line("");
        
        $this->line("Status: " . $payroll->status);
        $this->line("Period: " . ($payroll->payrollPeriod ? $payroll->payrollPeriod->name : 'N/A'));
        
        return 0;
    }
}
