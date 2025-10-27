<?php

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== PARAR CRIAÇÃO AUTOMÁTICA DE ROLES ===\n\n";

try {
    echo "🔍 INVESTIGANDO PROCESSO QUE CRIA ROLES...\n";
    echo str_repeat("-", 60) . "\n";
    
    // Verificar se algum job ou processo está rodando
    $beforeCount = DB::table('roles')->count();
    echo "Roles antes: {$beforeCount}\n";
    
    // Aguardar 2 segundos
    echo "Aguardando 2 segundos...\n";
    sleep(2);
    
    $afterCount = DB::table('roles')->count();
    echo "Roles depois: {$afterCount}\n";
    
    if ($afterCount > $beforeCount) {
        echo "⚠️  ROLES ESTÃO SENDO CRIADAS AUTOMATICAMENTE!\n";
        echo "Diferença: " . ($afterCount - $beforeCount) . " novas roles\n";
        
        // Buscar processo específico
        $newRoles = DB::table('roles')
            ->where('id', '>', $beforeCount)
            ->get();
            
        echo "Novas roles criadas:\n";
        foreach ($newRoles as $role) {
            echo "- {$role->name} (ID: {$role->id})\n";
        }
    } else {
        echo "✅ Nenhuma role nova criada automaticamente\n";
    }
    
    // ETAPA 1: DESATIVAR TODOS OS SEEDERS TEMPORARIAMENTE
    echo "\n🚫 DESATIVANDO SEEDERS TEMPORARIAMENTE...\n";
    echo str_repeat("-", 60) . "\n";
    
    $seedersPath = 'database/seeders/';
    $seeders = [
        'RolePermissionSeeder.php',
        'MRPPermissionsSeeder.php',
        'DatabaseSeeder.php'
    ];
    
    foreach ($seeders as $seeder) {
        $seederFile = $seedersPath . $seeder;
        if (file_exists($seederFile)) {
            $backupFile = $seederFile . '.backup';
            if (!file_exists($backupFile)) {
                copy($seederFile, $backupFile);
                echo "✅ Backup criado: {$backupFile}\n";
            }
            
            // Criar versão vazia do seeder
            $emptySeeder = "<?php\n\nnamespace Database\Seeders;\n\nuse Illuminate\Database\Seeder;\n\nclass " . 
                          str_replace('.php', '', $seeder) . " extends Seeder\n{\n" .
                          "    public function run(): void\n    {\n" .
                          "        // SEEDER TEMPORARIAMENTE DESATIVADO PARA LIMPEZA DE ROLES\n" .
                          "        // Restaure do arquivo .backup quando necessário\n" .
                          "    }\n}\n";
            
            file_put_contents($seederFile, $emptySeeder);
            echo "✅ Seeder desativado: {$seeder}\n";
        }
    }
    
    // ETAPA 2: ELIMINAR TODAS AS ROLES NOVAMENTE
    echo "\n💥 ELIMINANDO TODAS AS ROLES EXCETO SUPER-ADMIN...\n";
    echo str_repeat("-", 60) . "\n";
    
    $superAdminId = DB::table('roles')->where('name', 'super-admin')->value('id');
    if (!$superAdminId) {
        echo "⚠️  Criando super-admin...\n";
        $superAdminId = DB::table('roles')->insertGetId([
            'name' => 'super-admin',
            'guard_name' => 'web',
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }
    
    echo "✅ Super Admin ID: {$superAdminId}\n";
    
    // Remover relações
    $removedUserRoles = DB::table('model_has_roles')
        ->where('role_id', '!=', $superAdminId)
        ->delete();
    echo "✅ Removidas {$removedUserRoles} relações usuário-role\n";
    
    $removedRolePermissions = DB::table('role_has_permissions')
        ->where('role_id', '!=', $superAdminId)
        ->delete();
    echo "✅ Removidas {$removedRolePermissions} relações role-permissão\n";
    
    // Eliminar roles
    $deletedRoles = DB::table('roles')
        ->where('id', '!=', $superAdminId)
        ->delete();
    echo "✅ {$deletedRoles} roles eliminadas\n";
    
    // Configurar super-admin
    $allPermissions = Permission::all();
    DB::table('role_has_permissions')->where('role_id', $superAdminId)->delete();
    
    $permissionData = [];
    foreach ($allPermissions as $permission) {
        $permissionData[] = [
            'permission_id' => $permission->id,
            'role_id' => $superAdminId
        ];
    }
    
    if (!empty($permissionData)) {
        DB::table('role_has_permissions')->insert($permissionData);
    }
    echo "✅ Super Admin configurado com " . count($allPermissions) . " permissões\n";
    
    // ETAPA 3: VERIFICAR SE NOVAS ROLES SÃO CRIADAS
    echo "\n🔍 VERIFICANDO SE ROLES SÃO RECRIADAS...\n";
    echo str_repeat("-", 60) . "\n";
    
    $checkCount = DB::table('roles')->count();
    echo "Roles após limpeza: {$checkCount}\n";
    
    echo "Aguardando 3 segundos...\n";
    sleep(3);
    
    $finalCount = DB::table('roles')->count();
    echo "Roles após espera: {$finalCount}\n";
    
    if ($finalCount > $checkCount) {
        echo "❌ AINDA ESTÃO SENDO CRIADAS AUTOMATICAMENTE!\n";
        $againNewRoles = DB::table('roles')
            ->where('id', '>', $checkCount)
            ->get();
            
        echo "Roles recriadas:\n";
        foreach ($againNewRoles as $role) {
            echo "- {$role->name} (ID: {$role->id})\n";
        }
        
        echo "\n💡 POSSÍVEIS CAUSAS:\n";
        echo "1. Job/Queue em execução\n";
        echo "2. Middleware ou Service Provider\n";
        echo "3. Processo em background\n";
        echo "4. Cache/Session ativo\n";
        
    } else {
        echo "✅ NENHUMA ROLE RECRIADA! PROBLEMA RESOLVIDO!\n";
    }
    
    // ETAPA 4: LIMPAR CACHE COMPLETAMENTE
    echo "\n🧹 LIMPANDO CACHE COMPLETAMENTE...\n";
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
    
    echo "\n📊 RELATÓRIO FINAL:\n";
    echo str_repeat("=", 60) . "\n";
    
    $finalRoles = DB::table('roles')->get();
    echo "🎭 ROLES FINAIS: " . count($finalRoles) . "\n";
    foreach ($finalRoles as $role) {
        echo "  - {$role->name} (ID: {$role->id})\n";
    }
    
    if (count($finalRoles) === 1 && $finalRoles[0]->name === 'super-admin') {
        echo "\n🎉 SUCESSO! APENAS SUPER-ADMIN PERMANECE!\n";
        echo "\n📝 PARA RESTAURAR SEEDERS:\n";
        echo "1. Restaure os arquivos .backup quando necessário\n";
        echo "2. Use /admin/permissions-manager para criar roles modulares\n";
    } else {
        echo "\n⚠️  AINDA EXISTEM ROLES INDESEJADAS\n";
        echo "Pode ser necessário investigação mais profunda\n";
    }
    
} catch (Exception $e) {
    echo "\n❌ ERRO: " . $e->getMessage() . "\n";
    echo "Linha: " . $e->getLine() . "\n";
    echo "Arquivo: " . $e->getFile() . "\n";
    exit(1);
}
