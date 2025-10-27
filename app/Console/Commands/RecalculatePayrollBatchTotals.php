<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\HR\PayrollBatch;

class RecalculatePayrollBatchTotals extends Command
{
    protected $signature = 'payroll:recalculate-batch-totals';
    protected $description = 'Recalcula os totais líquidos dos batches de payroll';

    public function handle()
    {
        $this->info('Recalculando totais de batches...');
        
        $batches = PayrollBatch::whereIn('status', ['completed', 'approved', 'paid'])->get();
        
        $updated = 0;
        foreach ($batches as $batch) {
            $oldNetAmount = $batch->total_net_amount;
            
            // Recalcular: net = gross - deductions
            $calculatedNet = $batch->total_gross_amount - $batch->total_deductions;
            
            // Só atualizar se estiver diferente ou zerado
            if ($batch->total_net_amount == 0 || abs($batch->total_net_amount - $calculatedNet) > 0.01) {
                $batch->total_net_amount = $calculatedNet;
                $batch->save();
                
                $this->line("Batch #{$batch->id} - {$batch->name}");
                $this->line("  Gross: {$batch->total_gross_amount}");
                $this->line("  Deductions: {$batch->total_deductions}");
                $this->line("  Old Net: {$oldNetAmount}");
                $this->line("  New Net: {$calculatedNet}");
                $this->line('');
                
                $updated++;
            }
        }
        
        $this->info("Processo concluído! {$updated} batches atualizados de {$batches->count()} total.");
        
        return 0;
    }
}
