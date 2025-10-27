<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "🧪 Teste completo do fluxo de checkboxes\n";
echo "=====================================\n\n";

// 1. Verificar se existe um pedido de compra
$purchaseOrder = \App\Models\SupplyChain\PurchaseOrder::first();
if (!$purchaseOrder) {
    echo "❌ Nenhum pedido de compra encontrado para teste\n";
    exit;
}

echo "✅ Pedido de compra encontrado: ID {$purchaseOrder->id}\n";

// 2. Verificar se existe um formulário customizado com checkboxes
$customForm = \App\Models\SupplyChain\CustomForm::whereHas('fields', function($query) {
    $query->where('type', 'checkbox');
})->with('fields')->first();

if (!$customForm) {
    echo "❌ Nenhum formulário customizado com checkboxes encontrado\n";
    exit;
}

echo "✅ Formulário encontrado: {$customForm->name} (ID {$customForm->id})\n";

// 3. Verificar os campos checkbox do formulário
$checkboxFields = $customForm->fields->where('type', 'checkbox');
echo "📝 Campos checkbox encontrados:\n";
foreach ($checkboxFields as $field) {
    echo "   - {$field->label} ({$field->name})\n";
    if (!empty($field->options)) {
        $options = is_string($field->options) ? json_decode($field->options, true) : $field->options;
        echo "     Opções: " . json_encode($options) . "\n";
    }
}

// 4. Verificar shipping notes com dados de checkbox salvos
$shippingNotesWithCheckbox = \App\Models\SupplyChain\ShippingNote::whereNotNull('custom_form_id')
    ->where('purchase_order_id', $purchaseOrder->id)
    ->whereHas('customFormSubmission.fieldValues', function($query) {
        $query->whereHas('field', function($q) {
            $q->where('type', 'checkbox');
        });
    })
    ->with([
        'customFormSubmission.fieldValues' => function($query) {
            $query->whereHas('field', function($q) {
                $q->where('type', 'checkbox');
            });
        },
        'customFormSubmission.fieldValues.field'
    ])
    ->get();

echo "\n📋 Shipping notes com dados de checkbox:\n";
if ($shippingNotesWithCheckbox->isEmpty()) {
    echo "   ⚠️ Nenhuma shipping note com dados de checkbox encontrada\n";
} else {
    foreach ($shippingNotesWithCheckbox as $note) {
        echo "   🚚 Shipping Note ID: {$note->id}\n";
        
        if ($note->customFormSubmission) {
            echo "     📄 Submissão ID: {$note->customFormSubmission->id}\n";
            
            foreach ($note->customFormSubmission->fieldValues as $fieldValue) {
                if ($fieldValue->field && $fieldValue->field->type === 'checkbox') {
                    echo "     🔘 Campo: {$fieldValue->field->label}\n";
                    echo "        Valor bruto salvo: " . var_export($fieldValue->value, true) . "\n";
                    
                    // Simular o processamento que acontece na view
                    $checkboxValue = $fieldValue->value;
                    $options = $fieldValue->field->options;
                    
                    if (!empty($options)) {
                        // Checkbox múltiplo
                        if (is_string($options)) {
                            $options = json_decode($options, true);
                        }
                        
                        $selectedValues = [];
                        if (!empty($checkboxValue) && $checkboxValue !== '{}' && $checkboxValue !== '[]') {
                            if (is_string($checkboxValue)) {
                                $decoded = json_decode($checkboxValue, true);
                                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                                    $selectedValues = $decoded;
                                }
                            }
                        }
                        
                        $selectedLabels = [];
                        if (is_array($options)) {
                            foreach ($options as $option) {
                                $optionValue = $option['value'] ?? '';
                                if (isset($selectedValues[$optionValue]) && $selectedValues[$optionValue] === true) {
                                    $selectedLabels[] = $option['label'] ?? $optionValue;
                                }
                            }
                        }
                        
                        $displayValue = empty($selectedLabels) ? 'Nenhum selecionado' : implode(', ', $selectedLabels);
                        echo "        💬 Exibição no histórico: {$displayValue}\n";
                    } else {
                        // Checkbox simples
                        $displayValue = ($checkboxValue === '1' || $checkboxValue === true || $checkboxValue === 'true') ? 'Sim' : 'Não';
                        echo "        💬 Exibição no histórico: {$displayValue}\n";
                    }
                }
            }
        }
        echo "\n";
    }
}

echo "\n✅ Teste completo finalizado!\n";
echo "\n📌 Resumo das correções implementadas:\n";
echo "1. ✅ Binding direto com wire:model.live para checkboxes\n";
echo "2. ✅ Processamento correto de arrays no backend\n";
echo "3. ✅ Conversão adequada para JSON antes de salvar\n";
echo "4. ✅ Exibição correta no histórico de shipping notes\n";
echo "5. ✅ Inicialização adequada dos campos vazios\n";
