<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== DEBUG: Salary Advance Processing Conditions ===\n\n";

$employeeId = 14; // Ana Beatriz Lopes

// Buscar o advance ID 4 especificamente
$advance = \App\Models\HR\SalaryAdvance::find(4);

if (!$advance) {
    echo "❌ Advance ID 4 não encontrado\n";
    exit;
}

echo "📋 SALARY ADVANCE ENCONTRADO:\n";
echo "   ID: {$advance->id}\n";
echo "   Employee ID: {$advance->employee_id}\n";
echo "   Status: {$advance->status}\n";
echo "   Amount: {$advance->amount} KZ\n";
echo "   Installments: {$advance->installments}\n";
echo "   Remaining: {$advance->remaining_installments}\n";
echo "   Installment Amount: {$advance->installment_amount} KZ\n";
echo "   Request Date: " . ($advance->request_date ? $advance->request_date->format('Y-m-d') : 'null') . "\n";
echo "   First Deduction Date: " . ($advance->first_deduction_date ? $advance->first_deduction_date->format('Y-m-d') : 'null') . "\n\n";

// Simular as condições do processamento
$periodStart = now()->startOfMonth();
$periodEnd = now()->endOfMonth();

echo "📅 PERÍODO DE PROCESSAMENTO:\n";
echo "   Start: {$periodStart->format('Y-m-d')}\n";
echo "   End: {$periodEnd->format('Y-m-d')}\n\n";

echo "🔍 VERIFICANDO CONDIÇÕES:\n";

// Condição 1: Status aprovado
echo "   1. Status = 'approved': " . ($advance->status === 'approved' ? '✅ SIM' : '❌ NÃO') . "\n";

// Condição 2: Remaining installments > 0
echo "   2. Remaining installments > 0: " . ($advance->remaining_installments > 0 ? '✅ SIM' : '❌ NÃO') . "\n";

// Condição 3: First deduction date <= period end
$firstDeductionCondition = $advance->first_deduction_date && $advance->first_deduction_date <= $periodEnd;
echo "   3. First deduction date <= period end: " . ($firstDeductionCondition ? '✅ SIM' : '❌ NÃO') . "\n";
if ($advance->first_deduction_date) {
    echo "      First Deduction: {$advance->first_deduction_date->format('Y-m-d')}\n";
    echo "      Period End: {$periodEnd->format('Y-m-d')}\n";
}

// Verificar pagamentos existentes no período
echo "\n🏦 PAGAMENTOS EXISTENTES NO PERÍODO:\n";
$existingPayments = $advance->payments()
    ->whereBetween('payment_date', [$periodStart, $periodEnd])
    ->get();

echo "   Pagamentos encontrados: " . $existingPayments->count() . "\n";
foreach($existingPayments as $payment) {
    echo "      - {$payment->amount} KZ em {$payment->payment_date->format('d/m/Y')} - {$payment->notes}\n";
}

// Condição 4: Não há pagamento existente no período
$noExistingPayment = $existingPayments->count() === 0;
echo "   4. Sem pagamento no período: " . ($noExistingPayment ? '✅ SIM' : '❌ NÃO') . "\n";

// Resultado final
echo "\n📊 RESULTADO FINAL:\n";
$shouldProcess = ($advance->status === 'approved') && 
                 ($advance->remaining_installments > 0) && 
                 $firstDeductionCondition && 
                 $noExistingPayment;

echo "   Deve ser processado: " . ($shouldProcess ? '✅ SIM' : '❌ NÃO') . "\n";

if (!$shouldProcess) {
    echo "\n⚠️  MOTIVOS PARA NÃO PROCESSAR:\n";
    if ($advance->status !== 'approved') {
        echo "   - Status não é 'approved'\n";
    }
    if (!($advance->remaining_installments > 0)) {
        echo "   - Sem parcelas restantes\n";
    }
    if (!$firstDeductionCondition) {
        echo "   - First deduction date posterior ao período ou nula\n";
    }
    if (!$noExistingPayment) {
        echo "   - Já existe pagamento no período\n";
    }
}

echo "\n=== DEBUG CONCLUÍDO ===\n";
