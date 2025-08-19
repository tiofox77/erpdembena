<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== DEBUG: View do Recibo ===\n\n";

$employeeId = 14;

// Simular rota do recibo
$request = new \Illuminate\Http\Request(['employee_id' => $employeeId]);

echo "📋 TESTANDO ROTA DO RECIBO:\n";
echo "URL: /payroll/receipt?employee_id={$employeeId}\n\n";

// Instanciar controller e chamar método
$controller = new \App\Http\Controllers\PayrollReceiptController();

try {
    // Vamos testar se o controller tem o método correto
    $reflection = new ReflectionClass($controller);
    $methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);
    
    echo "📚 MÉTODOS DISPONÍVEIS NO CONTROLLER:\n";
    foreach($methods as $method) {
        if ($method->class === 'App\Http\Controllers\PayrollReceiptController') {
            echo "   - {$method->name}\n";
        }
    }
    
    echo "\n🎯 TESTANDO MÉTODO showReceiptView:\n";
    
    // Simular a view response
    $response = $controller->showReceiptView($request);
    
    if ($response instanceof \Illuminate\View\View) {
        $data = $response->getData();
        
        echo "✅ View renderizada com sucesso!\n";
        echo "📊 DADOS DA VIEW:\n";
        
        foreach(['workedDays', 'absences', 'extraHours', 'employeeName', 'employeeId'] as $key) {
            $value = $data[$key] ?? 'NULL';
            echo "   {$key}: {$value}\n";
        }
        
        if (isset($data['salaryDiscountsDetailed'])) {
            echo "\n💰 DESCONTOS DETALHADOS:\n";
            echo "   Count: " . $data['salaryDiscountsDetailed']->count() . "\n";
            foreach($data['salaryDiscountsDetailed'] as $discount) {
                echo "      - {$discount['type_name']}: {$discount['total_amount']} KZ\n";
            }
        }
        
        echo "\n🔍 VERIFICAÇÃO DE AUSÊNCIAS:\n";
        echo "   Valor 'absences': " . ($data['absences'] ?? 'NULL') . "\n";
        echo "   Tipo: " . gettype($data['absences'] ?? null) . "\n";
        echo "   É zero? " . (($data['absences'] ?? null) === 0 ? "SIM" : "NÃO") . "\n";
        echo "   Template mostrará: " . ($data['absences'] ?? '0') . "\n";
        
    } else {
        echo "❌ Response não é uma view\n";
        echo "Type: " . get_class($response) . "\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}

echo "\n=== DEBUG CONCLUÍDO ===\n";
