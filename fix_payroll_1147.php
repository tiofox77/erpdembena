<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Atualizando Payroll #1147...\n\n";

$payroll = App\Models\HR\Payroll::find(1147);
$batchItem = App\Models\HR\PayrollBatchItem::where('payroll_id', 1147)->first();

if (!$payroll || !$batchItem) {
    echo "ERRO: Payroll ou Batch Item não encontrado!\n";
    exit(1);
}

echo "=== VALORES ANTES ===\n";
echo "Christmas Subsidy: " . number_format($payroll->christmas_subsidy_amount ?? 0, 2) . "\n";
echo "Vacation Subsidy: " . number_format($payroll->vacation_subsidy_amount ?? 0, 2) . "\n";
echo "Additional Bonus: " . number_format($payroll->additional_bonus ?? 0, 2) . "\n";
echo "Gross Salary: " . number_format($payroll->gross_salary ?? 0, 2) . "\n";
echo "Net Salary: " . number_format($payroll->net_salary ?? 0, 2) . "\n\n";

// Atualizar com valores do batch item
$christmasAmount = (float) ($batchItem->christmas_subsidy_amount ?? 0);
$vacationAmount = (float) ($batchItem->vacation_subsidy_amount ?? 0);
$additionalBonus = (float) ($batchItem->additional_bonus ?? 0);

// Recalcular gross salary
$basicSalary = (float) $payroll->basic_salary;
$transportAllowance = (float) $payroll->transport_allowance;
$foodAllowance = (float) $payroll->food_allowance;
$familyAllowance = (float) $payroll->family_allowance;
$positionSubsidy = (float) $payroll->position_subsidy;
$performanceSubsidy = (float) $payroll->performance_subsidy;
$overtimeAmount = (float) $payroll->overtime_amount;

$newGrossSalary = $basicSalary + $transportAllowance + $foodAllowance + $familyAllowance 
    + $positionSubsidy + $performanceSubsidy + $overtimeAmount
    + $christmasAmount + $vacationAmount + $additionalBonus;

// Recalcular net salary
$totalDeductions = (float) ($payroll->deductions ?? 0);
$newNetSalary = $newGrossSalary - $totalDeductions;

$payroll->update([
    'christmas_subsidy_amount' => $christmasAmount,
    'vacation_subsidy_amount' => $vacationAmount,
    'additional_bonus' => $additionalBonus,
    'gross_salary' => $newGrossSalary,
    'net_salary' => $newNetSalary,
]);

echo "=== VALORES DEPOIS ===\n";
echo "Christmas Subsidy: " . number_format($christmasAmount, 2) . " ✓\n";
echo "Vacation Subsidy: " . number_format($vacationAmount, 2) . " ✓\n";
echo "Additional Bonus: " . number_format($additionalBonus, 2) . " ✓\n";
echo "Gross Salary: " . number_format($newGrossSalary, 2) . " ✓✓\n";
echo "Net Salary: " . number_format($newNetSalary, 2) . " ✓✓✓\n\n";

echo "PAYROLL #1147 ATUALIZADO COM SUCESSO! ✓\n";
