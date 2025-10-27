<?php

require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;

echo "\n=== CRIAÇÃO DE ROLES MODULARES COM ACESSO COMPLETO ===\n";
echo "Timestamp: " . date('Y-m-d H:i:s') . "\n\n";

try {
    DB::beginTransaction();
    
    // Definir os módulos e suas permissões
    $modules = [
        'maintenance' => [
            'name' => 'maintenance-full-access',
            'display_name' => 'Manutenção - Acesso Total',
            'permissions_pattern' => 'maintenance.',
            'color' => '#FF6B6B'
        ],
        'supply-chain' => [
            'name' => 'supply-chain-full-access', 
            'display_name' => 'Supply Chain - Acesso Total',
            'permissions_pattern' => 'supplychain.',
            'color' => '#4ECDC4'
        ],
        'mrp' => [
            'name' => 'mrp-full-access',
            'display_name' => 'MRP - Acesso Total',
            'permissions_pattern' => 'mrp.',
            'color' => '#45B7D1'
        ],
        'hr' => [
            'name' => 'hr-full-access',
            'display_name' => 'RH - Acesso Total',
            'permissions_pattern' => 'hr.',
            'color' => '#96CEB4'
        ]
    ];
    
    // Limpar cache de permissões
    app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    
    foreach ($modules as $module => $config) {
        echo "\n--- Processando módulo: {$config['display_name']} ---\n";
        
        // Criar ou buscar a role
        $role = Role::firstOrCreate(
            ['name' => $config['name']], 
            ['guard_name' => 'web']
        );
        echo "✓ Role '{$config['name']}' criada/encontrada (ID: {$role->id})\n";
        
        // Buscar todas as permissões do módulo
        $permissions = Permission::where('name', 'LIKE', $config['permissions_pattern'] . '%')->get();
        
        if ($permissions->isEmpty()) {
            echo "⚠ Nenhuma permissão encontrada com padrão '{$config['permissions_pattern']}%'\n";
            
            // Tentar buscar com outros padrões conhecidos
            $alternativePatterns = [
                str_replace('_', '-', $config['permissions_pattern']),
                str_replace('.', '-', $config['permissions_pattern']),
                str_replace('.', '_', $config['permissions_pattern']),
                $module . '.',
                $module . '-',
                $module . '_'
            ];
            
            foreach ($alternativePatterns as $pattern) {
                $permissions = Permission::where('name', 'LIKE', $pattern . '%')->get();
                if (!$permissions->isEmpty()) {
                    echo "✓ Encontradas {$permissions->count()} permissões com padrão alternativo '{$pattern}%'\n";
                    break;
                }
            }
        } else {
            echo "✓ Encontradas {$permissions->count()} permissões para o módulo\n";
        }
        
        // Atribuir permissões à role
        if (!$permissions->isEmpty()) {
            $role->syncPermissions($permissions);
            echo "✓ {$permissions->count()} permissões atribuídas à role\n";
            
            // Listar algumas permissões como exemplo
            $sample = $permissions->take(5);
            echo "  Exemplos de permissões atribuídas:\n";
            foreach ($sample as $perm) {
                echo "    - {$perm->name}\n";
            }
            if ($permissions->count() > 5) {
                echo "    ... e mais " . ($permissions->count() - 5) . " permissões\n";
            }
        }
    }
    
    // Criar role de visualização para todos os módulos
    echo "\n--- Criando role de Visualização Global ---\n";
    $viewRole = Role::firstOrCreate(
        ['name' => 'viewer-all-modules'],
        ['guard_name' => 'web']
    );
    
    // Buscar todas as permissões de visualização
    $viewPermissions = Permission::where(function($query) {
        $query->where('name', 'LIKE', '%.view')
              ->orWhere('name', 'LIKE', '%.read')
              ->orWhere('name', 'LIKE', '%.index')
              ->orWhere('name', 'LIKE', '%.list')
              ->orWhere('name', 'LIKE', '%.show');
    })->get();
    
    if (!$viewPermissions->isEmpty()) {
        $viewRole->syncPermissions($viewPermissions);
        echo "✓ Role 'viewer-all-modules' criada com {$viewPermissions->count()} permissões de visualização\n";
    }
    
    DB::commit();
    
    // Resumo final
    echo "\n=== RESUMO FINAL ===\n";
    $allRoles = Role::where('name', 'LIKE', '%-full-access')
                    ->orWhere('name', 'viewer-all-modules')
                    ->get();
    
    foreach ($allRoles as $role) {
        $permCount = $role->permissions()->count();
        echo "✓ {$role->name}: {$permCount} permissões\n";
    }
    
    // Verificar se existem permissões órfãs (sem módulo)
    echo "\n--- Verificando permissões órfãs ---\n";
    $allModulePermissions = Permission::where(function($query) {
        $query->where('name', 'LIKE', 'maintenance%')
              ->orWhere('name', 'LIKE', 'supply_chain%')
              ->orWhere('name', 'LIKE', 'supply-chain%')
              ->orWhere('name', 'LIKE', 'mrp%')
              ->orWhere('name', 'LIKE', 'hr%');
    })->pluck('id');
    
    $orphanPermissions = Permission::whereNotIn('id', $allModulePermissions)->limit(20)->get();
    
    if (!$orphanPermissions->isEmpty()) {
        echo "⚠ Encontradas permissões que não pertencem aos módulos principais:\n";
        foreach ($orphanPermissions as $perm) {
            echo "  - {$perm->name}\n";
        }
        if (Permission::whereNotIn('id', $allModulePermissions)->count() > 20) {
            echo "  ... e mais " . (Permission::whereNotIn('id', $allModulePermissions)->count() - 20) . " permissões\n";
        }
    } else {
        echo "✓ Todas as permissões estão associadas a módulos\n";
    }
    
    // Limpar cache novamente
    app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    
    echo "\n✅ PROCESSO CONCLUÍDO COM SUCESSO!\n";
    echo "As roles modulares foram criadas e podem ser clonadas/modificadas conforme necessário.\n";
    
} catch (\Exception $e) {
    DB::rollBack();
    echo "\n❌ ERRO: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
