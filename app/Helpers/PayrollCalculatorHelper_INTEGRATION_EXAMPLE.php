<?php

/**
 * EXEMPLO DE INTEGRAÇÃO DO PayrollCalculatorHelper
 * 
 * Este arquivo demonstra como integrar o PayrollCalculatorHelper
 * no componente PayrollBatch para garantir cálculos consistentes
 */

namespace App\Livewire\HR;

use App\Helpers\PayrollCalculatorHelper;
use App\Models\HR\PayrollBatchItem;
use App\Models\HR\Employee;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * EXEMPLO 1: Calcular item individual do batch
 */
class PayrollBatchExample
{
    /**
     * Calcular payroll para um item do batch usando o helper
     */
    public function calculateBatchItem(PayrollBatchItem $item): array
    {
        $employee = $item->employee;
        $batch = $item->batch;
        
        // Obter período do batch
        $period = $batch->payrollPeriod;
        $startDate = Carbon::parse($period->start_date);
        $endDate = Carbon::parse($period->end_date);
        
        // Criar calculator
        $calculator = new PayrollCalculatorHelper($employee, $startDate, $endDate);
        
        // Carregar todos os dados
        $calculator->loadAllEmployeeData();
        
        // Configurar subsídios do item (se existirem)
        if (isset($item->christmas_subsidy)) {
            $calculator->setChristmasSubsidy((bool) $item->christmas_subsidy);
        }
        
        if (isset($item->vacation_subsidy)) {
            $calculator->setVacationSubsidy((bool) $item->vacation_subsidy);
        }
        
        if (isset($item->additional_bonus)) {
            $calculator->setAdditionalBonus((float) $item->additional_bonus);
        }
        
        // Calcular tudo
        $results = $calculator->calculate();
        
        // Atualizar item do batch com os resultados
        $item->update([
            'basic_salary' => $results['basic_salary'],
            'transport_allowance' => $results['transport_allowance'],
            'food_allowance' => $results['food_benefit'],
            'overtime_amount' => $results['total_overtime_amount'],
            'bonus_amount' => $results['bonus_amount'],
            'christmas_subsidy_amount' => $results['christmas_subsidy_amount'],
            'vacation_subsidy_amount' => $results['vacation_subsidy_amount'],
            'gross_salary' => $results['gross_salary'],
            'inss_deduction' => $results['inss_3_percent'],
            'irt_deduction' => $results['irt'],
            'advance_deduction' => $results['advance_deduction'],
            'discount_deduction' => $results['total_salary_discounts'],
            'late_deduction' => $results['late_deduction'],
            'absence_deduction' => $results['absence_deduction'],
            'total_deductions' => $results['total_deductions'],
            'net_salary' => $results['net_salary'],
            'present_days' => $results['present_days'],
            'absent_days' => $results['absent_days'],
            'late_days' => $results['late_arrivals'],
            'total_working_days' => $results['total_working_days'],
        ]);
        
        return $results;
    }
    
    /**
     * Recalcular item quando subsídios são alterados
     */
    public function recalculateItemWithSubsidies(
        PayrollBatchItem $item,
        bool $christmasSubsidy,
        bool $vacationSubsidy,
        float $additionalBonus
    ): array {
        $employee = $item->employee;
        $batch = $item->batch;
        
        $period = $batch->payrollPeriod;
        $startDate = Carbon::parse($period->start_date);
        $endDate = Carbon::parse($period->end_date);
        
        // Criar calculator
        $calculator = new PayrollCalculatorHelper($employee, $startDate, $endDate);
        
        // Carregar dados
        $calculator->loadAllEmployeeData();
        
        // Configurar subsídios
        $calculator->setChristmasSubsidy($christmasSubsidy);
        $calculator->setVacationSubsidy($vacationSubsidy);
        $calculator->setAdditionalBonus($additionalBonus);
        
        // Calcular
        $results = $calculator->calculate();
        
        // Atualizar item
        $item->update([
            'christmas_subsidy' => $christmasSubsidy,
            'vacation_subsidy' => $vacationSubsidy,
            'additional_bonus' => $additionalBonus,
            'christmas_subsidy_amount' => $results['christmas_subsidy_amount'],
            'vacation_subsidy_amount' => $results['vacation_subsidy_amount'],
            'gross_salary' => $results['gross_salary'],
            'total_deductions' => $results['total_deductions'],
            'net_salary' => $results['net_salary'],
        ]);
        
        return $results;
    }
}

/**
 * EXEMPLO 2: Integração no método editItem do PayrollBatch
 */
class PayrollBatchEditItemExample
{
    public PayrollBatchItem $editingItem;
    public float $edit_additional_bonus = 0;
    public bool $edit_christmas_subsidy = false;
    public bool $edit_vacation_subsidy = false;
    public array $calculatedData = [];
    
