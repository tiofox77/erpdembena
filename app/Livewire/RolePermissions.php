<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Livewire\Attributes\Url;
use Livewire\Attributes\Computed;

class RolePermissions extends Component
{
    use WithPagination;

    // URL properties
    #[Url(history: true)]
    public $search = '';

    #[Url]
    public $activeTab = 'roles';

    #[Url]
    public $filterPermissionGroup = '';

    // State properties
    public $perPage = 30;
    public $showRoleModal = false;
    public $showPermissionModal = false;
    public $showDeleteModal = false;
    public $isEditing = false;
    public $deleteType = '';
    public $deleteId = null;

    // Form data
    public $role = [
        'name' => '',
        'permissions' => [],
    ];

    public $permission = [
        'name' => '',
        'guard_name' => 'web',
    ];

    // Temporary property for permission editing
    public $selectedPermissions = [];
    
    // New properties for enhanced UI
    public $permissionSearch = '';
    public $selectedModuleFilter = '';
    public $showSelectedPermissionsList = false;
    
    // Properties for permission modal enhancements
    public $permissionName = '';
    public $permissionDescription = '';
    public $selectedPermissionModule = '';
    public $selectedPermissionAction = '';

    public function mount()
    {
        if (!auth()->user()->can('roles.manage')) {
            return redirect()->route('maintenance.dashboard')->with('error', 'You do not have permission to access this page.');
        }

        $this->loadPermissions();
    }

    /**
     * Load permissions data
     */
    protected function loadPermissions()
    {
        // Ensure all required permissions exist
        $this->ensureRequiredPermissionsExist();
        
        // This method is primarily for computed properties
        // Permission groups are loaded in getPermissionGroupsProperty
        // Roles are loaded in getRolesProperty
        // Permissions are loaded in getPermissionsProperty
    }
    
