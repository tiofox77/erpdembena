<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$payroll = App\Models\HR\Payroll::where('employee_id', 226)->latest()->first();

if (!$payroll) {
    echo "PAYROLL NÃO ENCONTRADO PARA EMPLOYEE ID 226\n";
    exit(1);
}

echo "============================================\n";
echo "ANÁLISE DO PAYROLL - EMPLOYEE ID 226\n";
echo "============================================\n\n";

echo "PAYROLL ID: {$payroll->id}\n";
echo "Employee ID: {$payroll->employee_id}\n";
echo "Employee Name: {$payroll->employee->full_name}\n";
echo "Period: " . ($payroll->payrollPeriod ? $payroll->payrollPeriod->name : 'N/A') . "\n";
echo "Payment Date: {$payroll->payment_date}\n";
echo "Status: {$payroll->status}\n\n";

echo "=== EARNINGS (REMUNERAÇÕES) ===\n";
echo "Basic Salary: " . number_format($payroll->basic_salary ?? 0, 2) . "\n";
echo "Transport Allowance: " . number_format($payroll->transport_allowance ?? 0, 2) . "\n";
echo "Food Allowance: " . number_format($payroll->food_allowance ?? 0, 2) . "\n";
echo "Family Allowance: " . number_format($payroll->family_allowance ?? 0, 2) . "\n";
echo "Christmas Subsidy: " . number_format($payroll->christmas_subsidy_amount ?? 0, 2) . "\n";
echo "Vacation Subsidy: " . number_format($payroll->vacation_subsidy_amount ?? 0, 2) . "\n";
echo "Additional Bonus: " . number_format($payroll->additional_bonus ?? 0, 2) . "\n";
echo "Profile Bonus: " . number_format($payroll->profile_bonus ?? 0, 2) . "\n";
echo "Position Subsidy: " . number_format($payroll->position_subsidy ?? 0, 2) . "\n";
echo "Performance Subsidy: " . number_format($payroll->performance_subsidy ?? 0, 2) . "\n";
echo "Overtime Amount: " . number_format($payroll->overtime_amount ?? 0, 2) . "\n";
echo "Overtime Hours: " . number_format($payroll->total_overtime_hours ?? 0, 2) . "\n";
echo "Gross Salary: " . number_format($payroll->gross_salary ?? 0, 2) . " ✓\n\n";

echo "=== DEDUCTIONS (DESCONTOS) ===\n";
echo "INSS 3%: " . number_format($payroll->inss_3_percent ?? $payroll->social_security ?? 0, 2) . "\n";
echo "IRT: " . number_format($payroll->tax ?? 0, 2) . "\n";
echo "Advance Deduction: " . number_format($payroll->advance_deduction ?? 0, 2) . "\n";
echo "Late Deduction: " . number_format($payroll->late_deduction ?? 0, 2) . "\n";
echo "Absence Deduction: " . number_format($payroll->absence_deduction ?? 0, 2) . "\n";
echo "Salary Discounts: " . number_format($payroll->total_salary_discounts ?? 0, 2) . "\n";
echo "Total Deductions: " . number_format($payroll->deductions ?? $payroll->total_deductions_calculated ?? 0, 2) . " ✓\n\n";

echo "=== NET SALARY (VENCIMENTO LÍQUIDO) ===\n";
echo "Net Salary: " . number_format($payroll->net_salary ?? 0, 2) . " ✓✓✓\n\n";

echo "=== ATTENDANCE (PRESENÇA) ===\n";
echo "Present Days: " . ($payroll->present_days ?? 0) . "\n";
echo "Absent Days: " . ($payroll->absent_days ?? 0) . "\n";
echo "Late Arrivals: " . ($payroll->late_arrivals ?? 0) . "\n\n";

echo "=== CAMPOS NULL/VAZIOS ===\n";
$nullFields = [];
foreach ($payroll->getAttributes() as $key => $value) {
    if (is_null($value) || $value === 0 || $value === '0.00') {
        $nullFields[] = $key;
    }
}
if (!empty($nullFields)) {
    echo implode(", ", $nullFields) . "\n\n";
} else {
    echo "Todos os campos têm valores!\n\n";
}

echo "=== BATCH INFO ===\n";
if ($payroll->payroll_batch_id) {
    $batchItem = App\Models\HR\PayrollBatchItem::where('payroll_id', $payroll->id)->first();
    if ($batchItem) {
        echo "Batch Item ID: {$batchItem->id}\n";
        echo "Batch Item - Christmas Subsidy (bool): " . ($batchItem->christmas_subsidy ? 'TRUE' : 'FALSE') . "\n";
        echo "Batch Item - Vacation Subsidy (bool): " . ($batchItem->vacation_subsidy ? 'TRUE' : 'FALSE') . "\n";
        echo "Batch Item - Additional Bonus: " . number_format($batchItem->additional_bonus ?? 0, 2) . "\n";
        echo "Batch Item - Christmas Amount: " . number_format($batchItem->christmas_subsidy_amount ?? 0, 2) . "\n";
        echo "Batch Item - Vacation Amount: " . number_format($batchItem->vacation_subsidy_amount ?? 0, 2) . "\n";
    }
}

echo "\n============================================\n";
