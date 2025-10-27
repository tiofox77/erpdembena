<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\HR\Employee;
use App\Models\HR\ShiftAssignment;
use App\Models\HR\Shift;
use Carbon\Carbon;

/**
 * Script para configurar rotação semanal de turnos para funcionários específicos
 * 
 * Como usar:
 * 1. Edite o array $employeeIds abaixo com os IDs dos funcionários
 * 2. Execute: php configure_employee_rotation.php
 */

echo "=== CONFIGURAR ROTAÇÃO SEMANAL DE TURNOS ===\n\n";

// ====================================================================
// CONFIGURAÇÃO: Edite aqui os IDs dos funcionários que precisam rotação
// ====================================================================
$employeeIds = [
    // Exemplo: 4, 69, 82
    // Adicione os IDs dos funcionários aqui
];

if (empty($employeeIds)) {
    echo "ℹ️  Configure os IDs dos funcionários no array \$employeeIds\n";
    echo "Exemplo: \$employeeIds = [4, 69, 82];\n";
    exit(0);
}

// Buscar shifts
$morningShift = Shift::where('name', 'LIKE', '%Morning%')->first();
$nightShift = Shift::where('name', 'LIKE', '%Night%')->orWhere('name', 'LIKE', '%Nigth%')->first();

if (!$morningShift || !$nightShift) {
    echo "❌ Turnos não encontrados!\n";
    exit(1);
}

echo "Configuração de Rotação:\n";
echo "  • Turno A: {$morningShift->name}\n";
echo "  • Turno B: {$nightShift->name}\n";
echo "  • Tipo: Semanal (alterna cada semana)\n\n";

echo "Funcionários a configurar: " . count($employeeIds) . "\n\n";

$today = Carbon::today();
$updated = 0;
$notFound = 0;

foreach ($employeeIds as $employeeId) {
    $employee = Employee::find($employeeId);
    
    if (!$employee) {
        echo "✗ Funcionário ID {$employeeId} não encontrado\n";
        $notFound++;
        continue;
    }
    
    // Buscar ou criar assignment
    $assignment = ShiftAssignment::where('employee_id', $employeeId)
        ->where('start_date', '<=', $today)
        ->where(function($query) use ($today) {
            $query->whereNull('end_date')
                ->orWhere('end_date', '>=', $today);
        })
        ->first();
    
    if (!$assignment) {
        // Criar novo assignment
        $assignment = new ShiftAssignment();
        $assignment->employee_id = $employeeId;
        $assignment->shift_id = $morningShift->id;
        $assignment->start_date = $today;
        $assignment->end_date = null;
        $assignment->is_permanent = true;
    }
    
    // Configurar rotação
    $assignment->rotation_pattern = [
        'type' => 'weekly',
        'interval' => 1,
        'shifts' => [$morningShift->id, $nightShift->id],
    ];
    
    $assignment->notes = 'Rotação semanal: Morning ↔ Night';
    $assignment->save();
    
    $activeShiftId = $assignment->getActiveShiftForDate($today);
    $activeShift = Shift::find($activeShiftId);
    $nextChange = $assignment->getNextRotationDate($today);
    
    echo "✓ {$employee->full_name}\n";
    echo "    Turno ativo hoje: {$activeShift->name}\n";
    echo "    Próxima mudança: " . $nextChange->format('d/m/Y') . "\n\n";
    
    $updated++;
}

echo str_repeat('=', 70) . "\n";
echo "RESUMO:\n";
echo "  Configurados: {$updated}\n";
echo "  Não encontrados: {$notFound}\n\n";

if ($updated > 0) {
    echo "✅ Rotação configurada com sucesso!\n";
}

echo "\n=== FIM ===\n";
