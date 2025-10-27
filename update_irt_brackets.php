<?php

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== ATUALIZAR: Escalões IRT conforme tabela oficial de Angola ===\n\n";

// Escalões atuais no sistema
$currentBrackets = \App\Models\HR\IRTTaxBracket::where('is_active', true)
    ->orderBy('bracket_number')
    ->get(['bracket_number', 'min_income', 'max_income', 'fixed_amount', 'tax_rate', 'description'])
    ->toArray();

echo "📊 ESCALÕES ATUAIS NO SISTEMA:\n";
echo str_repeat("-", 80) . "\n";
foreach ($currentBrackets as $bracket) {
    $maxIncome = $bracket['max_income'] ? number_format($bracket['max_income'], 0) : 'Sem limite';
    echo sprintf(
        "Escalão %d: %s - %s | Parcela: %s | Taxa: %s%%\n",
        $bracket['bracket_number'],
        number_format($bracket['min_income'], 0),
        $maxIncome,
        number_format($bracket['fixed_amount'], 0),
        $bracket['tax_rate']
    );
}

echo "\n📋 ESCALÕES OFICIAIS (fornecidos pelo utilizador):\n";
echo str_repeat("-", 80) . "\n";
echo "1. Isenção até 100.000 Kz\n";
echo "2. 100.001–150.000 Kz: 13% sem parcela fixa\n";
echo "3. 150.001–200.000 Kz: 12.500 + 16%\n";
echo "4. 200.001–300.000 Kz: 31.250 + 18%\n";
echo "5. 300.001–500.000 Kz: 49.250 + 19%\n";
echo "6. 500.001–1.000.000 Kz: 87.250 + 20%\n";
echo "7. 1.000.001–1.500.000 Kz: 187.249 + 21%\n";

echo "\n🔍 COMPARAÇÃO E DISCREPÂNCIAS:\n";
echo str_repeat("-", 80) . "\n";

$officialBrackets = [
    ['escalao' => 1, 'min' => 0, 'max' => 100000, 'parcela' => 0, 'taxa' => 0],
    ['escalao' => 2, 'min' => 100001, 'max' => 150000, 'parcela' => 0, 'taxa' => 13],
    ['escalao' => 3, 'min' => 150001, 'max' => 200000, 'parcela' => 12500, 'taxa' => 16],
    ['escalao' => 4, 'min' => 200001, 'max' => 300000, 'parcela' => 31250, 'taxa' => 18],
    ['escalao' => 5, 'min' => 300001, 'max' => 500000, 'parcela' => 49250, 'taxa' => 19],
    ['escalao' => 6, 'min' => 500001, 'max' => 1000000, 'parcela' => 87250, 'taxa' => 20],
    ['escalao' => 7, 'min' => 1000001, 'max' => 1500000, 'parcela' => 187249, 'taxa' => 21],
];

$discrepancies = [];

foreach ($officialBrackets as $official) {
    $current = collect($currentBrackets)->where('bracket_number', $official['escalao'])->first();
    
    if (!$current) {
        echo "❌ Escalão {$official['escalao']}: NÃO EXISTE no sistema\n";
        $discrepancies[] = $official['escalao'];
        continue;
    }
    
    $issues = [];
    
    if ($current['min_income'] != $official['min']) {
        $issues[] = "Min: {$current['min_income']} vs {$official['min']}";
    }
    
    if ($current['max_income'] != $official['max']) {
        $issues[] = "Max: {$current['max_income']} vs {$official['max']}";
    }
    
    if ($current['fixed_amount'] != $official['parcela']) {
        $issues[] = "Parcela: {$current['fixed_amount']} vs {$official['parcela']}";
    }
    
    if ($current['tax_rate'] != $official['taxa']) {
        $issues[] = "Taxa: {$current['tax_rate']}% vs {$official['taxa']}%";
    }
    
    if (!empty($issues)) {
        echo "⚠️  Escalão {$official['escalao']}: " . implode(', ', $issues) . "\n";
        $discrepancies[] = $official['escalao'];
    } else {
        echo "✅ Escalão {$official['escalao']}: CORRETO\n";
    }
}

// Verificar escalões extras no sistema
$extraBrackets = collect($currentBrackets)->filter(function($bracket) use ($officialBrackets) {
    return !collect($officialBrackets)->contains('escalao', $bracket['bracket_number']);
});

if ($extraBrackets->count() > 0) {
    echo "\n📝 ESCALÕES EXTRAS NO SISTEMA (não mencionados na tabela oficial):\n";
    foreach ($extraBrackets as $extra) {
        echo "Escalão {$extra['bracket_number']}: {$extra['description']}\n";
    }
}

echo "\n" . str_repeat("=", 80) . "\n";

if (empty($discrepancies)) {
    echo "✅ SISTEMA ESTÁ ATUALIZADO!\n";
    echo "Todos os escalões principais estão conforme a tabela oficial de Angola.\n";
} else {
    echo "❌ SISTEMA PRECISA DE ATUALIZAÇÃO!\n";
    echo "Escalões com discrepâncias: " . implode(', ', array_unique($discrepancies)) . "\n";
    echo "\n💡 RECOMENDAÇÃO:\n";
    echo "Execute: php artisan hr:setup-irt-brackets\n";
    echo "Ou atualize manualmente os escalões com discrepâncias.\n";
}

echo "\n🧮 TESTE DE CÁLCULO (salário de exemplo: 210.000 Kz):\n";
$testSalary = 210000;
$calculatedTax = \App\Models\HR\IRTTaxBracket::calculateIRT($testSalary);
$bracket = \App\Models\HR\IRTTaxBracket::getBracketForIncome($testSalary);

echo "Salário: " . number_format($testSalary, 0) . " Kz\n";
echo "IRT calculado: " . number_format($calculatedTax, 2) . " Kz\n";
echo "Escalão aplicado: " . ($bracket ? $bracket->bracket_number : 'N/A') . "\n";

// Cálculo manual conforme tabela oficial
$manualTax = 0;
if ($testSalary > 200000) {
    $manualTax = 31250 + (($testSalary - 200000) * 0.18);
}
echo "IRT manual (tabela oficial): " . number_format($manualTax, 2) . " Kz\n";
echo "Diferença: " . number_format(abs($calculatedTax - $manualTax), 2) . " Kz\n";

echo str_repeat("=", 80) . "\n";
