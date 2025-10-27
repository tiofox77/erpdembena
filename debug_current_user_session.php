<?php

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== DEBUG: Sessão Atual do Utilizador ===\n\n";

// Check if there's a logged in user
$user = auth()->user();

if (!$user) {
    echo "❌ NENHUM UTILIZADOR LOGADO\n";
    echo "🔧 Solução: Fazer login no sistema\n";
    exit;
}

echo "👤 UTILIZADOR LOGADO: {$user->name} ({$user->email})\n";
echo "🆔 ID: {$user->id}\n";
echo "🏷️  Roles: " . $user->roles->pluck('name')->join(', ') . "\n";
echo "📊 Total Permissões: " . $user->getAllPermissions()->count() . "\n\n";

// Test maintenance menu permissions specifically
$maintenancePermissions = [
    'maintenance.dashboard.view',
    'maintenance.equipment.view', 
    'maintenance.plan.view',
    'maintenance.corrective.view',
    'areas.view',
    'lines.view',
    'maintenance.technicians.view',
    'holidays.view',
    'maintenance.reports'
];

echo "🔍 TESTE DE PERMISSÕES DE MANUTENÇÃO:\n";
echo str_repeat("-", 40) . "\n";

$hasAllMaintenancePerms = true;
foreach ($maintenancePermissions as $perm) {
    $has = $user->can($perm);
    $status = $has ? "✅" : "❌";
    echo sprintf("%-30s %s\n", $perm, $status);
    
    if (!$has) {
        $hasAllMaintenancePerms = false;
    }
}

// Test canAny for main menu
$canSeeMainMenu = $user->canAny([
    'maintenance.dashboard.view', 
    'maintenance.equipment.view', 
    'maintenance.plan.view', 
    'maintenance.corrective.view', 
    'areas.view', 
    'lines.view', 
    'maintenance.technicians.view', 
    'holidays.view'
]);

echo "\n📋 RESULTADO:\n";
echo str_repeat("-", 15) . "\n";
echo "Pode ver menu principal: " . ($canSeeMainMenu ? "✅ SIM" : "❌ NÃO") . "\n";
echo "Tem todas as permissões: " . ($hasAllMaintenancePerms ? "✅ SIM" : "❌ NÃO") . "\n";

if ($user->email === 'maintenance@dembena-group.com') {
    echo "\n✅ ESTE É O UTILIZADOR CORRETO (maintenance@dembena-group.com)\n";
    
    if ($hasAllMaintenancePerms) {
        echo "\n🎯 DIAGNÓSTICO:\n";
        echo "   ✅ Utilizador tem todas as permissões necessárias\n";
        echo "   ✅ Deveria ver todos os menus de manutenção\n";
        echo "\n💡 SE AINDA NÃO VÊ OS MENUS:\n";
        echo "   1. 🔄 Fazer logout/login completo\n";
        echo "   2. 🧹 Limpar cache do browser (Ctrl+Shift+Del)\n";
        echo "   3. 🕵️  Testar em modo incógnito/privado\n";
        echo "   4. 🔍 Verificar console do browser (F12) para erros\n";
        echo "   5. 🌐 Testar em outro browser\n";
    } else {
        echo "\n❌ PROBLEMA: Utilizador não tem todas as permissões\n";
    }
} else {
    echo "\n⚠️  ATENÇÃO: Este não é o utilizador maintenance@dembena-group.com\n";
    echo "   Utilizador atual: {$user->email}\n";
    echo "   Para testar com maintenance@dembena-group.com, faça login com essa conta\n";
}

// Check session info
echo "\n🔍 INFORMAÇÕES DA SESSÃO:\n";
echo str_repeat("-", 25) . "\n";
echo "Session ID: " . session_id() . "\n";
echo "Guard: " . auth()->getDefaultDriver() . "\n";
echo "Session Lifetime: " . config('session.lifetime') . " minutos\n";

// Check if user is maintenance-manager
$isMaintenanceManager = $user->hasRole('maintenance-manager');
echo "É maintenance-manager: " . ($isMaintenanceManager ? "✅ SIM" : "❌ NÃO") . "\n";

if ($isMaintenanceManager) {
    $role = $user->roles->where('name', 'maintenance-manager')->first();
    if ($role) {
        echo "Role ID: {$role->id}\n";
        echo "Role Guard: {$role->guard_name}\n";
        echo "Permissões da Role: " . $role->permissions->count() . "\n";
    }
}

echo "\n" . str_repeat("=", 50) . "\n";
echo "🎯 CONCLUSÃO:\n";

if ($user->email === 'maintenance@dembena-group.com' && $hasAllMaintenancePerms) {
    echo "✅ Utilizador correto com permissões corretas\n";
    echo "🔧 Problema é de cache/sessão do browser\n";
} elseif ($user->email !== 'maintenance@dembena-group.com') {
    echo "⚠️  Fazer login com maintenance@dembena-group.com\n";
} else {
    echo "❌ Permissões em falta - executar correção\n";
}

echo str_repeat("=", 50) . "\n";
