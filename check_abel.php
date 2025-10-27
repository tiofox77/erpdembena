<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\HR\Employee;
use App\Models\HR\Attendance;

echo "=== VERIFICANDO ABEL FRANCISCO ===\n\n";

$employee = Employee::where('biometric_id', '1')->first();

if ($employee) {
    echo "✓ Funcionário encontrado:\n";
    echo "  Nome: {$employee->full_name}\n";
    echo "  ID: {$employee->id}\n";
    echo "  Biometric ID: {$employee->biometric_id}\n\n";
    
    $attendances = Attendance::where('employee_id', $employee->id)->get();
    echo "Total de presenças: " . $attendances->count() . "\n\n";
    
    if ($attendances->count() > 0) {
        echo "Registos encontrados:\n";
        foreach ($attendances as $att) {
            $timeIn = $att->time_in ? $att->time_in->format('H:i') : 'null';
            $timeOut = $att->time_out ? $att->time_out->format('H:i') : 'null';
            echo "  - Data: {$att->date->format('Y-m-d')}, Status: {$att->status}, In: {$timeIn}, Out: {$timeOut}\n";
        }
    } else {
        echo "❌ Nenhum registo de presença encontrado!\n";
    }
} else {
    echo "❌ Funcionário com biometric_id=1 não encontrado\n";
}

echo "\n=== TOTAL DE PRESENÇAS NA BD ===\n";
echo "Total geral: " . Attendance::count() . "\n";
