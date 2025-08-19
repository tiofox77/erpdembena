<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$employeeId = 14; // Ana Beatriz Lopes

echo "=== DEBUG DETALHADO: Horas Extras Employee ID {$employeeId} ===\n\n";

// 1. Verificar TODOS os registos (incluindo pending, rejected)
echo "=== TODOS OS REGISTOS DE HORAS EXTRAS (TODOS OS STATUS) ===\n";

$allRecords = \App\Models\HR\OvertimeRecord::where('employee_id', $employeeId)
    ->orderBy('date', 'desc')
    ->get();

echo "Total de registos: " . $allRecords->count() . "\n\n";

foreach($allRecords as $record) {
    echo "📋 ID: {$record->id} | Data: {$record->date->format('d/m/Y')} | Status: {$record->status}\n";
    echo "   Horas: {$record->hours} | Valor: {$record->amount} AOA\n";
    echo "   Descrição: " . ($record->description ?: 'N/A') . "\n";
    echo "   ---\n";
}

// 2. Verificar especificamente julho 2025 (TODOS OS STATUS)
echo "\n=== JULHO 2025 - TODOS OS STATUS ===\n";

$julyRecords = \App\Models\HR\OvertimeRecord::where('employee_id', $employeeId)
    ->whereBetween('date', ['2025-07-01', '2025-07-31'])
    ->get();

echo "Registos em julho 2025: " . $julyRecords->count() . "\n";

if ($julyRecords->count() > 0) {
    foreach($julyRecords as $record) {
        echo "📋 {$record->date->format('d/m/Y')}: {$record->hours}h, Status: {$record->status}, Valor: {$record->amount}\n";
    }
} else {
    echo "❌ Nenhum registo em julho 2025\n";
}

// 3. Verificar se há folha de pagamento processada que deveria incluir horas extras
echo "\n=== FOLHAS DE PAGAMENTO EXISTENTES ===\n";

$payrolls = \App\Models\HR\Payroll::where('employee_id', $employeeId)
    ->with('payrollPeriod')
    ->orderBy('created_at', 'desc')
    ->get();

echo "Total de folhas de pagamento: " . $payrolls->count() . "\n";

foreach($payrolls as $payroll) {
    echo "\n📊 Payroll ID: {$payroll->id}\n";
    echo "   Período: " . ($payroll->payrollPeriod ? $payroll->payrollPeriod->name : 'N/A') . "\n";
    if ($payroll->payrollPeriod) {
        echo "   Datas: {$payroll->payrollPeriod->start_date->format('d/m/Y')} - {$payroll->payrollPeriod->end_date->format('d/m/Y')}\n";
    }
    echo "   Horas extras: {$payroll->overtime_hours}h\n";
    echo "   Valor horas extras: {$payroll->overtime_amount} AOA\n";
    echo "   Status: {$payroll->status}\n";
    echo "   Criado em: {$payroll->created_at->format('d/m/Y H:i')}\n";
    
    // Verificar se há horas extras aprovadas no período desta folha que não foram incluídas
    if ($payroll->payrollPeriod) {
        $periodOvertime = \App\Models\HR\OvertimeRecord::where('employee_id', $employeeId)
            ->whereBetween('date', [
                $payroll->payrollPeriod->start_date->format('Y-m-d'),
                $payroll->payrollPeriod->end_date->format('Y-m-d')
            ])
            ->where('status', 'approved')
            ->get();
            
        $totalPeriodHours = $periodOvertime->sum('hours');
        $totalPeriodAmount = $periodOvertime->sum('amount');
        
        echo "   Horas extras disponíveis no período: {$totalPeriodHours}h ({$totalPeriodAmount} AOA)\n";
        
        if ($totalPeriodHours != $payroll->overtime_hours || $totalPeriodAmount != $payroll->overtime_amount) {
            echo "   ⚠️  DISCREPÂNCIA: Valores na folha não coincidem com registos aprovados!\n";
        }
    }
    echo "   ---\n";
}

// 4. Simular o cálculo de horas extras para julho 2025
echo "\n=== SIMULAÇÃO: PROCESSAMENTO JULHO 2025 ===\n";

$julyPeriods = \App\Models\HR\PayrollPeriod::where('start_date', '<=', '2025-07-31')
    ->where('end_date', '>=', '2025-07-01')
    ->get();

foreach($julyPeriods as $period) {
    echo "📅 Período: {$period->name}\n";
    echo "   Datas: {$period->start_date->format('d/m/Y')} - {$period->end_date->format('d/m/Y')}\n";
    
    $periodOvertime = \App\Models\HR\OvertimeRecord::where('employee_id', $employeeId)
        ->whereBetween('date', [
            $period->start_date->format('Y-m-d'),
            $period->end_date->format('Y-m-d')
        ])
        ->where('status', 'approved')
        ->get();
    
    echo "   Horas extras aprovadas: {$periodOvertime->sum('hours')}h\n";
    echo "   Valor total: {$periodOvertime->sum('amount')} AOA\n";
    
    if ($periodOvertime->count() > 0) {
        echo "   Detalhes:\n";
        foreach($periodOvertime as $ot) {
            echo "     - {$ot->date->format('d/m/Y')}: {$ot->hours}h = {$ot->amount} AOA\n";
        }
    }
    echo "   ---\n";
}

echo "\n=== DEBUG CONCLUÍDO ===\n";
