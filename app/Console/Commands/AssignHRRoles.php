<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Spatie\Permission\Models\Role;

class AssignHRRoles extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'hr:assign-role {email} {role}';

    /**
     * The console command description.
     */
    protected $description = 'Assign HR roles to users (hr-employee-manager or hr-payroll-processor)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $email = $this->argument('email');
        $roleName = $this->argument('role');

        // Validate role name
        $validRoles = ['hr-employee-manager', 'hr-payroll-processor'];
        if (!in_array($roleName, $validRoles)) {
            $this->error("Invalid role. Valid roles are: " . implode(', ', $validRoles));
            return 1;
        }

        // Find user by email
        $user = User::where('email', $email)->first();
        if (!$user) {
            $this->error("User with email '{$email}' not found.");
            return 1;
        }

        // Check if role exists
        $role = Role::where('name', $roleName)->first();
        if (!$role) {
            $this->error("Role '{$roleName}' not found. Please run migrations first.");
            return 1;
        }

        // Assign role to user
        if ($user->hasRole($roleName)) {
            $this->info("User '{$email}' already has role '{$roleName}'.");
        } else {
            $user->assignRole($roleName);
            $this->info("Role '{$roleName}' assigned to user '{$email}' successfully!");
        }

        // Show user's current roles
        $userRoles = $user->getRoleNames()->toArray();
        $this->info("User's current roles: " . implode(', ', $userRoles));

        return 0;
    }
}
