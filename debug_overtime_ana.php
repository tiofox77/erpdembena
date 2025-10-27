<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== DEBUG: Horas Extras Ana Beatriz Lopes - Julho 2025 ===\n\n";

// 1. Buscar funcionária Ana Beatriz Lopes
$employee = \App\Models\HR\Employee::where('full_name', 'like', '%Ana Beatriz Lopes%')->first();

if (!$employee) {
    echo "❌ Funcionária Ana Beatriz Lopes não encontrada!\n";
    
    // Listar funcionários similares
    $similar = \App\Models\HR\Employee::where('full_name', 'like', '%Ana%')
        ->orWhere('full_name', 'like', '%Beatriz%')
        ->orWhere('full_name', 'like', '%Lopes%')
        ->get();
    
    echo "\nFuncionários com nomes similares:\n";
    foreach($similar as $emp) {
        echo "- ID: {$emp->id}, Nome: {$emp->full_name}\n";
    }
    exit;
}

echo "✅ Funcionária encontrada:\n";
echo "   ID: {$employee->id}\n";
echo "   Nome: {$employee->full_name}\n";
echo "   Email: {$employee->email}\n";
echo "   Status: {$employee->status}\n\n";

// 2. Verificar TODOS os registos de horas extras do funcionário
echo "=== TODOS OS REGISTOS DE HORAS EXTRAS ===\n";

$allOvertimeRecords = \App\Models\HR\OvertimeRecord::where('employee_id', $employee->id)
    ->orderBy('date')
    ->get();

echo "Total de registos encontrados (TODOS): " . $allOvertimeRecords->count() . "\n\n";

if ($allOvertimeRecords->count() > 0) {
    foreach($allOvertimeRecords as $record) {
        echo "📋 Registo ID: {$record->id}\n";
        echo "   Data: {$record->date->format('d/m/Y')}\n";
        echo "   Início: {$record->start_time}\n";
        echo "   Fim: {$record->end_time}\n";
        echo "   Horas: {$record->hours}\n";
        echo "   Taxa: {$record->rate} AOA\n";
        echo "   Valor: {$record->amount} AOA\n";
        echo "   Status: {$record->status}\n";
        echo "   Aprovado por: " . ($record->approved_by ? $record->approved_by : 'N/A') . "\n";
        echo "   Aprovado em: " . ($record->approved_at ? $record->approved_at->format('d/m/Y') : 'N/A') . "\n";
        echo "   Descrição: {$record->description}\n";
        echo "   ---\n";
    }
}

// 2b. Verificar especificamente julho 2025
echo "\n=== REGISTOS DE HORAS EXTRAS - JULHO 2025 ===\n";

$overtimeRecords = \App\Models\HR\OvertimeRecord::where('employee_id', $employee->id)
    ->whereBetween('date', ['2025-07-01', '2025-07-31'])
    ->orderBy('date')
    ->get();

echo "Total de registos em julho 2025: " . $overtimeRecords->count() . "\n\n";

if ($overtimeRecords->count() > 0) {
    foreach($overtimeRecords as $record) {
        echo "📋 Registo ID: {$record->id}\n";
        echo "   Data: {$record->date->format('d/m/Y')}\n";
        echo "   Início: {$record->start_time}\n";
        echo "   Fim: {$record->end_time}\n";
        echo "   Horas: {$record->hours}\n";
        echo "   Taxa: {$record->rate} AOA\n";
        echo "   Valor: {$record->amount} AOA\n";
        echo "   Status: {$record->status}\n";
        echo "   Aprovado por: " . ($record->approved_by ? $record->approved_by : 'N/A') . "\n";
        echo "   Aprovado em: " . ($record->approved_at ? $record->approved_at->format('d/m/Y') : 'N/A') . "\n";
        echo "   Descrição: {$record->description}\n";
        echo "   ---\n";
    }
} else {
    echo "❌ Nenhum registo de horas extras encontrado para julho 2025!\n\n";
    
    // Verificar outros meses
    echo "=== VERIFICANDO OUTROS MESES ===\n";
    $allOvertime = \App\Models\HR\OvertimeRecord::where('employee_id', $employee->id)->get();
    echo "Total de registos de horas extras (todos os meses): " . $allOvertime->count() . "\n";
    
    if ($allOvertime->count() > 0) {
        echo "Últimos 5 registos:\n";
        $recent = $allOvertime->sortByDesc('date')->take(5);
        foreach($recent as $record) {
            echo "- {$record->date->format('d/m/Y')}: {$record->hours}h, Status: {$record->status}\n";
        }
    }
}

// 3. Verificar período de folha de pagamento para julho 2025
echo "\n=== PERÍODOS DE FOLHA DE PAGAMENTO ===\n";
$payrollPeriods = \App\Models\HR\PayrollPeriod::where('start_date', '<=', '2025-07-31')
    ->where('end_date', '>=', '2025-07-01')
    ->get();

echo "Períodos que cobrem julho 2025: " . $payrollPeriods->count() . "\n";
foreach($payrollPeriods as $period) {
    echo "- {$period->name}: {$period->start_date->format('d/m/Y')} - {$period->end_date->format('d/m/Y')}\n";
}

// 4. Verificar folha de pagamento existente
echo "\n=== FOLHA DE PAGAMENTO EXISTENTE ===\n";
$payrolls = \App\Models\HR\Payroll::where('employee_id', $employee->id)
    ->whereHas('payrollPeriod', function($query) {
        $query->where('start_date', '<=', '2025-07-31')
              ->where('end_date', '>=', '2025-07-01');
    })
    ->with('payrollPeriod')
    ->get();

echo "Folhas de pagamento para julho 2025: " . $payrolls->count() . "\n";
foreach($payrolls as $payroll) {
    echo "- ID: {$payroll->id}\n";
    echo "  Período: {$payroll->payrollPeriod->name}\n";
    echo "  Horas extras: {$payroll->overtime_hours}h\n";
    echo "  Valor horas extras: {$payroll->overtime_amount} AOA\n";
    echo "  Status: {$payroll->status}\n";
    echo "  ---\n";
}

echo "\n=== DEBUG CONCLUÍDO ===\n";
