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

echo "=== LIMPEZA COMPLETA - MANTER APENAS SUPER-ADMIN ===\n\n";

try {
    // ETAPA 1: STATUS ATUAL
    echo "📊 ETAPA 1: Verificando status atual...\n";
    echo str_repeat("-", 60) . "\n";
    
    $allRoles = Role::all();
    echo "Total de roles existentes: " . $allRoles->count() . "\n";
    
    foreach ($allRoles as $role) {
        $userCount = User::role($role->name)->count();
        echo "  - {$role->name} ({$userCount} usuários)\n";
    }
    
    // ETAPA 2: PRESERVAR SUPER ADMIN
    echo "\n🛡️  ETAPA 2: Preservando Super Admin...\n";
    echo str_repeat("-", 60) . "\n";
    
    $superAdminRole = Role::where('name', 'super-admin')->first();
    if (!$superAdminRole) {
        echo "⚠️  Super Admin não encontrado, criando...\n";
        $superAdminRole = Role::create(['name' => 'super-admin', 'guard_name' => 'web']);
        
        // Dar todas as permissões ao super admin
        $allPermissions = Permission::all();
        $superAdminRole->syncPermissions($allPermissions);
    }
    
    $superAdminUsers = User::role('super-admin')->get();
    echo "✅ Super Admin preservado:\n";
    foreach ($superAdminUsers as $user) {
        echo "  - {$user->name} ({$user->email})\n";
    }
    
    // ETAPA 3: REMOVER USUARIOS DE OUTRAS ROLES
    echo "\n👥 ETAPA 3: Removendo usuários de outras roles...\n";
    echo str_repeat("-", 60) . "\n";
    
    $rolesToClean = Role::where('name', '!=', 'super-admin')->get();
    $usersDetached = 0;
    
    foreach ($rolesToClean as $role) {
        $usersWithRole = User::role($role->name)->get();
        if ($usersWithRole->count() > 0) {
            echo "🧹 Removendo usuários da role: {$role->name}\n";
            foreach ($usersWithRole as $user) {
                $user->removeRole($role->name);
                echo "  - {$user->name} removido de {$role->name}\n";
                $usersDetached++;
            }
        }
    }
    
    echo "✅ Total de usuários desvinculados: {$usersDetached}\n";
    
    // ETAPA 4: DELETAR TODAS AS ROLES EXCETO SUPER-ADMIN
    echo "\n🗑️  ETAPA 4: Removendo todas as roles (exceto super-admin)...\n";
    echo str_repeat("-", 60) . "\n";
    
    $rolesToDelete = Role::where('name', '!=', 'super-admin')->get();
    $deletedCount = 0;
    
    foreach ($rolesToDelete as $role) {
        echo "  🗑️  Removendo role: {$role->name}\n";
        
        // Remover todas as permissões da role antes de deletar
        $role->permissions()->detach();
        
        // Deletar a role
        $role->delete();
        $deletedCount++;
    }
    
    echo "✅ Total de roles removidas: {$deletedCount}\n";
    
    // ETAPA 5: LIMPEZA DE PERMISSÕES ÓRFÃS
    echo "\n🧹 ETAPA 5: Verificando permissões órfãs...\n";
    echo str_repeat("-", 60) . "\n";
    
    $allPermissions = Permission::all();
    echo "Total de permissões no sistema: " . $allPermissions->count() . "\n";
    
    // Garantir que super-admin tem todas as permissões
    $superAdminRole->syncPermissions($allPermissions);
    echo "✅ Super Admin configurado com todas as " . $allPermissions->count() . " permissões\n";
    
    // ETAPA 6: LIMPAR CACHE
    echo "\n🧹 ETAPA 6: Limpando cache de permissões...\n";
    echo str_repeat("-", 60) . "\n";
    
    app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    echo "✅ Cache de permissões limpo\n";
    
    // ETAPA 7: VERIFICAÇÃO FINAL
    echo "\n📊 ETAPA 7: Verificação final...\n";
    echo str_repeat("-", 60) . "\n";
    
    $finalRoles = Role::all();
    $finalPermissions = Permission::all();
    
    echo "🎭 ROLES RESTANTES:\n";
    foreach ($finalRoles as $role) {
        $userCount = User::role($role->name)->count();
        $permCount = $role->permissions->count();
        echo "  ✓ {$role->name} ({$userCount} usuários, {$permCount} permissões)\n";
    }
    
    echo "\n📊 ESTATÍSTICAS FINAIS:\n";
    echo str_repeat("=", 80) . "\n";
    echo "  • Roles restantes: " . $finalRoles->count() . " (apenas super-admin)\n";
    echo "  • Permissões totais: " . $finalPermissions->count() . "\n";
    echo "  • Roles removidas: {$deletedCount}\n";
    echo "  • Usuários desvinculados: {$usersDetached}\n";
    
    // VERIFICAR SE ALGUM USUÁRIO FICOU SEM ROLE
    $usersWithoutRole = User::whereDoesntHave('roles')->get();
    if ($usersWithoutRole->count() > 0) {
        echo "\n⚠️  USUÁRIOS SEM ROLE DETECTADOS:\n";
        foreach ($usersWithoutRole as $user) {
            echo "  - {$user->name} ({$user->email})\n";
        }
        echo "\n💡 RECOMENDAÇÃO: Atribua a role super-admin aos usuários administradores\n";
    }
    
    echo "\n🎉 LIMPEZA CONCLUÍDA COM SUCESSO!\n";
    echo "\n💡 SISTEMA AGORA TEM APENAS:\n";
    echo "  ✅ 1 Role: super-admin\n";
    echo "  ✅ " . $finalPermissions->count() . " Permissões (todas no super-admin)\n";
    echo "  ✅ Sistema limpo e organizado\n";
    
    echo "\n🔄 PRÓXIMOS PASSOS:\n";
    echo "1. Fazer logout/login para renovar sessões\n";
    echo "2. Criar novas roles conforme necessário\n";
    echo "3. Atribuir roles apropriadas aos usuários\n";
    
} catch (Exception $e) {
    echo "\n❌ ERRO: " . $e->getMessage() . "\n";
    echo "Linha: " . $e->getLine() . "\n";
    echo "Arquivo: " . $e->getFile() . "\n";
    exit(1);
}
