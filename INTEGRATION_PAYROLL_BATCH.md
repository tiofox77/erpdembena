# Integração do PayrollCalculatorHelper no PayrollBatch

## Arquivo: app/Livewire/HR/PayrollBatch.php

### 1. Adicionar método para calcular item do batch

Adicione este método na classe `PayrollBatch`:

```php
/**
 * Calcular item do batch usando PayrollCalculatorHelper
 */
public function calculateBatchItemWithHelper(PayrollBatchItem $item): array
{
    try {
        $employee = $item->employee;
        $batch = $item->batch;
        $period = $batch->payrollPeriod;
        
        $startDate = \Carbon\Carbon::parse($period->start_date);
        $endDate = \Carbon\Carbon::parse($period->end_date);
        
        // Criar calculator
        $calculator = new \App\Helpers\PayrollCalculatorHelper($employee, $startDate, $endDate);
        
        // Carregar todos os dados
        $calculator->loadAllEmployeeData();
        
        // Configurar subsídios do item
        $calculator->setChristmasSubsidy($item->christmas_subsidy ?? false);
        $calculator->setVacationSubsidy($item->vacation_subsidy ?? false);
        $calculator->setAdditionalBonus($item->additional_bonus ?? 0);
        
        // Calcular
        $results = $calculator->calculate();
        
        // Atualizar item com resultados
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
        
        \Log::info('Item do batch calculado com helper', [
            'item_id' => $item->id,
            'employee_id' => $employee->id,
            'net_salary' => $results['net_salary'],
        ]);
        
        return $results;
        
    } catch (\Exception $e) {
        \Log::error('Erro ao calcular item do batch', [
            'item_id' => $item->id,
            'error' => $e->getMessage(),
        ]);
        
        $item->update([
            'status' => 'error',
            'notes' => 'Erro: ' . $e->getMessage(),
        ]);
        
        throw $e;
    }
}
```

### 2. Atualizar método editItem para usar o helper

Substitua o método `editItem` existente:

```php
/**
 * Abrir modal de edição de item
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
    
    $this->showEditItemModal = true;
}

/**
 * Recalcular item em edição usando helper
 */
public function recalculateEditingItem(): void
{
    if (!$this->editingItem) {
        return;
    }
    
    try {
        $employee = $this->editingItem->employee;
        $period = $this->editingItem->batch->payrollPeriod;
        
        $startDate = \Carbon\Carbon::parse($period->start_date);
        $endDate = \Carbon\Carbon::parse($period->end_date);
        
        // Criar calculator
        $calculator = new \App\Helpers\PayrollCalculatorHelper($employee, $startDate, $endDate);
        
        // Carregar dados
        $calculator->loadAllEmployeeData();
        
        // Configurar subsídios
        $calculator->setChristmasSubsidy($this->edit_christmas_subsidy);
        $calculator->setVacationSubsidy($this->edit_vacation_subsidy);
        $calculator->setAdditionalBonus($this->edit_additional_bonus);
        
        // Calcular
        $this->calculatedData = $calculator->calculate();
        
        \Log::info('Item recalculado em edição', [
            'employee_id' => $employee->id,
            'gross_salary' => $this->calculatedData['gross_salary'],
            'net_salary' => $this->calculatedData['net_salary'],
        ]);
        
    } catch (\Exception $e) {
        \Log::error('Erro ao recalcular item em edição', [
            'item_id' => $this->editingItem->id ?? null,
            'error' => $e->getMessage(),
        ]);
        
        session()->flash('error', 'Erro ao calcular: ' . $e->getMessage());
    }
}

/**
 * Salvar item editado
 */
public function saveEditedItem(): void
{
    if (!$this->editingItem || empty($this->calculatedData)) {
        session()->flash('error', 'Dados de cálculo não disponíveis');
        return;
    }
    
    try {
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
        
        $this->showEditItemModal = false;
        $this->editingItem = null;
        $this->calculatedData = [];
        
        session()->flash('success', 'Item atualizado com sucesso!');
        
    } catch (\Exception $e) {
        \Log::error('Erro ao salvar item editado', [
            'item_id' => $this->editingItem->id ?? null,
            'error' => $e->getMessage(),
        ]);
        
        session()->flash('error', 'Erro ao salvar: ' . $e->getMessage());
    }
}
```

### 3. Adicionar listeners para recalcular automaticamente

Adicione estes métodos para recalcular quando os valores mudam:

```php
/**
 * Recalcular quando bônus adicional muda
 */
public function updatedEditAdditionalBonus(): void
{
    $this->recalculateEditingItem();
}

/**
 * Recalcular quando subsídio de Natal muda
 */
public function updatedEditChristmasSubsidy(): void
{
    $this->recalculateEditingItem();
}

/**
 * Recalcular quando subsídio de férias muda
 */
public function updatedEditVacationSubsidy(): void
{
    $this->recalculateEditingItem();
}
```

### 4. Processar batch completo

Adicione método para processar todos os itens do batch:

```php
/**
 * Processar todos os itens do batch usando helper
 */
public function processBatchWithHelper(int $batchId): void
{
    try {
        $batch = PayrollBatchModel::with(['batchItems.employee', 'payrollPeriod'])->findOrFail($batchId);
        
        $totalProcessed = 0;
        $totalErrors = 0;
        
        foreach ($batch->batchItems as $item) {
            try {
                $this->calculateBatchItemWithHelper($item);
                $totalProcessed++;
            } catch (\Exception $e) {
                $totalErrors++;
            }
        }
        
        // Atualizar status do batch
        $batch->update([
            'status' => $totalErrors > 0 ? 'partially_processed' : 'processed',
            'processed_at' => now(),
        ]);
        
        session()->flash('success', "Batch processado: {$totalProcessed} itens calculados, {$totalErrors} erros");
        
        \Log::info('Batch processado com helper', [
            'batch_id' => $batchId,
            'total_items' => $batch->batchItems->count(),
            'processed' => $totalProcessed,
            'errors' => $totalErrors,
        ]);
        
    } catch (\Exception $e) {
        \Log::error('Erro ao processar batch', [
            'batch_id' => $batchId,
            'error' => $e->getMessage(),
        ]);
        
        session()->flash('error', 'Erro ao processar batch: ' . $e->getMessage());
    }
}
```

## Resumo das Mudanças

### ✅ Métodos Adicionados
1. `calculateBatchItemWithHelper()` - Calcula item individual
2. `recalculateEditingItem()` - Recalcula item em edição
3. `saveEditedItem()` - Salva item editado
4. `updatedEditAdditionalBonus()` - Listener para bônus
5. `updatedEditChristmasSubsidy()` - Listener para Natal
6. `updatedEditVacationSubsidy()` - Listener para férias
7. `processBatchWithHelper()` - Processa batch completo

### ✅ Benefícios
- **Consistência**: Mesmos cálculos em individual e batch
- **Manutenibilidade**: Lógica centralizada no helper
- **Testabilidade**: Fácil de testar
- **Confiabilidade**: Tratamento de erros robusto
- **Logs**: Rastreamento completo de operações

### 📝 Próximos Passos
1. Implementar os métodos acima no `PayrollBatch.php`
2. Testar cálculos com dados reais
3. Comparar resultados com cálculos antigos
4. Remover código legado após validação
