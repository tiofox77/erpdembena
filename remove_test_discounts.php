<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$employeeId = 14; // Ana Beatriz Lopes

echo "=== Remover descontos de teste para Employee ID {$employeeId} ===\n\n";

try {
    // Buscar descontos de teste em julho 2025
    $testDiscounts = \App\Models\HR\SalaryDiscount::where('employee_id', $employeeId)
        ->whereBetween('first_deduction_date', ['2025-07-01', '2025-07-31'])
        ->where('reason', 'LIKE', '%Teste%')
        ->get();
    
    echo "Descontos de teste encontrados: " . $testDiscounts->count() . "\n\n";
    
    if ($testDiscounts->count() > 0) {
        foreach ($testDiscounts as $discount) {
            echo "Removendo: {$discount->reason} - {$discount->discount_type} - {$discount->installment_amount} KZ\n";
            $discount->delete();
        }
        
        echo "\n✅ Todos os descontos de teste foram removidos!\n";
    } else {
        echo "❌ Nenhum desconto de teste encontrado.\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
}

echo "\n=== CONCLUÍDO ===\n";
