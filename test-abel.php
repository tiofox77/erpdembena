<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\HR\Employee;

$employee = Employee::where('full_name', 'LIKE', '%ABEL%')->first();

if (!$employee) {
    echo "❌ Funcionário ABEL não encontrado\n";
    exit(1);
}

echo "✅ Funcionário: {$employee->full_name}\n";
echo "   ID: {$employee->id}\n";
echo "   base_salary (coluna real): " . ($employee->base_salary ?? 'NULL') . "\n";
echo "   basic_salary (accessor): " . ($employee->basic_salary ?? 'NULL') . "\n";

// Testar se accessor funciona
if ($employee->base_salary == $employee->basic_salary) {
    echo "\n✅ ACCESSOR FUNCIONANDO CORRETAMENTE!\n";
    echo "   Ambos retornam: {$employee->base_salary}\n";
} else {
    echo "\n❌ PROBLEMA: Valores diferentes!\n";
    echo "   base_salary: {$employee->base_salary}\n";
    echo "   basic_salary: {$employee->basic_salary}\n";
}

// Testar computed property simulada
$basicSalary = $employee->basic_salary;
$christmasAmount = $basicSalary * 0.5;
$vacationAmount = $basicSalary * 0.5;

echo "\n📊 CÁLCULO ESPERADO:\n";
echo "   Salário Base: " . number_format($basicSalary, 2) . " AOA\n";
echo "   Christmas (50%): " . number_format($christmasAmount, 2) . " AOA\n";
echo "   Vacation (50%): " . number_format($vacationAmount, 2) . " AOA\n";
