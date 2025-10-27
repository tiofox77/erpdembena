<?php

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== ANÁLISE DE SEPARAÇÃO POR MÓDULOS ===\n\n";

try {
    // Buscar todas as permissões existentes
    $allPermissions = Permission::all();
    echo "📊 Total de permissões no sistema: " . $allPermissions->count() . "\n\n";

    // Definir padrões de módulos
    $modulePatterns = [
        'maintenance' => [
            'patterns' => [
                '/^maintenance\./',
                '/^equipment\./',
                '/^preventive\./',
                '/^corrective\./',
                '/^areas\./',
                '/^technicians\./',
                '/^parts\./',
                '/^stock\./',
                '/^reports\./',
                '/^users\./',
                '/^roles\./',
                '/^settings\./'
            ],
            'permissions' => [],
            'description' => 'Módulo de Manutenção - Gestão de equipamentos, manutenção preventiva/corretiva'
        ],
        'mrp' => [
            'patterns' => [
                '/^mrp\./'
            ],
            'permissions' => [],
            'description' => 'Módulo MRP - Planejamento de recursos materiais e produção'
        ],
        'supply_chain' => [
            'patterns' => [
                '/^supplychain\./'
            ],
            'permissions' => [],
            'description' => 'Módulo Supply Chain - Gestão de cadeia de suprimentos'
        ],
        'hr' => [
            'patterns' => [
                '/^hr\./'
            ],
            'permissions' => [],
            'description' => 'Módulo HR - Recursos humanos e folha de pagamento'
        ],
        'general' => [
            'patterns' => [],
            'permissions' => [],
            'description' => 'Permissões gerais do sistema'
        ]
    ];

    // Classificar permissões por módulo
    foreach ($allPermissions as $permission) {
        $classified = false;
        
        foreach ($modulePatterns as $module => &$moduleData) {
            if ($module === 'general') continue;
            
            foreach ($moduleData['patterns'] as $pattern) {
                if (preg_match($pattern, $permission->name)) {
                    $moduleData['permissions'][] = $permission->name;
                    $classified = true;
                    break 2;
                }
            }
        }
        
        if (!$classified) {
            $modulePatterns['general']['permissions'][] = $permission->name;
        }
    }

    // Relatório por módulo
    echo "📋 CLASSIFICAÇÃO POR MÓDULOS:\n";
    echo str_repeat("=", 80) . "\n";

    foreach ($modulePatterns as $module => $data) {
        echo "\n🔧 " . strtoupper($module) . " (" . count($data['permissions']) . " permissões)\n";
        echo str_repeat("-", 60) . "\n";
        echo "📝 " . $data['description'] . "\n\n";
        
        if (!empty($data['permissions'])) {
            sort($data['permissions']);
            foreach ($data['permissions'] as $i => $perm) {
                echo sprintf("  %2d. %s\n", $i + 1, $perm);
            }
        } else {
            echo "  (Nenhuma permissão encontrada)\n";
        }
    }

    // Analisar roles existentes
    echo "\n\n🎭 ANÁLISE DE ROLES EXISTENTES:\n";
    echo str_repeat("=", 80) . "\n";

    $existingRoles = Role::with('permissions')->get();
    foreach ($existingRoles as $role) {
        echo "\n📌 ROLE: " . strtoupper($role->name) . "\n";
        echo "   Permissões: " . $role->permissions->count() . "\n";
        
        if ($role->name === 'super-admin') {
            echo "   ✅ Super Admin - Acesso total\n";
            continue;
        }
        
        // Analisar distribuição das permissões por módulo
        $roleModuleDistribution = [
            'maintenance' => 0,
            'mrp' => 0,
            'supply_chain' => 0,
            'hr' => 0,
            'general' => 0
        ];
        
        foreach ($role->permissions as $permission) {
            foreach ($modulePatterns as $module => $moduleData) {
                if (in_array($permission->name, $moduleData['permissions'])) {
                    $roleModuleDistribution[$module]++;
                    break;
                }
            }
        }
        
        foreach ($roleModuleDistribution as $module => $count) {
            if ($count > 0) {
                $percentage = round(($count / $role->permissions->count()) * 100, 1);
                echo "   - " . ucfirst($module) . ": {$count} ({$percentage}%)\n";
            }
        }
    }

    // Recomendações para separação
    echo "\n\n💡 RECOMENDAÇÕES PARA SEPARAÇÃO MODULAR:\n";
    echo str_repeat("=", 80) . "\n";

    echo "\n🏗️  ESTRUTURA RECOMENDADA:\n";
    foreach ($modulePatterns as $module => $data) {
        if ($module === 'general') continue;
        
        $roleName = $module . '-manager';
        echo "\n📋 ROLE: {$roleName}\n";
        echo "   Descrição: {$data['description']}\n";
        echo "   Permissões: " . count($data['permissions']) . "\n";
        echo "   Status: " . (Role::where('name', $roleName)->exists() ? "✅ Existe" : "❌ Não existe") . "\n";
    }

    // Verificar conflitos e sobreposições
    echo "\n\n🔍 ANÁLISE DE CONFLITOS:\n";
    echo str_repeat("-", 60) . "\n";

    $conflicts = [];
    $generalPermissions = $modulePatterns['general']['permissions'];
    
    if (!empty($generalPermissions)) {
        echo "⚠️  PERMISSÕES GERAIS ENCONTRADAS:\n";
        foreach ($generalPermissions as $perm) {
            echo "   - {$perm}\n";
        }
        echo "\n💡 Estas permissões precisam ser reclassificadas ou aplicadas a múltiplas roles.\n";
    } else {
        echo "✅ Nenhuma permissão geral encontrada - boa separação modular!\n";
    }

    // Verificar cobertura de permissões do sidebar
    echo "\n\n🎯 COBERTURA DO SIDEBAR:\n";
    echo str_repeat("-", 60) . "\n";

    $sidebarPath = 'resources/views/layouts/livewire.blade.php';
    $sidebarContent = file_get_contents($sidebarPath);
    
    preg_match_all('/@can(any)?\s*\(\s*[\'"](.+?)[\'"]\s*\)/', $sidebarContent, $sidebarPermissions);
    $sidebarPerms = array_unique($sidebarPermissions[2] ?? []);
    
    echo "📊 Permissões usadas no sidebar: " . count($sidebarPerms) . "\n";
    echo "📊 Permissões no banco: " . $allPermissions->count() . "\n";
    
    $unusedPermissions = [];
    foreach ($allPermissions as $permission) {
        if (!in_array($permission->name, $sidebarPerms)) {
            $unusedPermissions[] = $permission->name;
        }
    }
    
    if (!empty($unusedPermissions)) {
        echo "\n⚠️  PERMISSÕES NÃO USADAS NO SIDEBAR:\n";
        foreach ($unusedPermissions as $perm) {
            echo "   - {$perm}\n";
        }
    } else {
        echo "\n✅ Todas as permissões são usadas no sidebar\n";
    }

    // Script de criação das roles modulares
    echo "\n\n🛠️  SCRIPT PARA CRIAR ROLES MODULARES:\n";
    echo str_repeat("=", 80) . "\n";

    $scriptPath = 'create_modular_roles.php';
    $scriptContent = "<?php\n\n";
    $scriptContent .= "require_once 'vendor/autoload.php';\n\n";
    $scriptContent .= "use Spatie\\Permission\\Models\\Role;\n";
    $scriptContent .= "use Spatie\\Permission\\Models\\Permission;\n\n";
    $scriptContent .= "// Bootstrap Laravel\n";
    $scriptContent .= "\$app = require_once 'bootstrap/app.php';\n";
    $scriptContent .= "\$kernel = \$app->make(Illuminate\\Contracts\\Console\\Kernel::class);\n";
    $scriptContent .= "\$kernel->bootstrap();\n\n";
    $scriptContent .= "echo \"=== CRIANDO ROLES MODULARES ===\\n\\n\";\n\n";
    
    foreach ($modulePatterns as $module => $data) {
        if ($module === 'general' || empty($data['permissions'])) continue;
        
        $roleName = $module . '-manager';
        $scriptContent .= "// Criar role: {$roleName}\n";
        $scriptContent .= "\$role = Role::firstOrCreate(['name' => '{$roleName}', 'guard_name' => 'web']);\n";
        $scriptContent .= "\$permissions = [\n";
        
        foreach ($data['permissions'] as $perm) {
            $scriptContent .= "    '{$perm}',\n";
        }
        
        $scriptContent .= "];\n";
        $scriptContent .= "\$role->syncPermissions(\$permissions);\n";
        $scriptContent .= "echo \"✅ Role {$roleName} criada com \" . count(\$permissions) . \" permissões\\n\";\n\n";
    }
    
    $scriptContent .= "echo \"\\n🎉 ROLES MODULARES CRIADAS COM SUCESSO!\\n\";\n";
    
    file_put_contents($scriptPath, $scriptContent);
    echo "✅ Script criado: {$scriptPath}\n";

    echo "\n\n📈 ESTATÍSTICAS FINAIS:\n";
    echo str_repeat("=", 80) . "\n";
    echo "• Total de permissões: " . $allPermissions->count() . "\n";
    echo "• Maintenance: " . count($modulePatterns['maintenance']['permissions']) . " permissões\n";
    echo "• MRP: " . count($modulePatterns['mrp']['permissions']) . " permissões\n";
    echo "• Supply Chain: " . count($modulePatterns['supply_chain']['permissions']) . " permissões\n";
    echo "• HR: " . count($modulePatterns['hr']['permissions']) . " permissões\n";
    echo "• Gerais: " . count($modulePatterns['general']['permissions']) . " permissões\n";
    
    $coverage = round((($allPermissions->count() - count($modulePatterns['general']['permissions'])) / $allPermissions->count()) * 100, 1);
    echo "• Cobertura modular: {$coverage}%\n";

    echo "\n✅ ANÁLISE CONCLUÍDA!\n";

} catch (Exception $e) {
    echo "\n❌ ERRO: " . $e->getMessage() . "\n";
    echo "Linha: " . $e->getLine() . "\n";
    echo "Arquivo: " . $e->getFile() . "\n";
    exit(1);
}
