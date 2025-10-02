<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Models\HR\PayrollBatch;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class PayrollBatchProcessedListener implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle($event): void
    {
        $batchId = $event;
        
        try {
            $batch = PayrollBatch::find($batchId);
            
            if ($batch) {
                Log::info('Payroll batch processed event handled', [
                    'batch_id' => $batch->id,
                    'batch_name' => $batch->name,
                    'status' => $batch->status,
                    'processed_employees' => $batch->processed_employees,
                    'total_employees' => $batch->total_employees,
                ]);
                
                // Here you could send notifications, update external systems,
                // or perform any other post-processing tasks
                
                // Example: Send notification to admin users
                // Notification::send(User::role('admin')->get(), new PayrollBatchProcessedNotification($batch));
            }
        } catch (\Exception $e) {
            Log::error('Error handling payroll batch processed event', [
                'batch_id' => $batchId,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
