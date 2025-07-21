<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\HR\Employee;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CreateAttendanceData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hr:create-attendance-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create test attendance data for Dinis Paulo Loao Cahama and other employees';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('📊 Creating attendance and employee data...');

        // Create or find Dinis Paulo
        $employee = Employee::updateOrCreate(
            ['full_name' => 'Dinis Paulo Loao Cahama'],
            [
                'date_of_birth' => '1990-05-15',
                'gender' => 'male',
                'id_card' => '123456789LA042',
                'tax_number' => '5417789654',
                'address' => 'Rua da Independencia, 123, Luanda',
                'phone' => '+244 923 456 789',
                'email' => 'dinis.cahama@empresa.ao',
                'marital_status' => 'single',
                'dependents' => 0,
                'position_id' => 1,
                'department_id' => 1,
                'hire_date' => '2023-01-15',
                'employment_status' => 'active',
                'base_salary' => 180000.00,
                'food_benefit' => 25000.00,
                'transport_benefit' => 40000.00,
                'bonus_amount' => 15000.00,
            ]
        );

        $this->info('✅ Employee created/updated: ' . $employee->full_name . ' (ID: ' . $employee->id . ')');

        // Clear existing attendance for this employee in July 2025
        DB::table('attendances')
            ->where('employee_id', $employee->id)
            ->whereYear('date', 2025)
            ->whereMonth('date', 7)
            ->delete();

        // Create attendance data for July 2025
        $attendanceData = $this->generateJulyAttendance($employee->id);
        
        foreach ($attendanceData as $attendance) {
            DB::table('attendances')->insert($attendance);
        }

        $this->info('✅ Created ' . count($attendanceData) . ' attendance records for July 2025');
        
        // Show summary
        $summary = $this->getAttendanceSummary($employee->id);
        $this->table(
            ['Metric', 'Value'],
            [
                ['Employee', $employee->full_name],
                ['Period', 'July 2025'],
                ['Total Records', $summary['total_records']],
                ['Present Days', $summary['present']],
                ['Late Days', $summary['late']],
                ['Half Days', $summary['half_day']],
                ['Absent Days', $summary['absent']],
            ]
        );

        return Command::SUCCESS;
    }

    /**
     * Generate attendance records for July 2025
     */
    private function generateJulyAttendance(int $employeeId): array
    {
        $attendanceRecords = [];
        $startDate = Carbon::create(2025, 7, 1);
        $endDate = Carbon::create(2025, 7, 31);
        
        $current = $startDate->copy();
        
        while ($current <= $endDate) {
            // Only create records for weekdays
            if ($current->isWeekday()) {
                $status = $this->generateRealisticStatus();
                $timeData = $this->generateTimeData($status, $current);
                
                $attendanceRecords[] = [
                    'employee_id' => $employeeId,
                    'date' => $current->format('Y-m-d'),
                    'status' => $status,
                    'time_in' => $timeData['time_in'],
                    'time_out' => $timeData['time_out'],
                    'remarks' => $timeData['remarks'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            
            $current->addDay();
        }
        
        return $attendanceRecords;
    }

    /**
     * Generate realistic attendance status
     */
    private function generateRealisticStatus(): string
    {
        $random = rand(1, 100);
        
        // 80% present, 10% late, 8% half_day, 2% absent
        if ($random <= 80) {
            return 'present';
        } elseif ($random <= 90) {
            return 'late';
        } elseif ($random <= 98) {
            return 'half_day';
        } else {
            return 'absent';
        }
    }

    /**
     * Generate time data based on status
     */
    private function generateTimeData(string $status, Carbon $date): array
    {
        $baseData = [
            'time_in' => null,
            'time_out' => null,
            'remarks' => null,
        ];
        
        switch ($status) {
            case 'present':
                $timeIn = $date->copy()->setTime(8, rand(0, 15)); // 8:00-8:15
                $timeOut = $date->copy()->setTime(17, rand(0, 30)); // 17:00-17:30
                $baseData['time_in'] = $timeIn->format('Y-m-d H:i:s');
                $baseData['time_out'] = $timeOut->format('Y-m-d H:i:s');
                $baseData['remarks'] = 'Regular working day';
                break;
                
            case 'late':
                $timeIn = $date->copy()->setTime(8, rand(31, 59)); // 8:31-8:59
                $timeOut = $date->copy()->setTime(17, rand(0, 30));
                $baseData['time_in'] = $timeIn->format('Y-m-d H:i:s');
                $baseData['time_out'] = $timeOut->format('Y-m-d H:i:s');
                $baseData['remarks'] = 'Late arrival';
                break;
                
            case 'half_day':
                $timeIn = $date->copy()->setTime(8, rand(0, 30));
                $timeOut = $date->copy()->setTime(12, rand(0, 30)); // Half day
                $baseData['time_in'] = $timeIn->format('Y-m-d H:i:s');
                $baseData['time_out'] = $timeOut->format('Y-m-d H:i:s');
                $baseData['remarks'] = 'Half day work';
                break;
                
            case 'absent':
                $baseData['remarks'] = 'Absent without notification';
                break;
        }
        
        return $baseData;
    }

    /**
     * Get attendance summary for verification
     */
    private function getAttendanceSummary(int $employeeId): array
    {
        $records = DB::table('attendances')
            ->where('employee_id', $employeeId)
            ->whereYear('date', 2025)
            ->whereMonth('date', 7)
            ->get();

        return [
            'total_records' => $records->count(),
            'present' => $records->where('status', 'present')->count(),
            'late' => $records->where('status', 'late')->count(),
            'half_day' => $records->where('status', 'half_day')->count(),
            'absent' => $records->where('status', 'absent')->count(),
        ];
    }
}
