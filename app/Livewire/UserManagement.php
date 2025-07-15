<?php

namespace App\Livewire;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Url;
use Spatie\Permission\Models\Role;

class UserManagement extends Component
{
    use WithPagination;

    // URL parameters
    #[Url(history: true)]
    public $search = '';

    #[Url(history: true)]
    public $filterRole = '';

    #[Url(history: true)]
    public $filterDepartment = '';

    #[Url(history: true)]
    public $filterStatus = '';

    #[Url(history: true)]
    public $sortField = 'id';

    #[Url(history: true)]
    public $sortDirection = 'desc';

    // Modal states
    public $showModal = false;
    public $showDeleteModal = false;
    public $isEditing = false;
    public $deleteUserId = null;

    // User data for create/edit form
    public $user = [
        'first_name' => '',
        'last_name' => '',
        'email' => '',
        'phone' => '',
        'role' => '',
        'department' => '',
        'password' => '',
        'password_confirmation' => '',
        'is_active' => true
    ];

    // Define roles e departments
    public $roles = [];
    public $spatieRoles = [];

    public $departments = [
        'maintenance' => 'Maintenance',
        'production' => 'Production',
        'quality' => 'Quality Control',
        'logistics' => 'Logistics',
        'administration' => 'Administration',
        'it' => 'IT',
        'hr' => 'Human Resources'
    ];

    public function mount()
    {
        if (!auth()->user()->can('users.manage')) {
            return redirect()->route('maintenance.dashboard')->with('error', 'You do not have permission to access this page.');
        }

        // Load roles and users data
        $this->loadRoles();
        $this->loadUsers();
    }

    protected function loadRoles()
    {
        // Load all system roles
        $this->spatieRoles = Role::all();

        // Create an array for the roles select
        $this->roles = $this->spatieRoles->pluck('name', 'name')->toArray();
    }

    protected function loadUsers()
    {
        // This method will be called through getUsersProperty
    }

    // Validation rules
    protected function rules()
    {
        $rules = [
            'user.first_name' => 'required|string|max:255',
            'user.last_name' => 'required|string|max:255',
            'user.phone' => 'nullable|string|max:20',
            'user.role' => 'required|string|in:' . implode(',', array_keys($this->roles)),
            'user.department' => 'required|string|in:' . implode(',', array_keys($this->departments)),
            'user.is_active' => 'boolean'
        ];

        // Add conditional rules for different actions
        if ($this->isEditing) {
            // Editing - email must be unique except for current user
            $userId = $this->user['id'] ?? null;
            $rules['user.email'] = [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($userId)
            ];

            // Password is optional when editing
            if (!empty($this->user['password'])) {
                $rules['user.password'] = 'min:8|confirmed';
                $rules['user.password_confirmation'] = 'required';
            }
        } else {
            // Creating - email must be unique and password required
            $rules['user.email'] = 'required|email|max:255|unique:users,email';
            $rules['user.password'] = 'required|min:8|confirmed';
            $rules['user.password_confirmation'] = 'required';
        }

        return $rules;
    }

    // Custom validation messages
    protected function messages()
    {
        return [
            'user.first_name.required' => 'First name is required',
            'user.last_name.required' => 'Last name is required',
            'user.email.required' => 'Email address is required',
            'user.email.email' => 'Please enter a valid email address',
            'user.email.unique' => 'This email is already in use',
            'user.role.required' => 'Please select a role',
            'user.role.in' => 'Invalid role selected',
            'user.department.required' => 'Please select a department',
            'user.department.in' => 'Invalid department selected',
            'user.password.required' => 'Password is required',
            'user.password.min' => 'Password must be at least 8 characters',
            'user.password.confirmed' => 'Password confirmation does not match',
            'user.password_confirmation.required' => 'Please confirm your password',
        ];
    }

    // Reset pagination when filters change
    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedFilterRole()
    {
        $this->resetPage();
    }

    public function updatedFilterDepartment()
    {
        $this->resetPage();
    }

    public function updatedFilterStatus()
    {
        $this->resetPage();
    }

