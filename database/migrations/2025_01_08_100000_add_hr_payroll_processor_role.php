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
        // Criar permissões específicas para processamento de pagamentos
        $permissions = [
            // Permissões de processamento de pagamento (sem ver valores)
            'hr.payroll.process' => 'Processar folha de pagamento',
            'hr.payroll.approve' => 'Aprovar pagamentos',
            'hr.payroll.reject' => 'Rejeitar pagamentos',
            'hr.payroll.export' => 'Exportar dados de pagamento',
            
            // Permissões de attendance/faltas
            'hr.attendance.view' => 'Ver registo de presenças',
            'hr.attendance.edit' => 'Editar registo de presenças',
            'hr.attendance.report' => 'Ver relatórios de presenças',
            
            // Acesso ao dashboard HR
            'hr.dashboard' => 'Acesso ao dashboard de RH',
            
            // Ver lista de funcionários (sem informações salariais)
            'hr.employees.view' => 'Ver funcionários',
            
            // Departamentos (necessário para contexto)
            'hr.departments.view' => 'Ver departamentos',
            
            // Permissões NEGADAS para esta role (não incluir):
            // 'hr.employees.salary.view' => 'Ver salários de funcionários',
            // 'hr.employees.salary.edit' => 'Editar salários de funcionários',
            // 'hr.payroll.view' => 'Ver detalhes da folha de pagamento',
            // 'hr.payroll.create' => 'Criar folha de pagamento',
            // 'hr.payroll.edit' => 'Editar folha de pagamento',
        ];

        // Criar permissões se não existirem
        foreach ($permissions as $permission => $description) {
            Permission::firstOrCreate(
                ['name' => $permission],
                ['guard_name' => 'web']
            );
        }

        // Criar a role hr-payroll-processor
        $role = Role::firstOrCreate(
            ['name' => 'hr-payroll-processor'],
            ['guard_name' => 'web']
        );

        // Atribuir permissões à role (APENAS processamento e attendance)
        $permissionsToAssign = [
            'hr.payroll.process',
            'hr.payroll.approve', 
            'hr.payroll.reject',
            'hr.payroll.export',
            'hr.attendance.view',
            'hr.attendance.edit',
            'hr.attendance.report',
            'hr.dashboard',
            'hr.employees.view', // Lista básica sem salários
            'hr.departments.view',
            
            // NOTA: Intencionalmente EXCLUÍDAS as seguintes permissões:
            // 'hr.employees.salary.view' - Não pode ver valores de salário
            // 'hr.employees.salary.edit' - Não pode editar salários
            // 'hr.payroll.view' - Não pode ver detalhes da folha
            // 'hr.payroll.create' - Não pode criar novas folhas
            // 'hr.payroll.edit' - Não pode editar detalhes da folha
            // 'hr.employees.create' - Não pode criar funcionários
            // 'hr.employees.edit' - Não pode editar funcionários
        ];

        $role->syncPermissions($permissionsToAssign);

        // Logs para confirmar criação
        \Log::info('Role hr-payroll-processor criada com permissões:', $permissionsToAssign);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remover a role
        $role = Role::where('name', 'hr-payroll-processor')->first();
        if ($role) {
            $role->delete();
        }

        // Remover permissões específicas criadas (opcionalmente)
        $permissionsToRemove = [
            'hr.payroll.process',
            'hr.payroll.approve',
            'hr.payroll.reject', 
            'hr.payroll.export',
            'hr.attendance.report',
        ];

        foreach ($permissionsToRemove as $permission) {
            Permission::where('name', $permission)->delete();
        }
    }
};
