<?php

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== DEBUG: Acesso aos Submenus de Stock ===\n\n";

// Get maintenance user
$user = \App\Models\User::where('email', 'maintenance@dembena-group.com')->first();

if (!$user) {
    echo "❌ Utilizador maintenance@dembena-group.com não encontrado\n";
    exit;
}

echo "👤 Utilizador: {$user->name} ({$user->email})\n\n";

// Verificar permissões específicas para stock
$stockPermissions = [
    'stocks.stockin',
    'stocks.stockout', 
    'stocks.history',
    'stocks.part-requests',
    'stock.view',
    'stock.manage',
    'stock.in',
    'stock.out',
    'stock.history',
    'parts.view',
    'parts.manage',
    'parts.request',
    'equipment.parts.view',
    'equipment.parts.manage',
    'maintenance.equipment.view'
];

echo "🔍 VERIFICAÇÃO DE PERMISSÕES:\n";
echo str_repeat("-", 50) . "\n";

$hasPermissions = [];
$missingPermissions = [];

foreach ($stockPermissions as $perm) {
    $hasPermission = $user->can($perm);
    $existsInDb = \Spatie\Permission\Models\Permission::where('name', $perm)->exists();
    
    if ($hasPermission) {
        $hasPermissions[] = $perm;
        echo "✅ {$perm}\n";
    } else {
        $missingPermissions[] = $perm;
        echo "❌ {$perm}" . ($existsInDb ? " (existe na BD)" : " (NÃO existe na BD)") . "\n";
    }
}

echo "\n📊 RESUMO PERMISSÕES:\n";
echo "✅ Tem: " . count($hasPermissions) . " permissões\n";
echo "❌ Falta: " . count($missingPermissions) . " permissões\n";

// Verificar se as rotas existem
echo "\n🛣️ VERIFICAÇÃO DE ROTAS:\n";
echo str_repeat("-", 50) . "\n";

$stockRoutes = [
    'stocks.stockin',
    'stocks.stockout',
    'stocks.history',
    'stocks.part-requests'
];

foreach ($stockRoutes as $routeName) {
    try {
        $url = route($routeName);
        echo "✅ {$routeName} -> {$url}\n";
    } catch (Exception $e) {
        echo "❌ {$routeName} -> ERRO: " . $e->getMessage() . "\n";
    }
}

// Verificar permissão principal do menu
echo "\n🎯 ANÁLISE DO LAYOUT:\n";
echo str_repeat("-", 50) . "\n";

$mainEquipmentPermission = $user->can('maintenance.equipment.view');
echo "Permissão principal (@can('maintenance.equipment.view')): " . ($mainEquipmentPermission ? "✅ SIM" : "❌ NÃO") . "\n";

if ($mainEquipmentPermission) {
    echo "✅ O utilizador DEVE ver o menu Parts & Stock\n";
    echo "📝 NOTA: Os submenus de stock estão dentro do @can('maintenance.equipment.view')\n";
} else {
    echo "❌ O utilizador NÃO deve ver o menu Parts & Stock\n";
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "CONCLUSÃO:\n";

if ($mainEquipmentPermission) {
    echo "✅ Utilizador tem acesso ao menu principal\n";
    
    $missingRoutes = [];
    foreach ($stockRoutes as $routeName) {
        try {
            route($routeName);
        } catch (Exception $e) {
            $missingRoutes[] = $routeName;
        }
    }
    
    if (empty($missingRoutes)) {
        echo "✅ Todas as rotas de stock existem\n";
        echo "🎯 PROBLEMA: Provavelmente é de frontend (JavaScript/CSS)\n";
        echo "💡 SOLUÇÃO: Clicar no menu 'Equipment Parts' para expandir\n";
    } else {
        echo "❌ Rotas em falta: " . implode(', ', $missingRoutes) . "\n";
        echo "🎯 PROBLEMA: Rotas não definidas\n";
    }
} else {
    echo "❌ Utilizador não tem permissão para ver o menu\n";
    echo "🎯 PROBLEMA: Falta permissão 'maintenance.equipment.view'\n";
}

echo str_repeat("=", 60) . "\n";