    /**
     * Ensure required permissions exist in the system
     * Organized by modules: Maintenance, MRP, Supply Chain, HR
     */
    protected function ensureRequiredPermissionsExist()
    {
        $requiredPermissions = [
            // MAINTENANCE MODULE
            'equipment.view' => 'View equipment',
            'equipment.create' => 'Create equipment',
            'equipment.edit' => 'Edit equipment',
            'equipment.delete' => 'Delete equipment',
            'equipment.import' => 'Import equipment',
            'equipment.export' => 'Export equipment',
            'equipment.manage' => 'Manage equipment',
            'equipment.parts.view' => 'View equipment parts',
            'equipment.parts.manage' => 'Manage equipment parts',
            
            'preventive.view' => 'View preventive maintenance',
            'preventive.create' => 'Create preventive maintenance',
            'preventive.edit' => 'Edit preventive maintenance',
            'preventive.delete' => 'Delete preventive maintenance',
            'preventive.schedule' => 'Schedule preventive maintenance',
            'preventive.complete' => 'Complete preventive maintenance',
            'preventive.manage' => 'Manage preventive maintenance',
            
            'corrective.view' => 'View corrective maintenance',
            'corrective.create' => 'Create corrective maintenance',
            'corrective.edit' => 'Edit corrective maintenance',
            'corrective.delete' => 'Delete corrective maintenance',
            'corrective.complete' => 'Complete corrective maintenance',
            'corrective.manage' => 'Manage corrective maintenance',
            
            'reports.view' => 'View reports',
            'reports.export' => 'Export reports',
            'reports.dashboard' => 'Access reports dashboard',
            'reports.generate' => 'Generate reports',
            'reports.equipment.availability' => 'View equipment availability reports',
            'reports.equipment.reliability' => 'View equipment reliability reports',
            'reports.maintenance.types' => 'View maintenance types reports',
            'reports.maintenance.compliance' => 'View maintenance compliance reports',
            'reports.maintenance.plan' => 'View maintenance plan reports',
            'reports.resource.utilization' => 'View resource utilization reports',
            'reports.failure.analysis' => 'View failure analysis reports',
            'reports.downtime.impact' => 'View downtime impact reports',
            
            'users.view' => 'View users',
            'users.create' => 'Create users',
            'users.edit' => 'Edit users',
            'users.delete' => 'Delete users',
            'users.manage' => 'Manage users',
            
            'settings.view' => 'View settings',
            'settings.edit' => 'Edit settings',
            'settings.manage' => 'Manage settings',
            
            'areas.view' => 'View areas',
            'areas.create' => 'Create areas',
            'areas.edit' => 'Edit areas',
            'areas.delete' => 'Delete areas',
            'areas.manage' => 'Manage areas',
            
            'lines.view' => 'View production lines',
            'lines.create' => 'Create production lines',
            'lines.edit' => 'Edit production lines',
            'lines.delete' => 'Delete production lines',
            'lines.manage' => 'Manage production lines',
            
            'stocks.stockin' => 'Stock in operations',
            'stocks.stockout' => 'Stock out operations',
            'stocks.history' => 'View stock history',
            'stocks.part-requests' => 'Manage part requests',
            
            'task.view' => 'View tasks',
            'task.create' => 'Create tasks',
            'task.edit' => 'Edit tasks',
            'task.delete' => 'Delete tasks',
            'task.manage' => 'Manage tasks',
            
            'technicians.view' => 'View technicians',
            'technicians.create' => 'Create technicians',
            'technicians.edit' => 'Edit technicians',
            'technicians.delete' => 'Delete technicians',
            'technicians.manage' => 'Manage technicians',
            
            'roles.view' => 'View roles',
            'roles.create' => 'Create roles',
            'roles.edit' => 'Edit roles',
            'roles.delete' => 'Delete roles',
            'roles.manage' => 'Manage roles',
            
            'holidays.view' => 'View holidays',
            'holidays.create' => 'Create holidays',
            'holidays.edit' => 'Edit holidays',
            'holidays.delete' => 'Delete holidays',
            'holidays.manage' => 'Manage holidays',
            
            'history.equipment.timeline' => 'View equipment timeline history',
            'history.maintenance.audit' => 'View maintenance audit history',
            'history.parts.lifecycle' => 'View parts lifecycle history',
            'history.team.performance' => 'View team performance history',
            
            // SUPPLY CHAIN MODULE
            'supplychain.dashboard' => 'Access supply chain dashboard',
            
            'supplychain.purchase_orders.view' => 'View purchase orders',
            'supplychain.purchase_orders.create' => 'Create purchase orders',
            'supplychain.purchase_orders.edit' => 'Edit purchase orders',
            'supplychain.purchase_orders.delete' => 'Delete purchase orders',
            'supplychain.purchase_orders.export' => 'Export purchase orders',
            
            'supplychain.goods_receipts.view' => 'View goods receipts',
            'supplychain.goods_receipts.create' => 'Create goods receipts',
            'supplychain.goods_receipts.edit' => 'Edit goods receipts',
            'supplychain.goods_receipts.delete' => 'Delete goods receipts',
            'supplychain.goods_receipts.export' => 'Export goods receipts',
            
            'supplychain.products.view' => 'View products',
            'supplychain.products.create' => 'Create products',
            'supplychain.products.edit' => 'Edit products',
            'supplychain.products.delete' => 'Delete products',
            'supplychain.products.import' => 'Import products',
            'supplychain.products.export' => 'Export products',
            
            'supplychain.suppliers.view' => 'View suppliers',
            'supplychain.suppliers.create' => 'Create suppliers',
            'supplychain.suppliers.edit' => 'Edit suppliers',
            'supplychain.suppliers.delete' => 'Delete suppliers',
            
            'supplychain.inventory.view' => 'View inventory',
            'supplychain.inventory.adjust' => 'Adjust inventory',
            'supplychain.inventory.export' => 'Export inventory',
            'hr.employees.view' => 'View employees',
            'hr.employees.create' => 'Create employees',
            'hr.employees.edit' => 'Edit employees',
            'hr.employees.delete' => 'Delete employees',
            'hr.departments.view' => 'View departments',
            'hr.departments.manage' => 'Manage departments',
            'hr.positions.view' => 'View positions',
            'hr.positions.manage' => 'Manage positions',
            'hr.attendance.view' => 'View attendance',
            'hr.attendance.manage' => 'Manage attendance',
            'hr.payroll.view' => 'View payroll',
            'hr.payroll.process' => 'Process payroll',
            'hr.leave.view' => 'View leave requests',
            'hr.leave.create' => 'Create leave requests',
            'hr.leave.approve' => 'Approve leave requests',
            'hr.performance.view' => 'View performance evaluations',
            'hr.performance.create' => 'Create performance evaluations',
            'hr.training.view' => 'View training records',
            'hr.training.manage' => 'Manage training programs',
            'hr.contracts.view' => 'View employment contracts',
            'hr.contracts.manage' => 'Manage employment contracts',
            'hr.reports.view' => 'View HR reports',
            'hr.reports.export' => 'Export HR reports',
            
            // SYSTEM ADMINISTRATION
            'system.roles.view' => 'View roles',
            'system.roles.create' => 'Create roles',
            'system.roles.edit' => 'Edit roles',
            'system.roles.delete' => 'Delete roles',
            'system.permissions.view' => 'View permissions',
            'system.permissions.create' => 'Create permissions',
            'system.permissions.edit' => 'Edit permissions',
            'system.permissions.delete' => 'Delete permissions',
            'system.users.view' => 'View users',
            'system.users.create' => 'Create users',
            'system.users.edit' => 'Edit users',
            'system.users.delete' => 'Delete users',
            'system.settings.view' => 'View system settings',
            'system.settings.edit' => 'Edit system settings',
            'system.backup.create' => 'Create system backups',
            'system.backup.restore' => 'Restore system backups',
            'system.logs.view' => 'View system logs',
            'system.audit.view' => 'View audit trails',
        ];
        
        // Create permissions if they don't exist
        foreach ($requiredPermissions as $name => $description) {
            Permission::firstOrCreate(
                ['name' => $name],
                [
                    'name' => $name,
                    'guard_name' => 'web',
                    'description' => $description
                ]
            );
        }
        
        // Setup default roles with appropriate permissions
        $this->setupDefaultRoles();
    }

