<?php

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== DEBUG COMPLETO DO MENU MAINTENANCE ===\n\n";

try {
    // 1. Verificar se a role existe
    $role = Role::where('name', 'maintenance-manager')->first();
    
    if (!$role) {
        echo "❌ ERRO: A role 'maintenance-manager' não existe.\n";
        exit(1);
    }
    
    echo "✅ Role 'maintenance-manager' existe\n";
    echo "📊 Permissões da role: " . $role->permissions->count() . "\n\n";
    
    // 2. Listar usuários com essa role
    $usersWithRole = User::role('maintenance-manager')->get();
    echo "👥 USUÁRIOS COM ROLE MAINTENANCE-MANAGER:\n";
    echo str_repeat("-", 50) . "\n";
    
    if ($usersWithRole->count() === 0) {
        echo "❌ NENHUM usuário tem a role 'maintenance-manager'\n";
        echo "💡 SOLUÇÃO: Atribuir a role a algum usuário\n\n";
    } else {
        foreach ($usersWithRole as $user) {
            echo "  ✅ {$user->name} (ID: {$user->id}) - {$user->email}\n";
        }
        echo "\n";
    }
    
    // 3. Verificar permissões específicas que o menu verifica
    $menuPermissions = [
        'maintenance.view',
        'maintenance.dashboard', 
        'maintenance.equipment.view',
        'maintenance.corrective.view',
        'maintenance.plan.view'
    ];
    
    echo "🔍 VERIFICANDO PERMISSÕES DO MENU:\n";
    echo str_repeat("-", 50) . "\n";
    
    $hasPermissions = false;
    foreach ($menuPermissions as $perm) {
        if ($role->hasPermissionTo($perm)) {
            echo "  ✅ {$perm} - PRESENTE\n";
            $hasPermissions = true;
        } else {
            echo "  ❌ {$perm} - AUSENTE\n";
        }
    }
    
    if (!$hasPermissions) {
        echo "\n❌ PROBLEMA: Role não tem NENHUMA das permissões que o menu verifica!\n";
    } else {
        echo "\n✅ Role tem pelo menos uma permissão necessária para o menu\n";
    }
    
    // 4. Limpar cache de permissões
    echo "\n🧹 LIMPANDO CACHE DE PERMISSÕES...\n";
    app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    echo "✅ Cache de permissões limpo\n";
    
    // 5. Testar uma permissão específica
    if ($usersWithRole->count() > 0) {
        $testUser = $usersWithRole->first();
        echo "\n🧪 TESTE COM USUÁRIO: {$testUser->name}\n";
        echo str_repeat("-", 50) . "\n";
        
        if ($testUser->can('maintenance.view')) {
            echo "  ✅ Usuário PODE acessar maintenance.view\n";
        } else {
            echo "  ❌ Usuário NÃO PODE acessar maintenance.view\n";
        }
        
        if ($testUser->can('maintenance.dashboard')) {
            echo "  ✅ Usuário PODE acessar maintenance.dashboard\n";
        } else {
            echo "  ❌ Usuário NÃO PODE acessar maintenance.dashboard\n";
        }
        
        if ($testUser->hasRole('maintenance-manager')) {
            echo "  ✅ Usuário TEM a role maintenance-manager\n";
        } else {
            echo "  ❌ Usuário NÃO TEM a role maintenance-manager\n";
        }
    }
    
    // 6. Verificar se o layout está correto
    echo "\n📄 VERIFICANDO LAYOUT:\n";
    echo str_repeat("-", 50) . "\n";
    
    $layoutPath = 'resources/views/layouts/livewire.blade.php';
    if (file_exists($layoutPath)) {
        $layoutContent = file_get_contents($layoutPath);
        if (strpos($layoutContent, "maintenance.view") !== false) {
            echo "  ✅ Layout contém verificação de 'maintenance.view'\n";
        } else {
            echo "  ❌ Layout NÃO contém verificação de 'maintenance.view'\n";
        }
        
        if (strpos($layoutContent, "@canany") !== false) {
            echo "  ✅ Layout usa @canany\n";
        } else {
            echo "  ❌ Layout NÃO usa @canany\n";
        }
    } else {
        echo "  ❌ Arquivo de layout não encontrado\n";
    }
    
    echo "\n📋 DIAGNÓSTICO FINAL:\n";
    echo str_repeat("=", 50) . "\n";
    
    if ($usersWithRole->count() === 0) {
        echo "🚨 PROBLEMA PRINCIPAL: Nenhum usuário tem a role 'maintenance-manager'\n";
        echo "💡 SOLUÇÃO: Execute o comando para atribuir a role a um usuário\n";
    } elseif (!$hasPermissions) {
        echo "🚨 PROBLEMA PRINCIPAL: Role não tem permissões necessárias\n";
        echo "💡 SOLUÇÃO: Adicionar permissões à role\n";
    } else {
        echo "✅ Configuração parece correta\n";
        echo "💡 POSSÍVEIS CAUSAS:\n";
        echo "   - Sessão do usuário precisa ser renovada (logout/login)\n";
        echo "   - Cache do navegador\n";
        echo "   - Usuário logado não tem a role\n";
    }
    
} catch (Exception $e) {
    echo "❌ ERRO: " . $e->getMessage() . "\n";
    exit(1);
}
