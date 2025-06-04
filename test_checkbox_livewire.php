<?php

// Script para testar o fluxo completo de dados dos checkboxes
// Execute via: php test_checkbox_livewire.php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Support\Facades\Log;

// Simular dados que vêm do wire:model
echo "=== TESTE DE PROCESSAMENTO DE CHECKBOX ===\n\n";

// Cenário 1: Checkbox múltiplo com algumas opções marcadas
echo "1. Checkbox múltiplo com opções marcadas:\n";
$formData = [
    'opcoes_entrega' => [
        'rapida' => true,
        'normal' => false,
        'expressa' => true,
        'economica' => false
    ]
];

echo "   Dados originais: " . json_encode($formData['opcoes_entrega']) . "\n";

// Processar como faz o método submitForm
$fieldValue = $formData['opcoes_entrega'];
if (is_array($fieldValue)) {
    $cleanedValues = [];
    foreach ($fieldValue as $key => $value) {
        echo "   Verificando {$key} = " . var_export($value, true) . "\n";
        if ($value === true || $value === 'true' || $value === 1 || $value === '1') {
            $cleanedValues[$key] = true;
            echo "   ✓ {$key} INCLUÍDO\n";
        } else {
            echo "   ✗ {$key} IGNORADO\n";
        }
    }
    $storedValue = empty($cleanedValues) ? '{}' : json_encode($cleanedValues);
    echo "   Resultado final: {$storedValue}\n\n";
}

// Cenário 2: Checkbox múltiplo sem opções marcadas
echo "2. Checkbox múltiplo sem opções marcadas:\n";
$formData2 = [
    'opcoes_entrega' => [
        'rapida' => false,
        'normal' => false,
        'expressa' => false,
        'economica' => false
    ]
];

echo "   Dados originais: " . json_encode($formData2['opcoes_entrega']) . "\n";
$fieldValue2 = $formData2['opcoes_entrega'];
if (is_array($fieldValue2)) {
    $cleanedValues2 = [];
    foreach ($fieldValue2 as $key => $value) {
        if ($value === true || $value === 'true' || $value === 1 || $value === '1') {
            $cleanedValues2[$key] = true;
        }
    }
    $storedValue2 = empty($cleanedValues2) ? '{}' : json_encode($cleanedValues2);
    echo "   Resultado final: {$storedValue2}\n\n";
}

// Cenário 3: Como o Livewire pode enviar os dados
echo "3. Dados como Livewire wire:model pode enviar:\n";
$livewireData = [
    'opcoes_entrega' => [
        'rapida' => 'true',
        'normal' => '',
        'expressa' => '1',
        'economica' => null
    ]
];

echo "   Dados do Livewire: " . json_encode($livewireData['opcoes_entrega']) . "\n";
$fieldValue3 = $livewireData['opcoes_entrega'];
if (is_array($fieldValue3)) {
    $cleanedValues3 = [];
    foreach ($fieldValue3 as $key => $value) {
        echo "   Verificando {$key} = " . var_export($value, true) . " (tipo: " . gettype($value) . ")\n";
        // Normalizar valor
        $normalizedValue = false;
        if (is_bool($value)) {
            $normalizedValue = $value;
        } elseif (is_numeric($value)) {
            $normalizedValue = (int)$value === 1;
        } elseif (is_string($value)) {
            $value = strtolower(trim($value));
            $normalizedValue = in_array($value, ['true', '1', 'on', 'yes', 'y']);
        }
        
        if ($normalizedValue) {
            $cleanedValues3[$key] = true;
            echo "   ✓ {$key} INCLUÍDO\n";
        } else {
            echo "   ✗ {$key} IGNORADO\n";
        }
    }
    $storedValue3 = empty($cleanedValues3) ? '{}' : json_encode($cleanedValues3);
    echo "   Resultado final: {$storedValue3}\n\n";
}

echo "=== FIM DOS TESTES ===\n";
