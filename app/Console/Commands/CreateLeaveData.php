<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\HR\Employee;
use App\Models\HR\LeaveType;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CreateLeaveData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hr:create-leave-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create test leave data for employees';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🏖️ Creating leave data...');

        // Get employees
        $employees = Employee::whereIn('full_name', [
            'Dinis Paulo Loao Cahama',
            'Maria José Santos Silva',
            'João Carlos Ferreira',
            'Ana Beatriz Lopes'
        ])->get();

        if ($employees->isEmpty()) {
            $this->warn('⚠️  No employees found. Run hr:create-attendance-data first.');
            return Command::FAILURE;
        }

        // Create basic leave types if they don't exist
        $this->createLeaveTypes();

        foreach ($employees as $employee) {
            $this->createLeavesForEmployee($employee);
        }

        $this->info('✅ Leave records created successfully!');
        $this->info('📊 Created leave records for ' . $employees->count() . ' employees');

        return Command::SUCCESS;
    }

    /**
     * Create basic leave types
     */
    private function createLeaveTypes(): void
    {
        $leaveTypes = [
            [
                'name' => 'Annual Leave',
                'days_allowed' => 22,
                'is_paid' => true,
                'requires_approval' => true,
                'description' => 'Annual vacation leave'
            ],
            [
                'name' => 'Sick Leave',
                'days_allowed' => 30,
                'is_paid' => true,
                'requires_approval' => false,
                'description' => 'Medical leave'
            ],
            [
                'name' => 'Personal Leave',
                'days_allowed' => 5,
                'is_paid' => false,
                'requires_approval' => true,
                'description' => 'Personal matters'
            ],
            [
                'name' => 'Maternity Leave',
                'days_allowed' => 120,
                'is_paid' => true,
                'requires_approval' => true,
                'description' => 'Maternity leave'
            ]
        ];

        foreach ($leaveTypes as $typeData) {
            LeaveType::updateOrCreate(
                ['name' => $typeData['name']],
                $typeData
            );
        }

        $this->info('✅ Leave types created/updated');
    }

    /**
     * Create leaves for an employee
     */
    private function createLeavesForEmployee(Employee $employee): void
    {
        $leaveTypes = LeaveType::all();
        
        if ($leaveTypes->isEmpty()) {
            return;
        }

        $leaveData = [
            [
                'leave_type_id' => $leaveTypes->where('name', 'Annual Leave')->first()?->id ?? 1,
                'start_date' => '2025-06-15',
                'end_date' => '2025-06-19',
                'total_days' => 5,
                'reason' => 'Annual vacation with family',
                'status' => 'approved',
            ],
            [
                'leave_type_id' => $leaveTypes->where('name', 'Sick Leave')->first()?->id ?? 2,
                'start_date' => '2025-07-08',
                'end_date' => '2025-07-09',
                'total_days' => 2,
                'reason' => 'Medical appointment and recovery',
                'status' => 'approved',
            ],
            [
                'leave_type_id' => $leaveTypes->where('name', 'Personal Leave')->first()?->id ?? 3,
                'start_date' => '2025-07-22',
                'end_date' => '2025-07-22',
                'total_days' => 1,
                'reason' => 'Personal family matter',
                'status' => 'pending',
            ]
        ];

        // Add maternity leave only for female employees
        if ($employee->gender === 'female') {
            $leaveData[] = [
                'leave_type_id' => $leaveTypes->where('name', 'Maternity Leave')->first()?->id ?? 4,
                'start_date' => '2025-08-01',
                'end_date' => '2025-11-29',
                'total_days' => 120,
                'reason' => 'Maternity leave',
                'status' => 'approved',
            ];
        }

        foreach ($leaveData as $leave) {
            DB::table('leaves')->updateOrInsert(
                [
                    'employee_id' => $employee->id,
                    'start_date' => $leave['start_date'],
                    'end_date' => $leave['end_date'],
                ],
                array_merge($leave, [
                    'employee_id' => $employee->id,
                    'approved_by' => $leave['status'] === 'approved' ? $employee->id : null, // Use employee ID or null
                    'approved_date' => $leave['status'] === 'approved' 
                        ? Carbon::parse($leave['start_date'])->subDays(3)->format('Y-m-d')
                        : null,
                    'is_paid_leave' => true,
                    'payment_percentage' => 100.00,
                    'affects_payroll' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }

        $this->info('✅ Created leave records for: ' . $employee->full_name);
    }
}
