<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\HR\Employee;
use App\Helpers\PayrollCalculatorHelper;
use Carbon\Carbon;

echo "\n==========================================\n";
echo "🔍 DIAGNÓSTICO DE SUBSÍDIOS DE NATAL/FÉRIAS\n";
echo "==========================================\n\n";

// Buscar um funcionário
$employee = Employee::first();

if (!$employee) {
    echo "❌ Nenhum funcionário encontrado no banco de dados!\n";
    exit(1);
}

echo "✅ Funcionário encontrado:\n";
echo "   ID: {$employee->id}\n";
echo "   Nome: {$employee->full_name}\n";
echo "   Coluna 'basic_salary': " . ($employee->basic_salary ?? 'NULL') . "\n";
echo "   Coluna 'base_salary': " . ($employee->base_salary ?? 'NULL') . "\n";
echo "\n";

// Verificar qual coluna existe
$columns = \DB::connection()->getSchemaBuilder()->getColumnListing('employees');
echo "📋 Colunas na tabela 'employees':\n";
if (in_array('basic_salary', $columns)) {
    echo "   ✅ 'basic_salary' existe\n";
} else {
    echo "   ❌ 'basic_salary' NÃO existe\n";
}

if (in_array('base_salary', $columns)) {
    echo "   ✅ 'base_salary' existe\n";
} else {
    echo "   ❌ 'base_salary' NÃO existe\n";
}
echo "\n";

// Testar Helper
$start = Carbon::now()->startOfMonth();
$end = Carbon::now()->endOfMonth();

echo "🔧 Testando PayrollCalculatorHelper...\n";
$calculator = new PayrollCalculatorHelper($employee, $start, $end);

// Verificar propriedade interna do helper
$reflection = new ReflectionClass($calculator);
$basicSalaryProperty = $reflection->getProperty('basicSalary');
$basicSalaryProperty->setAccessible(true);
$helperBasicSalary = $basicSalaryProperty->getValue($calculator);

echo "   Helper->basicSalary (interno): {$helperBasicSalary}\n";
echo "\n";

// Calcular
$calculator->loadAllEmployeeData();
$calculator->setChristmasSubsidy(true);
$calculator->setVacationSubsidy(true);
$results = $calculator->calculate();

echo "📊 Resultados do Helper:\n";
echo "   basic_salary: " . ($results['basic_salary'] ?? 'NULL') . "\n";
echo "   christmas_subsidy: " . ($results['christmas_subsidy'] ? 'true' : 'false') . "\n";
echo "   christmas_subsidy_amount: " . ($results['christmas_subsidy_amount'] ?? 'NULL') . "\n";
echo "   vacation_subsidy: " . ($results['vacation_subsidy'] ? 'true' : 'false') . "\n";
echo "   vacation_subsidy_amount: " . ($results['vacation_subsidy_amount'] ?? 'NULL') . "\n";
echo "\n";

// Cálculo manual
$manualChristmas = $helperBasicSalary * 0.5;
$manualVacation = $helperBasicSalary * 0.5;

echo "🧮 Cálculo Manual (50% do salário):\n";
echo "   Christmas: {$helperBasicSalary} * 0.5 = {$manualChristmas}\n";
echo "   Vacation: {$helperBasicSalary} * 0.5 = {$manualVacation}\n";
echo "\n";

// Diagnóstico
echo "🎯 DIAGNÓSTICO:\n";

if ($helperBasicSalary == 0) {
    echo "   ❌ PROBLEMA: Helper não está carregando o salário básico!\n";
    echo "   ➡️  Verificar linha 89 do PayrollCalculatorHelper.php\n";
    echo "   ➡️  Deve usar: \$employee->basic_salary (não base_salary)\n";
} else {
    echo "   ✅ Helper carrega salário corretamente: {$helperBasicSalary}\n";
}

if (($results['christmas_subsidy_amount'] ?? 0) == 0 && $results['christmas_subsidy'] == true) {
    echo "   ❌ PROBLEMA: Subsídio de Natal está marcado mas valor é 0!\n";
    echo "   ➡️  Verificar getChristmasSubsidyAmount() no Helper\n";
} else {
    echo "   ✅ Subsídio de Natal calculado corretamente\n";
}

if (($results['vacation_subsidy_amount'] ?? 0) == 0 && $results['vacation_subsidy'] == true) {
    echo "   ❌ PROBLEMA: Subsídio de Férias está marcado mas valor é 0!\n";
    echo "   ➡️  Verificar getVacationSubsidyAmount() no Helper\n";
} else {
    echo "   ✅ Subsídio de Férias calculado corretamente\n";
}

echo "\n==========================================\n";
echo "✅ Diagnóstico completo!\n";
echo "==========================================\n\n";
