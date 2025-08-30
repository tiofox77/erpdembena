<?php

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== CORRIGIR: Escalões IRT conforme tabela oficial ===\n\n";

// Escalões corretos conforme tabela oficial de Angola
$correctBrackets = [
    [
        'bracket_number' => 1,
        'min_income' => 0.00,
        'max_income' => 100000.00,
        'fixed_amount' => 0.00,
        'tax_rate' => 0.00,
        'description' => 'Escalão 1: até 100.000 AKZ - Isento',
        'is_active' => true
    ],
    [
        'bracket_number' => 2,
        'min_income' => 100001.00,
        'max_income' => 150000.00,
        'fixed_amount' => 0.00,
        'tax_rate' => 13.00,
        'description' => 'Escalão 2: 100.001 - 150.000 AKZ - 13% sem parcela fixa',
        'is_active' => true
    ],
    [
        'bracket_number' => 3,
        'min_income' => 150001.00,
        'max_income' => 200000.00,
        'fixed_amount' => 12500.00,
        'tax_rate' => 16.00,
        'description' => 'Escalão 3: 150.001 - 200.000 AKZ - 12.500 AKZ + 16%',
        'is_active' => true
    ],
    [
        'bracket_number' => 4,
        'min_income' => 200001.00,
        'max_income' => 300000.00,
        'fixed_amount' => 31250.00,
        'tax_rate' => 18.00,
        'description' => 'Escalão 4: 200.001 - 300.000 AKZ - 31.250 AKZ + 18%',
        'is_active' => true
    ],
    [
        'bracket_number' => 5,
        'min_income' => 300001.00,
        'max_income' => 500000.00,
        'fixed_amount' => 49250.00,
        'tax_rate' => 19.00,
        'description' => 'Escalão 5: 300.001 - 500.000 AKZ - 49.250 AKZ + 19%',
        'is_active' => true
    ],
    [
        'bracket_number' => 6,
        'min_income' => 500001.00,
        'max_income' => 1000000.00,
        'fixed_amount' => 87250.00,
        'tax_rate' => 20.00,
        'description' => 'Escalão 6: 500.001 - 1.000.000 AKZ - 87.250 AKZ + 20%',
        'is_active' => true
    ],
    [
        'bracket_number' => 7,
        'min_income' => 1000001.00,
        'max_income' => 1500000.00,
        'fixed_amount' => 187249.00,
        'tax_rate' => 21.00,
        'description' => 'Escalão 7: 1.000.001 - 1.500.000 AKZ - 187.249 AKZ + 21%',
        'is_active' => true
    ]
];

echo "🔧 ATUALIZANDO ESCALÕES:\n";
echo str_repeat("-", 60) . "\n";

$updated = 0;
$created = 0;

foreach ($correctBrackets as $bracketData) {
    $existing = \App\Models\HR\IRTTaxBracket::where('bracket_number', $bracketData['bracket_number'])->first();
    
    if ($existing) {
        $existing->update($bracketData);
        $updated++;
        echo "✅ Atualizado: Escalão {$bracketData['bracket_number']}\n";
    } else {
        \App\Models\HR\IRTTaxBracket::create($bracketData);
        $created++;
        echo "✅ Criado: Escalão {$bracketData['bracket_number']}\n";
    }
}

// Desativar escalões extras (8-12) que não estão na tabela oficial
$extraBrackets = \App\Models\HR\IRTTaxBracket::whereIn('bracket_number', [8, 9, 10, 11, 12])->get();
foreach ($extraBrackets as $extra) {
    $extra->update(['is_active' => false]);
    echo "⚠️  Desativado: Escalão {$extra->bracket_number} (não oficial)\n";
}

echo "\n✅ ATUALIZAÇÃO CONCLUÍDA!\n";
echo "Criados: $created | Atualizados: $updated\n";

echo "\n📊 ESCALÕES ATUALIZADOS:\n";
echo str_repeat("-", 80) . "\n";

$updatedBrackets = \App\Models\HR\IRTTaxBracket::where('is_active', true)
    ->orderBy('bracket_number')
    ->get();

foreach ($updatedBrackets as $bracket) {
    $maxIncome = $bracket->max_income ? number_format($bracket->max_income, 0) : 'Sem limite';
    echo sprintf(
        "Escalão %d: %s - %s | Parcela: %s | Taxa: %s%%\n",
        $bracket->bracket_number,
        number_format($bracket->min_income, 0),
        $maxIncome,
        number_format($bracket->fixed_amount, 0),
        $bracket->tax_rate
    );
}

echo "\n🧮 TESTE DE CÁLCULO CORRIGIDO (salário: 210.000 Kz):\n";
echo str_repeat("-", 50) . "\n";

$testSalary = 210000;
$calculatedTax = \App\Models\HR\IRTTaxBracket::calculateIRT($testSalary);
$bracket = \App\Models\HR\IRTTaxBracket::getBracketForIncome($testSalary);

echo "Salário: " . number_format($testSalary, 0) . " Kz\n";
echo "IRT calculado: " . number_format($calculatedTax, 2) . " Kz\n";
echo "Escalão aplicado: " . ($bracket ? $bracket->bracket_number : 'N/A') . "\n";

// Cálculo manual conforme tabela oficial
$manualTax = 31250 + (($testSalary - 200000) * 0.18);
echo "IRT manual (tabela oficial): " . number_format($manualTax, 2) . " Kz\n";
echo "Diferença: " . number_format(abs($calculatedTax - $manualTax), 2) . " Kz\n";

if (abs($calculatedTax - $manualTax) < 1) {
    echo "✅ CÁLCULO CORRETO!\n";
} else {
    echo "❌ Ainda há diferença no cálculo\n";
}

echo "\n" . str_repeat("=", 80) . "\n";
echo "SISTEMA IRT ATUALIZADO CONFORME TABELA OFICIAL DE ANGOLA\n";
echo str_repeat("=", 80) . "\n";
