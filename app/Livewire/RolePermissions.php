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

    // Propriedades com URL
    #[Url(history: true)]
    public $search = '';

    #[Url]
    public $activeTab = 'roles';

    #[Url]
    public $filterPermissionGroup = '';

    // Propriedades de estado
    public $perPage = 30;
    public $showRoleModal = false;
    public $showPermissionModal = false;
    public $showDeleteModal = false;
    public $isEditing = false;
    public $deleteType = '';
    public $deleteId = null;

    // Dados de formulário
    public $role = [
        'name' => '',
        'permissions' => [],
    ];

    public $permission = [
        'name' => '',
        'guard_name' => 'web',
    ];

    // Propriedade temporária para edição de permissões
    public $selectedPermissions = [];

    // Regras de validação
    protected function rules()
    {
        return [
            'role.name' => 'required|string|max:255',
            'permission.name' => 'required|string|max:255',
            'permission.guard_name' => 'required|string|max:255',
        ];
    }

    // Mensagens de validação
    protected function messages()
    {
        return [
            'role.name.required' => 'O nome da função é obrigatório.',
            'permission.name.required' => 'O nome da permissão é obrigatório.',
        ];
    }

    // Agrupa permissões por módulo para facilitar visualização
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

    // Lista de grupos de permissões para filtro
    #[Computed]
    public function getPermissionGroupNamesProperty()
    {
        return array_keys($this->permissionGroups);
    }

    // Lista de roles paginada
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

    // Lista de permissões paginada
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

    // Abrir modal para criar role
    public function openCreateRoleModal()
    {
        $this->reset('role', 'selectedPermissions');
        $this->isEditing = false;
        $this->showRoleModal = true;
    }

    // Abrir modal para editar role
    public function editRole($id)
    {
        try {
            $role = Role::with('permissions')->findOrFail($id);
            $this->role = [
                'id' => $role->id,
                'name' => $role->name,
            ];

            // Preencher as permissões selecionadas
            $this->selectedPermissions = $role->permissions->pluck('id')->toArray();

            $this->isEditing = true;
            $this->showRoleModal = true;
        } catch (\Exception $e) {
            Log::error('Erro ao editar função: ' . $e->getMessage());
            $this->dispatch('notify', type: 'error', message: 'Função não encontrada.');
        }
    }

    // Salvar role (criar ou atualizar)
    public function saveRole()
    {
        $this->validate([
            'role.name' => 'required|string|max:255',
        ]);

        try {
            if ($this->isEditing) {
                $role = Role::findOrFail($this->role['id']);
                $role->name = $this->role['name'];
                $role->save();

                // Sincronizar permissões
                $role->syncPermissions($this->selectedPermissions);

                $message = 'Função atualizada com sucesso.';
                $notificationType = 'info';
            } else {
                $role = Role::create([
                    'name' => $this->role['name'],
                    'guard_name' => 'web',
                ]);

                // Atribuir permissões
                $role->syncPermissions($this->selectedPermissions);

                $message = 'Função criada com sucesso.';
                $notificationType = 'success';
            }

            $this->dispatch('notify', type: $notificationType, message: $message);
            $this->showRoleModal = false;
            $this->reset('role', 'selectedPermissions');
        } catch (\Exception $e) {
            Log::error('Erro ao salvar função: ' . $e->getMessage());
            $this->dispatch('notify', type: 'error', message: 'Erro ao salvar função: ' . $e->getMessage());
        }
    }

    // Abrir modal para criar permissão
    public function openCreatePermissionModal()
    {
        $this->reset('permission');
        $this->isEditing = false;
        $this->showPermissionModal = true;
    }

    // Abrir modal para editar permissão
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
            Log::error('Erro ao editar permissão: ' . $e->getMessage());
            $this->dispatch('notify', type: 'error', message: 'Permissão não encontrada.');
        }
    }

    // Salvar permissão (criar ou atualizar)
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

                $message = 'Permissão atualizada com sucesso.';
                $notificationType = 'info';
            } else {
                Permission::create([
                    'name' => $this->permission['name'],
                    'guard_name' => $this->permission['guard_name'],
                ]);

                $message = 'Permissão criada com sucesso.';
                $notificationType = 'success';
            }

            $this->dispatch('notify', type: $notificationType, message: $message);
            $this->showPermissionModal = false;
            $this->reset('permission');
        } catch (\Exception $e) {
            Log::error('Erro ao salvar permissão: ' . $e->getMessage());
            $this->dispatch('notify', type: 'error', message: 'Erro ao salvar permissão: ' . $e->getMessage());
        }
    }

    // Abrir modal de confirmação de exclusão
    public function confirmDelete($id, $type)
    {
        $this->deleteId = $id;
        $this->deleteType = $type;
        $this->showDeleteModal = true;
    }

    // Processar exclusão confirmada
    public function deleteConfirmed()
    {
        try {
            if ($this->deleteType === 'role') {
                $role = Role::findOrFail($this->deleteId);

                // Evitar exclusão de funções críticas
                if (in_array($role->name, ['super-admin', 'admin'])) {
                    throw new \Exception('Não é possível excluir funções do sistema.');
                }

                $role->delete();
                $message = 'Função excluída com sucesso.';
            } else if ($this->deleteType === 'permission') {
                $permission = Permission::findOrFail($this->deleteId);
                $permission->delete();
                $message = 'Permissão excluída com sucesso.';
            }

            $this->dispatch('notify', type: 'warning', message: $message);
            $this->showDeleteModal = false;
            $this->reset(['deleteId', 'deleteType']);
        } catch (\Exception $e) {
            Log::error('Erro ao excluir ' . $this->deleteType . ': ' . $e->getMessage());
            $this->dispatch('notify', type: 'error', message: 'Erro ao excluir: ' . $e->getMessage());
        }
    }

    // Fechar modais
    public function closeModal()
    {
        $this->showRoleModal = false;
        $this->showPermissionModal = false;
        $this->showDeleteModal = false;
        $this->reset(['role', 'permission', 'selectedPermissions', 'deleteId', 'deleteType']);
    }

    // Método para validação em tempo real
    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
    }

    public function render()
    {
        return view('livewire.role-permissions');
    }
}