    // Validation rules
    protected function rules()
    {
        return [
            'role.name' => 'required|string|max:255',
            'permission.name' => 'required|string|max:255',
            'permission.guard_name' => 'required|string|max:255',
        ];
    }

    // Validation messages
    protected function messages()
    {
        return [
            'role.name.required' => 'The role name is required.',
            'permission.name.required' => 'The permission name is required.',
        ];
    }

    /**
     * Setup default roles with appropriate permissions
     */
    protected function setupDefaultRoles()
    {
        $defaultRoles = [
            'super-admin' => [
                'description' => 'Super Administrator with full access',
                'permissions' => ['*'] // All permissions
            ],
            'maintenance-manager' => [
                'description' => 'Maintenance Manager',
                'permissions' => [
                    'maintenance.*',
                    'supply-chain.inventory.view',
                    'supply-chain.stock.*',
                    'supply-chain.products.view',
                    'supply-chain.suppliers.view',
                    'hr.employees.view'
                ]
            ],
            'maintenance-technician' => [
                'description' => 'Maintenance Technician',
                'permissions' => [
                    'maintenance.dashboard',
                    'maintenance.equipment.view',
                    'maintenance.corrective.view',
                    'maintenance.corrective.edit',
                    'maintenance.preventive.view',
                    'maintenance.preventive.edit',
                    'maintenance.requests.create',
                    'maintenance.inventory.view'
                ]
            ],
            'production-manager' => [
                'description' => 'Production Manager (MRP)',
                'permissions' => [
                    'mrp.*',
                    'supply-chain.inventory.view',
                    'supply-chain.products.view',
                    'supply-chain.purchase-orders.view',
                    'hr.employees.view'
                ]
            ],
            'production-planner' => [
                'description' => 'Production Planner',
                'permissions' => [
                    'mrp.dashboard',
                    'mrp.bom.view',
                    'mrp.production.*',
                    'mrp.requirements.*',
                    'mrp.planning.*',
                    'mrp.capacity.view',
                    'mrp.forecasting.*'
                ]
            ],
            'supply-chain-manager' => [
                'description' => 'Supply Chain Manager',
                'permissions' => [
                    'supply-chain.*',
                    'maintenance.inventory.view',
                    'mrp.requirements.view'
                ]
            ],
            'purchasing-officer' => [
                'description' => 'Purchasing Officer',
                'permissions' => [
                    'supply-chain.dashboard',
                    'supply-chain.suppliers.*',
                    'supply-chain.purchase-orders.*',
                    'supply-chain.inventory.view',
                    'supply-chain.products.view'
                ]
            ],
            'warehouse-supervisor' => [
                'description' => 'Warehouse Supervisor',
                'permissions' => [
                    'supply-chain.dashboard',
                    'supply-chain.inventory.*',
                    'supply-chain.stock.*',
                    'supply-chain.warehouses.*',
                    'supply-chain.products.view'
                ]
            ],
            'hr-manager' => [
                'description' => 'Human Resources Manager',
                'permissions' => [
                    'hr.*'
                ]
            ],
            'hr-officer' => [
                'description' => 'Human Resources Officer',
                'permissions' => [
                    'hr.dashboard',
                    'hr.employees.view',
                    'hr.employees.edit',
                    'hr.attendance.*',
                    'hr.leave.*',
                    'hr.training.view'
                ]
            ],
            'employee' => [
                'description' => 'Regular Employee',
                'permissions' => [
                    'hr.leave.create',
                    'hr.attendance.view'
                ]
            ]
        ];
        
        foreach ($defaultRoles as $roleName => $roleData) {
            $role = Role::firstOrCreate(
                ['name' => $roleName],
                ['description' => $roleData['description']]
            );
            
            // Assign permissions to role
            if (in_array('*', $roleData['permissions'])) {
                // Give all permissions to super-admin
                $role->syncPermissions(Permission::all());
            } else {
                $permissionsToAssign = [];
                foreach ($roleData['permissions'] as $permissionPattern) {
                    if (str_ends_with($permissionPattern, '*')) {
                        // Wildcard permission - get all permissions that start with the prefix
                        $prefix = rtrim($permissionPattern, '*');
                        $matchingPermissions = Permission::where('name', 'like', $prefix . '%')->pluck('name')->toArray();
                        $permissionsToAssign = array_merge($permissionsToAssign, $matchingPermissions);
                    } else {
                        // Exact permission
                        $permissionsToAssign[] = $permissionPattern;
                    }
                }
                
                // Remove duplicates and sync permissions
                $permissionsToAssign = array_unique($permissionsToAssign);
                $existingPermissions = Permission::whereIn('name', $permissionsToAssign)->pluck('name')->toArray();
                $role->syncPermissions($existingPermissions);
            }
        }
    }
    
