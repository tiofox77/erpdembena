<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$employeeId = 14; // Ana Beatriz Lopes

echo "=== DEBUG: Dados de Presença Employee ID {$employeeId} ===\n\n";

// 1. Verificar dados de presença em julho 2025
$startDate = '2025-07-01';
$endDate = '2025-07-31';

echo "Período: {$startDate} a {$endDate}\n\n";

$attendanceRecords = \App\Models\HR\Attendance::where('employee_id', $employeeId)
    ->whereBetween('date', [$startDate, $endDate])
    ->orderBy('date')
    ->get();

echo "Total de registos de presença: " . $attendanceRecords->count() . "\n\n";

if ($attendanceRecords->count() > 0) {
    $presentDays = $attendanceRecords->where('status', 'present')->count();
    $absentDays = $attendanceRecords->where('status', 'absent')->count();
    $lateDays = $attendanceRecords->where('status', 'late')->count();
    $halfDays = $attendanceRecords->where('status', 'half_day')->count();
    
    echo "📊 RESUMO:\n";
    echo "   Dias presentes: {$presentDays}\n";
    echo "   Dias ausentes: {$absentDays}\n";
    echo "   Dias atrasados: {$lateDays}\n";
    echo "   Meio-dias: {$halfDays}\n";
    echo "   Dias trabalhados: " . ($presentDays + $lateDays + $halfDays) . "\n";
    echo "   Total ausências: {$absentDays}\n\n";
    
    echo "📋 REGISTOS DETALHADOS:\n";
    foreach($attendanceRecords as $record) {
        echo "   {$record->date->format('d/m/Y')}: {$record->status}";
        if ($record->time_in) {
            echo " (Entrada: {$record->time_in->format('H:i')})";
        }
        if ($record->time_out) {
            echo " (Saída: {$record->time_out->format('H:i')})";
        }
        if ($record->remarks) {
            echo " - {$record->remarks}";
        }
        echo "\n";
    }
} else {
    echo "❌ Nenhum registo de presença encontrado!\n\n";
    
    // Verificar se há registos noutros períodos
    $allAttendance = \App\Models\HR\Attendance::where('employee_id', $employeeId)
        ->orderBy('date', 'desc')
        ->limit(10)
        ->get();
    
    echo "Últimos 10 registos de presença (qualquer período):\n";
    foreach($allAttendance as $record) {
        echo "   {$record->date->format('d/m/Y')}: {$record->status}\n";
    }
}

// 2. Verificar estrutura da tabela
echo "\n=== ESTRUTURA DA TABELA ATTENDANCE ===\n";
try {
    $sample = \App\Models\HR\Attendance::first();
    if ($sample) {
        echo "Campos disponíveis: " . implode(', ', array_keys($sample->toArray())) . "\n";
        echo "Status possíveis encontrados: ";
        $statuses = \App\Models\HR\Attendance::distinct('status')->pluck('status')->toArray();
        echo implode(', ', $statuses) . "\n";
    }
} catch (\Exception $e) {
    echo "Erro ao verificar estrutura: " . $e->getMessage() . "\n";
}

echo "\n=== DEBUG CONCLUÍDO ===\n";
