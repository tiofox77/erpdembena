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
        // This method is intentionally left empty as permissions
        // are loaded through the computed properties
        // Permission groups are loaded in getPermissionGroupsProperty
        // Roles are loaded in getRolesProperty
        // Permissions are loaded in getPermissionsProperty
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

    // Group permissions by module for easier viewing
    #[Computed]
    public function getPermissionGroupsProperty()
    {
        $permissions = Permission::all();
        $groups = [];

        foreach ($permissions as $permission) {
            $parts = explode('.', $permission->name);
            $module = $parts[0] ?? 'other';

            if (!isset($groups[$module])) {
                $groups[$module] = [];
            }

            $groups[$module][] = $permission;
        }

        ksort($groups);
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
        $this->reset('role', 'selectedPermissions');
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
        $this->validate([
            'role.name' => 'required|string|max:255',
        ]);

        try {
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
                $role->name = $this->role['name'];
                $role->save();

                // Sync permissions with valid IDs
                $role->syncPermissions($this->selectedPermissions);

                $message = 'Role updated successfully.';
                $notificationType = 'info';
            } else {
                $role = Role::create([
                    'name' => $this->role['name'],
                    'guard_name' => 'web',
                ]);

                // Assign permissions with valid IDs
                if (!empty($this->selectedPermissions)) {
                    $role->syncPermissions($this->selectedPermissions);
                }

                $message = 'Role created successfully.';
                $notificationType = 'success';
            }

            $this->dispatch('notify', type: $notificationType, message: $message);
            $this->showRoleModal = false;
            $this->reset('role', 'selectedPermissions');
        } catch (\Exception $e) {
            Log::error('Error saving role: ' . $e->getMessage());
            Log::error('selectedPermissions: ' . json_encode($this->selectedPermissions));
            $this->dispatch('notify', type: 'error', message: 'Error saving role: ' . $e->getMessage());
        }
    }

    // Open modal to create permission
    public function openCreatePermissionModal()
    {
        $this->reset('permission');
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

            $this->isEditing = true;
            $this->showPermissionModal = true;
        } catch (\Exception $e) {
            Log::error('Error editing permission: ' . $e->getMessage());
            $this->dispatch('notify', type: 'error', message: 'Permission not found.');
        }
    }

    // Save permission (create or update)
    public function savePermission()
    {
        $this->validate([
            'permission.name' => 'required|string|max:255',
            'permission.guard_name' => 'required|string|max:255',
        ]);

        try {
            if ($this->isEditing) {
                $permission = Permission::findOrFail($this->permission['id']);
                $permission->name = $this->permission['name'];
                $permission->guard_name = $this->permission['guard_name'];
                $permission->save();

                $message = 'Permission updated successfully.';
                $notificationType = 'info';
            } else {
                Permission::create([
                    'name' => $this->permission['name'],
                    'guard_name' => $this->permission['guard_name'],
                ]);

                $message = 'Permission created successfully.';
                $notificationType = 'success';
            }

            $this->dispatch('notify', type: $notificationType, message: $message);
            $this->showPermissionModal = false;
            $this->reset('permission');
        } catch (\Exception $e) {
            Log::error('Error saving permission: ' . $e->getMessage());
            $this->dispatch('notify', type: 'error', message: 'Error saving permission: ' . $e->getMessage());
        }
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
        $this->reset(['role', 'permission', 'selectedPermissions', 'deleteId', 'deleteType']);
    }

    // Method for real-time validation
    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
    }

    public function render()
    {
        return view('livewire.role-permissions');
    }
}