    /**
     * Abrir modal de edição e calcular valores
     */
    public function editItem(int $itemId): void
    {
        $this->editingItem = PayrollBatchItem::with(['employee', 'batch.payrollPeriod'])->findOrFail($itemId);
        
        // Carregar valores atuais
        $this->edit_additional_bonus = $this->editingItem->additional_bonus ?? 0;
        $this->edit_christmas_subsidy = $this->editingItem->christmas_subsidy ?? false;
        $this->edit_vacation_subsidy = $this->editingItem->vacation_subsidy ?? false;
        
        // Calcular valores usando o helper
        $this->recalculateEditingItem();
        
        // Abrir modal
        $this->showEditItemModal = true;
    }
    
    /**
     * Recalcular quando valores são alterados
     */
    public function recalculateEditingItem(): void
    {
        if (!$this->editingItem) {
            return;
        }
        
        $employee = $this->editingItem->employee;
        $period = $this->editingItem->batch->payrollPeriod;
        
        $startDate = Carbon::parse($period->start_date);
        $endDate = Carbon::parse($period->end_date);
        
        // Criar calculator
        $calculator = new PayrollCalculatorHelper($employee, $startDate, $endDate);
        
        // Carregar dados
        $calculator->loadAllEmployeeData();
        
        // Configurar subsídios
        $calculator->setChristmasSubsidy($this->edit_christmas_subsidy);
        $calculator->setVacationSubsidy($this->edit_vacation_subsidy);
        $calculator->setAdditionalBonus($this->edit_additional_bonus);
        
        // Calcular
        $this->calculatedData = $calculator->calculate();
        
        Log::info('Item recalculado', [
            'employee_id' => $employee->id,
            'gross_salary' => $this->calculatedData['gross_salary'],
            'net_salary' => $this->calculatedData['net_salary'],
        ]);
    }
    
    /**
     * Salvar alterações do item
     */
    public function saveEditedItem(): void
    {
        if (!$this->editingItem || empty($this->calculatedData)) {
            return;
        }
        
        // Atualizar item com valores calculados
        $this->editingItem->update([
            'additional_bonus' => $this->edit_additional_bonus,
            'christmas_subsidy' => $this->edit_christmas_subsidy,
            'vacation_subsidy' => $this->edit_vacation_subsidy,
            'christmas_subsidy_amount' => $this->calculatedData['christmas_subsidy_amount'],
            'vacation_subsidy_amount' => $this->calculatedData['vacation_subsidy_amount'],
            'basic_salary' => $this->calculatedData['basic_salary'],
            'transport_allowance' => $this->calculatedData['transport_allowance'],
            'food_allowance' => $this->calculatedData['food_benefit'],
            'overtime_amount' => $this->calculatedData['total_overtime_amount'],
            'bonus_amount' => $this->calculatedData['bonus_amount'],
            'gross_salary' => $this->calculatedData['gross_salary'],
            'inss_deduction' => $this->calculatedData['inss_3_percent'],
            'irt_deduction' => $this->calculatedData['irt'],
            'advance_deduction' => $this->calculatedData['advance_deduction'],
            'discount_deduction' => $this->calculatedData['total_salary_discounts'],
            'late_deduction' => $this->calculatedData['late_deduction'],
            'absence_deduction' => $this->calculatedData['absence_deduction'],
            'total_deductions' => $this->calculatedData['total_deductions'],
            'net_salary' => $this->calculatedData['net_salary'],
            'present_days' => $this->calculatedData['present_days'],
            'absent_days' => $this->calculatedData['absent_days'],
            'late_days' => $this->calculatedData['late_arrivals'],
        ]);
        
        // Fechar modal
        $this->showEditItemModal = false;
        
        // Mensagem de sucesso
        session()->flash('success', 'Item atualizado com sucesso!');
    }
    
    /**
     * Listeners para recalcular automaticamente
     */
    public function updatedEditAdditionalBonus(): void
    {
        $this->recalculateEditingItem();
    }
    
    public function updatedEditChristmasSubsidy(): void
    {
        $this->recalculateEditingItem();
    }
    
    public function updatedEditVacationSubsidy(): void
    {
        $this->recalculateEditingItem();
    }
}

/**
 * EXEMPLO 3: Processar batch completo
 */
