<?php

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== LIMPEZA DEFINITIVA DE ROLES - VERSÃO SIMPLIFICADA ===\n\n";

try {
    echo "🎯 OBJETIVO: Deixar apenas super-admin no sistema\n";
    echo str_repeat("-", 70) . "\n";
    
    // Verificar super-admin
    $superAdminId = DB::table('roles')->where('name', 'super-admin')->value('id');
    if (!$superAdminId) {
        $superAdminId = DB::table('roles')->insertGetId([
            'name' => 'super-admin',
            'guard_name' => 'web',
            'created_at' => now(),
            'updated_at' => now()
        ]);
        echo "✅ Super-admin criado com ID: {$superAdminId}\n";
    } else {
        echo "✅ Super-admin encontrado com ID: {$superAdminId}\n";
    }
    
    // LOOP DE LIMPEZA AGRESSIVA
    echo "\n💥 INICIANDO LIMPEZA AGRESSIVA...\n";
    echo str_repeat("-", 70) . "\n";
    
    $maxAttempts = 20;
    $attempt = 0;
    
    do {
        $attempt++;
        echo "\n🔄 Tentativa {$attempt}/{$maxAttempts}\n";
        
        // 1. Contar roles antes
        $rolesBefore = DB::table('roles')->count();
        $unwantedRoles = DB::table('roles')->where('id', '!=', $superAdminId)->get();
        
        echo "Roles indesejadas encontradas: " . count($unwantedRoles) . "\n";
        
        if (count($unwantedRoles) === 0) {
            echo "✅ Nenhuma role indesejada encontrada!\n";
            break;
        }
        
        // 2. Listar roles a eliminar
        foreach ($unwantedRoles as $role) {
            echo "  🎭 {$role->name} (ID: {$role->id})\n";
        }
        
        // 3. ELIMINAÇÃO DIRECTA VIA SQL (Bypass Laravel/Eloquent)
        echo "Eliminando via SQL direto...\n";
        
        // Remover relações model_has_roles
        $removedModelRoles = DB::statement("DELETE FROM model_has_roles WHERE role_id != ?", [$superAdminId]);
        echo "  ✓ Relações model_has_roles limpas\n";
        
        // Remover relações role_has_permissions
        $removedRolePerms = DB::statement("DELETE FROM role_has_permissions WHERE role_id != ?", [$superAdminId]);
        echo "  ✓ Relações role_has_permissions limpas\n";
        
        // Remover roles
        $removedRoles = DB::statement("DELETE FROM roles WHERE id != ?", [$superAdminId]);
        echo "  ✓ Roles eliminadas\n";
        
        // 4. Configurar super-admin (rápido)
        DB::statement("DELETE FROM role_has_permissions WHERE role_id = ?", [$superAdminId]);
        
        $allPerms = Permission::pluck('id');
        $inserts = [];
        foreach ($allPerms as $permId) {
            $inserts[] = "({$permId}, {$superAdminId})";
        }
        
        if (!empty($inserts)) {
            $sql = "INSERT INTO role_has_permissions (permission_id, role_id) VALUES " . implode(',', $inserts);
            DB::statement($sql);
        }
        echo "  ✓ Super-admin reconfigurado com " . count($allPerms) . " permissões\n";
        
        // 5. Verificar resultado imediato
        $rolesAfter = DB::table('roles')->count();
        echo "Roles após limpeza: {$rolesAfter}\n";
        
        // 6. Aguardar e verificar recriação
        echo "Aguardando 1 segundo...\n";
        sleep(1);
        
        $rolesAfterWait = DB::table('roles')->count();
        echo "Roles após espera: {$rolesAfterWait}\n";
        
        if ($rolesAfterWait === 1) {
            echo "🎉 SUCESSO! Apenas 1 role (super-admin) permanece!\n";
            break;
        } else {
            echo "⚠️  {$rolesAfterWait} roles detectadas. Continuando limpeza...\n";
        }
        
        // Delay entre tentativas
        usleep(500000); // 0.5 segundo
        
    } while ($attempt < $maxAttempts);
    
    // VERIFICAÇÃO FINAL
    echo "\n📊 VERIFICAÇÃO FINAL:\n";
    echo str_repeat("=", 70) . "\n";
    
    $finalRoles = DB::table('roles')->get();
    echo "🎭 ROLES FINAIS: " . count($finalRoles) . "\n";
    
    foreach ($finalRoles as $role) {
        $userCount = DB::table('model_has_roles')->where('role_id', $role->id)->count();
        $permCount = DB::table('role_has_permissions')->where('role_id', $role->id)->count();
        echo "  ✓ {$role->name} (ID: {$role->id}) - {$userCount} usuários, {$permCount} permissões\n";
    }
    
    // Verificar usuários sem roles
    $usersWithoutRoles = DB::table('users')
        ->leftJoin('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
        ->whereNull('model_has_roles.role_id')
        ->count();
    echo "\n👥 Usuários sem role: {$usersWithoutRoles}\n";
    
    // LIMPAR CACHE
    echo "\n🧹 LIMPANDO CACHE...\n";
    app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    
    try {
        \Artisan::call('cache:clear');
        echo "✅ Cache limpo\n";
    } catch (Exception $e) {
        echo "⚠️  Cache: " . $e->getMessage() . "\n";
    }
    
    // RESULTADO FINAL
    if (count($finalRoles) === 1 && $finalRoles[0]->name === 'super-admin') {
        echo "\n🎉 MISSÃO CUMPRIDA!\n";
        echo "✅ Sistema limpo com apenas super-admin\n";
        echo "✅ {$attempt} tentativas necessárias\n";
        echo "\n🔄 PRÓXIMOS PASSOS:\n";
        echo "1. Fazer logout/login obrigatório\n";
        echo "2. Acessar /admin/permissions-manager\n";
        echo "3. Criar roles modulares conforme necessário\n";
    } else {
        echo "\n❌ FALHA: Ainda existem " . (count($finalRoles) - 1) . " roles indesejadas\n";
        echo "💡 Pode haver um processo em background muito persistente\n";
        echo "💡 Considere reiniciar o servidor web/base de dados\n";
    }
    
} catch (Exception $e) {
    echo "\n❌ ERRO: " . $e->getMessage() . "\n";
    echo "Linha: " . $e->getLine() . "\n";
    exit(1);
}
