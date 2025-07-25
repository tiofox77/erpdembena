<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\HR\Employee;
use App\Models\HR\Leave;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LeaveSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get employees to create leave records for
        $employees = Employee::whereIn('full_name', [
            'Dinis Paulo Loao Cahama',
            'Maria José Santos Silva',
            'João Carlos Ferreira',
            'Ana Beatriz Lopes'
        ])->get();

        if ($employees->isEmpty()) {
            $this->command->warn('⚠️  No employees found. Run AttendanceSeeder first.');
            return;
        }

        foreach ($employees as $employee) {
            $this->createLeavesForEmployee($employee);
        }

        $this->command->info('✅ Leave records created successfully!');
        $this->command->info('🏖️ Created leave records for ' . $employees->count() . ' employees');
    }

    /**
     * Create various types of leave records for an employee
     */
    private function createLeavesForEmployee(Employee $employee): void
    {
        $leaveTypes = [
            [
                'type' => 'annual',
                'start_date' => '2025-06-15',
                'end_date' => '2025-06-19',
                'days' => 5,
                'reason' => 'Annual vacation leave',
                'status' => 'approved',
            ],
            [
                'type' => 'sick',
                'start_date' => '2025-07-08',
                'end_date' => '2025-07-09',
                'days' => 2,
                'reason' => 'Medical appointment and recovery',
                'status' => 'approved',
            ],
            [
                'type' => 'personal',
                'start_date' => '2025-07-22',
                'end_date' => '2025-07-22',
                'days' => 1,
                'reason' => 'Personal matters',
                'status' => 'pending',
            ],
            [
                'type' => 'maternity',
                'start_date' => '2025-08-01',
                'end_date' => '2025-10-30',
                'days' => 90,
                'reason' => 'Maternity leave',
                'status' => 'approved',
            ] // Only for female employees
        ];

        foreach ($leaveTypes as $index => $leaveData) {
            // Skip maternity leave for male employees
            if ($leaveData['type'] === 'maternity' && $employee->gender === 'male') {
                continue;
            }

            // Skip some leaves randomly to create variety
            if (rand(1, 100) <= 30) { // 30% chance to skip
                continue;
            }

            Leave::updateOrCreate(
                [
                    'employee_id' => $employee->id,
                    'start_date' => $leaveData['start_date'],
                    'end_date' => $leaveData['end_date'],
                ],
                [
                    'type' => $leaveData['type'],
                    'days' => $leaveData['days'],
                    'reason' => $leaveData['reason'],
                    'status' => $leaveData['status'],
                    'applied_date' => Carbon::parse($leaveData['start_date'])->subDays(rand(7, 30)),
                    'approved_by' => 1, // Admin user
                    'approved_date' => $leaveData['status'] === 'approved' 
                        ? Carbon::parse($leaveData['start_date'])->subDays(rand(1, 7))
                        : null,
                    'remarks' => 'Generated by seeder',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        // Add some additional random leaves for variety
        if (rand(1, 100) <= 50) { // 50% chance
            $this->createRandomLeave($employee);
        }
    }

    /**
     * Create a random leave record
     */
    private function createRandomLeave(Employee $employee): void
    {
        $types = ['annual', 'sick', 'personal', 'emergency'];
        $type = $types[array_rand($types)];
        
        $startDate = Carbon::create(2025, rand(5, 8), rand(1, 28));
        $days = rand(1, 7); // 1-7 days
        $endDate = $startDate->copy()->addDays($days - 1);
        
        $statuses = ['pending', 'approved', 'rejected'];
        $status = $statuses[array_rand($statuses)];
        
        Leave::updateOrCreate(
            [
                'employee_id' => $employee->id,
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
            ],
            [
                'type' => $type,
                'days' => $days,
                'reason' => $this->generateLeaveReason($type),
                'status' => $status,
                'applied_date' => $startDate->copy()->subDays(rand(7, 30)),
                'approved_by' => $status !== 'pending' ? 1 : null,
                'approved_date' => $status === 'approved' 
                    ? $startDate->copy()->subDays(rand(1, 7))
                    : null,
                'remarks' => 'Random leave generated by seeder',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    /**
     * Generate appropriate reason based on leave type
     */
    private function generateLeaveReason(string $type): string
    {
        $reasons = [
            'annual' => [
                'Family vacation',
                'Rest and relaxation',
                'Travel to home country',
                'Personal time off'
            ],
            'sick' => [
                'Medical treatment',
                'Doctor appointment',
                'Recovery from illness',
                'Health check-up'
            ],
            'personal' => [
                'Family emergency',
                'Personal matters',
                'Family appointment',
                'Important personal business'
            ],
            'emergency' => [
                'Family emergency',
                'Urgent personal matter',
                'Medical emergency',
                'Unexpected situation'
            ]
        ];

        $typeReasons = $reasons[$type] ?? $reasons['personal'];
        return $typeReasons[array_rand($typeReasons)];
    }
}
