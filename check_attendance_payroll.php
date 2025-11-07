<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

echo "=================================================\n";
echo "🔍 DIAGNÓSTICO: Attendance vs Payroll Period\n";
echo "=================================================\n\n";

// 1. Verificar períodos de payroll existentes
echo "📅 PERÍODOS DE PAYROLL CADASTRADOS:\n";
echo "-------------------------------------------------\n";
$periods = DB::table('payroll_periods')
    ->orderBy('start_date', 'desc')
    ->get();

foreach ($periods as $period) {
    echo "ID: {$period->id}\n";
    echo "Nome: {$period->name}\n";
    echo "Início: {$period->start_date}\n";
    echo "Fim: {$period->end_date}\n";
    echo "Status: {$period->status}\n";
    echo "-------------------------------------------------\n";
}

// 2. Pedir ao usuário qual período verificar
echo "\n";
echo "Digite o ID do período que deseja verificar: ";
$periodId = trim(fgets(STDIN));

$selectedPeriod = DB::table('payroll_periods')->find($periodId);

if (!$selectedPeriod) {
    echo "❌ Período não encontrado!\n";
    exit(1);
}

echo "\n✅ Período selecionado: {$selectedPeriod->name}\n";
echo "   Início: {$selectedPeriod->start_date}\n";
echo "   Fim: {$selectedPeriod->end_date}\n\n";

// 3. Listar funcionários
echo "👥 FUNCIONÁRIOS ATIVOS:\n";
echo "-------------------------------------------------\n";
$employees = DB::table('employees')
    ->where('employment_status', 'active')
    ->select('id', 'first_name', 'last_name', 'employee_number', 'biometric_id')
    ->orderBy('first_name')
    ->get();

foreach ($employees as $emp) {
    echo "ID: {$emp->id} | {$emp->first_name} {$emp->last_name} | Nº: {$emp->employee_number} | Bio: " . ($emp->biometric_id ?? 'N/A') . "\n";
}

// 4. Pedir employee_id
echo "\nDigite o ID do funcionário para verificar: ";
$employeeId = trim(fgets(STDIN));

$employee = DB::table('employees')->find($employeeId);
if (!$employee) {
    echo "❌ Funcionário não encontrado!\n";
    exit(1);
}

echo "\n✅ Funcionário selecionado: {$employee->first_name} {$employee->last_name}\n\n";

// 5. Buscar registros de attendance
echo "🔍 BUSCANDO REGISTROS DE ATTENDANCE...\n";
echo "-------------------------------------------------\n";

$attendances = DB::table('attendances')
    ->where('employee_id', $employeeId)
    ->whereBetween('date', [$selectedPeriod->start_date, $selectedPeriod->end_date])
    ->orderBy('date', 'asc')
    ->get();

echo "Total de registros encontrados: " . $attendances->count() . "\n\n";

if ($attendances->count() > 0) {
    echo "📊 DETALHES DOS REGISTROS:\n";
    echo "-------------------------------------------------\n";
    foreach ($attendances as $att) {
        echo "Data: {$att->date} | Status: {$att->status} | Entrada: " . ($att->time_in ?? 'N/A') . " | Saída: " . ($att->time_out ?? 'N/A') . "\n";
    }
} else {
    echo "❌ NENHUM REGISTRO ENCONTRADO!\n\n";
    
    // Verificar se existem registros para esse funcionário em outros períodos
    echo "🔍 Verificando registros em OUTROS períodos...\n";
    echo "-------------------------------------------------\n";
    
    $otherAttendances = DB::table('attendances')
        ->where('employee_id', $employeeId)
        ->selectRaw('DATE_FORMAT(date, "%Y-%m") as month, COUNT(*) as total')
        ->groupBy('month')
        ->orderBy('month', 'desc')
        ->limit(12)
        ->get();
    
    if ($otherAttendances->count() > 0) {
        echo "Registros encontrados em outros meses:\n";
        foreach ($otherAttendances as $month) {
            echo "  • {$month->month}: {$month->total} registros\n";
        }
    } else {
        echo "❌ Este funcionário NÃO tem NENHUM registro de attendance na tabela!\n";
    }
    
    echo "\n";
    echo "🔍 Verificando se existem registros com OUTROS employee_id...\n";
    echo "-------------------------------------------------\n";
    
    // Verificar se existe attendance com biometric_id correspondente
    if ($employee->biometric_id) {
        $bioAttendances = DB::table('attendances')
            ->where('biometric_id', $employee->biometric_id)
            ->whereBetween('date', [$selectedPeriod->start_date, $selectedPeriod->end_date])
            ->count();
        
        echo "Registros com biometric_id '{$employee->biometric_id}': {$bioAttendances}\n";
        
        if ($bioAttendances > 0) {
            echo "⚠️ PROBLEMA IDENTIFICADO: Existem registros com biometric_id mas com employee_id diferente!\n";
            
            // Mostrar detalhes
            $details = DB::table('attendances')
                ->where('biometric_id', $employee->biometric_id)
                ->whereBetween('date', [$selectedPeriod->start_date, $selectedPeriod->end_date])
                ->select('employee_id', DB::raw('COUNT(*) as total'))
                ->groupBy('employee_id')
                ->get();
            
            echo "Distribuição:\n";
            foreach ($details as $detail) {
                echo "  • employee_id: " . ($detail->employee_id ?? 'NULL') . " -> {$detail->total} registros\n";
            }
        }
    }
}

echo "\n";
echo "🔍 ANÁLISE ADICIONAL:\n";
echo "-------------------------------------------------\n";

// Verificar estrutura da tabela attendance
echo "Estrutura da tabela 'attendances':\n";
$columns = DB::select("SHOW COLUMNS FROM attendances");
foreach ($columns as $col) {
    if (in_array($col->Field, ['id', 'employee_id', 'date', 'time_in', 'time_out', 'status', 'biometric_id'])) {
        echo "  • {$col->Field} ({$col->Type})\n";
    }
}

echo "\n";
echo "📊 Total de registros na tabela attendances: " . DB::table('attendances')->count() . "\n";

// Verificar range de datas
$dateRange = DB::table('attendances')
    ->selectRaw('MIN(date) as min_date, MAX(date) as max_date')
    ->first();

if ($dateRange->min_date) {
    echo "Range de datas: {$dateRange->min_date} até {$dateRange->max_date}\n";
}

echo "\n";
echo "🔍 VERIFICANDO QUERY EXATA DO LIVEWIRE:\n";
echo "-------------------------------------------------\n";
echo "Query executada:\n";
echo "SELECT * FROM attendances\n";
echo "WHERE employee_id = {$employeeId}\n";
echo "AND date BETWEEN '{$selectedPeriod->start_date}' AND '{$selectedPeriod->end_date}'\n";
echo "ORDER BY date ASC\n\n";

// Executar a query exata e mostrar SQL
$query = DB::table('attendances')
    ->where('employee_id', $employeeId)
    ->whereBetween('date', [$selectedPeriod->start_date, $selectedPeriod->end_date])
    ->orderBy('date', 'asc');

echo "SQL Real: " . $query->toSql() . "\n";
echo "Bindings: " . json_encode($query->getBindings()) . "\n";
echo "Resultado: " . $query->count() . " registros\n";

echo "\n=================================================\n";
echo "FIM DO DIAGNÓSTICO\n";
echo "=================================================\n";
