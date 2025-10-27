<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\HR\PayrollBatch;
use App\Models\HR\PayrollBatchItem;

class RecalculateBatchTotalsFromItems extends Command
{
    protected $signature = 'payroll:recalculate-batch-totals-from-items';
    protected $description = 'Recalcula os totais dos batches a partir dos items';

    public function handle()
    {
        $this->info('Recalculando totais dos batches a partir dos items...');
        
        $batches = PayrollBatch::whereIn('status', ['completed', 'approved', 'paid'])->get();
        
        $updated = 0;
        foreach ($batches as $batch) {
            $this->line("Processando Batch #{$batch->id} - {$batch->name}");
            
            // Somar a partir dos payroll_batch_items
            $totals = PayrollBatchItem::where('payroll_batch_id', $batch->id)
                ->selectRaw('
                    SUM(COALESCE(gross_salary, 0)) as total_gross,
                    SUM(COALESCE(net_salary, 0)) as total_net,
                    SUM(COALESCE(total_deductions, 0)) as total_deductions,
                    COUNT(*) as item_count
                ')
                ->first();
            
            $this->line("  Items: {$totals->item_count}");
            $this->line("  Gross: {$totals->total_gross}");
            $this->line("  Deductions: {$totals->total_deductions}");
            $this->line("  Net: {$totals->total_net}");
            
            $batch->update([
                'total_gross_amount' => $totals->total_gross ?? 0,
                'total_net_amount' => $totals->total_net ?? 0,
                'total_deductions' => $totals->total_deductions ?? 0,
            ]);
            
            $this->line('');
            $updated++;
        }
        
        $this->info("Processo concluído! {$updated} batches atualizados.");
        
        return 0;
    }
}
