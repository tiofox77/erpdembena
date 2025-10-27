<?php

declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;

echo "\n🔄 REORGANIZAÇÃO DE PERMISSÕES POR MÓDULOS\n";
echo "=".str_repeat('=', 55)."\n";
echo "⏰ Timestamp: " . date('Y-m-d H:i:s') . "\n\n";

try {
    DB::beginTransaction();
    
    // Mapeamento correto das permissões por módulo
    $moduleMapping = [
        'maintenance' => [
            // Prefixos principais de manutenção
            'maintenance.',
            'equipment.',
            'preventive.',
            'corrective.',
            'parts.',
            'areas.',
            'lines.',
            'technicians.',
            'task.',
            'stocks.',
            'holidays.',
            // Histórico relacionado com manutenção
            'history.equipment.',
            'history.maintenance.',
            'history.parts.',
            // Stock relacionado com peças/manutenção
            'stock.',
        ],
        'mrp' => [
            'mrp.',
            'production.',
            'manufacturing.',
            'planning.',
            'bom.',
            'workorder.',
        ],
        'supplychain' => [
            'supplychain.',
            'inventory.',
            'purchase.',
            'supplier.',
            'warehouse.',
            'goods.',
        ],
        'hr' => [
            'hr.',
            'payroll.',
            'attendance.',
            'employee.',
            'department.',
            'position.',
            'leave.',
            'performance.',
            'contracts.',
            'training.',
        ],
        'system' => [
            'system.',
            'admin.',
            'users.',
            'roles.',
            'permissions.',
            'settings.',
            'config.',
            'history.team.',
        ],
        'reports' => [
            'reports.',
            'dashboard.',
        ]
    ];
    
    echo "🔍 Analisando permissões atuais...\n\n";
    
    $permissions = Permission::orderBy('name')->get();
    $totalPermissions = $permissions->count();
    $reorganizedCount = 0;
    $unchangedCount = 0;
    
    $moduleStats = [
        'maintenance' => 0,
        'mrp' => 0,
        'supplychain' => 0,
        'hr' => 0,
        'system' => 0,
        'reports' => 0,
        'others' => 0
    ];
    
    echo "📊 REORGANIZAÇÃO POR MÓDULO:\n";
    echo str_repeat('-', 50) . "\n";
    
    foreach ($permissions as $permission) {
        $name = $permission->name;
        $oldModule = $permission->module ?? 'undefined';
        $newModule = null;
        
        // Determinar módulo correto baseado no mapeamento
        foreach ($moduleMapping as $module => $prefixes) {
            foreach ($prefixes as $prefix) {
                if (str_starts_with($name, $prefix)) {
                    $newModule = $module;
                    break 2;
                }
            }
        }
        
        if ($newModule === null) {
            $newModule = 'others';
            echo "⚠️  Permissão não categorizada: {$name}\n";
        }
        
        $moduleStats[$newModule]++;
        
        // Verificar se houve mudança
        if ($oldModule !== $newModule) {
            $reorganizedCount++;
            echo "🔄 {$name}: '{$oldModule}' → '{$newModule}'\n";
        } else {
            $unchangedCount++;
        }
    }
    
    echo "\n" . str_repeat('=', 55) . "\n";
    echo "📈 ESTATÍSTICAS FINAIS:\n\n";
    
    foreach ($moduleStats as $module => $count) {
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
    
    echo "\n📊 RESUMO DA REORGANIZAÇÃO:\n";
    echo "• Total de permissões: {$totalPermissions}\n";
    echo "• Reorganizadas: {$reorganizedCount}\n";
    echo "• Mantidas: {$unchangedCount}\n";
    echo "• Mal categorizadas: " . $moduleStats['others'] . "\n";
    
    // Identificar permissões específicas que precisam de atenção
    echo "\n🎯 PERMISSÕES QUE PRECISAM DE ATENÇÃO MANUAL:\n";
    echo str_repeat('-', 50) . "\n";
    
    $needsAttention = [];
    foreach ($permissions as $permission) {
        $name = $permission->name;
        
        // Casos específicos que podem precisar de revisão
        if (str_contains($name, 'history.team.performance')) {
            $needsAttention[] = "{$name} → Pode ser HR ou SYSTEM";
        }
        if (str_contains($name, 'reports.') && !str_contains($name, 'dashboard.')) {
            // Verificar se é report específico de um módulo
            if (str_contains($name, 'hr.reports.') || str_contains($name, 'maintenance.reports.') || 
                str_contains($name, 'supplychain.reports.') || str_contains($name, 'mrp.reports.')) {
                $needsAttention[] = "{$name} → Report específico, manter no módulo origem";
            }
        }
    }
    
    if (empty($needsAttention)) {
        echo "✅ Todas as permissões foram categorizadas corretamente!\n";
    } else {
        foreach ($needsAttention as $attention) {
            echo "⚠️  {$attention}\n";
        }
    }
    
    echo "\n" . str_repeat('=', 55) . "\n";
    echo "🔧 MAPEAMENTO CORRETO PARA IMPLEMENTAÇÃO:\n\n";
    
    echo "MAINTENANCE (🔧):\n";
    echo "  • maintenance.*, equipment.*, preventive.*, corrective.*\n";
    echo "  • parts.*, areas.*, lines.*, technicians.*, task.*\n";
    echo "  • stocks.*, stock.*, holidays.*\n";
    echo "  • history.equipment.*, history.maintenance.*, history.parts.*\n\n";
    
    echo "MRP (🏭):\n";
    echo "  • mrp.*, production.*, manufacturing.*\n";
    echo "  • planning.*, bom.*, workorder.*\n\n";
    
    echo "SUPPLY CHAIN (📦):\n";
    echo "  • supplychain.*, inventory.*, purchase.*\n";
    echo "  • supplier.*, warehouse.*, goods.*\n\n";
    
    echo "HR (👥):\n";
    echo "  • hr.*, payroll.*, attendance.*\n";
    echo "  • employee.*, department.*, position.*\n";
    echo "  • leave.*, performance.*, contracts.*, training.*\n\n";
    
    echo "SYSTEM (⚙️):\n";
    echo "  • system.*, admin.*, users.*, roles.*\n";
    echo "  • permissions.*, settings.*, config.*\n";
    echo "  • history.team.*\n\n";
    
    echo "REPORTS (📊):\n";
    echo "  • reports.* (globais), dashboard.*\n";
    echo "  • Nota: Reports específicos ficam no módulo origem\n\n";
    
    DB::commit();
    
    echo "✅ ANÁLISE DE REORGANIZAÇÃO CONCLUÍDA!\n";
    echo "💾 Próximo passo: Implementar no PermissionsManager\n";
    
} catch (\Exception $e) {
    DB::rollBack();
    echo "\n❌ ERRO durante reorganização: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n" . str_repeat('=', 55) . "\n";
