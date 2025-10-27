<?php

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== ELIMINAÇÃO DEFINITIVA DE TODAS AS ROLES EXCETO SUPER-ADMIN ===\n\n";

try {
    // ETAPA 1: Verificar status atual
    echo "📊 ETAPA 1: Status atual...\n";
    echo str_repeat("-", 60) . "\n";
    
    $allRoles = Role::all();
    echo "Total de roles: " . $allRoles->count() . "\n";
    
    // ETAPA 2: Identificar super-admin
    $superAdminRole = Role::where('name', 'super-admin')->first();
    if (!$superAdminRole) {
        echo "⚠️  Super Admin não encontrado, criando...\n";
        $superAdminRole = Role::create(['name' => 'super-admin', 'guard_name' => 'web']);
    }
    
    echo "✅ Super Admin ID: {$superAdminRole->id}\n";
    
    // ETAPA 3: Obter IDs das roles a eliminar
    $rolesToDelete = Role::where('id', '!=', $superAdminRole->id)->pluck('id')->toArray();
    echo "🎯 Roles a eliminar: " . count($rolesToDelete) . "\n";
    
    if (empty($rolesToDelete)) {
        echo "✅ Nenhuma role para eliminar. Sistema já limpo.\n";
        exit(0);
    }
    
    // ETAPA 4: Remover relações via SQL DIRETO (bypass Laravel)
    echo "\n🗑️  ETAPA 4: Removendo relações via SQL direto...\n";
    echo str_repeat("-", 60) . "\n";
    
    // Remover model_has_roles
    $removedUserRoles = DB::table('model_has_roles')
        ->whereIn('role_id', $rolesToDelete)
        ->delete();
    echo "✅ Removidas {$removedUserRoles} relações usuário-role\n";
    
    // Remover role_has_permissions
    $removedRolePermissions = DB::table('role_has_permissions')
        ->whereIn('role_id', $rolesToDelete)
        ->delete();
    echo "✅ Removidas {$removedRolePermissions} relações role-permissão\n";
    
    // ETAPA 5: Eliminar roles via SQL direto
    echo "\n💥 ETAPA 5: Eliminando roles via SQL direto...\n";
    echo str_repeat("-", 60) . "\n";
    
    $deletedRoles = DB::table('roles')
        ->whereIn('id', $rolesToDelete)
        ->delete();
    echo "✅ {$deletedRoles} roles eliminadas via SQL direto\n";
    
    // ETAPA 6: Configurar super-admin com todas as permissões
    echo "\n🛡️  ETAPA 6: Configurando Super Admin...\n";
    echo str_repeat("-", 60) . "\n";
    
    $allPermissions = Permission::all();
    
    // Remover todas as permissões do super-admin primeiro
    DB::table('role_has_permissions')
        ->where('role_id', $superAdminRole->id)
        ->delete();
    
    // Adicionar todas as permissões de volta
    $permissionData = [];
    foreach ($allPermissions as $permission) {
        $permissionData[] = [
            'permission_id' => $permission->id,
            'role_id' => $superAdminRole->id
        ];
    }
    
    if (!empty($permissionData)) {
        DB::table('role_has_permissions')->insert($permissionData);
    }
    
    echo "✅ Super Admin configurado com " . $allPermissions->count() . " permissões\n";
    
    // ETAPA 7: Limpar completamente o cache
    echo "\n🧹 ETAPA 7: Limpando cache completamente...\n";
    echo str_repeat("-", 60) . "\n";
    
    // Cache do Spatie
    app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    
    // Cache direto via Redis/File se existir
    try {
        if (function_exists('cache')) {
            cache()->flush();
        }
        echo "✅ Cache geral limpo\n";
    } catch (Exception $e) {
        echo "⚠️  Cache geral: " . $e->getMessage() . "\n";
    }
    
    // Tentar limpar cache do Laravel
    try {
        if (function_exists('artisan')) {
            \Artisan::call('cache:clear');
            \Artisan::call('config:clear');
            \Artisan::call('view:clear');
            echo "✅ Cache Laravel limpo\n";
        }
    } catch (Exception $e) {
        echo "⚠️  Cache Laravel: " . $e->getMessage() . "\n";
    }
    
    // ETAPA 8: Verificação final DEFINITIVA
    echo "\n📊 ETAPA 8: Verificação final...\n";
    echo str_repeat("-", 60) . "\n";
    
    // Contar via SQL direto
    $finalRoleCount = DB::table('roles')->count();
    $finalRoles = DB::table('roles')->get();
    
    echo "🎭 ROLES RESTANTES VIA SQL: {$finalRoleCount}\n";
    
    foreach ($finalRoles as $role) {
        $userCount = DB::table('model_has_roles')->where('role_id', $role->id)->count();
        $permCount = DB::table('role_has_permissions')->where('role_id', $role->id)->count();
        echo "  ✓ {$role->name} (ID: {$role->id}, {$userCount} usuários, {$permCount} permissões)\n";
    }
    
    // Verificar usuários sem roles
    $usersWithoutRoles = DB::table('users')
        ->leftJoin('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
        ->whereNull('model_has_roles.role_id')
        ->count();
    
    echo "\n⚠️  Usuários sem role: {$usersWithoutRoles}\n";
    
    // RELATÓRIO FINAL
    echo "\n📊 RELATÓRIO FINAL:\n";
    echo str_repeat("=", 80) . "\n";
    echo "• Roles iniciais: " . $allRoles->count() . "\n";
    echo "• Roles finais: {$finalRoleCount}\n";
    echo "• Roles eliminadas: " . ($allRoles->count() - $finalRoleCount) . "\n";
    echo "• Relações usuário-role removidas: {$removedUserRoles}\n";
    echo "• Relações role-permissão removidas: {$removedRolePermissions}\n";
    echo "• Permissões do super-admin: " . $allPermissions->count() . "\n";
    
    if ($finalRoleCount === 1) {
        echo "\n🎉 ELIMINAÇÃO DEFINITIVA CONCLUÍDA COM SUCESSO!\n";
        echo "✅ Apenas super-admin permanece no sistema\n";
        echo "✅ Sistema completamente limpo\n";
    } else {
        echo "\n❌ FALHA: Ainda existem " . ($finalRoleCount - 1) . " roles além do super-admin\n";
        echo "💡 Verificar manualmente na base de dados\n";
    }
    
    echo "\n🔄 ACÇÕES NECESSÁRIAS:\n";
    echo "1. Fazer logout/login OBRIGATÓRIO\n";
    echo "2. Limpar cache do browser\n";
    echo "3. Verificar interface de gestão\n";
    
} catch (Exception $e) {
    echo "\n❌ ERRO CRÍTICO: " . $e->getMessage() . "\n";
    echo "Linha: " . $e->getLine() . "\n";
    echo "Arquivo: " . $e->getFile() . "\n";
    echo "\n🔧 STACK TRACE:\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}
