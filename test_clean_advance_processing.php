<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== TESTE: Processamento com Salary Advance Limpo ===\n\n";

$employeeId = 14; // Ana Beatriz Lopes

try {
    // 1. Criar salary advance de teste
    echo "📋 CRIANDO SALARY ADVANCE DE TESTE:\n";
    $testAdvance = \App\Models\HR\SalaryAdvance::create([
        'employee_id' => $employeeId,
        'amount' => 50000,
        'installments' => 3,
        'installment_amount' => 16666.67,
        'remaining_installments' => 3,
        'reason' => 'Teste automático - advance processing',
        'request_date' => now(),
        'first_deduction_date' => now()->addMonth(), // Próximo mês
        'status' => 'approved',
        'approved_by' => 1,
        'approved_at' => now()
    ]);
    
    echo "✅ Advance criado: ID {$testAdvance->id}\n";
    echo "   Amount: {$testAdvance->amount} KZ\n";
    echo "   Installments: {$testAdvance->installments}\n"; 
    echo "   Remaining: {$testAdvance->remaining_installments}\n";
    echo "   First Deduction: {$testAdvance->first_deduction_date->format('d/m/Y')}\n\n";
    
    // 2. Criar período futuro para teste
    echo "📅 CRIANDO PERÍODO FUTURO:\n";
    $futureDate = now()->addMonth();
    $testPeriodName = 'Teste ' . $futureDate->format('Y-m-d H:i:s');
    $period = \App\Models\HR\PayrollPeriod::create([
        'name' => $testPeriodName,
        'start_date' => $futureDate->startOfMonth(),
        'end_date' => $futureDate->endOfMonth(),
        'status' => 'open',
        'payment_date' => $futureDate->endOfMonth()
    ]);
    
    echo "✅ Período criado: {$period->name}\n";
    echo "   Start: {$period->start_date->format('d/m/Y')}\n";
    echo "   End: {$period->end_date->format('d/m/Y')}\n\n";
    
    // 3. Criar folha de pagamento de teste
    echo "💰 CRIANDO FOLHA DE PAGAMENTO:\n";
    $testPayroll = \App\Models\HR\Payroll::create([
        'employee_id' => $employeeId,
        'payroll_period_id' => $period->id,
        'basic_salary' => 175000,
        'net_salary' => 150000,
        'status' => 'draft',
        'generated_by' => 1,
    ]);
    
    echo "✅ Folha criada: ID {$testPayroll->id}\n\n";
    
    // 4. Verificar estado ANTES da aprovação
    echo "📊 ANTES DA APROVAÇÃO:\n";
    $testAdvance->refresh();
    echo "   Advance ID {$testAdvance->id}: {$testAdvance->remaining_installments} parcelas restantes\n";
    
    $existingPayments = $testAdvance->payments()
        ->whereBetween('payment_date', [$period->start_date, $period->end_date])
        ->count();
    echo "   Pagamentos no período: {$existingPayments}\n\n";
    
    // 5. Aprovar folha
    echo "🔄 APROVANDO FOLHA:\n";
    $payrollComponent = new \App\Livewire\HR\Payroll();
    $payrollComponent->payroll_id = $testPayroll->id;
    $payrollComponent->approve();
    
    $testPayroll->refresh();
    echo "✅ Folha aprovada: Status = {$testPayroll->status}\n\n";
    
    // 6. Verificar estado DEPOIS da aprovação
    echo "📊 DEPOIS DA APROVAÇÃO:\n";
    $testAdvance->refresh();
    echo "   Advance ID {$testAdvance->id}: {$testAdvance->remaining_installments} parcelas restantes\n";
    
    $newPayments = $testAdvance->payments()
        ->whereBetween('payment_date', [$period->start_date, $period->end_date])
        ->get();
    echo "   Pagamentos no período: " . $newPayments->count() . "\n";
    
    foreach($newPayments as $payment) {
        echo "      ✅ {$payment->amount} KZ em {$payment->payment_date->format('d/m/Y')} - {$payment->notes}\n";
    }
    
    // 7. Resultado
    echo "\n📈 RESULTADO:\n";
    if ($newPayments->count() > $existingPayments) {
        echo "   ✅ SUCESSO: Salary Advance foi processado automaticamente!\n";
        echo "   Parcelas antes: 3, depois: {$testAdvance->remaining_installments}\n";
    } else {
        echo "   ❌ FALHA: Salary Advance não foi processado\n";
    }
    
    // 8. Limpar dados de teste
    echo "\n🗑️  LIMPANDO DADOS DE TESTE:\n";
    $testAdvance->payments()->delete();
    $testAdvance->delete();
    $testPayroll->delete();
    $period->delete();
    echo "   Dados de teste removidos\n";
    
} catch (\Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}

echo "\n=== TESTE CONCLUÍDO ===\n";
