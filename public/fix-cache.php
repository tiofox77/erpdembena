<?php
/**
 * Script de Emergência - Limpar Cache
 * 
 * Acesse via: https://dembenaerp.softec.vip/fix-cache.php
 * 
 * IMPORTANTE: Delete este arquivo após usar!
 */

// Verificação de segurança básica
$secret_key = 'dembena2025'; // Altere para um valor seguro
if (!isset($_GET['key']) || $_GET['key'] !== $secret_key) {
    die('Acesso negado. Use: fix-cache.php?key=dembena2025');
}

echo "<html><head><title>Fix Cache - Dembena ERP</title>";
echo "<style>body{font-family:monospace;background:#1e1e1e;color:#0f0;padding:20px;}";
echo "h1{color:#0f0;border-bottom:2px solid #0f0;padding-bottom:10px;}";
echo ".success{color:#0f0;} .error{color:#f00;} .info{color:#ff0;}</style></head><body>";

echo "<h1>🛠️ Script de Correção de Cache - Dembena ERP</h1>";
echo "<p class='info'>Iniciando limpeza de cache...</p><br>";

// Diretório base
$baseDir = dirname(__DIR__);

// Função para executar comandos artisan
function runArtisan($command) {
    global $baseDir;
    $output = [];
    $return = 0;
    
    exec("cd $baseDir && php artisan $command 2>&1", $output, $return);
    
    return [
        'success' => $return === 0,
        'output' => implode("\n", $output),
        'return' => $return
    ];
}

// Função para limpar diretório
function clearDirectory($path) {
    if (!is_dir($path)) {
        return false;
    }
    
    $files = glob($path . '/*');
    foreach ($files as $file) {
        if (is_file($file)) {
            unlink($file);
        }
    }
    return true;
}

// 1. Cache geral
echo "<p class='info'>[1/8] Limpando cache geral...</p>";
$result = runArtisan('cache:clear');
echo $result['success'] ? "<p class='success'>✓ Cache geral limpo</p>" : "<p class='error'>✗ Erro ao limpar cache</p>";

// 2. Config cache
echo "<p class='info'>[2/8] Limpando cache de configuração...</p>";
$result = runArtisan('config:clear');
echo $result['success'] ? "<p class='success'>✓ Config cache limpo</p>" : "<p class='error'>✗ Erro ao limpar config</p>";

// 3. View cache
echo "<p class='info'>[3/8] Limpando cache de views...</p>";
$result = runArtisan('view:clear');
echo $result['success'] ? "<p class='success'>✓ View cache limpo</p>" : "<p class='error'>✗ Erro ao limpar views</p>";

// 4. Route cache
echo "<p class='info'>[4/8] Limpando cache de rotas...</p>";
$result = runArtisan('route:clear');
echo $result['success'] ? "<p class='success'>✓ Route cache limpo</p>" : "<p class='error'>✗ Erro ao limpar rotas</p>";

// 5. Optimize clear
echo "<p class='info'>[5/8] Executando optimize:clear...</p>";
$result = runArtisan('optimize:clear');
echo $result['success'] ? "<p class='success'>✓ Otimização limpa</p>" : "<p class='error'>✗ Erro ao limpar otimização</p>";

// 6. Limpar Livewire temp
echo "<p class='info'>[6/8] Limpando arquivos temporários do Livewire...</p>";
$livewirePath = $baseDir . '/storage/framework/cache/livewire-tmp';
if (clearDirectory($livewirePath)) {
    echo "<p class='success'>✓ Livewire temp limpo</p>";
} else {
    echo "<p class='info'>→ Diretório Livewire não encontrado ou já limpo</p>";
}

// 7. Limpar sessions
echo "<p class='info'>[7/8] Limpando sessões...</p>";
$sessionsPath = $baseDir . '/storage/framework/sessions';
if (clearDirectory($sessionsPath)) {
    echo "<p class='success'>✓ Sessões limpas</p>";
} else {
    echo "<p class='info'>→ Diretório de sessões não encontrado ou já limpo</p>";
}

// 8. Recompilar config
echo "<p class='info'>[8/8] Recompilando configurações...</p>";
$result = runArtisan('config:cache');
echo $result['success'] ? "<p class='success'>✓ Configurações recompiladas</p>" : "<p class='error'>✗ Erro ao recompilar</p>";

echo "<br><hr>";
echo "<h2 class='success'>✅ Processo Concluído!</h2>";
echo "<p class='info'>Tente acessar novamente: <a href='/settings/system' style='color:#0ff;'>System Settings</a></p>";
echo "<br>";
echo "<p class='error'><strong>⚠️ IMPORTANTE: Delete este arquivo (fix-cache.php) por segurança!</strong></p>";
echo "<p class='info'>Execute: <code>rm public/fix-cache.php</code> via SSH</p>";

echo "</body></html>";