    // Group permissions by module for easier viewing
    #[Computed]
    public function getPermissionGroupsProperty()
    {
        $permissions = Permission::all();
        $groups = [
            'maintenance' => ['label' => 'Maintenance', 'icon' => 'fas fa-tools', 'permissions' => []],
            'mrp' => ['label' => 'MRP (Production)', 'icon' => 'fas fa-industry', 'permissions' => []],
            'supplychain' => ['label' => 'Supply Chain', 'icon' => 'fas fa-truck', 'permissions' => []],
            'hr' => ['label' => 'Human Resources', 'icon' => 'fas fa-users', 'permissions' => []],
            'system' => ['label' => 'System Administration', 'icon' => 'fas fa-cogs', 'permissions' => []],
            'other' => ['label' => 'Others', 'icon' => 'fas fa-question-circle', 'permissions' => []]
        ];

        foreach ($permissions as $permission) {
            $parts = explode('.', $permission->name);
            $module = $parts[0] ?? 'other';
            
            // Map permission prefixes to modules
            if (in_array($module, [
                'equipment', 'preventive', 'corrective', 'reports', 'users', 'settings', 
                'areas', 'lines', 'stocks', 'task', 'technicians', 'roles', 'holidays', 'history'
            ])) {
                $module = 'maintenance';
            } elseif (in_array($module, ['supplychain'])) {
                $module = 'supplychain';
            } elseif (in_array($module, ['mrp', 'production', 'bom', 'planning'])) {
                $module = 'mrp';
            } elseif (in_array($module, ['hr', 'employees', 'payroll', 'attendance'])) {
                $module = 'hr';
            } elseif (in_array($module, ['admin', 'system', 'config'])) {
                $module = 'system';
            }

            if (!isset($groups[$module])) {
                $groups['other']['permissions'][] = $permission;
            } else {
                $groups[$module]['permissions'][] = $permission;
            }
        }

        // Remove empty groups
        $groups = array_filter($groups, function($group) {
            return !empty($group['permissions']);
        });
        
        return $groups;
    }

    // List of permission groups for filtering
    #[Computed]
    public function getPermissionGroupNamesProperty()
    {
        return array_keys($this->permissionGroups);
    }

