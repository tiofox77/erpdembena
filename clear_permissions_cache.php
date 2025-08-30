<?php

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== LIMPAR: Cache de Permissões ===\n\n";

try {
    // Clear permission cache
    \Spatie\Permission\PermissionRegistrar::class;
    app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    echo "✅ Cache de permissões limpo\n";
    
    // Clear application cache
    \Illuminate\Support\Facades\Artisan::call('cache:clear');
    echo "✅ Cache da aplicação limpo\n";
    
    // Clear config cache
    \Illuminate\Support\Facades\Artisan::call('config:clear');
    echo "✅ Cache de configuração limpo\n";
    
    // Clear route cache
    \Illuminate\Support\Facades\Artisan::call('route:clear');
    echo "✅ Cache de rotas limpo\n";
    
    // Clear view cache
    \Illuminate\Support\Facades\Artisan::call('view:clear');
    echo "✅ Cache de views limpo\n";
    
    echo "\n" . str_repeat("=", 50) . "\n";
    echo "🔄 TESTE APÓS LIMPEZA DE CACHE:\n";
    echo str_repeat("-", 30) . "\n";
    
    // Test maintenance user again
    $maintenance = \App\Models\User::where('email', 'maintenance@dembena-group.com')->first();
    
    if ($maintenance) {
        echo "👤 Utilizador: {$maintenance->name}\n";
        echo "🏷️  Role: " . $maintenance->roles->pluck('name')->join(', ') . "\n";
        
        $canSeeMainMenu = $maintenance->canAny([
            'maintenance.dashboard.view',
            'maintenance.equipment.view',
            'maintenance.plan.view',
            'maintenance.corrective.view',
            'areas.view',
            'lines.view',
            'maintenance.technicians.view',
            'holidays.view'
        ]);
        
        echo "📋 Pode ver menu Maintenance: " . ($canSeeMainMenu ? "✅ SIM" : "❌ NÃO") . "\n";
        
        // Test individual permissions
        $testPermissions = [
            'maintenance.dashboard.view',
            'maintenance.equipment.view',
            'areas.view',
            'holidays.view'
        ];
        
        echo "\n🔍 Teste de permissões individuais:\n";
        foreach ($testPermissions as $perm) {
            $has = $maintenance->can($perm) ? "✅" : "❌";
            echo "   {$has} {$perm}\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Erro ao limpar cache: " . $e->getMessage() . "\n";
}

echo "\n" . str_repeat("=", 50) . "\n";
echo "✅ LIMPEZA DE CACHE CONCLUÍDA\n";
echo "🔄 Peça ao utilizador para fazer logout/login e testar novamente\n";
echo str_repeat("=", 50) . "\n";
