<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

echo "=================================================\n";
echo "🔍 DIAGNÓSTICO AUTOMÁTICO: Attendance vs Payroll\n";
echo "=================================================\n\n";

// 1. Verificar períodos de payroll existentes
echo "📅 PERÍODOS DE PAYROLL CADASTRADOS:\n";
echo "-------------------------------------------------\n";
$periods = DB::table('payroll_periods')
    ->orderBy('start_date', 'desc')
    ->get();

if ($periods->isEmpty()) {
    echo "❌ Nenhum período cadastrado!\n\n";
    exit(1);
}

foreach ($periods as $period) {
    echo "ID: {$period->id} | {$period->name} | {$period->start_date} até {$period->end_date} | Status: {$period->status}\n";
}

// Pegar o período mais recente (primeiro da lista)
$selectedPeriod = $periods->first();

echo "\n✅ Verificando período: {$selectedPeriod->name}\n";
echo "   Início: {$selectedPeriod->start_date}\n";
echo "   Fim: {$selectedPeriod->end_date}\n\n";

// 2. Listar funcionários ativos
echo "👥 FUNCIONÁRIOS ATIVOS:\n";
echo "-------------------------------------------------\n";
$employees = DB::table('employees')
    ->where('employment_status', 'active')
    ->select('id', 'full_name', 'biometric_id')
    ->orderBy('full_name')
    ->limit(10)
    ->get();

foreach ($employees as $emp) {
    echo "ID: {$emp->id} | {$emp->full_name} | Biometric ID: " . ($emp->biometric_id ?? 'N/A') . "\n";
}

echo "\n";

// 3. Para cada funcionário, verificar attendance
foreach ($employees as $employee) {
    echo "\n=================================================\n";
    echo "🔍 Verificando: {$employee->full_name} (ID: {$employee->id})\n";
    echo "=================================================\n";
    
    // Buscar registros de attendance
    $attendances = DB::table('attendances')
        ->where('employee_id', $employee->id)
        ->whereBetween('date', [$selectedPeriod->start_date, $selectedPeriod->end_date])
        ->orderBy('date', 'asc')
        ->get();
    
    echo "✅ Registros encontrados: " . $attendances->count() . "\n";
    
    if ($attendances->count() > 0) {
        echo "\n📊 Primeiros 5 registros:\n";
        foreach ($attendances->take(5) as $att) {
            echo "  • {$att->date} | Status: {$att->status} | Entrada: " . ($att->time_in ?? 'N/A') . "\n";
        }
    } else {
        echo "❌ NENHUM registro no período!\n";
        
        // Verificar se tem registros em outros períodos
        $otherAttendances = DB::table('attendances')
            ->where('employee_id', $employee->id)
            ->selectRaw('COUNT(*) as total, MIN(date) as min_date, MAX(date) as max_date')
            ->first();
        
        if ($otherAttendances->total > 0) {
            echo "⚠️ MAS existem {$otherAttendances->total} registros em outros períodos ({$otherAttendances->min_date} até {$otherAttendances->max_date})\n";
        } else {
            echo "⚠️ Este funcionário NÃO tem NENHUM registro na tabela attendances!\n";
        }
        
        // Verificar por biometric_id
        if ($employee->biometric_id) {
            $bioAttendances = DB::table('attendances')
                ->where('biometric_id', $employee->biometric_id)
                ->whereBetween('date', [$selectedPeriod->start_date, $selectedPeriod->end_date])
                ->get();
            
            if ($bioAttendances->count() > 0) {
                echo "🔍 ENCONTRADOS {$bioAttendances->count()} registros com biometric_id '{$employee->biometric_id}' mas employee_id DIFERENTE:\n";
                
                foreach ($bioAttendances->take(3) as $bio) {
                    echo "  • Date: {$bio->date} | employee_id na tabela: " . ($bio->employee_id ?? 'NULL') . " | Status: {$bio->status}\n";
                }
                
                echo "\n⚠️ PROBLEMA: Os registros têm biometric_id correto mas employee_id está errado ou NULL!\n";
            }
        }
    }
}

echo "\n\n=================================================\n";
echo "📊 ESTATÍSTICAS GERAIS DA TABELA ATTENDANCES\n";
echo "=================================================\n";

$stats = DB::table('attendances')->selectRaw('
    COUNT(*) as total,
    COUNT(DISTINCT employee_id) as distinct_employees,
    COUNT(DISTINCT biometric_id) as distinct_biometric,
    MIN(date) as min_date,
    MAX(date) as max_date,
    SUM(CASE WHEN employee_id IS NULL THEN 1 ELSE 0 END) as null_employee_id
')->first();

echo "Total de registros: {$stats->total}\n";
echo "Funcionários distintos (employee_id): {$stats->distinct_employees}\n";
echo "IDs biométricos distintos: {$stats->distinct_biometric}\n";
echo "Range de datas: {$stats->min_date} até {$stats->max_date}\n";
echo "Registros com employee_id NULL: {$stats->null_employee_id}\n";

// Verificar distribuição por mês
echo "\n📅 DISTRIBUIÇÃO POR MÊS:\n";
echo "-------------------------------------------------\n";
$monthly = DB::table('attendances')
    ->selectRaw('DATE_FORMAT(date, "%Y-%m") as month, COUNT(*) as total')
    ->groupBy('month')
    ->orderBy('month', 'desc')
    ->limit(12)
    ->get();

foreach ($monthly as $m) {
    echo "  • {$m->month}: {$m->total} registros\n";
}

// Verificar se o período de setembro tem registros
echo "\n🔍 REGISTROS NO PERÍODO DE SETEMBRO (2025-09-20 até 2025-10-20):\n";
echo "-------------------------------------------------\n";
$sepRecords = DB::table('attendances')
    ->whereBetween('date', ['2025-09-20', '2025-10-20'])
    ->selectRaw('COUNT(*) as total, COUNT(DISTINCT employee_id) as employees')
    ->first();

echo "Total de registros: {$sepRecords->total}\n";
echo "Funcionários com registro: {$sepRecords->employees}\n";

if ($sepRecords->total > 0) {
    echo "\n✅ Existem registros neste período!\n";
    echo "\n📊 Distribuição por employee_id:\n";
    
    $distribution = DB::table('attendances')
        ->whereBetween('date', ['2025-09-20', '2025-10-20'])
        ->selectRaw('employee_id, COUNT(*) as total')
        ->groupBy('employee_id')
        ->orderByDesc('total')
        ->limit(10)
        ->get();
    
    foreach ($distribution as $dist) {
        $empName = DB::table('employees')->find($dist->employee_id);
        $name = $empName ? $empName->full_name : "Desconhecido";
        echo "  • employee_id: " . ($dist->employee_id ?? 'NULL') . " ({$name}): {$dist->total} registros\n";
    }
} else {
    echo "❌ NÃO existem registros neste período!\n";
    echo "\n🔍 Verificando datas próximas...\n";
    
    $nearby = DB::table('attendances')
        ->whereBetween('date', ['2025-09-01', '2025-10-31'])
        ->selectRaw('DATE_FORMAT(date, "%Y-%m-%d") as day, COUNT(*) as total')
        ->groupBy('day')
        ->orderBy('day')
        ->get();
    
    if ($nearby->count() > 0) {
        echo "Registros encontrados em setembro/outubro:\n";
        foreach ($nearby as $day) {
            echo "  • {$day->day}: {$day->total} registros\n";
        }
    }
}

echo "\n=================================================\n";
echo "FIM DO DIAGNÓSTICO\n";
echo "=================================================\n";
