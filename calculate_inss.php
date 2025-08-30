<?php

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== CÁLCULO INSS: Salário 360.000 Kz ===\n\n";

$salary = 360000;

echo "💰 SALÁRIO BASE: " . number_format($salary, 0) . " Kz\n\n";

echo "📊 CÁLCULO INSS (Sistema Angolano):\n";
echo str_repeat("-", 50) . "\n";

// INSS 3% (desconto do funcionário)
$inss_employee = $salary * 0.03;
echo "INSS 3% (Funcionário): " . number_format($inss_employee, 2) . " Kz\n";

// INSS 8% (encargo patronal - apenas ilustrativo)
$inss_employer = $salary * 0.08;
echo "INSS 8% (Patronal - ilustrativo): " . number_format($inss_employer, 2) . " Kz\n";

// Total INSS
$total_inss = $inss_employee + $inss_employer;
echo "INSS Total: " . number_format($total_inss, 2) . " Kz\n";

echo "\n📝 DETALHES:\n";
echo str_repeat("-", 50) . "\n";
echo "• INSS 3% é descontado do salário do funcionário\n";
echo "• INSS 8% é encargo patronal (pago pela empresa)\n";
echo "• No recibo de salário aparece apenas o desconto de 3%\n";
echo "• O 8% é para fins informativos/contabilísticos\n";

echo "\n💡 RESUMO PARA SALÁRIO 360.000 Kz:\n";
echo str_repeat("=", 50) . "\n";
echo "Desconto INSS (3%): " . number_format($inss_employee, 2) . " Kz\n";
echo "Salário após INSS: " . number_format($salary - $inss_employee, 2) . " Kz\n";

echo "\n🧮 COMPARAÇÃO COM OUTROS VALORES:\n";
echo str_repeat("-", 50) . "\n";

$testSalaries = [150000, 200000, 250000, 300000, 360000, 500000];

foreach ($testSalaries as $testSalary) {
    $inss = $testSalary * 0.03;
    echo sprintf(
        "%s Kz -> INSS 3%%: %s Kz\n",
        number_format($testSalary, 0),
        number_format($inss, 2)
    );
}

echo str_repeat("=", 50) . "\n";
