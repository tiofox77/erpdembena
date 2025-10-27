<?php

require_once __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\HR\Employee;
use App\Models\HR\Attendance;
use Carbon\Carbon;

echo "Procurando funcionário Dinis Paulo...\n";

// Procurar funcionário
$employee = Employee::where('full_name', 'like', '%Dinis Paulo%')->first();

if (!$employee) {
    echo "❌ Funcionário não encontrado!\n";
    echo "Listando alguns funcionários:\n";
    Employee::take(10)->get(['id', 'full_name'])->each(function($emp) {
        echo "- ID: {$emp->id}, Nome: {$emp->full_name}\n";
    });
    exit;
}

echo "✅ Funcionário encontrado:\n";
echo "ID: {$employee->id}\n";
echo "Nome: {$employee->full_name}\n";
echo "Departamento: " . ($employee->department->name ?? 'N/A') . "\n";
echo "Salário base: " . ($employee->base_salary ?? 'N/A') . "\n\n";

// Verificar presenças para 07/2025
$startDate = Carbon::createFromDate(2025, 7, 1)->startOfMonth();
$endDate = $startDate->copy()->endOfMonth();

echo "Verificando presenças para período: {$startDate->format('Y-m-d')} a {$endDate->format('Y-m-d')}\n";

$attendances = Attendance::where('employee_id', $employee->id)
    ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
    ->get();

echo "Total de registos de presenças encontrados: " . $attendances->count() . "\n";

if ($attendances->count() > 0) {
    echo "\nDetalhes das presenças:\n";
    foreach ($attendances as $attendance) {
        echo "- Data: {$attendance->date}, Status: {$attendance->status}, Entrada: {$attendance->time_in}, Saída: {$attendance->time_out}\n";
    }
} else {
    echo "\n❌ Nenhuma presença encontrada para este período!\n";
    
    // Verificar se há presenças em outros meses
    $totalAttendances = Attendance::where('employee_id', $employee->id)->count();
    echo "Total de presenças em toda a base de dados para este funcionário: {$totalAttendances}\n";
    
    if ($totalAttendances > 0) {
        echo "\nÚltimas presenças registadas:\n";
        Attendance::where('employee_id', $employee->id)
            ->orderBy('date', 'desc')
            ->take(5)
            ->get()
            ->each(function($att) {
                echo "- Data: {$att->date}, Status: {$att->status}\n";
            });
    }
}
