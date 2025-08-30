<?php

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== AUDITORIA: Permissões Roles vs Layout ===\n\n";

// 1. Extrair permissões do layout livewire.blade.php
$layoutFile = file_get_contents(__DIR__ . '/resources/views/layouts/livewire.blade.php');

echo "🔍 EXTRAINDO PERMISSÕES DO LAYOUT:\n";
echo str_repeat("-", 40) . "\n";

// Regex patterns para encontrar permissões
$patterns = [
    '/@can\([\'"]([^\'"]+)[\'"]\)/',
    '/@canany\(\[([^\]]+)\]\)/',
    '/@cannot\([\'"]([^\'"]+)[\'"]\)/'
];

$layoutPermissions = [];

foreach ($patterns as $pattern) {
    preg_match_all($pattern, $layoutFile, $matches);
    
    if ($pattern === '/@canany\(\[([^\]]+)\]\)/') {
        // Handle @canany arrays
        foreach ($matches[1] as $match) {
            // Extract individual permissions from array
            preg_match_all('/[\'"]([^\'"]+)[\'"]/', $match, $arrayMatches);
            foreach ($arrayMatches[1] as $perm) {
                $layoutPermissions[] = trim($perm);
            }
        }
    } else {
        // Handle @can and @cannot
        foreach ($matches[1] as $perm) {
            $layoutPermissions[] = trim($perm);
        }
    }
}

$layoutPermissions = array_unique($layoutPermissions);
sort($layoutPermissions);

echo "Total de permissões encontradas no layout: " . count($layoutPermissions) . "\n\n";

// 2. Obter todas as permissões da base de dados
echo "🗄️  PERMISSÕES NA BASE DE DADOS:\n";
echo str_repeat("-", 30) . "\n";

$dbPermissions = \Spatie\Permission\Models\Permission::all()->pluck('name')->toArray();
sort($dbPermissions);

echo "Total de permissões na base de dados: " . count($dbPermissions) . "\n\n";

// 3. Comparar permissões
echo "⚖️  COMPARAÇÃO E ANÁLISE:\n";
echo str_repeat("=", 50) . "\n";

// Permissões no layout que NÃO existem na BD
$missingInDb = array_diff($layoutPermissions, $dbPermissions);
if (!empty($missingInDb)) {
    echo "❌ PERMISSÕES NO LAYOUT QUE NÃO EXISTEM NA BD:\n";
    echo str_repeat("-", 45) . "\n";
    foreach ($missingInDb as $perm) {
        echo "   ❌ {$perm}\n";
        
        // Find where it's used in layout
        $lines = explode("\n", $layoutFile);
        foreach ($lines as $lineNum => $line) {
            if (strpos($line, $perm) !== false) {
                echo "      Linha " . ($lineNum + 1) . ": " . trim($line) . "\n";
            }
        }
        echo "\n";
    }
} else {
    echo "✅ Todas as permissões do layout existem na base de dados\n";
}

// Permissões na BD que NÃO são usadas no layout
$unusedInLayout = array_diff($dbPermissions, $layoutPermissions);
if (!empty($unusedInLayout)) {
    echo "\n📋 PERMISSÕES NA BD NÃO USADAS NO LAYOUT:\n";
    echo str_repeat("-", 40) . "\n";
    
    // Group by module for better organization
    $groupedUnused = [];
    foreach ($unusedInLayout as $perm) {
        $parts = explode('.', $perm);
        $module = $parts[0] ?? 'other';
        $groupedUnused[$module][] = $perm;
    }
    
    foreach ($groupedUnused as $module => $perms) {
        echo "\n📁 {$module}:\n";
        foreach ($perms as $perm) {
            echo "   📄 {$perm}\n";
        }
    }
}

// 4. Verificar consistência de padrões
echo "\n\n🔍 ANÁLISE DE PADRÕES:\n";
echo str_repeat("=", 30) . "\n";

// Group layout permissions by module
$layoutGrouped = [];
foreach ($layoutPermissions as $perm) {
    $parts = explode('.', $perm);
    $module = $parts[0] ?? 'other';
    $layoutGrouped[$module][] = $perm;
}

echo "📊 PERMISSÕES POR MÓDULO NO LAYOUT:\n";
echo str_repeat("-", 35) . "\n";
foreach ($layoutGrouped as $module => $perms) {
    echo sprintf("%-20s %d permissões\n", $module . ":", count($perms));
}

// 5. Verificar roles específicas
echo "\n\n👥 VERIFICAÇÃO DE ROLES:\n";
echo str_repeat("=", 25) . "\n";

$roles = \Spatie\Permission\Models\Role::with('permissions')->get();

foreach ($roles as $role) {
    echo "\n🏷️  ROLE: {$role->name}\n";
    echo str_repeat("-", 20) . "\n";
    
    $rolePermissions = $role->permissions->pluck('name')->toArray();
    
    // Check which layout permissions this role has
    $hasLayoutPerms = array_intersect($rolePermissions, $layoutPermissions);
    $missingLayoutPerms = array_diff($layoutPermissions, $rolePermissions);
    
    echo "   ✅ Tem " . count($hasLayoutPerms) . " das " . count($layoutPermissions) . " permissões do layout\n";
    
    if ($role->name === 'super-admin') {
        $coverage = (count($hasLayoutPerms) / count($layoutPermissions)) * 100;
        echo "   📊 Cobertura: " . round($coverage, 1) . "%\n";
        
        if ($coverage < 100) {
            echo "   ⚠️  Super-admin deveria ter todas as permissões do layout!\n";
        }
    }
}

// 6. Sugestões de correção
echo "\n\n🛠️  SUGESTÕES DE CORREÇÃO:\n";
echo str_repeat("=", 30) . "\n";

if (!empty($missingInDb)) {
    echo "1. CRIAR PERMISSÕES EM FALTA:\n";
    foreach ($missingInDb as $perm) {
        echo "   \Spatie\Permission\Models\Permission::create(['name' => '{$perm}', 'guard_name' => 'web']);\n";
    }
    echo "\n";
}

echo "2. ADICIONAR PERMISSÕES À SUPER-ADMIN:\n";
$superAdmin = \Spatie\Permission\Models\Role::where('name', 'super-admin')->first();
if ($superAdmin) {
    $superAdminPerms = $superAdmin->permissions->pluck('name')->toArray();
    $missingSuperAdmin = array_diff($layoutPermissions, $superAdminPerms);
    
    if (!empty($missingSuperAdmin)) {
        foreach ($missingSuperAdmin as $perm) {
            if (in_array($perm, $dbPermissions)) {
                echo "   \$superAdmin->givePermissionTo('{$perm}');\n";
            }
        }
    } else {
        echo "   ✅ Super-admin já tem todas as permissões do layout\n";
    }
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "✅ AUDITORIA CONCLUÍDA\n";
echo str_repeat("=", 60) . "\n";
