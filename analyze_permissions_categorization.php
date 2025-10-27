<?php

declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;

echo "\n📊 ANÁLISE DE CATEGORIZAÇÃO DE PERMISSÕES POR MÓDULO\n";
echo "=".str_repeat('=', 65)."\n";
echo "⏰ Timestamp: " . date('Y-m-d H:i:s') . "\n\n";

try {
    // Buscar todas as permissões
    $permissions = Permission::orderBy('name')->get();
    $totalPermissions = $permissions->count();
    
    echo "🔍 Total de permissões encontradas: {$totalPermissions}\n\n";
    
    // Categorizar permissões atuais
    $categorizedPermissions = [
        'maintenance' => [],
        'mrp' => [],
        'supplychain' => [],
        'hr' => [],
        'system' => [],
        'reports' => [],
        'others' => []
    ];
    
    foreach ($permissions as $permission) {
        $name = $permission->name;
        $categorized = false;
        
        // Maintenance - Múltiplos padrões
        if (preg_match('/^(maintenance|equipment|preventive|corrective|parts|areas|lines|technicians|task|stocks|holidays)\./', $name)) {
            $categorizedPermissions['maintenance'][] = $name;
            $categorized = true;
        }
        // MRP/Production
        elseif (preg_match('/^(mrp|production|manufacturing|planning|bom|workorder)\./', $name)) {
            $categorizedPermissions['mrp'][] = $name;
            $categorized = true;
        }
        // Supply Chain
        elseif (preg_match('/^(supplychain|inventory|purchase|supplier|warehouse|goods)\./', $name)) {
            $categorizedPermissions['supplychain'][] = $name;
            $categorized = true;
        }
        // HR
        elseif (preg_match('/^(hr|payroll|attendance|employee|department|position|leave|performance)\./', $name)) {
            $categorizedPermissions['hr'][] = $name;
            $categorized = true;
        }
        // System/Admin
        elseif (preg_match('/^(system|admin|users|roles|permissions|settings|config)\./', $name)) {
            $categorizedPermissions['system'][] = $name;
            $categorized = true;
        }
        // Reports
        elseif (preg_match('/^(reports|dashboard)\./', $name)) {
            $categorizedPermissions['reports'][] = $name;
            $categorized = true;
        }
        // Others - permissões que não se encaixam nos padrões acima
        else {
            $categorizedPermissions['others'][] = $name;
        }
    }
    
    // Exibir estatísticas por módulo
    echo "📈 ESTATÍSTICAS POR MÓDULO:\n";
    echo str_repeat('-', 50) . "\n";
    
    foreach ($categorizedPermissions as $module => $perms) {
        $count = count($perms);
        $percentage = $totalPermissions > 0 ? round(($count / $totalPermissions) * 100, 1) : 0;
        $moduleIcon = match($module) {
            'maintenance' => '🔧',
            'mrp' => '🏭',
            'supplychain' => '📦',
            'hr' => '👥',
            'system' => '⚙️',
            'reports' => '📊',
            'others' => '❓'
        };
        
        echo "{$moduleIcon} " . strtoupper($module) . ": {$count} permissões ({$percentage}%)\n";
    }
    
    echo "\n" . str_repeat('=', 65) . "\n";
    echo "🔍 DETALHAMENTO POR MÓDULO:\n\n";
    
    foreach ($categorizedPermissions as $module => $perms) {
        if (empty($perms)) continue;
        
        $moduleIcon = match($module) {
            'maintenance' => '🔧',
            'mrp' => '🏭', 
            'supplychain' => '📦',
            'hr' => '👥',
            'system' => '⚙️',
            'reports' => '📊',
            'others' => '❓'
        };
        
        echo "{$moduleIcon} " . strtoupper($module) . " (" . count($perms) . " permissões):\n";
        echo str_repeat('-', 30) . "\n";
        
        // Agrupar por prefixo para melhor organização
        $groupedByPrefix = [];
        foreach ($perms as $perm) {
            $prefix = explode('.', $perm)[0];
            $groupedByPrefix[$prefix][] = $perm;
        }
        
        foreach ($groupedByPrefix as $prefix => $prefixPerms) {
            echo "  📁 {$prefix}.* (" . count($prefixPerms) . "):\n";
            foreach ($prefixPerms as $perm) {
                echo "    • {$perm}\n";
            }
            echo "\n";
        }
        
        echo "\n";
    }
    
    // Análise especial para "others" - estas são as que precisam ser reorganizadas
    if (!empty($categorizedPermissions['others'])) {
        echo "🚨 ANÁLISE CRÍTICA - PERMISSÕES MAL CATEGORIZADAS:\n";
        echo str_repeat('=', 65) . "\n";
        
        $otherPermissions = $categorizedPermissions['others'];
        echo "❓ Total de permissões em 'others': " . count($otherPermissions) . "\n\n";
        
        echo "📋 LISTA DE PERMISSÕES PARA RECLASSIFICAÇÃO:\n";
        foreach ($otherPermissions as $perm) {
            // Tentar sugerir categoria correta
            $suggestion = '';
            if (strpos($perm, 'maint') !== false || strpos($perm, 'equipment') !== false) {
                $suggestion = '→ Sugestão: MAINTENANCE';
            } elseif (strpos($perm, 'prod') !== false || strpos($perm, 'plan') !== false) {
                $suggestion = '→ Sugestão: MRP';
            } elseif (strpos($perm, 'supply') !== false || strpos($perm, 'inventory') !== false) {
                $suggestion = '→ Sugestão: SUPPLY CHAIN';
            } elseif (strpos($perm, 'user') !== false || strpos($perm, 'payroll') !== false) {
                $suggestion = '→ Sugestão: HR';
            } elseif (strpos($perm, 'report') !== false || strpos($perm, 'dash') !== false) {
                $suggestion = '→ Sugestão: REPORTS';
            } else {
                $suggestion = '→ Sugestão: SYSTEM';
            }
            
            echo "  • {$perm} {$suggestion}\n";
        }
    }
    
    // Recomendações de reorganização
    echo "\n" . str_repeat('=', 65) . "\n";
    echo "💡 RECOMENDAÇÕES DE REORGANIZAÇÃO:\n\n";
    
    echo "1. 🔧 MAINTENANCE:\n";
    echo "   • Consolidar: equipment.*, preventive.*, corrective.*, maintenance.*\n";
    echo "   • Incluir: parts.*, areas.*, lines.*, technicians.*, task.*, stocks.*\n\n";
    
    echo "2. 🏭 MRP:\n";
    echo "   • Consolidar: mrp.*, production.*, manufacturing.*\n";
    echo "   • Incluir: planning.*, bom.*, workorder.*\n\n";
    
    echo "3. 📦 SUPPLY CHAIN:\n";
    echo "   • Consolidar: supplychain.*, inventory.*, purchase.*\n";
    echo "   • Incluir: supplier.*, warehouse.*, goods.*\n\n";
    
    echo "4. 👥 HR:\n";
    echo "   • Consolidar: hr.*, payroll.*, attendance.*\n";
    echo "   • Incluir: employee.*, department.*, position.*, leave.*, performance.*\n\n";
    
    echo "5. ⚙️ SYSTEM:\n";
    echo "   • Consolidar: system.*, admin.*, users.*, roles.*\n";
    echo "   • Incluir: permissions.*, settings.*, config.*\n\n";
    
    echo "6. 📊 REPORTS:\n";
    echo "   • Consolidar: reports.*, dashboard.*\n";
    echo "   • Manter separado para acesso transversal\n\n";
    
    echo "✅ PRÓXIMOS PASSOS:\n";
    echo "1. Criar script de reorganização de permissões\n";
    echo "2. Implementar mapeamento correto no RolePermissions\n";
    echo "3. Atualizar interface de gestão de permissões\n";
    echo "4. Testar funcionalidade após reorganização\n";
    
} catch (\Exception $e) {
    echo "\n❌ ERRO durante análise: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n" . str_repeat('=', 65) . "\n";
