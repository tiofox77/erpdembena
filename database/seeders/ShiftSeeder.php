<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\HR\Shift;
use App\Models\HR\ShiftAssignment;
use App\Models\HR\Employee;
use Carbon\Carbon;

class ShiftSeeder extends Seeder
{
    public function run()
    {
        // Criar shifts básicos
        $shifts = [
            [
                'name' => 'Turno da Manhã',
                'start_time' => Carbon::createFromTime(8, 0, 0),
                'end_time' => Carbon::createFromTime(16, 0, 0),
                'break_duration' => 60,
                'description' => 'Turno regular da manhã',
                'is_night_shift' => false,
                'is_active' => true,
            ],
            [
                'name' => 'Turno da Tarde',
                'start_time' => Carbon::createFromTime(16, 0, 0),
                'end_time' => Carbon::createFromTime(24, 0, 0),
                'break_duration' => 60,
                'description' => 'Turno regular da tarde',
                'is_night_shift' => false,
                'is_active' => true,
            ],
            [
                'name' => 'Turno da Noite',
                'start_time' => Carbon::createFromTime(0, 0, 0),
                'end_time' => Carbon::createFromTime(8, 0, 0),
                'break_duration' => 60,
                'description' => 'Turno regular da noite',
                'is_night_shift' => true,
                'is_active' => true,
            ],
        ];

        foreach ($shifts as $shiftData) {
            $shift = Shift::create($shiftData);
            
            // Associar alguns funcionários aos shifts
            $employees = Employee::where('employment_status', 'active')
                ->limit(5)
                ->get();
                
            foreach ($employees as $employee) {
                ShiftAssignment::create([
                    'employee_id' => $employee->id,
                    'shift_id' => $shift->id,
                    'start_date' => Carbon::now()->subDays(30),
                    'end_date' => null,
                    'is_permanent' => true,
                    'assigned_by' => 1,
                    'notes' => 'Atribuição automática pelo seeder',
                ]);
            }
        }
    }
}
