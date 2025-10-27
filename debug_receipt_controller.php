<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== DEBUG: Controller do Recibo ===\n\n";

$employeeId = 14;

// Simular rota do recibo
$request = new \Illuminate\Http\Request(['employee_id' => $employeeId]);

echo "📋 TESTANDO CONTROLLER:\n";
echo "Employee ID: {$employeeId}\n\n";

// Instanciar controller
$controller = new \App\Http\Controllers\PayrollReceiptController();

try {
    echo "🎯 TESTANDO MÉTODO showReceiptHTML:\n";
    
    // Chamar o método do controller
    $response = $controller->showReceiptHTML($request);
    
    if ($response instanceof \Illuminate\View\View) {
        $data = $response->getData();
        
        echo "✅ View renderizada com sucesso!\n\n";
        
        echo "📊 DADOS IMPORTANTES:\n";
        echo "   workedDays: " . ($data['workedDays'] ?? 'NULL') . " (tipo: " . gettype($data['workedDays'] ?? null) . ")\n";
        echo "   absences: " . ($data['absences'] ?? 'NULL') . " (tipo: " . gettype($data['absences'] ?? null) . ")\n";
        echo "   extraHours: " . ($data['extraHours'] ?? 'NULL') . " (tipo: " . gettype($data['extraHours'] ?? null) . ")\n";
        echo "   employeeName: " . ($data['employeeName'] ?? 'NULL') . "\n";
        echo "   employeeId: " . ($data['employeeId'] ?? 'NULL') . "\n\n";
        
        echo "🔍 VERIFICAÇÃO ESPECÍFICA DE AUSÊNCIAS:\n";
        $absences = $data['absences'] ?? null;
        echo "   Valor bruto: " . var_export($absences, true) . "\n";
        echo "   É nulo? " . ($absences === null ? "SIM" : "NÃO") . "\n";
        echo "   É zero? " . ($absences === 0 ? "SIM" : "NÃO") . "\n";
        echo "   É string '0'? " . ($absences === '0' ? "SIM" : "NÃO") . "\n";
        echo "   Template vai mostrar: '{{ " . '$absences' . " ?? '0' }}' = '" . ($absences ?? '0') . "'\n\n";
        
        if (isset($data['salaryDiscountsDetailed'])) {
            echo "💰 DESCONTOS DETALHADOS:\n";
            echo "   Count: " . $data['salaryDiscountsDetailed']->count() . "\n";
            if ($data['salaryDiscountsDetailed']->count() > 0) {
                foreach($data['salaryDiscountsDetailed'] as $discount) {
                    echo "      - {$discount['type_name']}: {$discount['total_amount']} KZ\n";
                }
            } else {
                echo "      (nenhum desconto)\n";
            }
        } else {
            echo "💰 DESCONTOS: NULL\n";
        }
        
    } else {
        echo "❌ Response não é uma view\n";
        echo "Type: " . get_class($response) . "\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}

echo "\n=== DEBUG CONCLUÍDO ===\n";
