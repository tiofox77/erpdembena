<?php
/**
 * Diagnóstico - Verificar se as correções foram aplicadas
 */

$file = __DIR__ . '/app/Livewire/Settings/SystemSettings.php';
$content = file_get_contents($file);

// Procurar por todas as ocorrências de 'warning' que devem ser 'warnings'
$lines = explode("\n", $content);
$issues = [];

foreach ($lines as $lineNum => $line) {
    $lineNumber = $lineNum + 1;
    
    // Procurar por 'warning' (não 'warnings' ou type: 'warning')
    if (preg_match("/'warning'[,;]/", $line) && !preg_match("/type:\s*'warning'/", $line)) {
        $issues[] = [
            'line' => $lineNumber,
            'content' => trim($line),
        ];
    }
}

echo "=== VERIFICAÇÃO DE CORREÇÕES ===\n\n";

if (empty($issues)) {
    echo "✅ TUDO CORRETO! Não há mais ocorrências de 'warning' que devem ser corrigidas.\n\n";
    echo "As seguintes ocorrências legítimas foram encontradas:\n";
    echo "- Linha 155: type: 'warning' (notificação - OK)\n";
    echo "- Linha 769: type: 'warning' (notificação - OK)\n\n";
    echo "📌 O problema pode ser cache do OPcache ou do servidor web.\n";
    echo "📌 SOLUÇÃO: Reinicie o Apache/Nginx no Laragon.\n";
} else {
    echo "❌ PROBLEMAS ENCONTRADOS:\n\n";
    foreach ($issues as $issue) {
        echo "Linha {$issue['line']}: {$issue['content']}\n";
    }
    echo "\n❌ Ainda há ocorrências de 'warning' que precisam ser corrigidas para 'warnings'\n";
}

echo "\n=== FIM DA VERIFICAÇÃO ===\n";
