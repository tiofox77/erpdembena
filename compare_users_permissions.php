<?php

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== COMPARAR: Permissões entre criador@gmail.com e maintenance@dembena-group.com ===\n\n";

// Get both users
$criador = \App\Models\User::where('email', 'criador@gmail.com')->first();
$maintenance = \App\Models\User::where('email', 'maintenance@dembena-group.com')->first();

if (!$criador) {
    echo "❌ Utilizador criador@gmail.com não encontrado!\n";
    exit;
}

if (!$maintenance) {
    echo "❌ Utilizador maintenance@dembena-group.com não encontrado!\n";
    exit;
}

echo "👤 CRIADOR: {$criador->name} ({$criador->email})\n";
echo "🏷️  Roles: " . $criador->roles->pluck('name')->join(', ') . "\n";
echo "📊 Total de Permissões: " . $criador->getAllPermissions()->count() . "\n\n";

echo "👤 MAINTENANCE: {$maintenance->name} ({$maintenance->email})\n";
echo "🏷️  Roles: " . $maintenance->roles->pluck('name')->join(', ') . "\n";
echo "📊 Total de Permissões: " . $maintenance->getAllPermissions()->count() . "\n\n";

echo str_repeat("=", 60) . "\n";

// Test specific permissions from the menu
$menuPermissions = [
    'maintenance.dashboard.view',
    'maintenance.equipment.view',
    'maintenance.plan.view',
    'maintenance.corrective.view',
    'areas.view',
    'lines.view',
    'maintenance.technicians.view',
    'holidays.view',
    'maintenance.reports',
    'reports.view'
];

echo "🔍 TESTE DE PERMISSÕES ESPECÍFICAS DO MENU:\n";
echo str_repeat("-", 50) . "\n";
echo sprintf("%-35s %-10s %-10s\n", "PERMISSÃO", "CRIADOR", "MAINT.");
echo str_repeat("-", 50) . "\n";

foreach ($menuPermissions as $permission) {
    $criadorHas = $criador->can($permission) ? "✅ SIM" : "❌ NÃO";
    $maintenanceHas = $maintenance->can($permission) ? "✅ SIM" : "❌ NÃO";
    
    echo sprintf("%-35s %-10s %-10s\n", $permission, $criadorHas, $maintenanceHas);
}

echo "\n" . str_repeat("=", 60) . "\n";

// Check if maintenance user can see the main menu
$canSeeMainMenu = $maintenance->canAny([
    'maintenance.dashboard.view',
    'maintenance.equipment.view',
    'maintenance.plan.view',
    'maintenance.corrective.view',
    'areas.view',
    'lines.view',
    'maintenance.technicians.view',
    'holidays.view'
]);

echo "📋 RESULTADO DO MENU PRINCIPAL:\n";
echo str_repeat("-", 30) . "\n";
echo "Criador pode ver menu Maintenance: " . ($criador->canAny(['maintenance.dashboard.view', 'maintenance.equipment.view', 'maintenance.plan.view', 'maintenance.corrective.view', 'areas.view', 'lines.view', 'maintenance.technicians.view', 'holidays.view']) ? "✅ SIM" : "❌ NÃO") . "\n";
echo "Maintenance pode ver menu Maintenance: " . ($canSeeMainMenu ? "✅ SIM" : "❌ NÃO") . "\n\n";

// Get all permissions for maintenance user
echo "🔑 TODAS AS PERMISSÕES DO UTILIZADOR MAINTENANCE:\n";
echo str_repeat("-", 50) . "\n";
$maintenancePermissions = $maintenance->getAllPermissions()->pluck('name')->toArray();
sort($maintenancePermissions);

foreach ($maintenancePermissions as $perm) {
    echo "   ✅ {$perm}\n";
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "📊 ANÁLISE:\n";
echo "   Criador tem " . $criador->getAllPermissions()->count() . " permissões\n";
echo "   Maintenance tem " . $maintenance->getAllPermissions()->count() . " permissões\n";

if (!$canSeeMainMenu) {
    echo "\n❌ PROBLEMA IDENTIFICADO: Utilizador maintenance não consegue ver o menu principal!\n";
    echo "🔧 VERIFICAR:\n";
    echo "   1. Cache de permissões\n";
    echo "   2. Sincronização da base de dados\n";
    echo "   3. Middleware das rotas\n";
} else {
    echo "\n✅ Utilizador maintenance deveria conseguir ver o menu principal\n";
}

echo "\n" . str_repeat("=", 60) . "\n";
