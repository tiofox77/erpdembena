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

echo "=== LIMPEZA FORÇADA - ELIMINAR TODAS AS ROLES EXCETO SUPER-ADMIN ===\n\n";

try {
    // ETAPA 1: BACKUP DE SEGURANÇA
    echo "💾 ETAPA 1: Criando backup de segurança...\n";
    echo str_repeat("-", 60) . "\n";
    
    $backupData = [
        'roles' => Role::with('permissions')->get()->toArray(),
        'permissions' => Permission::all()->toArray(),
        'user_roles' => DB::table('model_has_roles')->get()->toArray(),
        'role_permissions' => DB::table('role_has_permissions')->get()->toArray()
    ];
    
    file_put_contents('backup_permissions_' . date('Y-m-d_H-i-s') . '.json', json_encode($backupData, JSON_PRETTY_PRINT));
    echo "✅ Backup criado com sucesso\n";
    
    // ETAPA 2: VERIFICAR STATUS ATUAL
    echo "\n📊 ETAPA 2: Status atual...\n";
    echo str_repeat("-", 60) . "\n";
    
    $allRoles = Role::all();
    echo "Total de roles: " . $allRoles->count() . "\n";
    
    foreach ($allRoles as $role) {
        $userCount = User::role($role->name)->count();
        echo "  - {$role->name} (ID: {$role->id}, Usuários: {$userCount})\n";
    }
    
    // ETAPA 3: PRESERVAR SUPER-ADMIN
    echo "\n🛡️  ETAPA 3: Preservando Super Admin...\n";
    echo str_repeat("-", 60) . "\n";
    
    $superAdminRole = Role::where('name', 'super-admin')->first();
    if (!$superAdminRole) {
        echo "⚠️  Super Admin não encontrado, criando...\n";
        $superAdminRole = Role::create(['name' => 'super-admin', 'guard_name' => 'web']);
    }
    
    $superAdminUsers = User::role('super-admin')->get();
    echo "✅ Super Admin preservado (ID: {$superAdminRole->id}) com " . $superAdminUsers->count() . " usuários\n";
    
    // ETAPA 4: REMOVER USUÁRIOS DE OUTRAS ROLES (MÉTODO DIRETO)
    echo "\n👥 ETAPA 4: Removendo usuários de outras roles...\n";
    echo str_repeat("-", 60) . "\n";
    
    $otherRoles = Role::where('name', '!=', 'super-admin')->get();
    $removedUserRoles = 0;
    
    foreach ($otherRoles as $role) {
        // Remover via SQL direto
        $removed = DB::table('model_has_roles')
            ->where('role_id', $role->id)
            ->delete();
        
        if ($removed > 0) {
            echo "  🗑️  Removidos {$removed} usuários da role: {$role->name}\n";
            $removedUserRoles += $removed;
        }
    }
    
    echo "✅ Total de relações usuário-role removidas: {$removedUserRoles}\n";
    
    // ETAPA 5: REMOVER PERMISSÕES DAS ROLES (MÉTODO DIRETO)
    echo "\n🔗 ETAPA 5: Removendo permissões das roles...\n";
    echo str_repeat("-", 60) . "\n";
    
    $removedRolePermissions = 0;
    foreach ($otherRoles as $role) {
        $removed = DB::table('role_has_permissions')
            ->where('role_id', $role->id)
            ->delete();
        
        if ($removed > 0) {
            echo "  🗑️  Removidas {$removed} permissões da role: {$role->name}\n";
            $removedRolePermissions += $removed;
        }
    }
    
    echo "✅ Total de relações role-permissão removidas: {$removedRolePermissions}\n";
    
    // ETAPA 6: DELETAR ROLES (MÉTODO DIRETO)
    echo "\n🗑️  ETAPA 6: Eliminando roles via SQL direto...\n";
    echo str_repeat("-", 60) . "\n";
    
    $roleIds = $otherRoles->pluck('id')->toArray();
    $rolesToDelete = $otherRoles->count();
    
    if (!empty($roleIds)) {
        // Delete direto na tabela roles
        $deleted = DB::table('roles')
            ->whereIn('id', $roleIds)
            ->delete();
        
        echo "✅ {$deleted} roles eliminadas via SQL direto\n";
    } else {
        echo "ℹ️  Nenhuma role para eliminar\n";
    }
    
    // ETAPA 7: CONFIGURAR SUPER-ADMIN COM TODAS AS PERMISSÕES
    echo "\n🛡️  ETAPA 7: Configurando Super Admin...\n";
    echo str_repeat("-", 60) . "\n";
    
    $allPermissions = Permission::all();
    $superAdminRole->syncPermissions($allPermissions);
    echo "✅ Super Admin configurado com " . $allPermissions->count() . " permissões\n";
    
    // ETAPA 8: LIMPAR CACHE COMPLETAMENTE
    echo "\n🧹 ETAPA 8: Limpando cache...\n";
    echo str_repeat("-", 60) . "\n";
    
    // Limpar cache do Spatie
    app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    
    // Limpar outros caches se existirem
    if (function_exists('artisan')) {
        try {
            \Artisan::call('cache:clear');
            \Artisan::call('config:clear');
            echo "✅ Cache do Laravel limpo\n";
        } catch (Exception $e) {
            echo "⚠️  Cache do Laravel não pôde ser limpo: " . $e->getMessage() . "\n";
        }
    }
    
    echo "✅ Cache de permissões limpo\n";
    
    // ETAPA 9: VERIFICAÇÃO FINAL
    echo "\n📊 ETAPA 9: Verificação final...\n";
    echo str_repeat("-", 60) . "\n";
    
    $finalRoles = Role::all();
    echo "🎭 ROLES RESTANTES: " . $finalRoles->count() . "\n";
    
    foreach ($finalRoles as $role) {
        $userCount = User::role($role->name)->count();
        $permCount = $role->permissions->count();
        echo "  ✓ {$role->name} (ID: {$role->id}, {$userCount} usuários, {$permCount} permissões)\n";
    }
    
    // Verificar usuários sem roles
    $usersWithoutRole = User::whereDoesntHave('roles')->get();
    if ($usersWithoutRole->count() > 0) {
        echo "\n⚠️  USUÁRIOS SEM ROLE:\n";
        foreach ($usersWithoutRole as $user) {
            echo "  - {$user->name} ({$user->email})\n";
        }
    }
    
    // RELATÓRIO FINAL
    echo "\n📊 RELATÓRIO FINAL:\n";
    echo str_repeat("=", 80) . "\n";
    echo "• Roles iniciais: " . $allRoles->count() . "\n";
    echo "• Roles finais: " . $finalRoles->count() . "\n";
    echo "• Roles eliminadas: " . ($allRoles->count() - $finalRoles->count()) . "\n";
    echo "• Relações usuário-role removidas: {$removedUserRoles}\n";
    echo "• Relações role-permissão removidas: {$removedRolePermissions}\n";
    echo "• Permissões totais: " . $allPermissions->count() . "\n";
    
    if ($finalRoles->count() === 1 && $finalRoles->first()->name === 'super-admin') {
        echo "\n🎉 LIMPEZA FORÇADA CONCLUÍDA COM SUCESSO!\n";
        echo "✅ Apenas super-admin permanece no sistema\n";
        echo "✅ Sistema limpo e pronto para reorganização\n";
    } else {
        echo "\n⚠️  ATENÇÃO: Ainda existem " . ($finalRoles->count() - 1) . " roles além do super-admin\n";
        echo "💡 Pode ser necessário limpeza manual adicional\n";
    }
    
    echo "\n🔄 PRÓXIMOS PASSOS:\n";
    echo "1. Fazer logout/login para renovar todas as sessões\n";
    echo "2. Verificar se apenas super-admin existe\n";
    echo "3. Criar novas roles modulares conforme necessário\n";
    
} catch (Exception $e) {
    echo "\n❌ ERRO: " . $e->getMessage() . "\n";
    echo "Linha: " . $e->getLine() . "\n";
    echo "Arquivo: " . $e->getFile() . "\n";
    exit(1);
}
