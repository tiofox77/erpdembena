<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== DEBUG: Dados passados para o template ===\n\n";

// Simular a chamada do controller
$controller = new \App\Http\Controllers\PayrollReceiptController();
$employeeId = 14;

// Obter dados como o controller faz
$data = $controller->getEmployeeReceiptData($employeeId);

echo "📋 DADOS PASSADOS PARA O TEMPLATE:\n\n";

echo "workedDays: " . ($data['workedDays'] ?? 'NULL') . "\n";
echo "absences: " . ($data['absences'] ?? 'NULL') . "\n";
echo "extraHours: " . ($data['extraHours'] ?? 'NULL') . "\n";
echo "employeeName: " . ($data['employeeName'] ?? 'NULL') . "\n";
echo "employeeId: " . ($data['employeeId'] ?? 'NULL') . "\n\n";

echo "🔍 VERIFICAÇÃO DE TIPOS:\n";
echo "workedDays type: " . gettype($data['workedDays'] ?? null) . "\n";
echo "absences type: " . gettype($data['absences'] ?? null) . "\n";
echo "extraHours type: " . gettype($data['extraHours'] ?? null) . "\n\n";

echo "🧮 VALORES CALCULADOS:\n";
if (isset($data['workedDays'])) {
    echo "workedDays value: {$data['workedDays']}\n";
}
if (isset($data['absences'])) {
    echo "absences value: {$data['absences']}\n";
}
if (isset($data['extraHours'])) {
    echo "extraHours value: {$data['extraHours']}\n";
}

echo "\n📊 DESCONTOS DETALHADOS:\n";
if (isset($data['salaryDiscountsDetailed'])) {
    echo "salaryDiscountsDetailed count: " . $data['salaryDiscountsDetailed']->count() . "\n";
    foreach($data['salaryDiscountsDetailed'] as $discount) {
        echo "   - {$discount['type_name']}: {$discount['total_amount']} KZ\n";
    }
} else {
    echo "salaryDiscountsDetailed: NULL\n";
}

echo "\n=== DEBUG CONCLUÍDO ===\n";
