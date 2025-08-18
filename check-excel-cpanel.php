<?php

/**
 * Script de verificação da biblioteca Laravel Excel para ambientes cPanel
 * Verifica dependências, permissões e configurações necessárias
 */

echo "=== VERIFICAÇÃO LARAVEL EXCEL PARA CPANEL ===\n\n";

// 1. Verificar extensões PHP necessárias
echo "1. EXTENSÕES PHP NECESSÁRIAS:\n";
$required_extensions = ['zip', 'xml', 'gd', 'simplexml', 'xmlreader', 'zlib'];
foreach ($required_extensions as $ext) {
    $status = extension_loaded($ext) ? '✅' : '❌';
    echo "   {$status} {$ext}\n";
}

// 2. Verificar limits PHP
echo "\n2. LIMITES PHP:\n";
echo "   Memory Limit: " . ini_get('memory_limit') . "\n";
echo "   Max Execution Time: " . ini_get('max_execution_time') . "s\n";
echo "   Upload Max Size: " . ini_get('upload_max_filesize') . "\n";
echo "   Post Max Size: " . ini_get('post_max_size') . "\n";

// 3. Verificar diretórios e permissões
echo "\n3. DIRETÓRIOS E PERMISSÕES:\n";
$directories = [
    'storage/framework/cache',
    'storage/framework/cache/laravel-excel',
    'storage/app',
    'storage/logs'
];

foreach ($directories as $dir) {
    $path = __DIR__ . '/' . $dir;
    if (is_dir($path)) {
        $writable = is_writable($path) ? '✅' : '❌';
        $perms = substr(sprintf('%o', fileperms($path)), -4);
        echo "   {$writable} {$dir} (chmod: {$perms})\n";
    } else {
        echo "   ❌ {$dir} (não existe)\n";
    }
}

// 4. Verificar se o Laravel Excel está instalado
echo "\n4. LARAVEL EXCEL:\n";
if (file_exists(__DIR__ . '/vendor/maatwebsite/excel/src/Excel.php')) {
    echo "   ✅ Package instalado\n";
    
    // Verificar versão
    $composer = json_decode(file_get_contents(__DIR__ . '/composer.lock'), true);
    $excel_version = 'desconhecida';
    foreach ($composer['packages'] as $package) {
        if ($package['name'] === 'maatwebsite/excel') {
            $excel_version = $package['version'];
            break;
        }
    }
    echo "   📦 Versão: {$excel_version}\n";
} else {
    echo "   ❌ Package não encontrado\n";
}

// 5. Verificar configuração Laravel
echo "\n5. CONFIGURAÇÃO LARAVEL:\n";
if (file_exists(__DIR__ . '/config/excel.php')) {
    echo "   ✅ config/excel.php existe\n";
} else {
    echo "   ❌ config/excel.php não encontrado\n";
}

// 6. Problemas comuns em cPanel
echo "\n6. PROBLEMAS COMUNS CPANEL:\n";
echo "   📋 Possíveis soluções:\n";
echo "   • Verificar se todas as extensões PHP estão habilitadas no cPanel\n";
echo "   • Aumentar memory_limit para pelo menos 256M\n";
echo "   • Verificar permissões 755 para directories e 644 para files\n";
echo "   • Criar diretório storage/framework/cache/laravel-excel manualmente\n";
echo "   • Verificar se vendor/ foi carregado corretamente\n";

// 7. Teste básico do Excel
echo "\n7. TESTE BÁSICO:\n";
try {
    // Tentar criar um diretório temporário
    $temp_dir = __DIR__ . '/storage/framework/cache/laravel-excel';
    if (!is_dir($temp_dir)) {
        if (mkdir($temp_dir, 0755, true)) {
            echo "   ✅ Diretório temporário criado\n";
        } else {
            echo "   ❌ Erro ao criar diretório temporário\n";
        }
    } else {
        echo "   ✅ Diretório temporário existe\n";
    }
    
    // Verificar se consegue escrever
    $test_file = $temp_dir . '/test.txt';
    if (file_put_contents($test_file, 'test') !== false) {
        echo "   ✅ Escrita no diretório temporário OK\n";
        unlink($test_file);
    } else {
        echo "   ❌ Erro na escrita do diretório temporário\n";
    }
    
} catch (Exception $e) {
    echo "   ❌ Erro no teste: " . $e->getMessage() . "\n";
}

echo "\n=== FIM DA VERIFICAÇÃO ===\n";
