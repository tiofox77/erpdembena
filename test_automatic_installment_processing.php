<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== TESTE: Processamento Automático de Prestações ===\n\n";

$employeeId = 14; // Ana Beatriz Lopes

// 1. Criar uma folha de pagamento de teste
echo "📋 CRIANDO FOLHA DE PAGAMENTO DE TESTE:\n";

try {
    // Criar período único de teste
    $testPeriodName = 'Teste ' . now()->format('Y-m-d H:i:s');
    $period = \App\Models\HR\PayrollPeriod::create([
        'name' => $testPeriodName,
        'start_date' => now()->startOfMonth(),
        'end_date' => now()->endOfMonth(),
        'status' => 'open',
        'payment_date' => now()->endOfMonth()
    ]);
    
    // Criar folha de pagamento de teste  
    $testPayroll = \App\Models\HR\Payroll::create([
        'employee_id' => $employeeId,
        'payroll_period_id' => $period->id,
        'basic_salary' => 175000,
        'net_salary' => 150000,
        'status' => 'draft',
        'generated_by' => 1,
    ]);
    
    echo "✅ Folha criada: ID {$testPayroll->id} - Status: {$testPayroll->status}\n";
    echo "   Período: {$period->name} ({$period->start_date->format('d/m/Y')} - {$period->end_date->format('d/m/Y')})\n\n";
    
    // 2. Verificar advances/discounts pendentes ANTES da aprovação
    echo "📊 ANTES DA APROVAÇÃO:\n";
    
    $advancesBefore = \App\Models\HR\SalaryAdvance::where('employee_id', $employeeId)
        ->where('status', 'approved')
        ->where('remaining_installments', '>', 0)
        ->get();
    
    echo "   Salary Advances pendentes: " . $advancesBefore->count() . "\n";
    foreach($advancesBefore as $advance) {
        echo "      - ID {$advance->id}: {$advance->remaining_installments} parcelas restantes ({$advance->installment_amount} KZ cada)\n";
    }
    
    $discountsBefore = \App\Models\HR\SalaryDiscount::where('employee_id', $employeeId)
        ->where('status', 'approved')
        ->where('remaining_installments', '>', 0)
        ->get();
    
    echo "   Salary Discounts pendentes: " . $discountsBefore->count() . "\n";
    foreach($discountsBefore as $discount) {
        echo "      - ID {$discount->id}: {$discount->remaining_installments} parcelas restantes ({$discount->installment_amount} KZ cada)\n";
    }
    
    // 3. Simular aprovação da folha
    echo "\n🔄 SIMULANDO APROVAÇÃO DA FOLHA:\n";
    
    // Instanciar o componente Livewire e simular aprovação
    $payrollComponent = new \App\Livewire\HR\Payroll();
    $payrollComponent->payroll_id = $testPayroll->id;
    
    // Executar método approve
    $payrollComponent->approve();
    
    // Recarregar dados
    $testPayroll->refresh();
    
    echo "✅ Folha aprovada: Status = {$testPayroll->status}\n";
    echo "   Aprovado por: {$testPayroll->approved_by}\n\n";
    
    // 4. Verificar advances/discounts DEPOIS da aprovação
    echo "📊 DEPOIS DA APROVAÇÃO:\n";
    
    $advancesAfter = \App\Models\HR\SalaryAdvance::where('employee_id', $employeeId)
        ->where('status', 'approved')
        ->where('remaining_installments', '>', 0)
        ->get();
    
    echo "   Salary Advances pendentes: " . $advancesAfter->count() . "\n";
    foreach($advancesAfter as $advance) {
        echo "      - ID {$advance->id}: {$advance->remaining_installments} parcelas restantes\n";
        
        // Verificar pagamentos recentes
        $recentPayments = $advance->payments()
            ->where('payment_date', '>=', now()->subDays(1))
            ->get();
        
        foreach($recentPayments as $payment) {
            echo "        ✅ Pagamento: {$payment->amount} KZ em {$payment->payment_date->format('d/m/Y')} - {$payment->notes}\n";
        }
    }
    
    $discountsAfter = \App\Models\HR\SalaryDiscount::where('employee_id', $employeeId)
        ->where('status', 'approved')
        ->where('remaining_installments', '>', 0)
        ->get();
    
    echo "   Salary Discounts pendentes: " . $discountsAfter->count() . "\n";
    foreach($discountsAfter as $discount) {
        echo "      - ID {$discount->id}: {$discount->remaining_installments} parcelas restantes\n";
        
        // Verificar pagamentos recentes
        $recentPayments = $discount->payments()
            ->where('payment_date', '>=', now()->subDays(1))
            ->get();
        
        foreach($recentPayments as $payment) {
            echo "        ✅ Pagamento: {$payment->amount} KZ em {$payment->payment_date->format('d/m/Y')} - {$payment->notes}\n";
        }
    }
    
    // 5. Verificar se houve processamento
    echo "\n📈 RESULTADOS:\n";
    $advancesProcessed = $advancesBefore->count() - $advancesAfter->count();
    $discountsProcessed = $discountsBefore->count() - $discountsAfter->count();
    
    echo "   Advances processados: {$advancesProcessed}\n";
    echo "   Discounts processados: {$discountsProcessed}\n";
    
    if ($advancesProcessed > 0 || $discountsProcessed > 0) {
        echo "   ✅ SUCESSO: Prestações foram processadas automaticamente!\n";
    } else {
        echo "   ⚠️  ATENÇÃO: Nenhuma prestação foi processada\n";
    }
    
    // 6. Limpar dados de teste
    echo "\n🗑️  LIMPANDO DADOS DE TESTE:\n";
    $testPayroll->delete();
    echo "   Folha de pagamento de teste removida\n";
    
} catch (\Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}

echo "\n=== TESTE CONCLUÍDO ===\n";
