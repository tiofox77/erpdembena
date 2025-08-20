<?php

require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;

echo "\n=== VERIFICAÇÃO DE PERMISSÕES E VIEWS POR MÓDULO ===\n";
echo "Timestamp: " . date('Y-m-d H:i:s') . "\n\n";

$modules = [
    'maintenance' => [
        'display' => 'Manutenção',
        'patterns' => ['maintenance.', 'maintenance-', 'maintenance_'],
        'view_paths' => ['maintenance', 'Maintenance']
    ],
    'supply_chain' => [
        'display' => 'Supply Chain',
        'patterns' => ['supplychain.', 'supply_chain.', 'supply-chain.'],
        'view_paths' => ['supply-chain', 'supply_chain', 'SupplyChain']
    ],
    'mrp' => [
        'display' => 'MRP',
        'patterns' => ['mrp.', 'mrp-', 'mrp_'],
        'view_paths' => ['mrp', 'MRP']
    ],
    'hr' => [
        'display' => 'Recursos Humanos',
        'patterns' => ['hr.', 'hr-', 'hr_', 'rh.'],
        'view_paths' => ['hr', 'HR', 'rh', 'RH']
    ]
];

foreach ($modules as $key => $module) {
    echo "\n" . str_repeat('=', 50) . "\n";
    echo "📦 MÓDULO: {$module['display']}\n";
    echo str_repeat('=', 50) . "\n";
    
    // Verificar Permissões
    echo "\n1️⃣ PERMISSÕES:\n";
    $totalPerms = 0;
    foreach ($module['patterns'] as $pattern) {
        $count = Permission::where('name', 'LIKE', $pattern . '%')->count();
        echo "   • {$pattern}*: {$count} permissões\n";
        $totalPerms += $count;
    }
    echo "   Total: {$totalPerms} permissões\n";
    
    // Verificar Views
    echo "\n2️⃣ VIEWS:\n";
    $viewBasePath = resource_path('views');
    foreach ($module['view_paths'] as $viewPath) {
        $fullPath = $viewBasePath . '/' . $viewPath;
        if (File::exists($fullPath) && File::isDirectory($fullPath)) {
            $bladeCount = count(File::glob($fullPath . '/**/*.blade.php'));
            echo "   • {$viewPath}/: {$bladeCount} arquivos blade\n";
        } else {
            echo "   • {$viewPath}/: não encontrado\n";
        }
    }
    
    // Verificar Livewire
    echo "\n3️⃣ LIVEWIRE:\n";
    $livewirePath = app_path('Livewire/' . ucfirst(str_replace('_', '', $key)));
    if (File::exists($livewirePath)) {
        $count = count(File::allFiles($livewirePath));
        echo "   ✓ {$count} componentes encontrados\n";
    } else {
        echo "   ✗ Sem componentes Livewire\n";
    }
    
    // Verificar Rotas
    echo "\n4️⃣ ROTAS:\n";
    $routeCount = 0;
    foreach (Route::getRoutes() as $route) {
        if (str_contains($route->uri(), $key) || str_contains($route->uri(), str_replace('_', '-', $key))) {
            $routeCount++;
        }
    }
    echo "   • {$routeCount} rotas encontradas\n";
}

// Resumo
echo "\n" . str_repeat('=', 50) . "\n";
echo "📊 RESUMO GERAL\n";
echo str_repeat('=', 50) . "\n";
echo "Total de permissões: " . Permission::count() . "\n";

echo "\n✅ Verificação concluída!\n";
