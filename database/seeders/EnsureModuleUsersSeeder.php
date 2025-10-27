<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class EnsureModuleUsersSeeder extends Seeder
{
    /**
     * Run the database seeds to ensure that Supply Chain and HR roles, permissions and users exist.
     * SEEDER DESATIVADO - Evita recriação automática de roles
     */
    public function run(): void
    {
        $this->command->info('Seeder desativado para evitar recriação automática de roles.');
        $this->command->info('Use o Gerenciador de Permissões em /admin/permissions-manager');
        return;
        
        // CÓDIGO DESATIVADO - Supply Chain permissions
        $supplyChainPermissions = [
            // Dashboard
            'supplychain.dashboard',
            
            // Purchase Orders
            'supplychain.purchase_orders.view',
            'supplychain.purchase_orders.create',
            'supplychain.purchase_orders.edit',
            'supplychain.purchase_orders.delete',
            'supplychain.purchase_orders.export',
            
            // Goods Receipts
            'supplychain.goods_receipts.view',
            'supplychain.goods_receipts.create',
            'supplychain.goods_receipts.edit',
            'supplychain.goods_receipts.delete',
            'supplychain.goods_receipts.export',
            
            // Products
            'supplychain.products.view',
            'supplychain.products.create',
            'supplychain.products.edit',
            'supplychain.products.delete',
            'supplychain.products.import',
            'supplychain.products.export',
            
            // Suppliers
            'supplychain.suppliers.view',
            'supplychain.suppliers.create',
            'supplychain.suppliers.edit',
            'supplychain.suppliers.delete',
            
            // Inventory
            'supplychain.inventory.view',
            'supplychain.inventory.adjust',
            'supplychain.inventory.export',
        ];
        
        // HR permissions
        $hrPermissions = [
            // Dashboard
            'hr.dashboard',
            
            // Employees
            'hr.employees.view',
            'hr.employees.create',
            'hr.employees.edit',
            'hr.employees.delete',
            'hr.employees.import',
            'hr.employees.export',
            
            // Departments
            'hr.departments.view',
            'hr.departments.create',
            'hr.departments.edit',
            'hr.departments.delete',
            
            // Positions
            'hr.positions.view',
            'hr.positions.create',
            'hr.positions.edit',
            'hr.positions.delete',
            
            // Attendance
            'hr.attendance.view',
            'hr.attendance.record',
            'hr.attendance.edit',
            'hr.attendance.export',
            
            // Leave Management
            'hr.leave.view',
            'hr.leave.request',
            'hr.leave.approve',
            'hr.leave.export',
            
            // Performance Reviews
            'hr.performance.view',
            'hr.performance.create',
            'hr.performance.edit',
            'hr.performance.delete',
        ];
        
        // Create all permissions if they don't exist
        foreach (array_merge($supplyChainPermissions, $hrPermissions) as $permission) {
            Permission::findOrCreate($permission, 'web');
        }
        
        // CÓDIGO DESATIVADO - Find or create admin and super-admin roles
        /*
        $adminRole = Role::findOrCreate('admin', 'web');
        $superAdminRole = Role::findOrCreate('super-admin', 'web');
        
        $adminRole->givePermissionTo(array_merge($supplyChainPermissions, $hrPermissions));
        $superAdminRole->givePermissionTo(array_merge($supplyChainPermissions, $hrPermissions));
        
        // Create or update Supply Chain Manager role
        $supplyChainRole = Role::findOrCreate('supply-chain-manager', 'web');
        $supplyChainRole->syncPermissions($supplyChainPermissions);
        
        // Create or update HR Manager role
        $hrRole = Role::findOrCreate('hr-manager', 'web');
        $hrRole->syncPermissions($hrPermissions);
        
        // Create user for Supply Chain if it doesn't exist
        $supplyChainUser = User::where('email', 'supplychain@erpdembena.com')->first();
        if (!$supplyChainUser) {
            $supplyChainUser = User::create([
                'name' => 'Supply Chain Manager',
                'email' => 'supplychain@erpdembena.com',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'remember_token' => Str::random(10),
            ]);
            $this->command->info('Created new Supply Chain user');
        }
        $supplyChainUser->syncRoles(['supply-chain-manager']);
        
        // Create user for HR if it doesn't exist
        $hrUser = User::where('email', 'hr@erpdembena.com')->first();
        if (!$hrUser) {
            $hrUser = User::create([
                'name' => 'HR Manager',
                'email' => 'hr@erpdembena.com',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'remember_token' => Str::random(10),
            ]);
            $this->command->info('Created new HR user');
        }
        $hrUser->syncRoles(['hr-manager']);
        
        $this->command->info('Module roles, permissions, and users updated successfully!');
        */
    }
}
