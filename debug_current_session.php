<?php

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== DEBUG: Sessão Atual do Browser ===\n\n";

// Start session to check current state
session_start();

echo "🔍 INFORMAÇÕES DA SESSÃO:\n";
echo str_repeat("-", 30) . "\n";
echo "Session ID: " . session_id() . "\n";
echo "Session Status: " . session_status() . "\n";

// Check Laravel session
if (session()->has('login_web_59ba36addc2b2f9401580f014c7f58ea4e30989d')) {
    $userId = session('login_web_59ba36addc2b2f9401580f014c7f58ea4e30989d');
    echo "✅ Laravel Session - User ID: {$userId}\n";
    
    $user = \App\Models\User::find($userId);
    if ($user) {
        echo "👤 Utilizador: {$user->name} ({$user->email})\n";
        echo "🏷️  Roles: " . $user->roles->pluck('name')->join(', ') . "\n\n";
        
        // Test specific permission for maintenance/plan
        echo "🔍 TESTE PARA /maintenance/plan:\n";
        echo str_repeat("-", 35) . "\n";
        echo "Middleware necessário: permission:preventive.view\n";
        
        $hasPreventive = $user->can('preventive.view');
        echo "Permissão 'preventive.view': " . ($hasPreventive ? "✅ TEM" : "❌ NÃO TEM") . "\n";
        
        // Check if permission exists
        $permExists = \Spatie\Permission\Models\Permission::where('name', 'preventive.view')->exists();
        echo "Permissão existe na BD: " . ($permExists ? "✅ SIM" : "❌ NÃO") . "\n\n";
        
        if (!$hasPreventive) {
            echo "🔍 PERMISSÕES RELACIONADAS COM PLANOS:\n";
            echo str_repeat("-", 40) . "\n";
            
            $planPermissions = $user->getAllPermissions()
                ->filter(function($perm) {
                    return strpos($perm->name, 'plan') !== false || 
                           strpos($perm->name, 'preventive') !== false;
                })
                ->pluck('name')
                ->toArray();
            
            if (empty($planPermissions)) {
                echo "❌ Nenhuma permissão de planos encontrada\n";
            } else {
                foreach ($planPermissions as $perm) {
                    echo "   ✅ {$perm}\n";
                }
            }
        }
        
    } else {
        echo "❌ Utilizador não encontrado na BD\n";
    }
} else {
    echo "❌ Nenhuma sessão Laravel ativa\n";
}

// Check all session data
echo "\n📋 DADOS DA SESSÃO:\n";
echo str_repeat("-", 20) . "\n";
foreach ($_SESSION as $key => $value) {
    if (is_string($value) && strlen($value) < 100) {
        echo "{$key}: {$value}\n";
    } else {
        echo "{$key}: " . gettype($value) . "\n";
    }
}

echo "\n" . str_repeat("=", 50) . "\n";
echo "💡 DIAGNÓSTICO:\n";

if (!session()->has('login_web_59ba36addc2b2f9401580f014c7f58ea4e30989d')) {
    echo "❌ Utilizador não está logado\n";
    echo "🛠️  Solução: Fazer login no sistema\n";
} else {
    $userId = session('login_web_59ba36addc2b2f9401580f014c7f58ea4e30989d');
    $user = \App\Models\User::find($userId);
    
    if ($user && !$user->can('preventive.view')) {
        echo "⚠️  Utilizador logado mas sem permissão 'preventive.view'\n";
        echo "🛠️  Soluções:\n";
        echo "   1. Adicionar permissão ao utilizador\n";
        echo "   2. Alterar middleware da rota\n";
        echo "   3. Criar permissão se não existir\n";
    }
}

echo str_repeat("=", 50) . "\n";