    // Paginated list of roles
    #[Computed]
    public function getRolesProperty()
    {
        return Role::when($this->search, function ($query) {
                return $query->where('name', 'like', '%' . $this->search . '%');
            })
            ->with('permissions')
            ->orderBy('name')
            ->paginate($this->perPage);
    }

    // Paginated list of permissions
    #[Computed]
    public function getPermissionsProperty()
    {
        return Permission::when($this->search, function ($query) {
                return $query->where('name', 'like', '%' . $this->search . '%');
            })
            ->when($this->filterPermissionGroup, function ($query) {
                return $query->where('name', 'like', $this->filterPermissionGroup . '.%');
            })
            ->orderBy('name')
            ->paginate($this->perPage);
    }

    // Open modal to create role
    public function openCreateRoleModal()
    {
        $this->reset('role');
        $this->selectedPermissions = [];
        $this->permissionSearch = '';
        $this->selectedModuleFilter = '';
        $this->showSelectedPermissionsList = false;
        $this->isEditing = false;
        $this->showRoleModal = true;
    }

    // Open modal to edit role
    public function editRole($id)
    {
        try {
            $role = Role::with('permissions')->findOrFail($id);
            $this->role = [
                'id' => $role->id,
                'name' => $role->name,
            ];

            // Fill in selected permissions
            $this->selectedPermissions = $role->permissions->pluck('id')->toArray();

            $this->isEditing = true;
            $this->showRoleModal = true;
        } catch (\Exception $e) {
            Log::error('Error editing role: ' . $e->getMessage());
            $this->dispatch('notify', type: 'error', message: 'Role not found.');
        }
    }

    // Save role (create or update)
    public function saveRole()
    {
        try {
            // Validar o nome da role com validação adicional de unicidade
            if ($this->isEditing) {
                $this->validate([
                    'role.name' => 'required|string|max:255',
                ]);
            } else {
                $this->validate([
                    'role.name' => 'required|string|max:255|unique:roles,name',
                ]);
            }
            
            // Formatar o nome da role para evitar problemas
            // Remover espaços duplos, caracteres especiais e padronizar
            $formattedRoleName = $this->formatRoleName($this->role['name']);
            
            // Check if selectedPermissions contains valid IDs
            if (!empty($this->selectedPermissions)) {
                // Filter only valid numeric IDs
                $this->selectedPermissions = array_filter($this->selectedPermissions, function($id) {
                    return is_numeric($id);
                });

                // Check if permissions exist in the database
                $validPermissionIds = Permission::whereIn('id', $this->selectedPermissions)->pluck('id')->toArray();
                $this->selectedPermissions = $validPermissionIds;
            }

            if ($this->isEditing) {
                $role = Role::findOrFail($this->role['id']);
                
                // Verificar se o nome será alterado e se já existe outro com esse nome
                if ($role->name !== $formattedRoleName) {
                    $existingRole = Role::where('name', $formattedRoleName)->where('id', '!=', $role->id)->first();
                    if ($existingRole) {
                        throw new \Exception("Uma função com o nome '{$formattedRoleName}' já existe.");
                    }
                }
                
                $role->name = $formattedRoleName;
                $role->save();

                // Sync permissions with valid IDs
                $role->syncPermissions($this->selectedPermissions);

                $message = "Função '{$formattedRoleName}' atualizada com sucesso.";
                $notificationType = 'info';
            } else {
                // Verificar novamente se já existe uma role com este nome
                $existingRole = Role::where('name', $formattedRoleName)->first();
                if ($existingRole) {
                    throw new \Exception("Uma função com o nome '{$formattedRoleName}' já existe.");
                }
                
                $role = Role::create([
                    'name' => $formattedRoleName,
                    'guard_name' => 'web',
                ]);

                // Assign permissions with valid IDs
                if (!empty($this->selectedPermissions)) {
                    $role->syncPermissions($this->selectedPermissions);
                }

                $message = "Função '{$formattedRoleName}' criada com sucesso.";
                $notificationType = 'success';
            }

            $this->dispatch('notify', type: $notificationType, message: $message);
            $this->showRoleModal = false;
            $this->reset('role', 'selectedPermissions');
        } catch (\Exception $e) {
            Log::error('Error saving role: ' . $e->getMessage());
            Log::error('selectedPermissions: ' . json_encode($this->selectedPermissions));
            $this->dispatch('notify', type: 'error', message: 'Erro ao salvar função: ' . $e->getMessage());
        }
    }
    
