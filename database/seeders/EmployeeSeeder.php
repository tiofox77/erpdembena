<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\HR\Employee;
use App\Models\HR\Department;
use Carbon\Carbon;

class EmployeeSeeder extends Seeder
{
    public function run()
    {
        // Verificar se existem departamentos
        $departments = Department::all();
        if ($departments->isEmpty()) {
            // Criar departamentos básicos
            $departments = collect([
                Department::create([
                    'name' => 'Recursos Humanos',
                    'description' => 'Departamento de RH',
                    'is_active' => true,
                ]),
                Department::create([
                    'name' => 'Produção',
                    'description' => 'Departamento de Produção',
                    'is_active' => true,
                ]),
                Department::create([
                    'name' => 'Vendas',
                    'description' => 'Departamento de Vendas',
                    'is_active' => true,
                ]),
            ]);
        }

        // Funcionários de teste
        $employees = [
            [
                'full_name' => 'João Silva',
                'email' => 'joao.silva@empresa.com',
                'phone' => '912345678',
                'department_id' => $departments->first()->id,
                'employment_status' => 'active',
                'hire_date' => Carbon::now()->subMonths(6),
            ],
            [
                'full_name' => 'Maria Santos',
                'email' => 'maria.santos@empresa.com',
                'phone' => '923456789',
                'department_id' => $departments->skip(1)->first()->id,
                'employment_status' => 'active',
                'hire_date' => Carbon::now()->subMonths(3),
            ],
            [
                'full_name' => 'Pedro Ferreira',
                'email' => 'pedro.ferreira@empresa.com',
                'phone' => '934567890',
                'department_id' => $departments->skip(2)->first()->id,
                'employment_status' => 'active',
                'hire_date' => Carbon::now()->subMonths(2),
            ],
            [
                'full_name' => 'Ana Costa',
                'email' => 'ana.costa@empresa.com',
                'phone' => '945678901',
                'department_id' => $departments->first()->id,
                'employment_status' => 'active',
                'hire_date' => Carbon::now()->subMonths(4),
            ],
            [
                'full_name' => 'Carlos Oliveira',
                'email' => 'carlos.oliveira@empresa.com',
                'phone' => '956789012',
                'department_id' => $departments->skip(1)->first()->id,
                'employment_status' => 'active',
                'hire_date' => Carbon::now()->subMonths(1),
            ],
        ];

        foreach ($employees as $employeeData) {
            Employee::create($employeeData);
        }
    }
}
