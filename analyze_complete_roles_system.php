<?php

require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;
use App\Models\User;

echo "\n=== ANÁLISE COMPLETA DO SISTEMA DE ROLES E PERMISSIONS ===\n";
echo "Timestamp: " . date('Y-m-d H:i:s') . "\n\n";

try {
    // 1. Analisar estrutura das tabelas
    echo "1️⃣ ESTRUTURA DAS TABELAS:\n";
    echo str_repeat('-', 50) . "\n";
    
    $tables = [
        'roles' => 'Roles principais',
        'permissions' => 'Permissões do sistema',
        'model_has_permissions' => 'Relacionamento User->Permissions',
        'model_has_roles' => 'Relacionamento User->Roles',
        'role_has_permissions' => 'Relacionamento Role->Permissions'
    ];
    
    foreach ($tables as $table => $description) {
        $count = DB::table($table)->count();
        echo "• {$description} ({$table}): {$count} registros\n";
        
        if ($table === 'roles') {
            $roles = DB::table($table)->select('id', 'name', 'guard_name', 'created_at')->get();
            foreach ($roles as $role) {
                $permCount = DB::table('role_has_permissions')->where('role_id', $role->id)->count();
                $userCount = DB::table('model_has_roles')->where('role_id', $role->id)->count();
                echo "    - ID {$role->id}: '{$role->name}' ({$permCount} perms, {$userCount} users)\n";
            }
        }
    }
    
    // 2. Analisar utilizadores com roles
    echo "\n2️⃣ UTILIZADORES COM ROLES:\n";
    echo str_repeat('-', 50) . "\n";
    
    $usersWithRoles = DB::table('users')
        ->join('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
        ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
        ->select('users.id', 'users.name', 'users.email', 'roles.name as role_name')
        ->get()
        ->groupBy('id');
    
    foreach ($usersWithRoles as $userId => $userRoles) {
        $user = $userRoles->first();
        $roleNames = $userRoles->pluck('role_name')->toArray();
        echo "• User {$user->id}: {$user->name} ({$user->email}) - Roles: " . implode(', ', $roleNames) . "\n";
    }
    
    $usersWithoutRoles = DB::table('users')
        ->leftJoin('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
        ->whereNull('model_has_roles.model_id')
        ->count();
    
    echo "\n• Utilizadores sem roles: {$usersWithoutRoles}\n";
    
    // 3. Analisar permissões órfãs
    echo "\n3️⃣ ANÁLISE DE PERMISSÕES:\n";
    echo str_repeat('-', 50) . "\n";
    
    $totalPermissions = Permission::count();
    $permissionsWithRoles = DB::table('permissions')
        ->join('role_has_permissions', 'permissions.id', '=', 'role_has_permissions.permission_id')
        ->distinct()
        ->count('permissions.id');
    
    $orphanPermissions = $totalPermissions - $permissionsWithRoles;
    
    echo "• Total de permissões: {$totalPermissions}\n";
    echo "• Permissões ligadas a roles: {$permissionsWithRoles}\n";
    echo "• Permissões órfãs: {$orphanPermissions}\n";
    
    // Agrupar permissões por módulo
    $permissionGroups = DB::table('permissions')
        ->selectRaw("
            CASE 
                WHEN name LIKE 'maintenance.%' THEN 'maintenance'
                WHEN name LIKE 'equipment.%' THEN 'maintenance'
                WHEN name LIKE 'preventive.%' THEN 'maintenance'
                WHEN name LIKE 'corrective.%' THEN 'maintenance'
                WHEN name LIKE 'mrp.%' THEN 'mrp'
                WHEN name LIKE 'production.%' THEN 'mrp'
                WHEN name LIKE 'supplychain.%' THEN 'supplychain'
                WHEN name LIKE 'inventory.%' THEN 'supplychain'
                WHEN name LIKE 'hr.%' THEN 'hr'
                WHEN name LIKE 'payroll.%' THEN 'hr'
                WHEN name LIKE 'attendance.%' THEN 'hr'
                WHEN name LIKE 'system.%' THEN 'system'
                WHEN name LIKE 'roles.%' THEN 'system'
                WHEN name LIKE 'users.%' THEN 'system'
                ELSE 'other'
            END as module,
            COUNT(*) as count
        ")
        ->groupBy('module')
        ->orderBy('count', 'desc')
        ->get();
    
    echo "\n📊 Permissões por módulo:\n";
    foreach ($permissionGroups as $group) {
        echo "  • {$group->module}: {$group->count} permissões\n";
    }
    
    // 4. Identificar conflitos potenciais
    echo "\n4️⃣ IDENTIFICAÇÃO DE CONFLITOS POTENCIAIS:\n";
    echo str_repeat('-', 50) . "\n";
    
    // Roles duplicadas
    $duplicateRoles = DB::table('roles')
        ->select('name', DB::raw('COUNT(*) as count'))
        ->groupBy('name')
        ->having('count', '>', 1)
        ->get();
    
    if ($duplicateRoles->count() > 0) {
        echo "⚠ ROLES DUPLICADAS ENCONTRADAS:\n";
        foreach ($duplicateRoles as $duplicate) {
            echo "  • '{$duplicate->name}': {$duplicate->count} ocorrências\n";
        }
    } else {
        echo "✓ Nenhuma role duplicada encontrada\n";
    }
    
    // Permissões duplicadas
    $duplicatePermissions = DB::table('permissions')
        ->select('name', DB::raw('COUNT(*) as count'))
        ->groupBy('name')
        ->having('count', '>', 1)
        ->get();
    
    if ($duplicatePermissions->count() > 0) {
        echo "⚠ PERMISSÕES DUPLICADAS ENCONTRADAS:\n";
        foreach ($duplicatePermissions as $duplicate) {
            echo "  • '{$duplicate->name}': {$duplicate->count} ocorrências\n";
        }
    } else {
        echo "✓ Nenhuma permissão duplicada encontrada\n";
    }
    
    // 5. Verificar integridade referencial
    echo "\n5️⃣ VERIFICAÇÃO DE INTEGRIDADE REFERENCIAL:\n";
    echo str_repeat('-', 50) . "\n";
    
    // model_has_roles órfãos
    $orphanUserRoles = DB::table('model_has_roles')
        ->leftJoin('users', 'model_has_roles.model_id', '=', 'users.id')
        ->leftJoin('roles', 'model_has_roles.role_id', '=', 'roles.id')
        ->where(function($query) {
            $query->whereNull('users.id')->orWhereNull('roles.id');
        })
        ->count();
    
    // role_has_permissions órfãos
    $orphanRolePermissions = DB::table('role_has_permissions')
        ->leftJoin('roles', 'role_has_permissions.role_id', '=', 'roles.id')
        ->leftJoin('permissions', 'role_has_permissions.permission_id', '=', 'permissions.id')
        ->where(function($query) {
            $query->whereNull('roles.id')->orWhereNull('permissions.id');
        })
        ->count();
    
    echo "• Relacionamentos user-role órfãos: {$orphanUserRoles}\n";
    echo "• Relacionamentos role-permission órfãos: {$orphanRolePermissions}\n";
    
    if ($orphanUserRoles > 0 || $orphanRolePermissions > 0) {
        echo "⚠ Foram encontrados relacionamentos órfãos que precisam ser limpos!\n";
    } else {
        echo "✓ Integridade referencial OK\n";
    }
    
    // 6. Resumo para limpeza
    echo "\n6️⃣ RESUMO PARA LIMPEZA:\n";
    echo str_repeat('-', 50) . "\n";
    
    $superAdminRole = Role::where('name', 'super-admin')->first();
    if ($superAdminRole) {
        $superAdminUsers = DB::table('model_has_roles')
            ->where('role_id', $superAdminRole->id)
            ->count();
        echo "✓ Role 'super-admin' encontrada (ID: {$superAdminRole->id}) com {$superAdminUsers} utilizadores\n";
    } else {
        echo "❌ CRITICAL: Role 'super-admin' NÃO encontrada! Precisa ser criada antes da limpeza.\n";
    }
    
    $rolesToDelete = Role::where('name', '!=', 'super-admin')->count();
    echo "• Roles a eliminar: {$rolesToDelete}\n";
    echo "• Utilizadores a reassinalar para super-admin: " . (DB::table('model_has_roles')->count() - $superAdminUsers) . "\n";
    
    echo "\n✅ ANÁLISE COMPLETA!\n";
    echo "Próximos passos:\n";
    echo "1. Verificar seeders e scripts automáticos\n";
    echo "2. Criar/executar script de limpeza\n";
    echo "3. Reatribuir todos os utilizadores à role super-admin\n";
    echo "4. Limpar relacionamentos órfãos\n";
    
} catch (\Exception $e) {
    echo "\n❌ ERRO durante análise: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
