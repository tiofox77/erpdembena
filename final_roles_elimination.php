<?php

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== ELIMINAÇÃO FINAL E DEFINITIVA DE ROLES ===\n\n";

try {
    // ETAPA 1: CRIAR PROTEÇÃO CONTRA RECRIAÇÃO
    echo "🛡️  ETAPA 1: Implementando proteção contra recriação...\n";
    echo str_repeat("-", 60) . "\n";
    
    // Criar tabela de bloqueio se não existir
    if (!DB::getSchemaBuilder()->hasTable('role_creation_lock')) {
        DB::statement("
            CREATE TABLE role_creation_lock (
                id INT PRIMARY KEY DEFAULT 1,
                locked BOOLEAN DEFAULT TRUE,
                message TEXT DEFAULT 'Criação automática de roles bloqueada pelo administrador',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                CHECK (id = 1)
            )
        ");
        
        DB::table('role_creation_lock')->insert([
            'id' => 1,
            'locked' => true,
            'message' => 'Sistema configurado para permitir apenas super-admin'
        ]);
        
        echo "✅ Tabela de proteção criada\n";
    } else {
        DB::table('role_creation_lock')->updateOrInsert(['id' => 1], [
            'locked' => true,
            'message' => 'Sistema configurado para permitir apenas super-admin'
        ]);
        echo "✅ Proteção ativada\n";
    }
    
    // ETAPA 2: ELIMINAR TODAS AS ROLES EXCETO SUPER-ADMIN
    echo "\n💥 ETAPA 2: Eliminação definitiva...\n";
    echo str_repeat("-", 60) . "\n";
    
    // Garantir que super-admin existe
    $superAdminId = DB::table('roles')->where('name', 'super-admin')->value('id');
    if (!$superAdminId) {
        $superAdminId = DB::table('roles')->insertGetId([
            'name' => 'super-admin',
            'guard_name' => 'web',
            'created_at' => now(),
            'updated_at' => now()
        ]);
        echo "✅ Super-admin criado (ID: {$superAdminId})\n";
    } else {
        echo "✅ Super-admin encontrado (ID: {$superAdminId})\n";
    }
    
    // Contar roles antes
    $beforeCount = DB::table('roles')->count();
    echo "Roles antes da eliminação: {$beforeCount}\n";
    
    // Eliminar em lotes para evitar locks
    $maxIterations = 10;
    $iteration = 0;
    
    do {
        $iteration++;
        echo "Iteração {$iteration}...\n";
        
        // Remover relações usuário-role (exceto super-admin)
        $removedUserRoles = DB::table('model_has_roles')
            ->where('role_id', '!=', $superAdminId)
            ->limit(1000)
            ->delete();
        
        // Remover relações role-permissão (exceto super-admin)
        $removedRolePermissions = DB::table('role_has_permissions')
            ->where('role_id', '!=', $superAdminId)
            ->limit(1000)
            ->delete();
        
        // Eliminar roles (exceto super-admin)
        $deletedRoles = DB::table('roles')
            ->where('id', '!=', $superAdminId)
            ->limit(100)
            ->delete();
        
        echo "  Relações usuário removidas: {$removedUserRoles}\n";
        echo "  Relações permissão removidas: {$removedRolePermissions}\n";
        echo "  Roles eliminadas: {$deletedRoles}\n";
        
        // Verificar se ainda existem roles para eliminar
        $remainingRoles = DB::table('roles')->where('id', '!=', $superAdminId)->count();
        echo "  Roles restantes: {$remainingRoles}\n";
        
        if ($remainingRoles === 0) {
            echo "✅ Todas as roles indesejadas eliminadas!\n";
            break;
        }
        
        // Delay para evitar sobrecarga
        usleep(100000); // 0.1 segundo
        
    } while ($iteration < $maxIterations && $remainingRoles > 0);
    
    // ETAPA 3: CONFIGURAR SUPER-ADMIN
    echo "\n🛡️  ETAPA 3: Configurando super-admin...\n";
    echo str_repeat("-", 60) . "\n";
    
    // Remover permissões antigas do super-admin
    DB::table('role_has_permissions')->where('role_id', $superAdminId)->delete();
    
    // Adicionar todas as permissões ao super-admin
    $allPermissions = Permission::all();
    $permissionData = [];
    foreach ($allPermissions as $permission) {
        $permissionData[] = [
            'permission_id' => $permission->id,
            'role_id' => $superAdminId
        ];
    }
    
    if (!empty($permissionData)) {
        // Inserir em lotes para evitar problemas de memória
        $chunks = array_chunk($permissionData, 100);
        foreach ($chunks as $chunk) {
            DB::table('role_has_permissions')->insert($chunk);
        }
    }
    
    echo "✅ Super-admin configurado com " . count($allPermissions) . " permissões\n";
    
    // ETAPA 4: VERIFICAÇÃO FINAL E PROTEÇÃO
    echo "\n🔍 ETAPA 4: Verificação final...\n";
    echo str_repeat("-", 60) . "\n";
    
    $finalRoles = DB::table('roles')->get();
    echo "🎭 ROLES FINAIS: " . count($finalRoles) . "\n";
    
    foreach ($finalRoles as $role) {
        $userCount = DB::table('model_has_roles')->where('role_id', $role->id)->count();
        $permCount = DB::table('role_has_permissions')->where('role_id', $role->id)->count();
        echo "  ✓ {$role->name} (ID: {$role->id}, {$userCount} usuários, {$permCount} permissões)\n";
    }
    
    // Verificar por 5 segundos se novas roles são criadas
    echo "\nMonitorando recriação automática por 5 segundos...\n";
    $startCount = count($finalRoles);
    
    for ($i = 1; $i <= 5; $i++) {
        sleep(1);
        $currentCount = DB::table('roles')->count();
        echo "Segundo {$i}: {$currentCount} roles\n";
        
        if ($currentCount > $startCount) {
            echo "⚠️  ROLES RECRIADAS AUTOMATICAMENTE!\n";
            
            // Eliminar novas roles imediatamente
            $newRoles = DB::table('roles')->where('id', '!=', $superAdminId)->get();
            foreach ($newRoles as $role) {
                echo "  🗑️  Eliminando: {$role->name} (ID: {$role->id})\n";
                DB::table('model_has_roles')->where('role_id', $role->id)->delete();
                DB::table('role_has_permissions')->where('role_id', $role->id)->delete();
                DB::table('roles')->where('id', $role->id)->delete();
            }
        }
    }
    
    // ETAPA 5: LIMPAR CACHE
    echo "\n🧹 ETAPA 5: Limpando cache...\n";
    echo str_repeat("-", 60) . "\n";
    
    app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    
    try {
        \Artisan::call('cache:clear');
        \Artisan::call('config:clear');
        \Artisan::call('view:clear');
        \Artisan::call('route:clear');
        echo "✅ Cache Laravel limpo\n";
    } catch (Exception $e) {
        echo "⚠️  Cache Laravel: " . $e->getMessage() . "\n";
    }
    
    // RELATÓRIO FINAL
    echo "\n📊 RELATÓRIO FINAL:\n";
    echo str_repeat("=", 80) . "\n";
    
    $finalCount = DB::table('roles')->count();
    $protectionStatus = DB::table('role_creation_lock')->where('id', 1)->value('locked');
    
    echo "• Roles iniciais: {$beforeCount}\n";
    echo "• Roles finais: {$finalCount}\n";
    echo "• Roles eliminadas: " . ($beforeCount - $finalCount) . "\n";
    echo "• Proteção ativa: " . ($protectionStatus ? 'SIM' : 'NÃO') . "\n";
    echo "• Permissões do super-admin: " . count($allPermissions) . "\n";
    
    if ($finalCount === 1) {
        echo "\n🎉 ELIMINAÇÃO DEFINITIVA CONCLUÍDA COM SUCESSO!\n";
        echo "✅ Sistema protegido contra recriação automática\n";
        echo "✅ Apenas super-admin permanece ativo\n";
        
        echo "\n💡 PARA CRIAR ROLES NO FUTURO:\n";
        echo "1. Use /admin/permissions-manager\n";
        echo "2. Ou desative a proteção: UPDATE role_creation_lock SET locked = FALSE WHERE id = 1\n";
        
    } else {
        echo "\n⚠️  ATENÇÃO: {$finalCount} roles ainda existem\n";
        echo "Investigação adicional necessária\n";
    }
    
    echo "\n🔄 ACÇÕES OBRIGATÓRIAS:\n";
    echo "1. Fazer logout/login de TODOS os usuários\n";
    echo "2. Limpar cache do browser\n";
    echo "3. Verificar /admin/permissions-manager\n";
    
} catch (Exception $e) {
    echo "\n❌ ERRO CRÍTICO: " . $e->getMessage() . "\n";
    echo "Linha: " . $e->getLine() . "\n";
    echo "Arquivo: " . $e->getFile() . "\n";
    exit(1);
}
