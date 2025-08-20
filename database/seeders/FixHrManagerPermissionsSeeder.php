<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use App\Models\User;

class FixHrManagerPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds to fix the HR Manager role permissions.
     * SEEDER DESATIVADO - Evita recriação automática de roles
     */
    public function run(): void
    {
        $this->command->info('Seeder desativado para evitar recriação automática de roles.');
        $this->command->info('Use o Gerenciador de Permissões em /admin/permissions-manager');
        return;
        
        // CÓDIGO DESATIVADO - Get HR Manager role
        /*
        $hrRole = Role::findByName('hr-manager', 'web');
        
        // Remove any non-HR related permissions
        $nonHrPermissions = [
            'reports.view', 
            'reports.dashboard',
            'reports.export'
        ];
        
        $hrRole->revokePermissionTo($nonHrPermissions);
        
        // Find the HR user
        $hrUser = User::where('email', 'hr@erpdembena.com')->first();
        
        if ($hrUser) {
            // Ensure user has HR Manager role only
            $hrUser->syncRoles(['hr-manager']);
            
            $this->command->info('HR Manager permissions fixed successfully!');
        } else {
            $this->command->error('HR Manager user not found!');
        }
        */
    }
}
