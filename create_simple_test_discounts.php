<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$employeeId = 14; // Ana Beatriz Lopes

echo "=== Criar descontos simples de teste para Employee ID {$employeeId} ===\n\n";

try {
    // Criar apenas 2 tipos que sabemos que funcionam
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
    
    echo "✅ Descontos de teste criados:\n";
    echo "   - Sindical: 25.000 KZ (15/07/2025)\n";
    echo "   - Outros: 15.000 KZ (20/07/2025)\n";
    echo "   - Total: 40.000 KZ\n\n";
    
    echo "IDs criados: {$discount1->id}, {$discount2->id}\n";
    
    // Verificar se foram criados corretamente
    $testDiscounts = \App\Models\HR\SalaryDiscount::where('employee_id', $employeeId)
        ->where('status', 'approved')
        ->whereBetween('first_deduction_date', ['2025-07-01', '2025-07-31'])
        ->get();
    
    echo "\n📊 Verificação:\n";
    echo "   Total descontos no período: " . $testDiscounts->count() . "\n";
    
    $grouped = $testDiscounts->groupBy('discount_type')
        ->map(function ($discounts, $type) {
            return [
                'type' => $type,
                'type_name' => $discounts->first()->discount_type_name,
                'total_amount' => $discounts->sum('installment_amount'),
                'count' => $discounts->count(),
            ];
        });
    
    echo "   Grupos: " . $grouped->count() . "\n";
    echo "   Condição múltiplos tipos: " . ($grouped->count() > 1 ? "SIM" : "NÃO") . "\n\n";
    
    foreach($grouped as $group) {
        echo "      - {$group['type_name']}: {$group['total_amount']} KZ\n";
    }
    
    echo "\n📋 Agora pode testar o recibo em: http://erpdembena.test/payroll/receipt?employee_id=14\n";
    
} catch (\Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
}

echo "\n=== CONCLUÍDO ===\n";