    // Real-time validation
    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
    }

    // Sorting
    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    // Open modal for creating a new user
    public function openModal()
    {
        $this->resetValidation();
        $this->isEditing = false;
        $this->user = [
            'first_name' => '',
            'last_name' => '',
            'email' => '',
            'phone' => '',
            'role' => '',
            'department' => '',
            'password' => '',
            'password_confirmation' => '',
            'is_active' => true
        ];
        $this->showModal = true;
    }

    // Open modal for editing a user
    public function editUser($id)
    {
        $this->resetValidation();
        $this->isEditing = true;

        $userToEdit = User::with('roles')->findOrFail($id);

        // Obter o primeiro role do usuário
        $userRole = $userToEdit->roles->first() ? $userToEdit->roles->first()->name : '';

        $this->user = [
            'id' => $userToEdit->id,
            'first_name' => $userToEdit->first_name,
            'last_name' => $userToEdit->last_name,
            'email' => $userToEdit->email,
            'phone' => $userToEdit->phone,
            'role' => $userRole, // Usar o role do Spatie
            'department' => $userToEdit->department,
            'password' => '',
            'password_confirmation' => '',
            'is_active' => $userToEdit->is_active
        ];

        $this->showModal = true;
    }

    // Save user (create or update)
    public function saveUser()
    {
        $validatedData = $this->validate();

        try {
            if ($this->isEditing) {
                // Update existing user
                $user = User::findOrFail($this->user['id']);

                $userData = [
                    'first_name' => $this->user['first_name'],
                    'last_name' => $this->user['last_name'],
                    'email' => $this->user['email'],
                    'phone' => $this->user['phone'],
                    'department' => $this->user['department'],
                    'is_active' => $this->user['is_active']
                ];

                // Only update password if provided
                if (!empty($this->user['password'])) {
                    $userData['password'] = Hash::make($this->user['password']);
                }

                $user->update($userData);

                // Atualizar o role usando o sistema de permissões do Spatie
                if (!empty($this->user['role'])) {
                    $user->syncRoles([$this->user['role']]);
                }

                $message = 'User updated successfully';
                $notificationType = 'info';

            } else {
                // Create new user
                $user = User::create([
                    'first_name' => $this->user['first_name'],
                    'last_name' => $this->user['last_name'],
                    'email' => $this->user['email'],
                    'phone' => $this->user['phone'],
                    'department' => $this->user['department'],
                    'password' => Hash::make($this->user['password']),
                    'is_active' => $this->user['is_active']
                ]);

                // Atribuir o role usando o sistema de permissões do Spatie
                if (!empty($this->user['role'])) {
                    $user->assignRole($this->user['role']);
                }

                $message = 'User created successfully';
                $notificationType = 'success';
            }

            // Send notification
            $this->dispatch('notify', type: $notificationType, message: $message);

            // Close modal and reset form properly
            $this->closeModal();

        } catch (\Exception $e) {
            Log::error('Error saving user: ' . $e->getMessage());
            $this->dispatch('notify', type: 'error', message: 'Error saving user: ' . $e->getMessage());
        }
    }

    // Confirm user deletion
    public function confirmDelete($userId)
    {
        $this->deleteUserId = $userId;
        $this->showDeleteModal = true;
    }

    // Delete a user
    public function deleteUser()
    {
        try {
            $user = User::findOrFail($this->deleteUserId);
            $user->delete();

            $this->dispatch('notify', type: 'warning', message: 'User deleted successfully');

            $this->showDeleteModal = false;
            $this->deleteUserId = null;

        } catch (\Exception $e) {
            Log::error('Error deleting user: ' . $e->getMessage());
            $this->dispatch('notify', type: 'error', message: 'Error deleting user: ' . $e->getMessage());
        }
    }

    // Toggle user active status
    public function toggleUserStatus($userId)
    {
        try {
            $user = User::findOrFail($userId);
            $user->is_active = !$user->is_active;
            $user->save();

            $status = $user->is_active ? 'activated' : 'deactivated';
            $this->dispatch('notify', type: 'info', message: "User {$status} successfully");

        } catch (\Exception $e) {
            Log::error('Error toggling user status: ' . $e->getMessage());
            $this->dispatch('notify', type: 'error', message: 'Error changing user status: ' . $e->getMessage());
        }
    }

    // Close modal
    public function closeModal()
    {
        $this->showModal = false;
        $this->showDeleteModal = false;
        $this->isEditing = false;
        $this->deleteUserId = null;
        
        // Reset user data properly
        $this->user = [
            'first_name' => '',
            'last_name' => '',
            'email' => '',
            'phone' => '',
            'role' => '',
            'department' => '',
            'password' => '',
            'password_confirmation' => '',
            'is_active' => true
        ];
        
        $this->resetValidation();
    }

    // Get users with filters and sorting
    public function getUsersProperty()
    {
        return User::with('roles')
            ->when($this->search, function ($query) {
                return $query->where(function ($q) {
                    $q->where('full_name', 'like', '%' . $this->search . '%')
                      ->orWhere('email', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->filterRole, function ($query) {
                // Filtrar por role usando relacionamento do Spatie
                return $query->role($this->filterRole);
            })
            ->when($this->filterDepartment, function ($query) {
                return $query->where('department', $this->filterDepartment);
            })
            ->when($this->filterStatus !== '', function ($query) {
                return $query->where('is_active', $this->filterStatus);
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(10);
    }

    public function render()
    {
        return view('livewire.user-management', [
            'users' => $this->users
        ]);
    }
}
