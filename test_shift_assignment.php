<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== TESTE CRIAÇÃO SHIFT ASSIGNMENT ===\n\n";

try {
    // Verificar se existem funcionários e shifts
    $employees = \App\Models\HR\Employee::where('employment_status', 'active')->take(3)->get();
    $shifts = \App\Models\HR\Shift::where('is_active', true)->take(3)->get();
    
    echo "Funcionários ativos: " . $employees->count() . "\n";
    echo "Shifts ativos: " . $shifts->count() . "\n\n";
    
    if ($employees->count() == 0 || $shifts->count() == 0) {
        echo "ERRO: Não há funcionários ou shifts suficientes para teste\n";
        exit(1);
    }
    
    // Pegar primeiro funcionário e primeiro shift
    $employee = $employees->first();
    $shift = $shifts->first();
    
    echo "Testando com:\n";
    echo "- Funcionário: {$employee->full_name} (ID: {$employee->id})\n";
    echo "- Shift: {$shift->name} (ID: {$shift->id})\n\n";
    
    // Verificar se já existe assignment
    $existingAssignment = \App\Models\HR\ShiftAssignment::where('employee_id', $employee->id)
        ->where('shift_id', $shift->id)
        ->first();
        
    if ($existingAssignment) {
        echo "AVISO: Já existe assignment para este funcionário e shift\n";
        echo "Assignment ID: {$existingAssignment->id}\n\n";
    }
    
    // Tentar criar novo assignment
    $assignmentData = [
        'employee_id' => $employee->id,
        'shift_id' => $shift->id,
        'start_date' => \Carbon\Carbon::today()->format('Y-m-d'),
        'end_date' => \Carbon\Carbon::today()->addDays(30)->format('Y-m-d'),
        'is_permanent' => false,
        'rotation_pattern' => null,
        'notes' => 'Teste criado via script de debug',
        'assigned_by' => $employees->count() > 1 ? $employees->get(1)->id : $employee->id,
    ];
    
    echo "Dados do assignment:\n";
    print_r($assignmentData);
    echo "\n";
    
    // Criar assignment
    $assignment = \App\Models\HR\ShiftAssignment::create($assignmentData);
    
    echo "✅ Assignment criado com sucesso!\n";
    echo "ID: {$assignment->id}\n";
    echo "Criado em: {$assignment->created_at}\n\n";
    
    // Verificar se foi salvo na BD
    $savedAssignment = \App\Models\HR\ShiftAssignment::find($assignment->id);
    if ($savedAssignment) {
        echo "✅ Assignment encontrado na base de dados\n";
        echo "Employee: {$savedAssignment->employee->full_name}\n";
        echo "Shift: {$savedAssignment->shift->name}\n";
        echo "Start Date: {$savedAssignment->start_date->format('d/m/Y')}\n";
        echo "End Date: " . ($savedAssignment->end_date ? $savedAssignment->end_date->format('d/m/Y') : 'N/A') . "\n";
    } else {
        echo "❌ Assignment NÃO encontrado na base de dados após criação\n";
    }
    
    // Listar todos os assignments
    echo "\n=== TODOS OS ASSIGNMENTS NA BD ===\n";
    $allAssignments = \App\Models\HR\ShiftAssignment::with(['employee', 'shift'])->get();
    foreach ($allAssignments as $a) {
        echo "ID: {$a->id} | {$a->employee->full_name} | {$a->shift->name} | {$a->start_date->format('d/m/Y')}\n";
    }
    
} catch (\Exception $e) {
    echo "❌ ERRO: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "Trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n=== FIM TESTE ===\n";
