<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== VERIFICAÇÃO DE PERMISSÕES NO SIDEBAR ===\n\n";

// Ler o arquivo do sidebar
$sidebarPath = 'resources/views/layouts/livewire.blade.php';
$content = file_get_contents($sidebarPath);

if (!$content) {
    echo "❌ Erro: Não foi possível ler o arquivo {$sidebarPath}\n";
    exit(1);
}

echo "📋 ANÁLISE DETALHADA DO SIDEBAR:\n";
echo str_repeat("=", 80) . "\n";

// Dividir em linhas para análise
$lines = explode("\n", $content);
$issues = [];
$menuSections = [];
$currentSection = null;

// Analisar linha por linha
for ($i = 0; $i < count($lines); $i++) {
    $line = trim($lines[$i]);
    $lineNumber = $i + 1;
    
    // Detectar início de seções de menu principais
    if (preg_match('/<!--\s*(.+?)\s+Menu.*-->/i', $line, $matches)) {
        $currentSection = $matches[1];
        $menuSections[$currentSection] = [
            'start_line' => $lineNumber,
            'has_canany' => false,
            'links' => [],
            'unprotected_links' => []
        ];
    }
    
    // Detectar @canany
    if (preg_match('/@canany\s*\(\s*\[(.*?)\]\s*\)/s', $line, $matches)) {
        if ($currentSection) {
            $menuSections[$currentSection]['has_canany'] = true;
            $menuSections[$currentSection]['canany_line'] = $lineNumber;
            $menuSections[$currentSection]['permissions'] = $matches[1];
        }
    }
    
    // Detectar @can
    if (preg_match('/@can\s*\(\s*[\'"](.+?)[\'"]\s*\)/', $line, $matches)) {
        if ($currentSection) {
            $menuSections[$currentSection]['links'][] = [
                'line' => $lineNumber,
                'permission' => $matches[1],
                'type' => 'protected'
            ];
        }
    }
    
    // Detectar links/menus sem proteção
    if (preg_match('/<a\s+href=.*?class=.*?sidebar-(submenu|menu)-item/i', $line)) {
        // Verificar se as linhas anteriores têm @can ou @canany
        $hasProtection = false;
        for ($j = max(0, $i - 5); $j <= $i; $j++) {
            if (preg_match('/@can(any)?\s*\(/', trim($lines[$j]))) {
                $hasProtection = true;
                break;
            }
        }
        
        if (!$hasProtection && $currentSection) {
            // Extrair o route do link
            if (preg_match('/route\s*\(\s*[\'"](.+?)[\'"]\s*\)/', $line, $routeMatch)) {
                $menuSections[$currentSection]['unprotected_links'][] = [
                    'line' => $lineNumber,
                    'route' => $routeMatch[1],
                    'content' => $line
                ];
            }
        }
    }
}

// Relatório detalhado
echo "🔍 SEÇÕES DE MENU ENCONTRADAS:\n";
echo str_repeat("-", 80) . "\n";

foreach ($menuSections as $section => $data) {
    echo "\n📁 SEÇÃO: " . strtoupper($section) . "\n";
    echo "   Linha inicial: {$data['start_line']}\n";
    
    if ($data['has_canany']) {
        echo "   ✅ Tem @canany: Linha {$data['canany_line']}\n";
        echo "   📝 Permissões: {$data['permissions']}\n";
    } else {
        echo "   ❌ SEM @canany principal!\n";
        $issues[] = "Seção {$section} não tem @canany principal (linha {$data['start_line']})";
    }
    
    if (!empty($data['links'])) {
        echo "   🔗 Links protegidos: " . count($data['links']) . "\n";
        foreach ($data['links'] as $link) {
            echo "      - Linha {$link['line']}: {$link['permission']}\n";
        }
    }
    
    if (!empty($data['unprotected_links'])) {
        echo "   ⚠️  Links SEM proteção: " . count($data['unprotected_links']) . "\n";
        foreach ($data['unprotected_links'] as $link) {
            echo "      ❌ Linha {$link['line']}: {$link['route']}\n";
            $issues[] = "Link desprotegido na linha {$link['line']}: {$link['route']}";
        }
    }
}

// Buscar outros padrões problemáticos
echo "\n\n🔍 VERIFICAÇÕES ADICIONAIS:\n";
echo str_repeat("-", 80) . "\n";

// 1. Links href sem @can
echo "1. Procurando por links <a href> sem proteção @can...\n";
$unprotectedHrefs = [];
$inCanBlock = false;
$canBlockDepth = 0;

