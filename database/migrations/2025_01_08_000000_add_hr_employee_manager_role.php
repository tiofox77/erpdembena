<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Criar permissões específicas para gestão de funcionários sem acesso a salários
        $permissions = [
            // Permissões básicas para funcionários
            'hr.employees.view' => 'Ver funcionários',
            'hr.employees.create' => 'Criar funcionários',
            'hr.employees.edit' => 'Editar funcionários',
            'hr.employees.delete' => 'Eliminar funcionários',
            
            // Permissões específicas de funcionários (excluindo salários)
            'hr.employees.personal_info.view' => 'Ver informações pessoais de funcionários',
            'hr.employees.personal_info.edit' => 'Editar informações pessoais de funcionários',
            'hr.employees.documents.view' => 'Ver documentos de funcionários',
            'hr.employees.documents.upload' => 'Fazer upload de documentos de funcionários',
            'hr.employees.contracts.view' => 'Ver contratos de funcionários',
            'hr.employees.contracts.edit' => 'Editar contratos de funcionários',
            
            // Acesso ao dashboard HR
            'hr.dashboard' => 'Acesso ao dashboard de RH',
            
            // Departamentos (necessário para gestão de funcionários)
            'hr.departments.view' => 'Ver departamentos',
            
            // Permissões de SALÁRIO - criar mas NÃO atribuir à role hr-employee-manager
            'hr.employees.salary.view' => 'Ver salários de funcionários',
            'hr.employees.salary.edit' => 'Editar salários de funcionários',
            'hr.payroll.view' => 'Ver folha de pagamento',
            'hr.payroll.create' => 'Criar folha de pagamento',
            'hr.payroll.edit' => 'Editar folha de pagamento',
        ];

        // Criar permissões se não existirem
        foreach ($permissions as $permission => $description) {
            Permission::firstOrCreate(
                ['name' => $permission],
                ['guard_name' => 'web']
            );
        }

        // Criar a role hr-employee-manager
        $role = Role::firstOrCreate(
            ['name' => 'hr-employee-manager'],
            ['guard_name' => 'web']
        );

        // Atribuir permissões à role (EXCLUINDO permissões de salário)
        $permissionsToAssign = [
            'hr.employees.view',
            'hr.employees.create',
            'hr.employees.edit',
            'hr.employees.personal_info.view',
            'hr.employees.personal_info.edit',
            'hr.employees.documents.view',
            'hr.employees.documents.upload',
            'hr.employees.contracts.view',
            'hr.employees.contracts.edit',
            'hr.dashboard',
            'hr.departments.view',
            
            // NOTA: Intencionalmente EXCLUÍDAS as seguintes permissões:
            // 'hr.employees.salary.view'
            // 'hr.employees.salary.edit'
            // 'hr.payroll.view'
            // 'hr.payroll.create'
            // 'hr.payroll.edit'
        ];

        $role->syncPermissions($permissionsToAssign);

        // Logs para confirmar criação
        \Log::info('Role hr-employee-manager criada com permissões:', $permissionsToAssign);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remover a role
        $role = Role::where('name', 'hr-employee-manager')->first();
        if ($role) {
            $role->delete();
        }

        // Remover permissões específicas criadas (opcionalmente)
        $permissionsToRemove = [
            'hr.employees.personal_info.view',
            'hr.employees.personal_info.edit',
            'hr.employees.documents.view',
            'hr.employees.documents.upload',
            'hr.employees.contracts.view',
            'hr.employees.contracts.edit',
        ];

        foreach ($permissionsToRemove as $permission) {
            Permission::where('name', $permission)->delete();
        }
    }
};
