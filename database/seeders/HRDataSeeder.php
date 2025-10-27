<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\HR\Department;
use App\Models\HR\JobCategory;
use App\Models\HR\JobPosition;
use App\Models\HR\LeaveType;

class HRDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create default departments
        $departments = [
            ['name' => 'Administration', 'description' => 'Administrative department'],
            ['name' => 'Human Resources', 'description' => 'HR department'],
            ['name' => 'Finance', 'description' => 'Finance and accounting department'],
            ['name' => 'Information Technology', 'description' => 'IT department'],
            ['name' => 'Operations', 'description' => 'Operations department'],
            ['name' => 'Marketing', 'description' => 'Marketing and sales department'],
            ['name' => 'Research & Development', 'description' => 'R&D department'],
            ['name' => 'Customer Service', 'description' => 'Customer service department'],
        ];

        foreach ($departments as $department) {
            Department::firstOrCreate(
                ['name' => $department['name']],
                ['description' => $department['description']]
            );
        }

        // Create default job categories
        $jobCategories = [
            [
                'name' => 'Executive', 
                'description' => 'Top-level management positions',
                'salary_grade' => 'E1-E3'
            ],
            [
                'name' => 'Management', 
                'description' => 'Mid-level management positions',
                'salary_grade' => 'M1-M5'
            ],
            [
                'name' => 'Professional', 
                'description' => 'Professional positions requiring specialized skills',
                'salary_grade' => 'P1-P6'
            ],
            [
                'name' => 'Technical', 
                'description' => 'Roles requiring technical expertise',
                'salary_grade' => 'T1-T5'
            ],
            [
                'name' => 'Administrative', 
                'description' => 'Administrative and support roles',
                'salary_grade' => 'A1-A4'
            ],
            [
                'name' => 'Operational', 
                'description' => 'Roles focused on operations and physical tasks',
                'salary_grade' => 'O1-O3'
            ],
        ];

        foreach ($jobCategories as $category) {
            JobCategory::firstOrCreate(
                ['name' => $category['name']],
                [
                    'description' => $category['description'],
                    'salary_grade' => $category['salary_grade']
                ]
            );
        }

        // Create default leave types
        $leaveTypes = [
            [
                'name' => 'Annual Leave',
                'description' => 'Regular vacation days',
                'days_allowed' => 22,
                'is_paid' => true
            ],
            [
                'name' => 'Sick Leave',
                'description' => 'Leave for health-related issues',
                'days_allowed' => 15,
                'is_paid' => true
            ],
            [
                'name' => 'Maternity Leave',
                'description' => 'Leave for new mothers',
                'days_allowed' => 90,
                'is_paid' => true
            ],
            [
                'name' => 'Paternity Leave',
                'description' => 'Leave for new fathers',
                'days_allowed' => 10,
                'is_paid' => true
            ],
            [
                'name' => 'Bereavement Leave',
                'description' => 'Leave due to death in family',
                'days_allowed' => 5,
                'is_paid' => true
            ],
            [
                'name' => 'Unpaid Leave',
                'description' => 'Leave without pay',
                'days_allowed' => 30,
                'is_paid' => false
            ],
        ];

        foreach ($leaveTypes as $type) {
            LeaveType::firstOrCreate(
                ['name' => $type['name']],
                [
                    'description' => $type['description'],
                    'days_allowed' => $type['days_allowed'],
                    'is_paid' => $type['is_paid'],
                    'requires_approval' => true,
                    'is_active' => true
                ]
            );
        }
    }
}
