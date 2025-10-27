<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== DEBUG: Lógica de Aprovação da Folha de Pagamento ===\n\n";

$employeeId = 14; // Ana Beatriz Lopes

// 1. Verificar folha de pagamento pendente
echo "📋 FOLHAS DE PAGAMENTO PENDENTES:\n";
$pendingPayrolls = \App\Models\HR\Payroll::where('employee_id', $employeeId)
    ->where('status', 'pending')
    ->get();

foreach($pendingPayrolls as $payroll) {
    echo "   ID: {$payroll->id} - Status: {$payroll->status} - Período: " . 
         $payroll->payrollPeriod?->name ?? 'N/A' . "\n";
}

// 2. Verificar salary advances pendentes
echo "\n💰 SALARY ADVANCES PENDENTES:\n";
$pendingAdvances = \App\Models\HR\SalaryAdvance::where('employee_id', $employeeId)
    ->where('status', 'approved')
    ->where('remaining_installments', '>', 0)
    ->get();

foreach($pendingAdvances as $advance) {
    echo "   ID: {$advance->id} - Valor: {$advance->amount} KZ - Parcelas restantes: {$advance->remaining_installments}\n";
    echo "      Valor parcela: {$advance->installment_amount} KZ\n";
    echo "      Primeira dedução: {$advance->first_deduction_date->format('d/m/Y')}\n";
}

// 3. Verificar salary discounts pendentes
echo "\n💸 SALARY DISCOUNTS PENDENTES:\n";
$pendingDiscounts = \App\Models\HR\SalaryDiscount::where('employee_id', $employeeId)
    ->where('status', 'approved')
    ->where('remaining_installments', '>', 0)
    ->get();

foreach($pendingDiscounts as $discount) {
    echo "   ID: {$discount->id} - Valor: {$discount->amount} KZ - Parcelas restantes: {$discount->remaining_installments}\n";
    echo "      Valor parcela: {$discount->installment_amount} KZ\n";
    echo "      Primeira dedução: {$discount->first_deduction_date->format('d/m/Y')}\n";
}

// 4. Simular processo de aprovação
echo "\n🔄 SIMULAÇÃO DO PROCESSO DE APROVAÇÃO:\n";

if ($pendingPayrolls->count() > 0) {
    $payroll = $pendingPayrolls->first();
    echo "Simulando aprovação da folha ID: {$payroll->id}\n";
    
    // Obter período da folha
    $payrollPeriod = $payroll->payrollPeriod;
    $periodStart = $payrollPeriod->start_date ?? now()->startOfMonth();
    $periodEnd = $payrollPeriod->end_date ?? now()->endOfMonth();
    
    echo "Período da folha: {$periodStart->format('d/m/Y')} - {$periodEnd->format('d/m/Y')}\n\n";
    
    // Verificar advances que deveriam ser deduzidos
    echo "📊 ADVANCES A SEREM PROCESSADOS:\n";
    $advancesToProcess = \App\Models\HR\SalaryAdvance::where('employee_id', $employeeId)
        ->where('status', 'approved')
        ->where('remaining_installments', '>', 0)
        ->where('first_deduction_date', '<=', $periodEnd)
        ->get();
    
    foreach($advancesToProcess as $advance) {
        echo "   - Advance ID {$advance->id}: Parcela de {$advance->installment_amount} KZ\n";
        
        // Verificar se já foi pago neste período
        $existingPayment = $advance->payments()
            ->whereBetween('payment_date', [$periodStart, $periodEnd])
            ->first();
        
        if ($existingPayment) {
            echo "     ✅ Já processado neste período: {$existingPayment->amount} KZ em {$existingPayment->payment_date->format('d/m/Y')}\n";
        } else {
            echo "     ❌ PENDENTE: Deveria ser processado automaticamente!\n";
        }
    }
    
    // Verificar discounts que deveriam ser deduzidos
    echo "\n📊 DISCOUNTS A SEREM PROCESSADOS:\n";
    $discountsToProcess = \App\Models\HR\SalaryDiscount::where('employee_id', $employeeId)
        ->where('status', 'approved')
        ->where('remaining_installments', '>', 0)
        ->where('first_deduction_date', '<=', $periodEnd)
        ->get();
    
    foreach($discountsToProcess as $discount) {
        echo "   - Discount ID {$discount->id}: Parcela de {$discount->installment_amount} KZ\n";
        
        // Verificar se já foi pago neste período
        $existingPayment = $discount->payments()
            ->whereBetween('payment_date', [$periodStart, $periodEnd])
            ->first();
        
        if ($existingPayment) {
            echo "     ✅ Já processado neste período: {$existingPayment->amount} KZ em {$existingPayment->payment_date->format('d/m/Y')}\n";
        } else {
            echo "     ❌ PENDENTE: Deveria ser processado automaticamente!\n";
        }
    }
    
} else {
    echo "❌ Nenhuma folha pendente encontrada\n";
}

echo "\n💡 CONCLUSÃO:\n";
echo "O método approve() atual apenas muda o status para 'approved'\n";
echo "Não há lógica automática para processar advances/discounts parcelados\n";
echo "É necessário implementar essa funcionalidade\n";

echo "\n=== DEBUG CONCLUÍDO ===\n";
