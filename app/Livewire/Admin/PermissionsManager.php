<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class PermissionsManager extends Component
{
    public $selectedScript = '';
    public $scriptOutput = '';
    public $isExecuting = false;
    public $showOutput = false;
    
    // Scripts para download manual (uso avançado)
    public $availableScripts = [
        'create_modular_roles_system' => 'Script: Criar Roles Modulares',
        'definitive_roles_cleanup' => 'Script: Limpeza de Roles (CUIDADO)'
    ];
    
    // Ações Seguras - Recomendadas para uso normal
    public $safeActions = [
        'create_modular_roles' => [
            'script' => 'create_modular_roles_system',
            'label' => '🛡️ Criar Roles Modulares',
            'icon' => 'shield-alt',
            'class' => 'btn-success',
            'confirm' => true,
            'description' => 'SEGURO: Cria 5 roles modulares (maintenance-manager, mrp-manager, etc.)'
        ],
        'reorganize_permissions' => [
            'action' => 'reorganizePermissions',
            'label' => '📋 Análise de Permissões',
            'icon' => 'chart-bar',
            'class' => 'btn-info',
            'confirm' => false,
            'description' => 'SEGURO: Apenas analisa e mostra estatísticas das permissões'
        ]
    ];
    
    // Ações de Manutenção - Usar com cuidado
    public $maintenanceActions = [
        'disable_seeders' => [
            'action' => 'disableSeeders',
            'label' => '📁 Preparar Deployment',
            'icon' => 'archive',
            'class' => 'btn-secondary',
            'confirm' => true,
            'description' => 'Desativa seeders automáticos - criará backups'
        ],
        'definitive_cleanup' => [
            'script' => 'definitive_roles_cleanup',
            'label' => '⚠️ Limpeza de Roles',
            'icon' => 'exclamation-triangle',
            'class' => 'btn-warning',
            'confirm' => true,
            'description' => 'ATENÇÃO: Remove roles desnecessárias - apenas super-admin'
        ]
    ];

    public function mount()
    {
        // Verificar se tem permissão para acessar
        if (!auth()->user()->hasRole('super-admin')) {
            abort(403, 'Acesso negado. Apenas Super Admin pode gerenciar permissões.');
        }
    }

    public function executeScript($scriptName = null)
    {
        $script = $scriptName ?: $this->selectedScript;
        
        if (empty($script)) {
            session()->flash('error', 'Por favor selecione um script para executar.');
            return;
        }

        $this->isExecuting = true;
        $this->showOutput = false;
        $this->scriptOutput = '';
        $this->selectedScript = $script;

        try {
            $scriptPath = base_path($script . '.php');
            
            if (!File::exists($scriptPath)) {
                throw new \Exception("Script não encontrado: {$scriptPath}");
            }

            // Executar o script via PHP
            $command = "php \"{$scriptPath}\"";
            $output = shell_exec($command . ' 2>&1');
            
            $this->scriptOutput = $output ?: 'Script executado sem output.';
            $this->showOutput = true;
            
            session()->flash('success', 'Script executado com sucesso!');
            
            // Limpar cache após execução
            app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
            
            // Emitir evento para atualizar a página
            $this->dispatch('rolesUpdated');
            
        } catch (\Exception $e) {
            $this->scriptOutput = "ERRO: " . $e->getMessage();
            $this->showOutput = true;
            session()->flash('error', 'Erro ao executar script: ' . $e->getMessage());
        } finally {
            $this->isExecuting = false;
        }
    }
    
    public function executeQuickAction($action)
    {
        // Verificar em ambas as listas de ações
        $config = null;
        if (isset($this->safeActions[$action])) {
            $config = $this->safeActions[$action];
        } elseif (isset($this->maintenanceActions[$action])) {
            $config = $this->maintenanceActions[$action];
        }
        
        if (!$config) {
            session()->flash('error', 'Ação não encontrada.');
            return;
        }
        
        // Verificar se é uma ação especial (não script)
        if (isset($config['action'])) {
            if ($config['confirm']) {
                $this->dispatch('confirm-action', [
                    'action' => $action,
                    'message' => 'Tem certeza que deseja executar esta ação?'
                ]);
                return;
            }
            
            // Executar método específico
            $this->{$config['action']}();
            return;
        }
        
        if ($config['confirm']) {
            $this->dispatch('confirm-action', [
                'action' => $action,
                'message' => 'Tem certeza que deseja executar esta ação?'
            ]);
            return;
        }
        
        $this->executeScript($config['script']);
    }
    
    public function confirmedAction($action)
    {
        // Verificar em ambas as listas de ações
        $config = null;
        if (isset($this->safeActions[$action])) {
            $config = $this->safeActions[$action];
        } elseif (isset($this->maintenanceActions[$action])) {
            $config = $this->maintenanceActions[$action];
        }
        
        if ($config) {
            if (isset($config['action'])) {
                // Executar método específico
                $this->{$config['action']}();
            } else {
                // Executar script
                $this->executeScript($config['script']);
            }
        }
    }

    public function downloadScript()
    {
        if (empty($this->selectedScript)) {
            session()->flash('error', 'Por favor selecione um script para baixar.');
            return;
        }

        $scriptPath = base_path($this->selectedScript . '.php');
        
        if (!File::exists($scriptPath)) {
            session()->flash('error', 'Script não encontrado.');
            return;
        }

        return response()->download($scriptPath);
    }

    public function clearOutput()
    {
        $this->scriptOutput = '';
        $this->showOutput = false;
    }

    public function delete($roleId = null)
    {
        if ($roleId) {
            // Eliminar role específica
            try {
                $role = Role::find($roleId);
                if ($role && $role->name !== 'super-admin') {
                    $roleName = $role->name;
                    $role->delete();
                    session()->flash('success', "Role '{$roleName}' eliminada com sucesso!");
                    $this->dispatch('rolesUpdated');
                } else {
                    session()->flash('error', 'Não é possível eliminar a role Super Admin ou role não encontrada.');
                }
            } catch (\Exception $e) {
                session()->flash('error', 'Erro ao eliminar role: ' . $e->getMessage());
            }
        } else {
            // Executar script de limpeza de roles seguro
            $this->executeScript('definitive_roles_cleanup');
        }
        
        // Limpar cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }
    
    public function disableSeeders()
    {
        $this->isExecuting = true;
        $this->showOutput = false;
        $this->scriptOutput = '';
        
        try {
            $seedersToDisable = [
                'SupplyChainHrRolesPermissionsSeeder',
                'EnsureModuleUsersSeeder',
                'FixHrManagerPermissionsSeeder',
                'FixSupplyChainManagerPermissionsSeeder',
                'RolePermissionSeeder',
                'MRPPermissionsSeeder'
            ];
            
            $output = [];
            $disabledCount = 0;
            
            foreach ($seedersToDisable as $seederName) {
                $seederPath = database_path("seeders/{$seederName}.php");
                
                if (File::exists($seederPath)) {
                    $content = File::get($seederPath);
                    
                    // Verificar se já está desativado
                    if (strpos($content, 'SEEDER DESATIVADO') !== false || 
                        strpos($content, 'SEEDER TEMPORARIAMENTE DESATIVADO') !== false) {
                        $output[] = "✓ {$seederName}: Já desativado";
                        continue;
                    }
                    
                    // Criar backup
                    $backupPath = $seederPath . '.backup';
                    if (!File::exists($backupPath)) {
                        File::copy($seederPath, $backupPath);
                        $output[] = "💾 {$seederName}: Backup criado";
                    }
                    
                    // Desativar seeder
                    $newContent = $this->disableSeederContent($content, $seederName);
                    File::put($seederPath, $newContent);
                    
                    $disabledCount++;
                    $output[] = "🚫 {$seederName}: Desativado com sucesso";
                } else {
                    $output[] = "⚠️ {$seederName}: Ficheiro não encontrado";
                }
            }
            
            $output[] = "";
            $output[] = "📊 RESUMO:";
            $output[] = "• Seeders desativados: {$disabledCount}";
            $output[] = "• Backups criados: {$disabledCount}";
            $output[] = "✅ Seeders prontos para deployment seguro!";
            
            $this->scriptOutput = implode("\n", $output);
            $this->showOutput = true;
            
            session()->flash('success', "Seeders desativados com sucesso! ({$disabledCount} ficheiros)");
            
        } catch (\Exception $e) {
            $this->scriptOutput = "ERRO: " . $e->getMessage();
            $this->showOutput = true;
            session()->flash('error', 'Erro ao desativar seeders: ' . $e->getMessage());
        } finally {
            $this->isExecuting = false;
        }
    }
    
    private function disableSeederContent($content, $seederName)
    {
        // Encontrar o método run()
        $pattern = '/(public function run\(\)[^{]*{)/';
        
        $replacement = '$1' . "\n        \$this->command->info('Seeder desativado para evitar recriação automática de roles.');\n        \$this->command->info('Use o Gerenciador de Permissões em /admin/permissions-manager');\n        return;\n        \n        // CÓDIGO DESATIVADO - ";
        
        $newContent = preg_replace($pattern, $replacement, $content);
        
        // Adicionar comentário no final do método
        $newContent = str_replace(
            'public function run(): void',
            "/**\n     * SEEDER DESATIVADO - Evita recriação automática de roles\n     */\n    public function run(): void",
            $newContent
        );
        
        return $newContent;
    }
    
    public function reorganizePermissions()
    {
        $this->isExecuting = true;
        $this->showOutput = false;
        $this->scriptOutput = '';
        
        try {
            // Mapeamento correto das permissões por módulo
            $moduleMapping = [
                'maintenance' => [
                    'maintenance.', 'equipment.', 'preventive.', 'corrective.',
                    'parts.', 'areas.', 'lines.', 'technicians.', 'task.',
                    'stocks.', 'stock.', 'holidays.',
                    'history.equipment.', 'history.maintenance.', 'history.parts.'
                ],
                'mrp' => [
                    'mrp.', 'production.', 'manufacturing.',
                    'planning.', 'bom.', 'workorder.'
                ],
                'supplychain' => [
                    'supplychain.', 'inventory.', 'purchase.',
                    'supplier.', 'warehouse.', 'goods.'
                ],
                'hr' => [
                    'hr.', 'payroll.', 'attendance.', 'employee.',
                    'department.', 'position.', 'leave.', 'performance.',
                    'contracts.', 'training.'
                ],
                'system' => [
                    'system.', 'admin.', 'users.', 'roles.',
                    'permissions.', 'settings.', 'config.',
                    'history.team.'
                ],
                'reports' => [
                    'reports.', 'dashboard.'
                ]
            ];
            
            $permissions = Permission::all();
            $output = [];
            $reorganizedCount = 0;
            
            $output[] = "📊 ANÁLISE DE REORGANIZAÇÃO DE PERMISSÕES";
            $output[] = str_repeat('=', 50);
            $output[] = "🔍 Total de permissões: " . $permissions->count();
            $output[] = "";
            
            $moduleStats = [
                'maintenance' => 0, 'mrp' => 0, 'supplychain' => 0,
                'hr' => 0, 'system' => 0, 'reports' => 0, 'others' => 0
            ];
            
            foreach ($permissions as $permission) {
                $name = $permission->name;
                $newModule = 'others';
                
                // Determinar módulo correto
                foreach ($moduleMapping as $module => $prefixes) {
                    foreach ($prefixes as $prefix) {
                        if (str_starts_with($name, $prefix)) {
                            $newModule = $module;
                            break 2;
                        }
                    }
                }
                
                // Casos especiais para reports específicos de módulos
                if ($newModule === 'reports' && (
                    str_contains($name, 'hr.reports.') ||
                    str_contains($name, 'supplychain.reports.') ||
                    str_contains($name, 'mrp.reports.')
                )) {
                    // Manter report no módulo de origem
                    if (str_contains($name, 'hr.reports.')) $newModule = 'hr';
                    elseif (str_contains($name, 'supplychain.reports.')) $newModule = 'supplychain';
                    elseif (str_contains($name, 'mrp.reports.')) $newModule = 'mrp';
                }
                
                $moduleStats[$newModule]++;
                $reorganizedCount++;
            }
            
            $output[] = "📈 ESTATÍSTICAS POR MÓDULO:";
            $output[] = str_repeat('-', 30);
            
            $moduleIcons = [
                'maintenance' => '🔧', 'mrp' => '🏭', 'supplychain' => '📦',
                'hr' => '👥', 'system' => '⚙️', 'reports' => '📄', 'others' => '❓'
            ];
            
            foreach ($moduleStats as $module => $count) {
                $percentage = $permissions->count() > 0 ? round(($count / $permissions->count()) * 100, 1) : 0;
                $icon = $moduleIcons[$module];
                $output[] = "{$icon} " . strtoupper($module) . ": {$count} permissões ({$percentage}%)";
            }
            
            $output[] = "";
            $output[] = "✅ REORGANIZAÇÃO CONCLUÍDA!";
            $output[] = "• Total reorganizado: {$reorganizedCount} permissões";
            $output[] = "• Mal categorizadas: " . $moduleStats['others'];
            
            if ($moduleStats['others'] === 0) {
                $output[] = "✨ Todas as permissões estão corretamente categorizadas!";
            }
            
            $this->scriptOutput = implode("\n", $output);
            $this->showOutput = true;
            
            session()->flash('success', "Permissões reorganizadas com sucesso! ({$reorganizedCount} permissões)");
            
            // Limpar cache de permissões
            app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
            
        } catch (\Exception $e) {
            $this->scriptOutput = "ERRO: " . $e->getMessage();
            $this->showOutput = true;
            session()->flash('error', 'Erro ao reorganizar permissões: ' . $e->getMessage());
        } finally {
            $this->isExecuting = false;
        }
    }

    public function getRolesStats()
    {
        return [
            'total_roles' => Role::count(),
            'total_permissions' => Permission::count(),
            'users_with_roles' => User::whereHas('roles')->count(),
            'users_without_roles' => User::whereDoesntHave('roles')->count()
        ];
    }

    public function render()
    {
        $stats = $this->getRolesStats();
        $roles = Role::with('permissions')->get();
        $recentUsers = User::whereDoesntHave('roles')->limit(10)->get();
        
        return view('livewire.admin.permissions-manager', [
            'stats' => $stats,
            'roles' => $roles,
            'recentUsers' => $recentUsers
        ])->layout('layouts.livewire', ['title' => 'Gerenciador de Permissões']);
    }
}
