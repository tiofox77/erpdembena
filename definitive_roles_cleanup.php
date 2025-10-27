<?php

declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;
use App\Models\User;

echo "\n🧹 LIMPEZA DEFINITIVA DE ROLES E PERMISSIONS\n";
echo "=".str_repeat('=', 60)."\n";
echo "⏰ Timestamp: " . date('Y-m-d H:i:s') . "\n\n";

try {
    DB::beginTransaction();

    // 1. BACKUP CRÍTICO - Verificar se super-admin existe
    echo "1️⃣ VERIFICAÇÃO DE SEGURANÇA\n";
    echo str_repeat('-', 40) . "\n";
    
    $superAdminRole = Role::where('name', 'super-admin')->first();
    if (!$superAdminRole) {
        echo "❌ CRITICAL ERROR: Role 'super-admin' não encontrada!\n";
        echo "⚠️  Criando role super-admin de emergência...\n";
        
        $superAdminRole = Role::create([
            'name' => 'super-admin',
            'guard_name' => 'web'
        ]);
        
        // Dar todas as permissões ao super-admin
        $allPermissions = Permission::all();
        $superAdminRole->syncPermissions($allPermissions);
        
        echo "✅ Role super-admin criada com {$allPermissions->count()} permissões\n";
    } else {
        echo "✅ Role 'super-admin' encontrada (ID: {$superAdminRole->id})\n";
    }

    // 2. REATRIBUIR TODOS OS UTILIZADORES PARA SUPER-ADMIN
    echo "\n2️⃣ REATRIBUIÇÃO DE UTILIZADORES\n";
    echo str_repeat('-', 40) . "\n";
    
    $allUsers = User::all();
    $reassignedCount = 0;
    
    foreach ($allUsers as $user) {
        $currentRoles = $user->roles->pluck('name')->toArray();
        
        if (!in_array('super-admin', $currentRoles)) {
            $user->syncRoles(['super-admin']);
            $reassignedCount++;
            echo "👤 User {$user->id} ({$user->email}) reatribuído para super-admin\n";
        } else {
            echo "✓ User {$user->id} ({$user->email}) já tem super-admin\n";
        }
    }
    
    echo "📊 Total de utilizadores reatribuídos: {$reassignedCount}\n";

    // 3. IDENTIFICAR ROLES A ELIMINAR
    echo "\n3️⃣ IDENTIFICAÇÃO DE ROLES A ELIMINAR\n";
    echo str_repeat('-', 40) . "\n";
    
    $rolesToDelete = Role::where('name', '!=', 'super-admin')->get();
    $roleCount = $rolesToDelete->count();
    
    echo "🎯 Roles identificadas para eliminação: {$roleCount}\n";
    foreach ($rolesToDelete as $role) {
        $userCount = DB::table('model_has_roles')->where('role_id', $role->id)->count();
        $permCount = DB::table('role_has_permissions')->where('role_id', $role->id)->count();
        echo "  • ID {$role->id}: '{$role->name}' ({$permCount} perms, {$userCount} users)\n";
    }

    // 4. LIMPAR RELACIONAMENTOS ÓRFÃOS PRIMEIRO
    echo "\n4️⃣ LIMPEZA DE RELACIONAMENTOS ÓRFÃOS\n";
    echo str_repeat('-', 40) . "\n";
    
    // model_has_roles órfãos
    $orphanUserRoles = DB::table('model_has_roles')
        ->leftJoin('users', 'model_has_roles.model_id', '=', 'users.id')
        ->leftJoin('roles', 'model_has_roles.role_id', '=', 'roles.id')
        ->where(function($query) {
            $query->whereNull('users.id')->orWhereNull('roles.id');
        })
        ->count();
    
    if ($orphanUserRoles > 0) {
        DB::table('model_has_roles')
            ->leftJoin('users', 'model_has_roles.model_id', '=', 'users.id')
            ->leftJoin('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->where(function($query) {
                $query->whereNull('users.id')->orWhereNull('roles.id');
            })
            ->delete();
        echo "🗑️  Removidos {$orphanUserRoles} relacionamentos user-role órfãos\n";
    }
    
    // role_has_permissions órfãos
    $orphanRolePermissions = DB::table('role_has_permissions')
        ->leftJoin('roles', 'role_has_permissions.role_id', '=', 'roles.id')
        ->leftJoin('permissions', 'role_has_permissions.permission_id', '=', 'permissions.id')
        ->where(function($query) {
            $query->whereNull('roles.id')->orWhereNull('permissions.id');
        })
        ->count();
    
    if ($orphanRolePermissions > 0) {
        DB::table('role_has_permissions')
            ->leftJoin('roles', 'role_has_permissions.role_id', '=', 'roles.id')
            ->leftJoin('permissions', 'role_has_permissions.permission_id', '=', 'permissions.id')
            ->where(function($query) {
                $query->whereNull('roles.id')->orWhereNull('permissions.id');
            })
            ->delete();
        echo "🗑️  Removidos {$orphanRolePermissions} relacionamentos role-permission órfãos\n";
    }

    // 5. ELIMINAR ROLES (EXCETO SUPER-ADMIN)
    echo "\n5️⃣ ELIMINAÇÃO DE ROLES\n";
    echo str_repeat('-', 40) . "\n";
    
    $deletedRoles = 0;
    $deletedRelations = 0;
    
    foreach ($rolesToDelete as $role) {
        // Remover relacionamentos role-permission
        $permRelations = DB::table('role_has_permissions')->where('role_id', $role->id)->count();
        DB::table('role_has_permissions')->where('role_id', $role->id)->delete();
        $deletedRelations += $permRelations;
        
        // Remover relacionamentos user-role (já deve estar limpo)
        DB::table('model_has_roles')->where('role_id', $role->id)->delete();
        
        // Eliminar a role
        $role->delete();
        $deletedRoles++;
        
        echo "❌ Role '{$role->name}' eliminada ({$permRelations} relações de permissões)\n";
    }
    
    echo "📊 Total de roles eliminadas: {$deletedRoles}\n";
    echo "📊 Total de relações eliminadas: {$deletedRelations}\n";

    // 6. VERIFICAÇÃO FINAL
    echo "\n6️⃣ VERIFICAÇÃO FINAL\n";
    echo str_repeat('-', 40) . "\n";
    
    $finalRoleCount = Role::count();
    $finalUserRoleCount = DB::table('model_has_roles')->count();
    $finalRolePermissionCount = DB::table('role_has_permissions')->count();
    $usersWithSuperAdmin = DB::table('model_has_roles')
        ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
        ->where('roles.name', 'super-admin')
        ->count();
    
    echo "✅ Roles restantes: {$finalRoleCount} (deve ser 1)\n";
    echo "✅ Relacionamentos user-role: {$finalUserRoleCount}\n";
    echo "✅ Relacionamentos role-permission: {$finalRolePermissionCount}\n";
    echo "✅ Utilizadores com super-admin: {$usersWithSuperAdmin}\n";
    
    // Verificar se super-admin tem todas as permissões
    $superAdminPermissions = DB::table('role_has_permissions')
        ->where('role_id', $superAdminRole->id)
        ->count();
    $totalPermissions = Permission::count();
    
    echo "✅ Permissões do super-admin: {$superAdminPermissions}/{$totalPermissions}\n";
    
    if ($superAdminPermissions < $totalPermissions) {
        echo "⚠️  Atribuindo permissões em falta ao super-admin...\n";
        $superAdminRole->syncPermissions(Permission::all());
        echo "✅ Super-admin agora tem todas as " . Permission::count() . " permissões\n";
    }

    // 7. RESUMO FINAL
    echo "\n7️⃣ RESUMO DA LIMPEZA\n";
    echo str_repeat('-', 40) . "\n";
    echo "🎉 LIMPEZA CONCLUÍDA COM SUCESSO!\n";
    echo "• Roles eliminadas: {$deletedRoles}\n";
    echo "• Utilizadores reatribuídos: {$reassignedCount}\n";
    echo "• Relacionamentos órfãos limpos: " . ($orphanUserRoles + $orphanRolePermissions) . "\n";
    echo "• Relacionamentos de permissões eliminados: {$deletedRelations}\n";
    echo "• Role super-admin preservada com todas as permissões\n";
    
    if ($finalRoleCount === 1 && $usersWithSuperAdmin === $allUsers->count()) {
        echo "\n🏆 ESTADO FINAL: PERFEITO!\n";
        echo "✓ Apenas 1 role (super-admin) existe\n";
        echo "✓ Todos os {$allUsers->count()} utilizadores têm super-admin\n";
        echo "✓ Todas as permissões preservadas\n";
        echo "✓ Base de dados limpa e consistente\n";
    } else {
        echo "\n⚠️  ATENÇÃO: Verificar estado final!\n";
    }

    DB::commit();
    
    echo "\n💾 TRANSAÇÃO CONFIRMADA - Mudanças guardadas!\n";
    echo "🔄 Reinicie o servidor web para limpar cache de permissões.\n";

} catch (\Exception $e) {
    DB::rollBack();
    echo "\n💥 ERRO DURANTE LIMPEZA: " . $e->getMessage() . "\n";
    echo "🔄 Transação revertida - nenhuma mudança foi feita.\n";
    echo "\nStack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}

echo "\n" . str_repeat('=', 70) . "\n";
echo "🎯 PRÓXIMOS PASSOS:\n";
echo "1. Desabilitar seeders automáticos\n";
echo "2. Desabilitar comandos automáticos\n";
echo "3. Limpar cache: php artisan cache:clear\n";
echo "4. Limpar config: php artisan config:clear\n";
echo "5. Testar sistema de roles na interface\n";
echo str_repeat('=', 70) . "\n";