    /**
     * Formata o nome da role para evitar problemas
     * - Remove espaços em excesso
     * - Converte para minúsculas
     * - Substitui espaços por hífens
     * - Remove caracteres especiais
     * 
     * @param string $name Nome da role
     * @return string Nome formatado
     */
    private function formatRoleName($name)
    {
        // Remover espaços em excesso e converter para minúsculas
        $name = trim(strtolower($name));
        
        // Se for uma role de manutenção, garantir que comece com 'maintenance-'
        if (stripos($name, 'maintenance') === 0 && stripos($name, 'maintenance-') !== 0) {
            $name = 'maintenance-' . substr($name, strlen('maintenance'));
        }
        
        // Se for uma string vazia após a limpeza, usar um valor padrão
        if (empty($name)) {
            return 'role-' . time();
        }
        
        return $name;
    }

    // Open modal to create permission
    public function openCreatePermissionModal()
    {
        $this->resetPermissionForm();
        $this->isEditing = false;
        $this->showPermissionModal = true;
    }

    // Open modal to edit permission
    public function editPermission($id)
    {
        try {
            $permission = Permission::findOrFail($id);
            $this->permission = [
                'id' => $permission->id,
                'name' => $permission->name,
                'guard_name' => $permission->guard_name,
            ];
            
            // Set the new properties
            $this->permissionName = $permission->name;
            $this->permissionDescription = '';
            $this->selectedPermissionModule = '';
            $this->selectedPermissionAction = '';

            $this->isEditing = true;
            $this->showPermissionModal = true;
        } catch (\Exception $e) {
            Log::error('Error editing permission: ' . $e->getMessage());
            $this->dispatch('notify', type: 'error', message: 'Permição não encontrada.');
        }
    }

    // Save permission (create or update)
    public function savePermission()
    {
        $this->validate([
            'permissionName' => 'required|string|max:255|unique:permissions,name,' . ($this->isEditing ? $this->permission['id'] ?? 'NULL' : 'NULL'),
        ]);

        try {
            if ($this->isEditing) {
                $permission = Permission::findOrFail($this->permission['id']);
                $permission->name = $this->permissionName;
                $permission->guard_name = 'web';
                $permission->save();

                $message = 'Permição atualizada com sucesso.';
                $notificationType = 'warning';
            } else {
                Permission::create([
                    'name' => $this->permissionName,
                    'guard_name' => 'web',
                ]);

                $message = 'Permição criada com sucesso.';
                $notificationType = 'success';
            }

            $this->dispatch('notify', type: $notificationType, message: $message);
            $this->showPermissionModal = false;
            $this->resetPermissionForm();
        } catch (\Exception $e) {
            Log::error('Error saving permission: ' . $e->getMessage());
            $this->dispatch('notify', type: 'error', message: 'Erro ao salvar permição: ' . $e->getMessage());
        }
    }
    
    // Reset permission form data
    private function resetPermissionForm()
    {
        $this->reset([
            'permissionName', 
            'permissionDescription', 
            'selectedPermissionModule', 
            'selectedPermissionAction'
        ]);
    }
    
    // Apply permission suggestion
    public function applyPermissionSuggestion()
    {
        if ($this->selectedPermissionModule && $this->selectedPermissionAction) {
            $this->permissionName = $this->selectedPermissionModule . '.' . $this->selectedPermissionAction;
        }
    }
    
    // Select all permissions for role
    public function selectAllPermissions()
    {
        $this->selectedPermissions = Permission::all()->pluck('id')->toArray();
        $this->dispatch('notify', type: 'info', message: 'Todas as permições foram selecionadas.');
    }
    
    // Deselect all permissions for role
    public function deselectAllPermissions()
    {
        $this->selectedPermissions = [];
        $this->dispatch('notify', type: 'info', message: 'Todas as permições foram desmarcadas.');
    }
    
