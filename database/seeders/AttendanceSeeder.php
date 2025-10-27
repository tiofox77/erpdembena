<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\HR\Employee;
use App\Models\HR\Attendance;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AttendanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ana Costa - Main test employee for July 2025
        $anaCosta = Employee::where('full_name', 'Ana Costa')->first();
        
        if ($anaCosta) {
            $this->command->info('🔍 Found Ana Costa, creating attendance records...');
            
            // Create comprehensive attendance records for Ana Costa - July 2025
            $anaAttendanceData = $this->generateAnaCostaJulyAttendance($anaCosta->id);
            
            foreach ($anaAttendanceData as $attendance) {
                Attendance::updateOrCreate(
                    [
                        'employee_id' => $attendance['employee_id'],
                        'date' => $attendance['date'],
                    ],
                    $attendance
                );
            }
            
            $this->command->info('✅ Ana Costa attendance created: ' . count($anaAttendanceData) . ' records');
        }

        // Dinis Paulo - Secondary test employee (check if already exists to avoid duplicate)
        $employee = Employee::where('full_name', 'Dinis Paulo Loao Cahama')
                          ->orWhere('id_card', '123456789LA042')
                          ->first();
                          
        if (!$employee) {
            $employee = Employee::create([
                'full_name' => 'Dinis Paulo Loao Cahama',
                'date_of_birth' => '1990-05-15',
                'gender' => 'male',
                'id_card' => '123456789LA042',
                'tax_number' => '5417789654',
                'address' => 'Rua da Independência, 123, Luanda',
                'phone' => '+244 923 456 789',
                'email' => 'dinis.cahama@empresa.ao',
                'marital_status' => 'single',
                'dependents' => 0,
                'position_id' => 1, // Assuming position exists
                'department_id' => 1, // Assuming department exists
                'hire_date' => '2023-01-15',
                'employment_status' => Employee::STATUS_ACTIVE,
                'base_salary' => 180000.00,
                'food_benefit' => 25000.00,
                'transport_benefit' => 40000.00,
                'bonus_amount' => 15000.00,
            ]);
            $this->command->info('✅ Dinis Paulo employee created');
        } else {
            $this->command->info('ℹ️ Dinis Paulo already exists, using existing record');
        }

        // Create attendance records for July 2025
        $attendanceData = $this->generateJulyAttendance($employee->id);
        
        foreach ($attendanceData as $attendance) {
            Attendance::updateOrCreate(
                [
                    'employee_id' => $attendance['employee_id'],
                    'date' => $attendance['date'],
                ],
                $attendance
            );
        }

        // Create additional employees with attendance
        $additionalEmployees = [
            [
                'full_name' => 'Maria José Santos Silva',
                'id_card' => '987654321LA043', 
                'email' => 'maria.santos@empresa.ao',
                'base_salary' => 150000.00,
            ],
            [
                'full_name' => 'João Carlos Ferreira',
                'id_card' => '456789123LA044',
                'email' => 'joao.ferreira@empresa.ao', 
                'base_salary' => 200000.00,
            ],
            [
                'full_name' => 'Ana Beatriz Lopes',
                'id_card' => '789123456LA045',
                'email' => 'ana.lopes@empresa.ao',
                'base_salary' => 175000.00,
            ]
        ];

        foreach ($additionalEmployees as $employeeData) {
            $emp = Employee::firstOrCreate(
                ['full_name' => $employeeData['full_name']],
                array_merge($employeeData, [
                    'date_of_birth' => '1985-03-20',
                    'gender' => 'female',
                    'tax_number' => '5417' . rand(100000, 999999),
                    'address' => 'Luanda, Angola',
                    'phone' => '+244 9' . rand(10000000, 99999999),
                    'marital_status' => 'married',
                    'dependents' => rand(0, 3),
                    'position_id' => 1,
                    'department_id' => 1,
                    'hire_date' => '2022-06-01',
                    'employment_status' => Employee::STATUS_ACTIVE,
                    'food_benefit' => 20000.00,
                    'transport_benefit' => 30000.00,
                    'bonus_amount' => 10000.00,
                ])
            );

            // Create some attendance for this employee too
            $empAttendance = $this->generateRandomAttendance($emp->id, 15); // 15 days
            foreach ($empAttendance as $attendance) {
                Attendance::updateOrCreate(
                    [
                        'employee_id' => $attendance['employee_id'],
                        'date' => $attendance['date'],
                    ],
                    $attendance
                );
            }
        }

        $this->command->info('✅ Attendance records created successfully!');
        $this->command->info('📊 Created attendance for ' . ($additionalEmployees ? count($additionalEmployees) + 1 : 1) . ' employees');
    }

    /**
     * Generate comprehensive attendance records for Ana Costa - July 2025
     */
    private function generateAnaCostaJulyAttendance(int $employeeId): array
    {
        $attendanceRecords = [];
        $startDate = Carbon::create(2025, 7, 1);
        $endDate = Carbon::create(2025, 7, 31);
        
        $current = $startDate->copy();
        
        while ($current <= $endDate) {
            // Skip weekends for regular work
            if ($current->isWeekday()) {
                // Ana Costa has excellent attendance - mostly present with some realistic variations
                $status = $this->generateAnaCostaStatus($current);
                $timeData = $this->generateAnaCostaTimeData($status, $current);
                
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
     * Generate realistic status for Ana Costa (high performer)
     */
    private function generateAnaCostaStatus(Carbon $date): string
    {
        $random = rand(1, 100);
        
        // Ana Costa: 92% present, 5% late, 2% half_day, 1% absent
        if ($random <= 92) {
            return Attendance::STATUS_PRESENT;
        } elseif ($random <= 97) {
            return Attendance::STATUS_LATE;
        } elseif ($random <= 99) {
            return Attendance::STATUS_HALFDAY;
        } else {
            return Attendance::STATUS_ABSENT;
        }
    }

    /**
     * Generate time data for Ana Costa with some overtime patterns
     */
    private function generateAnaCostaTimeData(string $status, Carbon $date): array
    {
        $baseData = [
            'time_in' => null,
            'time_out' => null,
            'remarks' => null,
        ];
        
        switch ($status) {
            case Attendance::STATUS_PRESENT:
                $timeIn = Carbon::today()->setTime(7, rand(45, 59)); // Early arrival 7:45-7:59
                $timeOut = Carbon::today()->setTime(17, rand(15, 45)); // 17:15-17:45
                $baseData['time_in'] = $timeIn;
                $baseData['time_out'] = $timeOut;
                $baseData['remarks'] = 'Regular working day - Production Operation';
                
                // Ana Costa occasionally works overtime (30% chance)
                if (rand(1, 100) <= 30) {
                    $extraHours = rand(1, 3); // 1-3 extra hours
                    $baseData['time_out'] = $timeOut->copy()->addHours($extraHours);
                    $baseData['remarks'] = 'Extended shift - Production targets';
                }
                
                // Some days include night shift work (15% chance)
                if (rand(1, 100) <= 15) {
                    $baseData['time_out'] = Carbon::today()->setTime(23, rand(0, 30)); // Until 23:00-23:30
                    $baseData['remarks'] = 'Night shift - Production Operation';
                }
                break;
                
            case Attendance::STATUS_LATE:
                $timeIn = Carbon::today()->setTime(8, rand(15, 30)); // 8:15-8:30 (minimal delay)
                $timeOut = Carbon::today()->setTime(17, rand(30, 45)); // Compensate with later finish
                $baseData['time_in'] = $timeIn;
                $baseData['time_out'] = $timeOut;
                $baseData['remarks'] = 'Late arrival - Traffic delay';
                break;
                
            case Attendance::STATUS_HALFDAY:
                $timeIn = Carbon::today()->setTime(7, rand(45, 59));
                $timeOut = Carbon::today()->setTime(13, rand(0, 15)); // Half day until 13:00-13:15
                $baseData['time_in'] = $timeIn;
                $baseData['time_out'] = $timeOut;
                $baseData['remarks'] = 'Half day - Personal appointment';
                break;
                
            case Attendance::STATUS_ABSENT:
                $baseData['remarks'] = 'Planned absence - Personal matter';
                break;
        }
        
        return $baseData;
    }

    /**
     * Generate attendance records for July 2025 for Dinis Paulo
     */
    private function generateJulyAttendance(int $employeeId): array
    {
        $attendanceRecords = [];
        $startDate = Carbon::create(2025, 7, 1);
        $endDate = Carbon::create(2025, 7, 31);
        
        $current = $startDate->copy();
        
        while ($current <= $endDate) {
            // Skip weekends
            if ($current->isWeekday()) {
                $status = $this->generateRealisticStatus($current);
                $timeData = $this->generateTimeData($status);
                
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
     * Generate random attendance for other employees
     */
    private function generateRandomAttendance(int $employeeId, int $days): array
    {
        $attendanceRecords = [];
        $startDate = Carbon::create(2025, 7, 1);
        
        for ($i = 0; $i < $days; $i++) {
            $date = $startDate->copy()->addDays($i);
            
            // Skip weekends
            if ($date->isWeekday()) {
                $status = $this->generateRealisticStatus($date);
                $timeData = $this->generateTimeData($status);
                
                $attendanceRecords[] = [
                    'employee_id' => $employeeId,
                    'date' => $date->format('Y-m-d'),
                    'status' => $status,
                    'time_in' => $timeData['time_in'],
                    'time_out' => $timeData['time_out'],
                    'remarks' => $timeData['remarks'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }
        
        return $attendanceRecords;
    }

    /**
     * Generate realistic attendance status
     */
    private function generateRealisticStatus(Carbon $date): string
    {
        $random = rand(1, 100);
        
        // 85% present, 8% late, 5% half_day, 2% absent
        if ($random <= 85) {
            return Attendance::STATUS_PRESENT;
        } elseif ($random <= 93) {
            return Attendance::STATUS_LATE;
        } elseif ($random <= 98) {
            return Attendance::STATUS_HALFDAY;
        } else {
            return Attendance::STATUS_ABSENT;
        }
    }

    /**
     * Generate time data based on status
     */
    private function generateTimeData(string $status): array
    {
        $baseData = [
            'time_in' => null,
            'time_out' => null,
            'remarks' => null,
        ];
        
        switch ($status) {
            case Attendance::STATUS_PRESENT:
                $timeIn = Carbon::today()->setTime(8, rand(0, 30)); // 8:00-8:30
                $timeOut = Carbon::today()->setTime(17, rand(0, 30)); // 17:00-17:30
                $baseData['time_in'] = $timeIn;
                $baseData['time_out'] = $timeOut;
                $baseData['remarks'] = 'Regular working day';
                
                // Occasionally extend work hours slightly
                if (rand(1, 100) <= 20) {
                    $extraHours = rand(1, 2); // 1-2 extra hours occasionally
                    $baseData['time_out'] = $timeOut->copy()->addHours($extraHours);
                    $baseData['remarks'] = 'Extended working day';
                }
                break;
                
            case Attendance::STATUS_LATE:
                $timeIn = Carbon::today()->setTime(8, rand(31, 59)); // 8:31-8:59
                $timeOut = Carbon::today()->setTime(17, rand(0, 30));
                $baseData['time_in'] = $timeIn;
                $baseData['time_out'] = $timeOut;
                $baseData['remarks'] = 'Late arrival';
                break;
                
            case Attendance::STATUS_HALFDAY:
                $timeIn = Carbon::today()->setTime(8, rand(0, 30));
                $timeOut = Carbon::today()->setTime(12, rand(0, 30)); // Half day
                $baseData['time_in'] = $timeIn;
                $baseData['time_out'] = $timeOut;
                $baseData['remarks'] = 'Half day work';
                break;
                
            case Attendance::STATUS_ABSENT:
                $baseData['remarks'] = 'Absent without notification';
                break;
        }
        
        return $baseData;
    }
}
