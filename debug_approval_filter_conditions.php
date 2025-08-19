<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== DEBUG: Condições de Filtro na Aprovação ===\n\n";

$employeeId = 14; // Ana Beatriz Lopes
$periodStart = \Carbon\Carbon::parse('2025-07-01');
$periodEnd = \Carbon\Carbon::parse('2025-07-31');

echo "📅 PERÍODO DA FOLHA:\n";
echo "   Start: {$periodStart->format('Y-m-d')}\n";
echo "   End: {$periodEnd->format('Y-m-d')}\n\n";

// 1. Verificar todos os advances para o employee
echo "🔍 TODOS OS SALARY ADVANCES (Employee ID {$employeeId}):\n";
$allAdvances = \App\Models\HR\SalaryAdvance::where('employee_id', $employeeId)->get();

foreach($allAdvances as $advance) {
    echo "   ID {$advance->id}:\n";
    echo "      Status: {$advance->status}\n";
    echo "      Remaining: {$advance->remaining_installments}\n";
    echo "      First Deduction: " . ($advance->first_deduction_date ? $advance->first_deduction_date->format('Y-m-d') : 'null') . "\n";
    echo "      Amount: {$advance->installment_amount} KZ\n";
    
    // Verificar cada condição
    $statusOk = $advance->status === 'approved';
    $remainingOk = $advance->remaining_installments > 0;
    $dateOk = $advance->first_deduction_date && $advance->first_deduction_date <= $periodEnd;
    
    echo "      ✓ Status approved: " . ($statusOk ? 'SIM' : 'NÃO') . "\n";
    echo "      ✓ Remaining > 0: " . ($remainingOk ? 'SIM' : 'NÃO') . "\n";
    echo "      ✓ First deduction <= period end: " . ($dateOk ? 'SIM' : 'NÃO') . "\n";
    echo "      → PASSA FILTRO: " . ($statusOk && $remainingOk && $dateOk ? 'SIM' : 'NÃO') . "\n\n";
}

// 2. Verificar todos os discounts para o employee
echo "🔍 TODOS OS SALARY DISCOUNTS (Employee ID {$employeeId}):\n";
$allDiscounts = \App\Models\HR\SalaryDiscount::where('employee_id', $employeeId)->get();

foreach($allDiscounts as $discount) {
    echo "   ID {$discount->id}:\n";
    echo "      Status: {$discount->status}\n";
    echo "      Remaining: {$discount->remaining_installments}\n";
    echo "      First Deduction: " . ($discount->first_deduction_date ? $discount->first_deduction_date->format('Y-m-d') : 'null') . "\n";
    echo "      Amount: {$discount->installment_amount} KZ\n";
    
    // Verificar cada condição
    $statusOk = $discount->status === 'approved';
    $remainingOk = $discount->remaining_installments > 0;
    $dateOk = $discount->first_deduction_date && $discount->first_deduction_date <= $periodEnd;
    
    echo "      ✓ Status approved: " . ($statusOk ? 'SIM' : 'NÃO') . "\n";
    echo "      ✓ Remaining > 0: " . ($remainingOk ? 'SIM' : 'NÃO') . "\n";
    echo "      ✓ First deduction <= period end: " . ($dateOk ? 'SIM' : 'NÃO') . "\n";
    echo "      → PASSA FILTRO: " . ($statusOk && $remainingOk && $dateOk ? 'SIM' : 'NÃO') . "\n\n";
}

// 3. Simular query exata usada no código
echo "📊 RESULTADOS DA QUERY EXATA:\n";

$advancesQuery = \App\Models\HR\SalaryAdvance::where('employee_id', $employeeId)
    ->where('status', 'approved')
    ->where('remaining_installments', '>', 0)
    ->where('first_deduction_date', '<=', $periodEnd);

echo "Advances encontrados pela query: " . $advancesQuery->count() . "\n";

$discountsQuery = \App\Models\HR\SalaryDiscount::where('employee_id', $employeeId)
    ->where('status', 'approved')
    ->where('remaining_installments', '>', 0)
    ->where('first_deduction_date', '<=', $periodEnd);

echo "Discounts encontrados pela query: " . $discountsQuery->count() . "\n\n";

echo "🔧 POSSÍVEIS SOLUÇÕES:\n";
echo "1. Verificar se first_deduction_date está corretamente definida\n";
echo "2. Verificar se o período da folha está correto\n";
echo "3. Considerar usar período atual em vez do período da folha\n";

echo "\n=== DEBUG CONCLUÍDO ===\n";