class ProcessPayrollBatchExample
{
    /**
     * Processar todos os itens de um batch
     */
    public function processBatch(int $batchId): void
    {
        $batch = \App\Models\HR\PayrollBatch::with(['batchItems.employee', 'payrollPeriod'])->findOrFail($batchId);
        
        $period = $batch->payrollPeriod;
        $startDate = Carbon::parse($period->start_date);
        $endDate = Carbon::parse($period->end_date);
        
        $totalProcessed = 0;
        $totalErrors = 0;
        
        foreach ($batch->batchItems as $item) {
            try {
                $employee = $item->employee;
                
                // Criar calculator
                $calculator = new PayrollCalculatorHelper($employee, $startDate, $endDate);
                
                // Carregar dados
                $calculator->loadAllEmployeeData();
                
                // Configurar subsídios
                $calculator->setChristmasSubsidy($item->christmas_subsidy ?? false);
                $calculator->setVacationSubsidy($item->vacation_subsidy ?? false);
                $calculator->setAdditionalBonus($item->additional_bonus ?? 0);
                
                // Calcular
                $results = $calculator->calculate();
                
                // Atualizar item
                $item->update([
                    'basic_salary' => $results['basic_salary'],
                    'transport_allowance' => $results['transport_allowance'],
                    'food_allowance' => $results['food_benefit'],
                    'overtime_amount' => $results['total_overtime_amount'],
                    'bonus_amount' => $results['bonus_amount'],
                    'christmas_subsidy_amount' => $results['christmas_subsidy_amount'],
                    'vacation_subsidy_amount' => $results['vacation_subsidy_amount'],
                    'gross_salary' => $results['gross_salary'],
                    'inss_deduction' => $results['inss_3_percent'],
                    'irt_deduction' => $results['irt'],
                    'advance_deduction' => $results['advance_deduction'],
                    'discount_deduction' => $results['total_salary_discounts'],
                    'late_deduction' => $results['late_deduction'],
                    'absence_deduction' => $results['absence_deduction'],
                    'total_deductions' => $results['total_deductions'],
                    'net_salary' => $results['net_salary'],
                    'present_days' => $results['present_days'],
                    'absent_days' => $results['absent_days'],
                    'late_days' => $results['late_arrivals'],
                    'total_working_days' => $results['total_working_days'],
                    'status' => 'calculated',
                ]);
                
                $totalProcessed++;
                
                Log::info('Item processado com sucesso', [
                    'employee_id' => $employee->id,
                    'employee_name' => $employee->full_name,
                    'net_salary' => $results['net_salary'],
                ]);
                
            } catch (\Exception $e) {
                $totalErrors++;
                
                Log::error('Erro ao processar item do batch', [
                    'item_id' => $item->id,
                    'employee_id' => $item->employee_id,
                    'error' => $e->getMessage(),
                ]);
                
                $item->update([
                    'status' => 'error',
                    'notes' => 'Erro: ' . $e->getMessage(),
                ]);
            }
        }
        
        // Atualizar status do batch
        $batch->update([
            'status' => $totalErrors > 0 ? 'partially_processed' : 'processed',
            'processed_at' => now(),
        ]);
        
        Log::info('Batch processado', [
            'batch_id' => $batchId,
            'total_items' => $batch->batchItems->count(),
            'processed' => $totalProcessed,
            'errors' => $totalErrors,
        ]);
    }
}

/**
 * EXEMPLO 4: Uso no Job de processamento assíncrono
 */
namespace App\Jobs;

use App\Helpers\PayrollCalculatorHelper;
use App\Models\HR\PayrollBatch;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessPayrollBatchJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    protected int $batchId;
    
    public function __construct(int $batchId)
    {
        $this->batchId = $batchId;
    }
    
    public function handle(): void
    {
        $batch = PayrollBatch::with(['batchItems.employee', 'payrollPeriod'])->findOrFail($this->batchId);
        
        $period = $batch->payrollPeriod;
        $startDate = Carbon::parse($period->start_date);
        $endDate = Carbon::parse($period->end_date);
        
        foreach ($batch->batchItems as $item) {
            try {
                $employee = $item->employee;
                
                // Usar o helper para calcular
                $calculator = new PayrollCalculatorHelper($employee, $startDate, $endDate);
                $calculator->loadAllEmployeeData();
                $calculator->setChristmasSubsidy($item->christmas_subsidy ?? false);
                $calculator->setVacationSubsidy($item->vacation_subsidy ?? false);
                $calculator->setAdditionalBonus($item->additional_bonus ?? 0);
                
                $results = $calculator->calculate();
                
                // Atualizar item
                $item->update([
                    'basic_salary' => $results['basic_salary'],
                    'gross_salary' => $results['gross_salary'],
                    'net_salary' => $results['net_salary'],
                    'total_deductions' => $results['total_deductions'],
                    'inss_deduction' => $results['inss_3_percent'],
                    'irt_deduction' => $results['irt'],
                    'status' => 'calculated',
                ]);
                
            } catch (\Exception $e) {
                Log::error('Erro no job de processamento', [
                    'item_id' => $item->id,
                    'error' => $e->getMessage(),
                ]);
                
                $item->update(['status' => 'error']);
            }
        }
        
        $batch->update([
            'status' => 'processed',
            'processed_at' => now(),
        ]);
    }
}
