<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=================================================\n";
echo "🔍 DIAGNÓSTICO: Employee ID Mismatch\n";
echo "=================================================\n\n";

// Buscar employee_ids que têm registros de attendance
echo "📊 Employee IDs na tabela ATTENDANCES (período setembro):\n";
echo "-------------------------------------------------\n";

$attendanceEmpIds = DB::table('attendances')
    ->whereBetween('date', ['2025-09-20', '2025-10-20'])
    ->selectRaw('employee_id, COUNT(*) as total')
    ->groupBy('employee_id')
    ->orderBy('employee_id')
    ->limit(20)
    ->get();

echo "Total de employee_ids distintos: " . $attendanceEmpIds->count() . "\n\n";

$foundInEmployees = 0;
$notFoundInEmployees = 0;
$mismatches = [];

foreach ($attendanceEmpIds as $attEmp) {
    $empId = $attEmp->employee_id;
    
    // Verificar se existe na tabela employees
    $employee = DB::table('employees')->find($empId);
    
    if ($employee) {
        $foundInEmployees++;
        echo "✅ ID {$empId} -> {$attEmp->total} registros | {$employee->full_name}\n";
    } else {
        $notFoundInEmployees++;
        echo "❌ ID {$empId} -> {$attEmp->total} registros | NÃO EXISTE na tabela employees!\n";
        $mismatches[] = $empId;
    }
}

echo "\n=================================================\n";
echo "📊 RESUMO:\n";
echo "-------------------------------------------------\n";
echo "✅ IDs encontrados na tabela employees: {$foundInEmployees}\n";
echo "❌ IDs NÃO encontrados na tabela employees: {$notFoundInEmployees}\n";

if ($notFoundInEmployees > 0) {
    echo "\n⚠️  PROBLEMA CRÍTICO IDENTIFICADO!\n";
    echo "-------------------------------------------------\n";
    echo "Os registros de attendance têm employee_ids que NÃO existem\n";
    echo "na tabela employees. Isso impede o cálculo de payroll.\n\n";
    
    echo "💡 SOLUÇÕES POSSÍVEIS:\n";
    echo "1. Corrigir os employee_ids na tabela attendances\n";
    echo "2. Reimportar os dados de attendance usando os IDs corretos\n";
    echo "3. Criar um script de correção para mapear IDs antigos para novos\n";
}

// Verificar também o inverso: employees sem attendance
echo "\n\n🔍 Funcionários ATIVOS sem registros de attendance no período:\n";
echo "-------------------------------------------------\n";

$employeesWithoutAttendance = DB::table('employees')
    ->where('employment_status', 'active')
    ->whereNotIn('id', function($query) {
        $query->select('employee_id')
            ->from('attendances')
            ->whereBetween('date', ['2025-09-20', '2025-10-20'])
            ->distinct();
    })
    ->select('id', 'full_name')
    ->limit(20)
    ->get();

echo "Total: " . $employeesWithoutAttendance->count() . " funcionários\n\n";

foreach ($employeesWithoutAttendance as $emp) {
    echo "  • ID {$emp->id}: {$emp->full_name}\n";
}

echo "\n=================================================\n";
echo "FIM DO DIAGNÓSTICO\n";
echo "=================================================\n";
