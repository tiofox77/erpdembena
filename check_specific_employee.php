<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

// Pegar um employee_id que você testou no payroll
echo "Digite o employee_id que você tentou processar no payroll: ";
$employeeId = trim(fgets(STDIN));

echo "\n=================================================\n";
echo "🔍 VERIFICANDO EMPLOYEE_ID: {$employeeId}\n";
echo "=================================================\n\n";

// Verificar se o funcionário existe
$employee = DB::table('employees')->find($employeeId);

if (!$employee) {
    echo "❌ Funcionário com ID {$employeeId} NÃO EXISTE na tabela employees!\n";
    exit(1);
}

echo "✅ Funcionário encontrado: {$employee->full_name}\n";
echo "   Biometric ID: " . ($employee->biometric_id ?? 'N/A') . "\n";
echo "   Status: {$employee->employment_status}\n\n";

// Verificar registros de attendance
echo "🔍 BUSCANDO REGISTROS DE ATTENDANCE...\n";
echo "-------------------------------------------------\n";

$attendances = DB::table('attendances')
    ->where('employee_id', $employeeId)
    ->whereBetween('date', ['2025-09-20', '2025-10-20'])
    ->orderBy('date')
    ->get();

echo "Total encontrado: {$attendances->count()} registros\n\n";

if ($attendances->count() > 0) {
    echo "✅ REGISTROS ENCONTRADOS! Primeiros 10:\n";
    echo "-------------------------------------------------\n";
    foreach ($attendances->take(10) as $att) {
        echo "Data: {$att->date} | Status: {$att->status} | Entrada: " . ($att->time_in ?? 'N/A') . "\n";
    }
} else {
    echo "❌ NENHUM registro encontrado!\n\n";
    
    echo "🔍 Verificando employee_ids que TÊM registros no período...\n";
    echo "-------------------------------------------------\n";
    
    $empIds = DB::table('attendances')
        ->whereBetween('date', ['2025-09-20', '2025-10-20'])
        ->selectRaw('employee_id, COUNT(*) as total')
        ->groupBy('employee_id')
        ->orderByDesc('total')
        ->limit(20)
        ->get();
    
    echo "Employee IDs com mais registros:\n";
    foreach ($empIds as $emp) {
        // Tentar pegar o nome
        $empData = DB::table('employees')->find($emp->employee_id);
        $name = $empData ? $empData->full_name : "Não encontrado na tabela employees!";
        
        echo "  • employee_id: {$emp->employee_id} -> {$emp->total} registros | Nome: {$name}\n";
    }
    
    echo "\n⚠️ PROBLEMA IDENTIFICADO:\n";
    echo "Os registros de attendance têm employee_ids DIFERENTES dos IDs na tabela employees!\n";
    echo "Isso pode ter acontecido na importação do ponto.\n";
}

echo "\n=================================================\n";
echo "FIM\n";
echo "=================================================\n";
