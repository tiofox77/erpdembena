<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\HR\Payroll;

echo "=== VERIFICAÇÃO DE PAYROLLS ===\n\n";

$total = Payroll::count();
echo "Total de Payrolls: {$total}\n\n";

if ($total > 0) {
    echo "Últimos 10 Payrolls:\n";
    $payrolls = Payroll::with(['employee', 'payrollPeriod'])
        ->orderBy('created_at', 'desc')
        ->limit(10)
        ->get();
    
    foreach ($payrolls as $payroll) {
        echo "ID: {$payroll->id} | ";
        echo "Funcionário: " . ($payroll->employee->full_name ?? 'N/A') . " | ";
        echo "Período: " . ($payroll->payrollPeriod->name ?? 'N/A') . " | ";
        echo "Salário Líquido: " . number_format($payroll->net_salary, 2) . " AOA | ";
        echo "Status: {$payroll->status}\n";
    }
} else {
    echo "❌ Nenhum payroll encontrado no banco de dados!\n";
    echo "\nPossíveis causas:\n";
    echo "1. Nenhum payroll foi criado ainda\n";
    echo "2. Erro ao salvar payrolls\n";
    echo "3. Tabela está vazia\n";
}

echo "\n=== FIM ===\n";
