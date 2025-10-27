<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\HR\Employee;
use App\Models\HR\Attendance;
use Carbon\Carbon;

echo "=== Adicionando Presenças para Funcionário ID 7 ===\n\n";

// Verificar se funcionário existe
$employee = Employee::find(7);
if (!$employee) {
    echo "❌ Funcionário com ID 7 não encontrado!\n";
    exit(1);
}

echo "✅ Funcionário encontrado: {$employee->full_name}\n";
echo "📍 Departamento: " . ($employee->department ? $employee->department->name : 'N/A') . "\n\n";

// Função para verificar se é dia útil (segunda a sexta)
function isWorkingDay($date) {
    $dayOfWeek = $date->dayOfWeek;
    return $dayOfWeek >= 1 && $dayOfWeek <= 5; // 1=Segunda, 5=Sexta
}

// Função para adicionar presenças de um mês
function addAttendanceForMonth($employeeId, $year, $month, $employeeName) {
    $startDate = Carbon::createFromDate($year, $month, 1);
    $endDate = $startDate->copy()->endOfMonth();
    
    $monthName = $startDate->format('F Y');
    echo "📅 Processando {$monthName}...\n";
    
    $workingDays = 0;
    $addedDays = 0;
    $skippedDays = 0;
    
    $current = $startDate->copy();
    while ($current <= $endDate) {
        if (isWorkingDay($current)) {
            $workingDays++;
            
            // Verificar se já existe registro para este dia
            $existing = Attendance::where('employee_id', $employeeId)
                                ->where('date', $current->format('Y-m-d'))
                                ->first();
            
            if ($existing) {
                echo "   ⚠️  {$current->format('d/m/Y')} - Já existe registro\n";
                $skippedDays++;
            } else {
                // Criar registro de presença
                $timeIn = $current->copy()->setTime(8, 0, 0); // 08:00
                $timeOut = $current->copy()->setTime(17, 0, 0); // 17:00
                
                Attendance::create([
                    'employee_id' => $employeeId,
                    'date' => $current->format('Y-m-d'),
                    'time_in' => $timeIn,
                    'time_out' => $timeOut,
                    'status' => 'present',
                    'remarks' => 'Presença adicionada automaticamente - Jornada completa',
                ]);
                
                echo "   ✅ {$current->format('d/m/Y')} - Presente (08:00-17:00)\n";
                $addedDays++;
            }
        }
        $current->addDay();
    }
    
    echo "   📊 Resumo {$monthName}:\n";
    echo "      - Dias úteis: {$workingDays}\n";
    echo "      - Adicionados: {$addedDays}\n";
    echo "      - Já existiam: {$skippedDays}\n\n";
    
    return ['working' => $workingDays, 'added' => $addedDays, 'skipped' => $skippedDays];
}

// Adicionar presenças para Julho 2025
echo "🗓️  JULHO 2025\n";
$julyStats = addAttendanceForMonth(7, 2025, 7, $employee->full_name);

// Adicionar presenças para Agosto 2025  
echo "🗓️  AGOSTO 2025\n";
$augustStats = addAttendanceForMonth(7, 2025, 8, $employee->full_name);

// Resumo final
echo "=== RESUMO FINAL ===\n";
echo "👤 Funcionário: {$employee->full_name} (ID: 7)\n";
echo "📅 Períodos: Julho e Agosto 2025\n";
echo "📊 Estatísticas:\n";
echo "   - Total dias úteis: " . ($julyStats['working'] + $augustStats['working']) . "\n";
echo "   - Total adicionados: " . ($julyStats['added'] + $augustStats['added']) . "\n";
echo "   - Total já existiam: " . ($julyStats['skipped'] + $augustStats['skipped']) . "\n";

// Verificar registros criados
$totalRecords = Attendance::where('employee_id', 7)
                         ->whereBetween('date', ['2025-07-01', '2025-08-31'])
                         ->count();

echo "✅ Total de registros de presença para Jul-Ago 2025: {$totalRecords}\n";
echo "\n🎉 Processo concluído com sucesso!\n";
