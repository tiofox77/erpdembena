<?php

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== TESTE: Utilizador Logado no Browser ===\n\n";

// Simulate web request to get actual session
$request = \Illuminate\Http\Request::createFromGlobals();
app()->instance('request', $request);

// Check Laravel auth (without starting session manually)
$user = auth()->user();

if ($user) {
    echo "✅ UTILIZADOR LOGADO:\n";
    echo str_repeat("-", 25) . "\n";
    echo "👤 Nome: {$user->name}\n";
    echo "📧 Email: {$user->email}\n";
    echo "🆔 ID: {$user->id}\n";
    echo "🏷️  Roles: " . $user->roles->pluck('name')->join(', ') . "\n";
    echo "📊 Total Permissões: " . $user->getAllPermissions()->count() . "\n\n";
    
    // Test maintenance permissions
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
    
    echo "🔍 PERMISSÕES DE MANUTENÇÃO:\n";
    echo str_repeat("-", 30) . "\n";
    
    $hasAllPerms = true;
    foreach ($maintenancePermissions as $perm) {
        $has = $user->can($perm);
        $status = $has ? "✅" : "❌";
        echo sprintf("%-30s %s\n", $perm, $status);
        if (!$has) $hasAllPerms = false;
    }
    
    // Test main menu access
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
    
    echo "\n📋 ACESSO AOS MENUS:\n";
    echo str_repeat("-", 20) . "\n";
    echo "Menu Principal Maintenance: " . ($canSeeMainMenu ? "✅ SIM" : "❌ NÃO") . "\n";
    echo "Todas as Permissões: " . ($hasAllPerms ? "✅ SIM" : "❌ NÃO") . "\n";
    
    // Check if this is maintenance user
    if ($user->email === 'maintenance@dembena-group.com') {
        echo "\n🎯 UTILIZADOR MAINTENANCE CORRETO!\n";
        
        if ($hasAllPerms && $canSeeMainMenu) {
            echo "✅ Deveria ver todos os menus de manutenção\n";
            echo "\n💡 SE NÃO VÊ OS MENUS:\n";
            echo "   1. Pressionar F5 para recarregar a página\n";
            echo "   2. Limpar cache do browser (Ctrl+Shift+Del)\n";
            echo "   3. Verificar console do browser (F12) para erros JavaScript\n";
            echo "   4. Testar em modo incógnito\n";
        } else {
            echo "❌ Problema com permissões\n";
        }
    } else {
        echo "\n⚠️  UTILIZADOR DIFERENTE:\n";
        echo "   Logado: {$user->email}\n";
        echo "   Esperado: maintenance@dembena-group.com\n";
        echo "   🔧 Fazer login com a conta correta\n";
    }
    
} else {
    echo "❌ NENHUM UTILIZADOR LOGADO\n";
    echo "🔧 Verificar se fez login corretamente\n";
    
    // Check session data
    echo "\n🔍 DADOS DA SESSÃO:\n";
    echo str_repeat("-", 20) . "\n";
    
    if (isset($_SESSION) && !empty($_SESSION)) {
        foreach ($_SESSION as $key => $value) {
            if (is_string($value) && strlen($value) < 50) {
                echo "{$key}: {$value}\n";
            } else {
                echo "{$key}: " . gettype($value) . "\n";
            }
        }
    } else {
        echo "Sessão vazia ou não iniciada\n";
    }
}

echo "\n" . str_repeat("=", 50) . "\n";