    // Toggle permissions for a specific module
    public function toggleModulePermissions($module)
    {
        $modulePermissions = Permission::where('name', 'like', $module . '.%')->pluck('id')->toArray();
        
        // Check if all module permissions are already selected
        $allSelected = !array_diff($modulePermissions, $this->selectedPermissions);
        
        if ($allSelected) {
            // Remove all module permissions
            $this->selectedPermissions = array_diff($this->selectedPermissions, $modulePermissions);
            $moduleLabel = $this->getModuleLabel($module);
            $this->dispatch('notify', type: 'info', message: "Permições do módulo {$moduleLabel} foram desmarcadas.");
        } else {
            // Add all module permissions
            $this->selectedPermissions = array_unique(array_merge($this->selectedPermissions, $modulePermissions));
            $moduleLabel = $this->getModuleLabel($module);
            $this->dispatch('notify', type: 'info', message: "Permições do módulo {$moduleLabel} foram selecionadas.");
        }
    }
    
    // Get module label for display
    private function getModuleLabel($module)
    {
        $labels = [
            'maintenance' => 'Manutenção',
            'mrp' => 'MRP',
            'supplychain' => 'Supply Chain',
            'hr' => 'Recursos Humanos',
            'system' => 'Sistema',
            'reports' => 'Relatórios',
            'inventory' => 'Supply Chain', // legacy mapping
            'stock' => 'Supply Chain', // legacy mapping
            'parts' => 'Supply Chain', // legacy mapping
        ];
        
        return $labels[$module] ?? ucfirst($module);
    }
    
    // Get filtered permissions based on search and module filter
    public function getFilteredPermissionGroups()
    {
        $groups = $this->permissionGroups;
        
        // Apply search filter
        if (!empty($this->permissionSearch)) {
            $searchTerm = strtolower($this->permissionSearch);
            foreach ($groups as $module => $permissions) {
                $groups[$module] = $permissions->filter(function ($permission) use ($searchTerm) {
                    return str_contains(strtolower($permission->name), $searchTerm);
                });
                
                // Remove empty groups
                if ($groups[$module]->isEmpty()) {
                    unset($groups[$module]);
                }
            }
        }
        
        // Apply module filter
        if (!empty($this->selectedModuleFilter)) {
            $groups = array_filter($groups, function ($key) {
                return $key === $this->selectedModuleFilter;
            }, ARRAY_FILTER_USE_KEY);
        }
        
        return $groups;
    }

    // Open delete confirmation modal
    public function confirmDelete($id, $type)
    {
        $this->deleteId = $id;
        $this->deleteType = $type;
        $this->showDeleteModal = true;
    }

    // Process confirmed deletion
    public function deleteConfirmed()
    {
        try {
            if ($this->deleteType === 'role') {
                $role = Role::findOrFail($this->deleteId);

                // Prevent deletion of critical roles
                if (in_array($role->name, ['super-admin', 'admin'])) {
                    throw new \Exception('Cannot delete system roles.');
                }

                $role->delete();
                $message = 'Role deleted successfully.';
            } else if ($this->deleteType === 'permission') {
                $permission = Permission::findOrFail($this->deleteId);
                $permission->delete();
                $message = 'Permission deleted successfully.';
            }

            $this->dispatch('notify', type: 'warning', message: $message);
            $this->showDeleteModal = false;
            $this->reset(['deleteId', 'deleteType']);
        } catch (\Exception $e) {
            Log::error('Error deleting ' . $this->deleteType . ': ' . $e->getMessage());
            $this->dispatch('notify', type: 'error', message: 'Error deleting: ' . $e->getMessage());
        }
    }

    // Close modals
    public function closeModal()
    {
        $this->showRoleModal = false;
        $this->showPermissionModal = false;
        $this->showDeleteModal = false;
        $this->reset([
            'role', 
            'permission', 
            'selectedPermissions', 
            'deleteId', 
            'deleteType',
            'permissionSearch',
            'selectedModuleFilter',
            'showSelectedPermissionsList',
            'permissionName',
            'permissionDescription',
            'selectedPermissionModule',
            'selectedPermissionAction'
        ]);
    }

    // Method for real-time validation
    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
    }
    
    // Temporary public method to create all permissions
    public function createAllPermissions()
    {
        $this->ensureRequiredPermissionsExist();
        $this->dispatch('notify', type: 'success', message: 'Todas as permissões foram criadas com sucesso!');
    }

    public function render()
    {
        return view('livewire.role-permissions');
    }
}
