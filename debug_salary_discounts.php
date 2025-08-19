<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$employeeId = 14; // Ana Beatriz Lopes

echo "=== DEBUG: Descontos Salariais Employee ID {$employeeId} ===\n\n";

// 1. Verificar descontos salariais em julho 2025
$startDate = '2025-07-01';
$endDate = '2025-07-31';

echo "Período: {$startDate} a {$endDate}\n\n";

$salaryDiscounts = \App\Models\HR\SalaryDiscount::where('employee_id', $employeeId)
    ->where('status', 'approved')
    ->whereBetween('first_deduction_date', [$startDate, $endDate])
    ->get();

echo "Total de descontos salariais: " . $salaryDiscounts->count() . "\n\n";

if ($salaryDiscounts->count() > 0) {
    echo "📊 RESUMO DOS DESCONTOS:\n";
    foreach($salaryDiscounts as $discount) {
        echo "   Tipo: {$discount->discount_type} ({$discount->discount_type_name})\n";
        echo "   Valor: {$discount->installment_amount} KZ\n";
        echo "   Razão: {$discount->reason}\n";
        echo "   Status: {$discount->status}\n";
        echo "   Data: {$discount->first_deduction_date->format('d/m/Y')}\n\n";
    }
    
    // Agrupar por tipo
    $groupedDiscounts = $salaryDiscounts->groupBy('discount_type')
        ->map(function ($discounts, $type) {
            return [
                'type' => $type,
                'type_name' => $discounts->first()->discount_type_name,
                'total_amount' => $discounts->sum('installment_amount'),
                'count' => $discounts->count(),
            ];
        });
    
    echo "📋 AGRUPADOS POR TIPO:\n";
    foreach($groupedDiscounts as $group) {
        echo "   {$group['type_name']}: {$group['total_amount']} KZ ({$group['count']} desconto(s))\n";
    }
    
    echo "\nTotal geral: " . $salaryDiscounts->sum('installment_amount') . " KZ\n";
} else {
    echo "❌ Nenhum desconto salarial encontrado!\n\n";
    
    // Verificar se há descontos noutros períodos
    $allDiscounts = \App\Models\HR\SalaryDiscount::where('employee_id', $employeeId)
        ->orderBy('first_deduction_date', 'desc')
        ->limit(5)
        ->get();
    
    echo "Últimos 5 descontos salariais (qualquer período):\n";
    foreach($allDiscounts as $discount) {
        echo "   {$discount->first_deduction_date->format('d/m/Y')}: {$discount->discount_type_name} - {$discount->installment_amount} KZ\n";
    }
}

// 2. Verificar tipos disponíveis no sistema
echo "\n=== TIPOS DE DESCONTO DISPONÍVEIS ===\n";
$availableTypes = \App\Models\HR\SalaryDiscount::distinct('discount_type')->pluck('discount_type', 'discount_type');
foreach($availableTypes as $type) {
    echo "   {$type}\n";
}

echo "\n=== DEBUG CONCLUÍDO ===\n";
