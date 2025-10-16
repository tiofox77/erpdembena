<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\HR\PayrollBatchItem;

class CheckPayrollDeductions extends Command
{
    protected $signature = 'payroll:check-deductions {item_id}';
    protected $description = 'Verifica as deduções de um payroll batch item';

    public function handle()
    {
        $itemId = $this->argument('item_id');
        $item = PayrollBatchItem::with('payroll')->find($itemId);
        
        if (!$item || !$item->payroll) {
            $this->error("Item #{$itemId} não encontrado ou sem payroll associado");
            return 1;
        }
        
        $p = $item->payroll;
        
        $this->info("=== Payroll Data ===");
        $this->line("Gross Salary: " . number_format($p->gross_salary ?? 0, 2));
        $this->line("");
        
        $this->line("Deductions (coluna geral): " . number_format($p->deductions ?? 0, 2));
        $this->line("Tax (IRT): " . number_format($p->tax ?? 0, 2));
        $this->line("Social Security (INSS geral): " . number_format($p->social_security ?? 0, 2));
        $this->line("Deductions IRT: " . number_format($p->deductions_irt ?? 0, 2));
        $this->line("INSS 3%: " . number_format($p->inss_3_percent ?? 0, 2));
        $this->line("INSS 8%: " . number_format($p->inss_8_percent ?? 0, 2));
        $this->line("Absence Deduction: " . number_format($p->absence_deduction_amount ?? 0, 2));
        $this->line("");
        
        $totalAll = ($p->deductions ?? 0) + ($p->tax ?? 0) + ($p->social_security ?? 0) +
                   ($p->deductions_irt ?? 0) + ($p->inss_3_percent ?? 0) + ($p->inss_8_percent ?? 0) +
                   ($p->absence_deduction_amount ?? 0);
        
        $this->line("Total somando TUDO: " . number_format($totalAll, 2));
        $this->line("");
        
        $this->line("Net Salary (registrado): " . number_format($p->net_salary ?? 0, 2));
        $this->line("Net Calculado (gross - total_all): " . number_format(($p->gross_salary ?? 0) - $totalAll, 2));
        
        return 0;
    }
}
