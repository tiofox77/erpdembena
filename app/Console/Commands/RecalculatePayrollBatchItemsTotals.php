<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\HR\PayrollBatch;
use App\Models\HR\PayrollBatchItem;

class RecalculatePayrollBatchItemsTotals extends Command
{
    protected $signature = 'payroll:recalculate-batch-items-totals';
    protected $description = 'Recalcula os totais de deduções dos items de batch';

    public function handle()
    {
        $this->info('Recalculando totais de batch items...');
        
        $batches = PayrollBatch::whereIn('status', ['completed', 'approved', 'paid'])
            ->with('batchItems.payroll')
            ->get();
        
        $updatedItems = 0;
        $updatedBatches = 0;
        
        foreach ($batches as $batch) {
            $this->line("Processando Batch #{$batch->id} - {$batch->name}");
            
            foreach ($batch->batchItems as $item) {
                if ($item->payroll) {
                    $payroll = $item->payroll;
                    
                    // O campo 'deductions' já contém o total de todas as deduções
                    // Não devemos somar os campos individuais pois causaria duplicação
                    $totalDeductions = $payroll->deductions ?? 0;
                    
                    // Atualizar item se necessário
                    if ($item->total_deductions != $totalDeductions) {
                        $item->total_deductions = $totalDeductions;
                        $item->save();
                        
                        $this->line("  Item #{$item->id} - Deductions: {$totalDeductions}");
                        $updatedItems++;
                    }
                }
            }
            
            // Recalcular totais do batch
            $totals = PayrollBatchItem::where('payroll_batch_id', $batch->id)
                ->selectRaw('
                    SUM(gross_salary) as total_gross,
                    SUM(net_salary) as total_net,
                    SUM(total_deductions) as total_deductions
                ')
                ->first();
            
            $oldDeductions = $batch->total_deductions;
            $oldNet = $batch->total_net_amount;
            
            $batch->update([
                'total_gross_amount' => $totals->total_gross ?? 0,
                'total_net_amount' => $totals->total_net ?? 0,
                'total_deductions' => $totals->total_deductions ?? 0,
            ]);
            
            if ($oldDeductions != $batch->total_deductions || $oldNet != $batch->total_net_amount) {
                $this->line("  Batch totalizados:");
                $this->line("    Gross: {$batch->total_gross_amount}");
                $this->line("    Old Deductions: {$oldDeductions} → New: {$batch->total_deductions}");
                $this->line("    Old Net: {$oldNet} → New: {$batch->total_net_amount}");
                $updatedBatches++;
            }
            
            $this->line('');
        }
        
        $this->info("Processo concluído!");
        $this->info("{$updatedItems} items atualizados");
        $this->info("{$updatedBatches} batches atualizados");
        
        return 0;
    }
}
