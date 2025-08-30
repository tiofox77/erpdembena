<?php

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== DEBUG: Acesso à página /maintenance/plan ===\n\n";

// Check current logged user
$user = auth()->user();

if (!$user) {
    echo "❌ Nenhum utilizador logado\n";
    exit;
}

echo "👤 Utilizador Logado: {$user->name} ({$user->email})\n";
echo "🏷️  Roles: " . $user->roles->pluck('name')->join(', ') . "\n";
echo "📊 Total Permissões: " . $user->getAllPermissions()->count() . "\n\n";

echo "🔍 ANÁLISE DA ROTA /maintenance/plan:\n";
echo str_repeat("-", 50) . "\n";
echo "Middleware necessário: permission:preventive.view\n\n";

// Test the specific permission
$hasPreventiveView = $user->can('preventive.view');
echo "Permissão 'preventive.view': " . ($hasPreventiveView ? "✅ TEM" : "❌ NÃO TEM") . "\n\n";

if (!$hasPreventiveView) {
    echo "🔍 VERIFICAR PERMISSÕES RELACIONADAS:\n";
    echo str_repeat("-", 40) . "\n";
    
    $relatedPermissions = [
        'maintenance.plan.view',
        'maintenance.plan.manage', 
        'maintenance.plan.create',
        'maintenance.plan.edit',
        'maintenance.plan.delete',
        'preventive.create',
        'preventive.edit',
        'preventive.delete',
        'preventive.manage'
    ];
    
    foreach ($relatedPermissions as $perm) {
        $has = $user->can($perm) ? "✅" : "❌";
        echo "   {$has} {$perm}\n";
    }
    
    echo "\n📋 TODAS AS PERMISSÕES DO UTILIZADOR:\n";
    echo str_repeat("-", 40) . "\n";
    $allPermissions = $user->getAllPermissions()->pluck('name')->toArray();
    sort($allPermissions);
    
    $planRelated = array_filter($allPermissions, function($perm) {
        return strpos($perm, 'plan') !== false || strpos($perm, 'preventive') !== false;
    });
    
    if (empty($planRelated)) {
        echo "❌ Nenhuma permissão relacionada com 'plan' ou 'preventive' encontrada\n";
    } else {
        echo "Permissões relacionadas encontradas:\n";
        foreach ($planRelated as $perm) {
            echo "   ✅ {$perm}\n";
        }
    }
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "🛠️  SOLUÇÕES:\n";
echo str_repeat("-", 15) . "\n";

if (!$hasPreventiveView) {
    echo "1. Adicionar permissão 'preventive.view' ao utilizador/role\n";
    echo "2. OU alterar middleware da rota para usar permissão existente\n";
    echo "3. OU criar a permissão 'preventive.view' na base de dados\n";
    
    // Check if permission exists in database
    $permissionExists = \Spatie\Permission\Models\Permission::where('name', 'preventive.view')->exists();
    echo "\n🔍 Permissão 'preventive.view' existe na BD: " . ($permissionExists ? "✅ SIM" : "❌ NÃO") . "\n";
    
    if (!$permissionExists) {
        echo "\n⚠️  PROBLEMA: A permissão 'preventive.view' não existe na base de dados!\n";
        echo "Necessário criar a permissão ou alterar o middleware da rota.\n";
    }
} else {
    echo "✅ Utilizador tem a permissão necessária\n";
    echo "Problema pode ser de cache ou middleware adicional\n";
}

echo "\n" . str_repeat("=", 60) . "\n";
