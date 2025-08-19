<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$employeeId = 14; // Ana Beatriz Lopes

echo "=== DEBUG: Dados Completos do Recibo Employee ID {$employeeId} ===\n\n";

// Simular a lógica do controller
$startDate = '2025-07-01';
$endDate = '2025-07-31';

// 1. Dados de presença
echo "📅 DADOS DE PRESENÇA:\n";
$attendanceRecords = \App\Models\HR\Attendance::where('employee_id', $employeeId)
    ->whereBetween('date', [$startDate, $endDate])
    ->get();

$presentDays = $attendanceRecords->where('status', 'present')->count();
$absentDays = $attendanceRecords->where('status', 'absent')->count();
$lateDays = $attendanceRecords->where('status', 'late')->count();
$halfDays = $attendanceRecords->where('status', 'half_day')->count();

$workedDays = $presentDays + $lateDays + $halfDays;
$totalAbsences = $absentDays;

echo "   Presentes: {$presentDays}\n";
echo "   Ausentes: {$absentDays}\n"; 
echo "   Atrasados: {$lateDays}\n";
echo "   Meio-dias: {$halfDays}\n";
echo "   ✅ Dias trabalhados: {$workedDays}\n";
echo "   ✅ Total ausências: {$totalAbsences}\n\n";

// 2. Descontos salariais
echo "💰 DESCONTOS SALARIAIS:\n";
$salaryDiscounts = \App\Models\HR\SalaryDiscount::where('employee_id', $employeeId)
    ->where('status', 'approved')
    ->whereBetween('first_deduction_date', [$startDate, $endDate])
    ->get();

echo "   Total de descontos no período: " . $salaryDiscounts->count() . "\n";

if ($salaryDiscounts->count() > 0) {
    $groupedDiscounts = $salaryDiscounts->groupBy('discount_type')
        ->map(function ($discounts, $type) {
            return [
                'type' => $type,
                'type_name' => $discounts->first()->discount_type_name,
                'total_amount' => $discounts->sum('installment_amount'),
                'count' => $discounts->count(),
            ];
        });
    
    echo "   ✅ Descontos agrupados:\n";
    foreach($groupedDiscounts as $group) {
        echo "      - {$group['type_name']}: {$group['total_amount']} KZ (count: {$group['count']})\n";
    }
    
    echo "   ✅ Condição para mostrar múltiplos tipos: " . ($groupedDiscounts->count() > 1 ? "SIM" : "NÃO") . "\n";
} else {
    echo "   ❌ Nenhum desconto encontrado no período\n";
    
    // Verificar se há descontos aprovados em qualquer período
    $anyDiscounts = \App\Models\HR\SalaryDiscount::where('employee_id', $employeeId)
        ->where('status', 'approved')
        ->limit(3)
        ->get();
    
    echo "   Descontos aprovados (qualquer período): " . $anyDiscounts->count() . "\n";
    foreach($anyDiscounts as $discount) {
        echo "      - {$discount->first_deduction_date->format('d/m/Y')}: {$discount->discount_type_name} ({$discount->installment_amount} KZ)\n";
    }
}

echo "\n=== TESTE: Criar desconto temporário para testar ===\n";

try {
    // Criar um desconto temporário para julho 2025
    $testDiscount1 = \App\Models\HR\SalaryDiscount::create([
        'employee_id' => $employeeId,
        'request_date' => '2025-07-15',
        'amount' => 25000,
        'installments' => 1,
        'installment_amount' => 25000,
        'first_deduction_date' => '2025-07-15',
        'remaining_installments' => 0,
        'reason' => 'Teste Sindical',
        'discount_type' => 'union',
        'status' => 'approved',
        'approved_by' => 1,
        'approved_at' => now(),
    ]);
    
    $testDiscount2 = \App\Models\HR\SalaryDiscount::create([
        'employee_id' => $employeeId,
        'request_date' => '2025-07-20',
        'amount' => 15000,
        'installments' => 1,
        'installment_amount' => 15000,
        'first_deduction_date' => '2025-07-20',
        'remaining_installments' => 0,
        'reason' => 'Teste Outros',
        'discount_type' => 'others',
        'status' => 'approved',
        'approved_by' => 1,
        'approved_at' => now(),
    ]);
    
    echo "✅ Descontos de teste criados!\n";
    
    // Testar novamente a lógica
    $testDiscounts = \App\Models\HR\SalaryDiscount::where('employee_id', $employeeId)
        ->where('status', 'approved')
        ->whereBetween('first_deduction_date', [$startDate, $endDate])
        ->get();
    
    $testGrouped = $testDiscounts->groupBy('discount_type')
        ->map(function ($discounts, $type) {
            return [
                'type' => $type,
                'type_name' => $discounts->first()->discount_type_name,
                'total_amount' => $discounts->sum('installment_amount'),
                'count' => $discounts->count(),
            ];
        });
    
    echo "📋 RESULTADO COM DESCONTOS DE TESTE:\n";
    echo "   Total grupos: " . $testGrouped->count() . "\n";
    echo "   Condição múltiplos tipos: " . ($testGrouped->count() > 1 ? "SIM" : "NÃO") . "\n";
    
    foreach($testGrouped as $group) {
        echo "      - {$group['type_name']}: {$group['total_amount']} KZ (count: {$group['count']})\n";
    }
    
    // Limpar os descontos de teste
    $testDiscount1->delete();
    $testDiscount2->delete();
    echo "\n✅ Descontos de teste removidos!\n";
    
} catch (\Exception $e) {
    echo "❌ Erro ao criar descontos de teste: " . $e->getMessage() . "\n";
}

echo "\n=== DEBUG CONCLUÍDO ===\n";
