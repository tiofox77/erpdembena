<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$employeeId = 14; // Ana Beatriz Lopes

echo "=== Criar descontos de teste para Employee ID {$employeeId} ===\n\n";

try {
    // Criar descontos para julho 2025 para testar múltiplos tipos
    $discount1 = \App\Models\HR\SalaryDiscount::create([
        'employee_id' => $employeeId,
        'request_date' => '2025-07-10',
        'amount' => 25000,
        'installments' => 1,
        'installment_amount' => 25000,
        'first_deduction_date' => '2025-07-15',
        'remaining_installments' => 0,
        'reason' => 'Desconto Sindical Teste',
        'discount_type' => 'union',
        'status' => 'approved',
        'approved_by' => 1,
        'approved_at' => now(),
    ]);
    
    $discount2 = \App\Models\HR\SalaryDiscount::create([
        'employee_id' => $employeeId,
        'request_date' => '2025-07-15',
        'amount' => 15000,
        'installments' => 1,
        'installment_amount' => 15000,
        'first_deduction_date' => '2025-07-20',
        'remaining_installments' => 0,
        'reason' => 'Outros Descontos Teste',
        'discount_type' => 'others',
        'status' => 'approved',
        'approved_by' => 1,
        'approved_at' => now(),
    ]);
    
    $discount3 = \App\Models\HR\SalaryDiscount::create([
        'employee_id' => $employeeId,
        'request_date' => '2025-07-20',
        'amount' => 12000,
        'installments' => 1,
        'installment_amount' => 12000,
        'first_deduction_date' => '2025-07-25',
        'remaining_installments' => 0,
        'reason' => 'Quixiquila Teste',
        'discount_type' => 'quixiquila',
        'status' => 'approved',
        'approved_by' => 1,
        'approved_at' => now(),
    ]);
    
    echo "✅ Descontos de teste criados:\n";
    echo "   - Sindical: 25.000 KZ (15/07/2025)\n";
    echo "   - Outros: 15.000 KZ (20/07/2025)\n";
    echo "   - Quixiquila: 12.000 KZ (25/07/2025)\n";
    echo "   - Total: 52.000 KZ\n\n";
    
    echo "IDs criados: {$discount1->id}, {$discount2->id}, {$discount3->id}\n";
    
    echo "\n📋 Agora pode testar o recibo em: http://erpdembena.test/payroll/receipt?employee_id=14\n";
    echo "\n🗑️  Para remover os descontos de teste, execute: php remove_test_discounts.php\n";
    
} catch (\Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
}

echo "\n=== CONCLUÍDO ===\n";
