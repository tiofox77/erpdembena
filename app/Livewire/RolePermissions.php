<?php

declare(strict_types=1);

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Url;
use Livewire\Attributes\Computed;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RolePermissions extends Component
{
    use WithPagination;

    #[Url(history: true)]
    public string $search = '';

    #[Url]
    public string $activeTab = 'roles';

    #[Url]
    public string $filterPermissionGroup = '';

    public int $perPage = 25;
    public bool $showRoleModal = false;
    public bool $showPermissionModal = false;
    public bool $showDeleteModal = false;
    public bool $showDuplicateModal = false;
    public bool $isEditing = false;
    public string $deleteType = '';
    public ?int $deleteId = null;
    public ?int $duplicateRoleId = null;
    public string $duplicateRoleName = '';

    public array $roleData = [
        'id' => null,
        'name' => '',
        'description' => ''
    ];

    public array $permissionData = [
        'id' => null,
        'name' => '',
        'description' => ''
    ];

    public array $selectedPermissions = [];
    public string $permissionSearch = '';
    public string $selectedModuleFilter = '';

    public function mount(): void
    {
        // Use the same permission pattern as super-admin - allow multiple ways to access
        if (!auth()->user()->canAny(['roles.manage', 'maintenance.roles.manage', 'system.roles.view', 'system.roles.edit'])) {
            abort(403, 'Acesso negado. Apenas utilizadores com permissões de gestão de roles podem aceder a esta página.');
        }
    }

    #[Computed]
    public function roles()
    {
        return Role::query()
            ->when($this->search, fn($query) => $query->where('name', 'like', '%' . $this->search . '%'))
            ->with(['permissions' => fn($q) => $q->select('id', 'name')])
            ->orderBy('name')
            ->paginate($this->perPage);
    }

    #[Computed]
    public function permissions()
    {
        return Permission::query()
            ->when($this->search, fn($query) => $query->where('name', 'like', '%' . $this->search . '%'))
            ->when($this->filterPermissionGroup, fn($query) => 
                $query->where('name', 'like', $this->filterPermissionGroup . '.%')
            )
            ->orderBy('name')
            ->paginate($this->perPage);
    }

    #[Computed]
    public function permissionGroups(): array
    {
        $permissions = Permission::all(['id', 'name']);
        $groups = [
            'maintenance' => ['label' => '🔧 Manutenção', 'permissions' => []],
            'mrp' => ['label' => '🏭 MRP (Produção)', 'permissions' => []],
            'supplychain' => ['label' => '📦 Supply Chain', 'permissions' => []],
            'hr' => ['label' => '👥 Recursos Humanos', 'permissions' => []],
            'system' => ['label' => '⚙️ Sistema', 'permissions' => []],
            'reports' => ['label' => '📊 Relatórios', 'permissions' => []],
            'other' => ['label' => '❓ Outras', 'permissions' => []]
        ];

        foreach ($permissions as $permission) {
            $module = $this->getPermissionModule($permission->name);
            $groups[$module]['permissions'][] = $permission;
        }

        return array_filter($groups, fn($group) => !empty($group['permissions']));
    }

    public function openCreateRoleModal(): void
    {
        $this->resetRoleData();
        $this->selectedPermissions = [];
        $this->isEditing = false;
        $this->showRoleModal = true;
    }

    public function editRole(int $roleId): void
    {
        try {
            $role = Role::with('permissions')->findOrFail($roleId);
            
            $this->roleData = [
                'id' => $role->id,
                'name' => $role->name,
                'description' => $role->description ?? ''
            ];
            
            $this->selectedPermissions = $role->permissions->pluck('id')->toArray();
            $this->isEditing = true;
            $this->showRoleModal = true;
            
        } catch (\Exception $e) {
            $this->dispatch('notify', type: 'error', message: 'Role não encontrada.');
            Log::error('Erro ao editar role: ' . $e->getMessage());
        }
    }

    public function saveRole(): void
    {
        $this->validate([
            'roleData.name' => 'required|string|max:255|unique:roles,name,' . ($this->roleData['id'] ?? 'NULL'),
            'roleData.description' => 'nullable|string|max:500'
        ]);

        try {
            DB::beginTransaction();

            if ($this->isEditing && $this->roleData['id']) {
                $role = Role::findOrFail($this->roleData['id']);
                $role->update([
                    'name' => $this->roleData['name'],
                    'description' => $this->roleData['description']
                ]);
                $message = "Role '{$role->name}' actualizada com sucesso.";
            } else {
                $role = Role::create([
                    'name' => $this->roleData['name'],
                    'description' => $this->roleData['description'],
                    'guard_name' => 'web'
                ]);
                $message = "Role '{$role->name}' criada com sucesso.";
            }

            if (!empty($this->selectedPermissions)) {
                $validPermissions = Permission::whereIn('id', $this->selectedPermissions)->pluck('id');
                $role->syncPermissions($validPermissions);
            } else {
                $role->syncPermissions([]);
            }

            DB::commit();
            
            $this->dispatch('notify', type: 'success', message: $message);
            $this->closeModal();
            $this->resetPage();

        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('notify', type: 'error', message: 'Erro ao guardar role: ' . $e->getMessage());
            Log::error('Erro ao guardar role: ' . $e->getMessage());
        }
    }

    public function openCreatePermissionModal(): void
    {
        $this->resetPermissionData();
        $this->isEditing = false;
        $this->showPermissionModal = true;
    }

    public function editPermission(int $permissionId): void
    {
        try {
            $permission = Permission::findOrFail($permissionId);
            
            $this->permissionData = [
                'id' => $permission->id,
                'name' => $permission->name,
                'description' => $permission->description ?? ''
            ];
            
            $this->isEditing = true;
            $this->showPermissionModal = true;
            
        } catch (\Exception $e) {
            $this->dispatch('notify', type: 'error', message: 'Permissão não encontrada.');
            Log::error('Erro ao editar permissão: ' . $e->getMessage());
        }
    }

    public function savePermission(): void
    {
        $this->validate([
            'permissionData.name' => 'required|string|max:255|unique:permissions,name,' . ($this->permissionData['id'] ?? 'NULL'),
            'permissionData.description' => 'nullable|string|max:500'
        ]);

        try {
            if ($this->isEditing && $this->permissionData['id']) {
                $permission = Permission::findOrFail($this->permissionData['id']);
                $permission->update([
                    'name' => $this->permissionData['name'],
                    'description' => $this->permissionData['description']
                ]);
                $message = "Permissão '{$permission->name}' actualizada com sucesso.";
            } else {
                $permission = Permission::create([
                    'name' => $this->permissionData['name'],
                    'description' => $this->permissionData['description'],
                    'guard_name' => 'web'
                ]);
                $message = "Permissão '{$permission->name}' criada com sucesso.";
            }

            $this->dispatch('notify', type: 'success', message: $message);
            $this->closeModal();
            $this->resetPage();

        } catch (\Exception $e) {
            $this->dispatch('notify', type: 'error', message: 'Erro ao guardar permissão: ' . $e->getMessage());
            Log::error('Erro ao guardar permissão: ' . $e->getMessage());
        }
    }

    public function delete(int $id = null, string $type = 'role'): void
    {
        if ($id) {
            $this->confirmDelete($id, $type);
        } else {
            $this->deleteConfirmed();
        }
    }

    public function confirmDelete(int $id, string $type): void
    {
        $this->deleteId = $id;
        $this->deleteType = $type;
        $this->showDeleteModal = true;
    }

    public function deleteConfirmed(): void
    {
        try {
            if ($this->deleteType === 'role') {
                $role = Role::findOrFail($this->deleteId);
                
                if (in_array($role->name, ['super-admin', 'admin'])) {
                    throw new \Exception('Não é possível eliminar roles críticas do sistema.');
                }
                
                $role->delete();
                $message = "Role '{$role->name}' eliminada com sucesso.";
                
            } elseif ($this->deleteType === 'permission') {
                $permission = Permission::findOrFail($this->deleteId);
                $permission->delete();
                $message = "Permissão '{$permission->name}' eliminada com sucesso.";
            }

            $this->dispatch('notify', type: 'success', message: $message);
            $this->closeModal();
            $this->resetPage();

        } catch (\Exception $e) {
            $this->dispatch('notify', type: 'error', message: 'Erro ao eliminar: ' . $e->getMessage());
            Log::error('Erro ao eliminar ' . $this->deleteType . ': ' . $e->getMessage());
        }
    }

    public function selectAllPermissions(): void
    {
        $this->selectedPermissions = Permission::pluck('id')->toArray();
        $this->dispatch('notify', type: 'info', message: 'Todas as permissões seleccionadas.');
    }

    public function deselectAllPermissions(): void
    {
        $this->selectedPermissions = [];
        $this->dispatch('notify', type: 'info', message: 'Todas as permissões desseleccionadas.');
    }

    public function toggleModulePermissions(string $module): void
    {
        // Usar a mesma lógica de agrupamento do método permissionGroups
        $allPermissions = Permission::all(['id', 'name']);
        $modulePermissions = [];
        
        foreach ($allPermissions as $permission) {
            if ($this->getPermissionModule($permission->name) === $module) {
                $modulePermissions[] = $permission->id;
            }
        }
        
        $allSelected = !array_diff($modulePermissions, $this->selectedPermissions);
        
        if ($allSelected) {
            $this->selectedPermissions = array_diff($this->selectedPermissions, $modulePermissions);
            $message = "Permissões do módulo {$this->getModuleLabel($module)} desseleccionadas.";
        } else {
            $this->selectedPermissions = array_unique(array_merge($this->selectedPermissions, $modulePermissions));
            $message = "Permissões do módulo {$this->getModuleLabel($module)} seleccionadas.";
        }
        
        $this->dispatch('notify', type: 'info', message: $message);
    }

    public function openDuplicateModal(int $roleId): void
    {
        $role = Role::findOrFail($roleId);
        $this->duplicateRoleId = $roleId;
        $this->duplicateRoleName = $role->name . ' - Cópia';
        $this->showDuplicateModal = true;
    }

    public function duplicateRole(): void
    {
        $this->validate([
            'duplicateRoleName' => 'required|string|max:255|unique:roles,name'
        ], [
            'duplicateRoleName.required' => 'O nome da função é obrigatório.',
            'duplicateRoleName.unique' => 'Já existe uma função com este nome.',
            'duplicateRoleName.max' => 'O nome da função não pode ter mais de 255 caracteres.'
        ]);

        try {
            DB::beginTransaction();

            $originalRole = Role::with('permissions')->findOrFail($this->duplicateRoleId);
            
            $newRole = Role::create([
                'name' => $this->duplicateRoleName,
                'guard_name' => $originalRole->guard_name ?? 'web'
            ]);

            // Copiar todas as permissões da função original
            $newRole->syncPermissions($originalRole->permissions);

            DB::commit();

            $this->dispatch('notify', type: 'success', message: 'Função duplicada com sucesso!');
            $this->closeDuplicateModal();
            $this->resetPage();

        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('notify', type: 'error', message: 'Erro ao duplicar função: ' . $e->getMessage());
            Log::error('Erro ao duplicar função: ' . $e->getMessage());
        }
    }

    public function closeDuplicateModal(): void
    {
        $this->showDuplicateModal = false;
        $this->duplicateRoleId = null;
        $this->duplicateRoleName = '';
        $this->resetErrorBag(['duplicateRoleName']);
    }

    public function closeModal(): void
    {
        $this->showRoleModal = false;
        $this->showPermissionModal = false;
        $this->showDeleteModal = false;
        $this->showDuplicateModal = false;
        $this->resetRoleData();
        $this->resetPermissionData();
        $this->selectedPermissions = [];
        $this->deleteId = null;
        $this->deleteType = '';
        $this->duplicateRoleId = null;
        $this->duplicateRoleName = '';
        $this->isEditing = false;
    }

    private function resetRoleData(): void
    {
        $this->roleData = [
            'id' => null,
            'name' => '',
            'description' => ''
        ];
    }

    private function resetPermissionData(): void
    {
        $this->permissionData = [
            'id' => null,
            'name' => '',
            'description' => ''
        ];
    }

    private function getPermissionModule(string $permissionName): string
    {
        // Mapeamento correto baseado na análise completa
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

        // Determinar módulo correto
        foreach ($moduleMapping as $module => $prefixes) {
            foreach ($prefixes as $prefix) {
                if (str_starts_with($permissionName, $prefix)) {
                    // Casos especiais para reports específicos de módulos
                    if ($module === 'reports' && (
                        str_contains($permissionName, 'hr.reports.') ||
                        str_contains($permissionName, 'supplychain.reports.') ||
                        str_contains($permissionName, 'mrp.reports.')
                    )) {
                        // Manter report no módulo de origem
                        if (str_contains($permissionName, 'hr.reports.')) return 'hr';
                        elseif (str_contains($permissionName, 'supplychain.reports.')) return 'supplychain';
                        elseif (str_contains($permissionName, 'mrp.reports.')) return 'mrp';
                    }
                    return $module;
                }
            }
        }

        return 'other';
    }

    private function getModuleLabel(string $module): string
    {
        $labels = [
            'maintenance' => '🔧 Manutenção',
            'mrp' => '🏭 MRP (Produção)',
            'supplychain' => '📦 Supply Chain',
            'hr' => '👥 Recursos Humanos',
            'system' => '⚙️ Sistema',
            'reports' => '📊 Relatórios',
            'other' => '❓ Outras'
        ];
        
        return $labels[$module] ?? ucfirst($module);
    }

    public function updated($propertyName): void
    {
        if (str_starts_with($propertyName, 'roleData.') || str_starts_with($propertyName, 'permissionData.')) {
            $this->validateOnly($propertyName);
        }
        
        if ($propertyName === 'duplicateRoleName') {
            $this->validateOnly('duplicateRoleName');
        }
    }

    public function render()
    {
        return view('livewire.role-permissions');
    }
}
