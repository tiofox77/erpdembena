<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\HR\Employee;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class VerifyAttendanceData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hr:verify-attendance-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verify attendance data for Dinis Paulo Loao Cahama';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🔍 Verifying attendance data...');

        $employee = Employee::where('full_name', 'Dinis Paulo Loao Cahama')->first();
        
        if (!$employee) {
            $this->error('❌ Employee "Dinis Paulo Loao Cahama" not found!');
            return Command::FAILURE;
        }

        $this->info('✅ Employee found: ' . $employee->full_name . ' (ID: ' . $employee->id . ')');

        // Check July 2025 attendance
        $attendances = DB::table('attendances')
            ->where('employee_id', $employee->id)
            ->whereYear('date', 2025)
            ->whereMonth('date', 7)
            ->orderBy('date')
            ->get();

        $this->info('📊 July 2025 Attendance Summary:');
        $this->table(
            ['Metric', 'Count'],
            [
                ['Total Records', $attendances->count()],
                ['Present Days', $attendances->where('status', 'present')->count()],
                ['Late Days', $attendances->where('status', 'late')->count()],
                ['Half Days', $attendances->where('status', 'half_day')->count()],
                ['Absent Days', $attendances->where('status', 'absent')->count()],
            ]
        );

        // Show first few records for verification
        if ($attendances->isNotEmpty()) {
            $this->info('🗓️ First 5 Attendance Records:');
            $firstFive = $attendances->take(5);
            $this->table(
                ['Date', 'Status', 'Time In', 'Time Out', 'Remarks'],
                $firstFive->map(fn($att) => [
                    $att->date,
                    $att->status,
                    $att->time_in ?? 'N/A',
                    $att->time_out ?? 'N/A',
                    $att->remarks ?? 'N/A'
                ])->toArray()
            );
        }

        // Check working days calculation
        $workingDays = 0;
        $current = \Carbon\Carbon::create(2025, 7, 1);
        $end = \Carbon\Carbon::create(2025, 7, 31);
        
        while ($current <= $end) {
            if ($current->isWeekday()) {
                $workingDays++;
            }
            $current->addDay();
        }

        $this->info('🗅 Working Days in July 2025: ' . $workingDays);
        $this->info('🎯 Total Present/Late/Half Days: ' . ($attendances->whereIn('status', ['present', 'late', 'half_day'])->count()));
        $this->info('😴 Calculated Absent Days: ' . ($workingDays - $attendances->whereIn('status', ['present', 'late', 'half_day'])->count()));

        // Check leave data
        $this->info('');
        $this->info('🏖️ Leave Records Verification:');
        
        $leaves = DB::table('leaves')
            ->where('employee_id', $employee->id)
            ->join('leave_types', 'leaves.leave_type_id', '=', 'leave_types.id')
            ->select('leaves.*', 'leave_types.name as type_name')
            ->orderBy('start_date')
            ->get();

        if ($leaves->isEmpty()) {
            $this->warn('⚠️ No leave records found');
        } else {
            $this->info('✅ Found ' . $leaves->count() . ' leave records:');
            $this->table(
                ['Type', 'Start Date', 'End Date', 'Days', 'Status', 'Reason'],
                $leaves->map(fn($leave) => [
                    $leave->type_name,
                    $leave->start_date,
                    $leave->end_date,
                    $leave->total_days,
                    $leave->status,
                    substr($leave->reason, 0, 30) . '...'
                ])->toArray()
            );
        }

        return Command::SUCCESS;
    }
}
