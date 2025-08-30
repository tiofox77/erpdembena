<?php

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== CORRIGIR: Middleware das Rotas de Stock ===\n\n";

// Get maintenance user
$user = \App\Models\User::where('email', 'maintenance@dembena-group.com')->first();

if (!$user) {
    echo "❌ Utilizador maintenance@dembena-group.com não encontrado\n";
    exit;
}

echo "👤 Utilizador: {$user->name} ({$user->email})\n\n";

// Verificar permissão atual do middleware
$middlewarePermission = 'inventory.manage';
$hasMiddlewarePermission = $user->can($middlewarePermission);

echo "🔍 PROBLEMA IDENTIFICADO:\n";
echo str_repeat("-", 50) . "\n";
echo "Middleware das rotas de stock: permission:{$middlewarePermission}\n";
echo "Utilizador tem '{$middlewarePermission}': " . ($hasMiddlewarePermission ? "✅ SIM" : "❌ NÃO") . "\n";

if (!$hasMiddlewarePermission) {
    echo "\n🎯 SOLUÇÃO: Adicionar permissão '{$middlewarePermission}' ao utilizador\n";
    
    // Verificar se a permissão existe
    $permissionExists = \Spatie\Permission\Models\Permission::where('name', $middlewarePermission)->exists();
    
    if (!$permissionExists) {
        echo "⚠️  Permissão '{$middlewarePermission}' não existe na BD\n";
        echo "📝 Criando permissão...\n";
        
        try {
            \Spatie\Permission\Models\Permission::create(['name' => $middlewarePermission]);
            echo "✅ Permissão '{$middlewarePermission}' criada\n";
        } catch (Exception $e) {
            echo "❌ Erro ao criar permissão: " . $e->getMessage() . "\n";
            exit;
        }
    }
    
    // Adicionar permissão à role maintenance-manager
    $maintenanceRole = \Spatie\Permission\Models\Role::where('name', 'maintenance-manager')->first();
    
    if ($maintenanceRole) {
        try {
            $maintenanceRole->givePermissionTo($middlewarePermission);
            echo "✅ Permissão '{$middlewarePermission}' adicionada à role 'maintenance-manager'\n";
        } catch (Exception $e) {
            echo "❌ Erro ao adicionar permissão à role: " . $e->getMessage() . "\n";
        }
    }
    
    // Limpar cache
    app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    echo "✅ Cache de permissões limpo\n";
    
    // Verificar novamente
    $user->refresh();
    $hasPermissionNow = $user->can($middlewarePermission);
    echo "\n🔍 VERIFICAÇÃO FINAL:\n";
    echo "Utilizador agora tem '{$middlewarePermission}': " . ($hasPermissionNow ? "✅ SIM" : "❌ NÃO") . "\n";
    
    if ($hasPermissionNow) {
        echo "\n✅ PROBLEMA RESOLVIDO!\n";
        echo "🎯 O utilizador agora deve conseguir aceder aos menus de stock\n";
    } else {
        echo "\n❌ PROBLEMA PERSISTE\n";
        echo "🔧 Pode ser necessário fazer logout/login para actualizar a sessão\n";
    }
} else {
    echo "\n✅ Utilizador já tem a permissão necessária\n";
    echo "🤔 O problema pode ser outro:\n";
    echo "   - Sessão expirada (fazer logout/login)\n";
    echo "   - Cache do browser\n";
    echo "   - Middleware adicional nas rotas\n";
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "ROTAS AFECTADAS:\n";
echo "- /stocks/stock-in\n";
echo "- /stocks/stock-out\n";
echo "- /stocks/stock-history\n";
echo "- /stocks/part-requests\n";
echo str_repeat("=", 60) . "\n";
