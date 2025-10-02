<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\HR\Employee;
use PhpOffice\PhpSpreadsheet\IOFactory;

echo "=== SINCRONIZAÇÃO DE IDs BIOMÉTRICOS ===\n\n";

// Solicitar o caminho do arquivo Excel
echo "Digite o caminho do arquivo Excel de presença: ";
$handle = fopen("php://stdin", "r");
$excelFile = trim(fgets($handle));

if (!file_exists($excelFile)) {
    echo "❌ Arquivo não encontrado: $excelFile\n";
    exit(1);
}

echo "📂 Lendo arquivo Excel...\n";

try {
    $spreadsheet = IOFactory::load($excelFile);
    $worksheet = $spreadsheet->getActiveSheet();
    $rows = $worksheet->toArray();
    
    // Remover cabeçalho
    $header = array_shift($rows);
    
    echo "✓ Arquivo carregado. Encontradas " . count($rows) . " linhas.\n\n";
    echo "Iniciando sincronização...\n\n";
    
    $updated = 0;
    $notFound = 0;
    $skipped = 0;
    $errors = [];
    
    foreach ($rows as $index => $row) {
        $empId = $row[0] ?? null; // Coluna A - Emp ID
        $name = $row[1] ?? null;  // Coluna B - Name
        
        if (empty($empId) || empty($name)) {
            $skipped++;
            continue;
        }
        
        // Limpar nome
        $cleanName = trim($name);
        
        // Buscar funcionário por nome (fuzzy match)
        $employee = Employee::where(function($query) use ($cleanName) {
            $query->where('full_name', 'LIKE', '%' . $cleanName . '%')
                  ->orWhereRaw('LOWER(full_name) LIKE ?', ['%' . strtolower($cleanName) . '%']);
        })->first();
        
        if (!$employee) {
            // Tentar buscar por partes do nome
            $nameParts = explode(' ', $cleanName);
            foreach ($nameParts as $part) {
                if (strlen($part) > 3) {
                    $employee = Employee::whereRaw('LOWER(full_name) LIKE ?', ['%' . strtolower($part) . '%'])->first();
                    if ($employee) break;
                }
            }
        }
        
        if ($employee) {
            // Verificar se já tem biometric_id diferente
            if ($employee->biometric_id && $employee->biometric_id != $empId) {
                echo "⚠️  Emp ID {$empId} ({$cleanName}): Funcionário '{$employee->full_name}' já tem biometric_id '{$employee->biometric_id}'\n";
                $skipped++;
                continue;
            }
            
            // Atualizar biometric_id
            $employee->biometric_id = (string)$empId;
            $employee->save();
            
            echo "✓ Emp ID {$empId} → {$employee->full_name}\n";
            $updated++;
        } else {
            $errors[] = "❌ Emp ID {$empId}: Funcionário '{$cleanName}' não encontrado na BD";
            $notFound++;
        }
    }
    
    echo "\n=== RESUMO ===\n";
    echo "✓ Atualizados: {$updated}\n";
    echo "⚠ Não encontrados: {$notFound}\n";
    echo "⊘ Ignorados: {$skipped}\n";
    
    if (!empty($errors)) {
        echo "\n=== ERROS ===\n";
        foreach (array_slice($errors, 0, 20) as $error) {
            echo "{$error}\n";
        }
        if (count($errors) > 20) {
            echo "... e mais " . (count($errors) - 20) . " erros\n";
        }
    }
    
    echo "\n✓ Sincronização concluída!\n";
    
} catch (\Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}