for ($i = 0; $i < count($lines); $i++) {
    $line = trim($lines[$i]);
    $lineNumber = $i + 1;
    
    // Detectar início de bloco @can
    if (preg_match('/@can(any)?\s*\(/', $line)) {
        $inCanBlock = true;
        $canBlockDepth++;
    }
    
    // Detectar fim de bloco @can
    if (preg_match('/@endcan(any)?/', $line)) {
        $canBlockDepth--;
        if ($canBlockDepth <= 0) {
            $inCanBlock = false;
            $canBlockDepth = 0;
        }
    }
    
    // Verificar se há links dentro do sidebar que não estão protegidos
    if (preg_match('/<a\s+href=.*?sidebar/', $line) && !$inCanBlock) {
        if (preg_match('/route\s*\(\s*[\'"](.+?)[\'"]\s*\)/', $line, $matches)) {
            $unprotectedHrefs[] = [
                'line' => $lineNumber,
                'route' => $matches[1],
                'content' => substr($line, 0, 100) . '...'
            ];
        }
    }
}

if (!empty($unprotectedHrefs)) {
    echo "   ❌ Encontrados " . count($unprotectedHrefs) . " links desprotegidos:\n";
    foreach ($unprotectedHrefs as $href) {
        echo "      Linha {$href['line']}: {$href['route']}\n";
        $issues[] = "Link desprotegido: {$href['route']} (linha {$href['line']})";
    }
} else {
    echo "   ✅ Todos os links estão dentro de blocos @can/@canany\n";
}

// 2. Verificar permissões órfãs (sem rotas)
echo "\n2. Verificando permissões órfãs...\n";
preg_match_all('/@can(any)?\s*\(\s*[\'"](.+?)[\'"]\s*\)/', $content, $allPermissions);
preg_match_all('/route\s*\(\s*[\'"](.+?)[\'"]\s*\)/', $content, $allRoutes);

$permissions = array_unique($allPermissions[2] ?? []);
$routes = array_unique($allRoutes[1] ?? []);

echo "   📊 Total de permissões encontradas: " . count($permissions) . "\n";
echo "   📊 Total de rotas encontradas: " . count($routes) . "\n";

// 3. Verificar consistência de nomes
echo "\n3. Verificando consistência de nomes de permissões...\n";
$permissionPatterns = [
    'maintenance' => '/^(maintenance\.|equipment\.|preventive\.|corrective\.|areas\.|technicians\.|parts\.|stock\.|reports\.|users\.|roles\.|settings\.)/i',
    'mrp' => '/^mrp\./i',
    'supplychain' => '/^supplychain\./i',
    'hr' => '/^hr\./i'
];

$inconsistentPermissions = [];
foreach ($permissions as $permission) {
    $matched = false;
    foreach ($permissionPatterns as $module => $pattern) {
        if (preg_match($pattern, $permission)) {
            $matched = true;
            break;
        }
    }
    if (!$matched) {
        $inconsistentPermissions[] = $permission;
    }
}

if (!empty($inconsistentPermissions)) {
    echo "   ⚠️  Permissões com padrões inconsistentes:\n";
    foreach ($inconsistentPermissions as $perm) {
        echo "      - {$perm}\n";
    }
} else {
    echo "   ✅ Todas as permissões seguem padrões consistentes\n";
}

// RESUMO FINAL
echo "\n\n📊 RESUMO FINAL:\n";
echo str_repeat("=", 80) . "\n";

if (empty($issues)) {
    echo "🎉 EXCELENTE! Nenhum problema encontrado no sidebar.\n";
    echo "✅ Todas as seções têm proteção @canany\n";
    echo "✅ Todos os links estão protegidos com @can\n";
    echo "✅ Sistema de permissões está bem configurado\n";
} else {
    echo "⚠️  PROBLEMAS ENCONTRADOS: " . count($issues) . "\n\n";
    foreach ($issues as $i => $issue) {
        echo ($i + 1) . ". {$issue}\n";
    }
    
    echo "\n💡 RECOMENDAÇÕES:\n";
    echo "1. Adicionar @canany para seções sem proteção\n";
    echo "2. Envolver links desprotegidos em blocos @can\n";
    echo "3. Verificar se as permissões existem no banco de dados\n";
    echo "4. Testar com usuários de diferentes roles\n";
}

echo "\n✅ VERIFICAÇÃO CONCLUÍDA!\n";
