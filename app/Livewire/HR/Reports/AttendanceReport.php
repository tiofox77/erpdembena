<?php

declare(strict_types=1);

namespace App\Livewire\HR\Reports;

use App\Models\HR\Attendance;
use App\Models\HR\Employee;
use App\Models\HR\Department;
use App\Models\HR\Leave;
use App\Models\HR\LeaveType;
use Livewire\Component;
use Livewire\WithPagination;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AttendanceReport extends Component
{
    use WithPagination;

    public string $period = 'current_month';
    public $startDate;
    public $endDate;
    public string $customStartDate = '';
    public string $customEndDate = '';
    public string $departmentFilter = '';
    public string $employeeFilter = '';
    public string $statusFilter = '';
    public string $viewMode = 'detailed'; // 'detailed', 'summary', or 'calendar'
    public int $calendarMonth;
    public int $calendarYear;
    public string $calendarEmployeeFilter = '';

    public function mount()
    {
        $this->calendarMonth = (int) now()->month;
        $this->calendarYear = (int) now()->year;
        $this->updateDateRange();
    }

    public function updatedPeriod()
    {
        if ($this->period !== 'custom') {
            $this->updateDateRange();
        }
        $this->resetPage();
    }

    public function updatedCustomStartDate()
    {
        if ($this->period === 'custom' && $this->customStartDate && $this->customEndDate) {
            $this->startDate = Carbon::parse($this->customStartDate);
            $this->endDate = Carbon::parse($this->customEndDate);
            $this->resetPage();
        }
    }

    public function updatedCustomEndDate()
    {
        if ($this->period === 'custom' && $this->customStartDate && $this->customEndDate) {
            $this->startDate = Carbon::parse($this->customStartDate);
            $this->endDate = Carbon::parse($this->customEndDate);
            $this->resetPage();
        }
    }

    public function updatedDepartmentFilter()
    {
        $this->resetPage();
    }

    public function updatedEmployeeFilter()
    {
        $this->resetPage();
    }

    public function updatedStatusFilter()
    {
        $this->resetPage();
    }

    public function updatedViewMode()
    {
        $this->resetPage();
    }

    public function updatedCalendarEmployeeFilter()
    {
        // no-op, reactivity handles it
    }

    public function previousMonth()
    {
        $date = Carbon::create($this->calendarYear, $this->calendarMonth, 1)->subMonth();
        $this->calendarMonth = (int) $date->month;
        $this->calendarYear = (int) $date->year;
    }

    public function nextMonth()
    {
        $date = Carbon::create($this->calendarYear, $this->calendarMonth, 1)->addMonth();
        $this->calendarMonth = (int) $date->month;
        $this->calendarYear = (int) $date->year;
    }

    public function goToCurrentMonth()
    {
        $this->calendarMonth = (int) now()->month;
        $this->calendarYear = (int) now()->year;
    }

    private function updateDateRange()
    {
        $now = Carbon::now();

        switch ($this->period) {
            case 'current_month':
                $this->startDate = $now->copy()->startOfMonth();
                $this->endDate = $now->copy()->endOfMonth();
                break;
            case 'last_month':
                $this->startDate = $now->copy()->subMonth()->startOfMonth();
                $this->endDate = $now->copy()->subMonth()->endOfMonth();
                break;
            case 'current_quarter':
                $this->startDate = $now->copy()->startOfQuarter();
                $this->endDate = $now->copy()->endOfQuarter();
                break;
            case 'current_year':
                $this->startDate = $now->copy()->startOfYear();
                $this->endDate = $now->copy()->endOfYear();
                break;
            case 'custom':
                // Keep current dates
                break;
            default:
                $this->startDate = $now->copy()->startOfMonth();
                $this->endDate = $now->copy()->endOfMonth();
        }
    }

    /**
     * Detailed attendance records (paginated)
     */
    public function getAttendanceRecordsProperty()
    {
        $query = Attendance::with(['employee.department'])
            ->whereBetween('date', [$this->startDate->format('Y-m-d'), $this->endDate->format('Y-m-d')]);

        if ($this->departmentFilter) {
            $query->whereHas('employee', function ($q) {
                $q->where('department_id', $this->departmentFilter);
            });
        }

        if ($this->employeeFilter) {
            $query->whereHas('employee', function ($q) {
                $q->where('full_name', 'like', '%' . $this->employeeFilter . '%');
            });
        }

        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        return $query->orderBy('date', 'desc')->orderBy('employee_id')->paginate(25);
    }

    /**
     * Employee summary view (paginated) - aggregated per employee
     */
    public function getEmployeeSummaryProperty()
    {
        $start = $this->startDate->format('Y-m-d');
        $end = $this->endDate->format('Y-m-d');

        $query = Employee::where('employment_status', 'active')
            ->with('department');

        if ($this->departmentFilter) {
            $query->where('department_id', $this->departmentFilter);
        }

        if ($this->employeeFilter) {
            $query->where('full_name', 'like', '%' . $this->employeeFilter . '%');
        }

        $employees = $query->orderBy('full_name')->paginate(20);

        // Get attendance stats for these employees
        $employeeIds = $employees->pluck('id')->toArray();

        $attendanceStats = Attendance::whereIn('employee_id', $employeeIds)
            ->whereBetween('date', [$start, $end])
            ->select(
                'employee_id',
                DB::raw("SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present_count"),
                DB::raw("SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as absent_count"),
                DB::raw("SUM(CASE WHEN status = 'late' THEN 1 ELSE 0 END) as late_count"),
                DB::raw("SUM(CASE WHEN status = 'half_day' THEN 1 ELSE 0 END) as half_day_count"),
                DB::raw("SUM(CASE WHEN status = 'leave' THEN 1 ELSE 0 END) as leave_count"),
                DB::raw('COUNT(*) as total_records')
            )
            ->groupBy('employee_id')
            ->get()
            ->keyBy('employee_id');

        // Get approved leave days for these employees
        $leaveStats = Leave::whereIn('employee_id', $employeeIds)
            ->where('status', 'approved')
            ->where('start_date', '<=', $end)
            ->where('end_date', '>=', $start)
            ->with('leaveType')
            ->get()
            ->groupBy('employee_id');

        // Attach stats to each employee
        $employees->getCollection()->transform(function ($employee) use ($attendanceStats, $leaveStats, $start, $end) {
            $stats = $attendanceStats->get($employee->id);
            $employee->attendance_stats = [
                'present' => $stats->present_count ?? 0,
                'absent' => $stats->absent_count ?? 0,
                'late' => $stats->late_count ?? 0,
                'half_day' => $stats->half_day_count ?? 0,
                'leave_attendance' => $stats->leave_count ?? 0,
                'total_records' => $stats->total_records ?? 0,
            ];

            // Calculate leave days from Leave Management
            $employeeLeaves = $leaveStats->get($employee->id, collect());
            $leaveDaysInPeriod = 0;
            foreach ($employeeLeaves as $leave) {
                $leaveStart = Carbon::parse($leave->start_date);
                $leaveEnd = Carbon::parse($leave->end_date);
                $effectiveStart = $leaveStart->greaterThan(Carbon::parse($start)) ? $leaveStart->copy() : Carbon::parse($start);
                $effectiveEnd = $leaveEnd->lessThan(Carbon::parse($end)) ? $leaveEnd->copy() : Carbon::parse($end);

                $current = $effectiveStart->copy();
                while ($current->lte($effectiveEnd)) {
                    if ($current->isWeekday()) {
                        $leaveDaysInPeriod++;
                    }
                    $current->addDay();
                }
            }
            $employee->attendance_stats['leave_management'] = $leaveDaysInPeriod;

            return $employee;
        });

        return $employees;
    }

    /**
     * Summary statistics cards
     */
    public function getSummaryProperty()
    {
        $start = $this->startDate->format('Y-m-d');
        $end = $this->endDate->format('Y-m-d');

        $query = Attendance::whereBetween('date', [$start, $end]);

        if ($this->departmentFilter) {
            $query->whereHas('employee', function ($q) {
                $q->where('department_id', $this->departmentFilter);
            });
        }

        $totalRecords = (clone $query)->count();
        $presentCount = (clone $query)->where('status', 'present')->count();
        $absentCount = (clone $query)->where('status', 'absent')->count();
        $lateCount = (clone $query)->where('status', 'late')->count();
        $halfDayCount = (clone $query)->where('status', 'half_day')->count();
        $leaveCount = (clone $query)->where('status', 'leave')->count();

        // Active employees count
        $employeeQuery = Employee::where('employment_status', 'active');
        if ($this->departmentFilter) {
            $employeeQuery->where('department_id', $this->departmentFilter);
        }
        $activeEmployees = $employeeQuery->count();

        // Leave Management approved leaves in period
        $leaveQuery = Leave::where('status', 'approved')
            ->where('start_date', '<=', $end)
            ->where('end_date', '>=', $start);
        if ($this->departmentFilter) {
            $leaveQuery->whereHas('employee', function ($q) {
                $q->where('department_id', $this->departmentFilter);
            });
        }
        $approvedLeaves = $leaveQuery->count();

        // Attendance rate
        $attendanceRate = $totalRecords > 0
            ? round((($presentCount + $lateCount + $leaveCount) / $totalRecords) * 100, 1)
            : 0;

        return [
            'total_records' => $totalRecords,
            'present_count' => $presentCount,
            'absent_count' => $absentCount,
            'late_count' => $lateCount,
            'half_day_count' => $halfDayCount,
            'leave_count' => $leaveCount,
            'active_employees' => $activeEmployees,
            'approved_leaves' => $approvedLeaves,
            'attendance_rate' => $attendanceRate,
        ];
    }

    /**
     * Department breakdown
     */
    public function getDepartmentBreakdownProperty()
    {
        $start = $this->startDate->format('Y-m-d');
        $end = $this->endDate->format('Y-m-d');

        return Department::withCount([
            'employees as active_employees_count' => function ($q) {
                $q->where('employment_status', 'active');
            }
        ])
        ->get()
        ->map(function ($dept) use ($start, $end) {
            $attendances = Attendance::whereHas('employee', function ($q) use ($dept) {
                $q->where('department_id', $dept->id);
            })->whereBetween('date', [$start, $end]);

            $total = (clone $attendances)->count();

            $dept->stats = [
                'total' => $total,
                'present' => (clone $attendances)->where('status', 'present')->count(),
                'absent' => (clone $attendances)->where('status', 'absent')->count(),
                'late' => (clone $attendances)->where('status', 'late')->count(),
                'leave' => (clone $attendances)->where('status', 'leave')->count(),
                'rate' => $total > 0
                    ? round(((clone $attendances)->whereIn('status', ['present', 'late', 'leave'])->count() / $total) * 100, 1)
                    : 0,
            ];

            return $dept;
        })
        ->filter(fn($d) => $d->active_employees_count > 0)
        ->sortByDesc(fn($d) => $d->stats['rate']);
    }

    /**
     * Calendar data: builds a grid of days with attendance info per employee
     */
    public function getCalendarDataProperty()
    {
        $monthStart = Carbon::create($this->calendarYear, $this->calendarMonth, 1)->startOfMonth();
        $monthEnd = $monthStart->copy()->endOfMonth();
        $daysInMonth = $monthEnd->day;

        // Build array of days
        $days = [];
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $date = Carbon::create($this->calendarYear, $this->calendarMonth, $d);
            $days[] = [
                'day' => $d,
                'date' => $date->format('Y-m-d'),
                'dow' => $date->dayOfWeek, // 0=Sun, 6=Sat
                'is_weekend' => $date->isWeekend(),
                'label' => $date->translatedFormat('D'),
            ];
        }

        // Get employees
        $empQuery = Employee::where('employment_status', 'active')->with('department');
        if ($this->departmentFilter) {
            $empQuery->where('department_id', $this->departmentFilter);
        }
        if ($this->calendarEmployeeFilter) {
            $empQuery->where('full_name', 'like', '%' . $this->calendarEmployeeFilter . '%');
        }
        $employees = $empQuery->orderBy('full_name')->limit(50)->get();

        $employeeIds = $employees->pluck('id')->toArray();

        // Get all attendance records for the month
        $attendances = Attendance::whereIn('employee_id', $employeeIds)
            ->whereBetween('date', [$monthStart->format('Y-m-d'), $monthEnd->format('Y-m-d')])
            ->get()
            ->groupBy(function ($att) {
                return $att->employee_id . '_' . Carbon::parse($att->date)->format('Y-m-d');
            });

        // Get approved leaves overlapping this month
        $leaves = Leave::whereIn('employee_id', $employeeIds)
            ->where('status', 'approved')
            ->where('start_date', '<=', $monthEnd->format('Y-m-d'))
            ->where('end_date', '>=', $monthStart->format('Y-m-d'))
            ->with('leaveType')
            ->get();

        // Build leave lookup: employee_id => [date => leaveType]
        $leaveLookup = [];
        foreach ($leaves as $leave) {
            $lStart = Carbon::parse($leave->start_date);
            $lEnd = Carbon::parse($leave->end_date);
            $effStart = $lStart->greaterThan($monthStart) ? $lStart->copy() : $monthStart->copy();
            $effEnd = $lEnd->lessThan($monthEnd) ? $lEnd->copy() : $monthEnd->copy();

            $cur = $effStart->copy();
            while ($cur->lte($effEnd)) {
                $key = $leave->employee_id . '_' . $cur->format('Y-m-d');
                $leaveLookup[$key] = [
                    'type' => $leave->leaveType->name ?? 'Leave',
                    'is_paid' => $leave->leaveType ? $leave->leaveType->is_paid : true,
                ];
                $cur->addDay();
            }
        }

        // Build grid per employee
        $grid = [];
        foreach ($employees as $emp) {
            $row = [
                'employee' => $emp,
                'days' => [],
                'stats' => ['present' => 0, 'absent' => 0, 'late' => 0, 'half_day' => 0, 'leave' => 0, 'leave_mgmt' => 0],
            ];

            foreach ($days as $dayInfo) {
                $key = $emp->id . '_' . $dayInfo['date'];
                $att = $attendances->get($key);
                $leaveInfo = $leaveLookup[$key] ?? null;

                $status = null;
                $tooltip = '';

                if ($att && $att->count() > 0) {
                    $record = $att->first();
                    $status = $record->status;
                    $tooltip = ucfirst($status);
                    if ($record->time_in) $tooltip .= ' | ' . Carbon::parse($record->time_in)->format('H:i');
                    if ($record->time_out) $tooltip .= '-' . Carbon::parse($record->time_out)->format('H:i');

                    if (in_array($status, ['present', 'late'])) $row['stats']['present']++;
                    if ($status === 'late') $row['stats']['late']++;
                    if ($status === 'absent') $row['stats']['absent']++;
                    if ($status === 'half_day') $row['stats']['half_day']++;
                    if ($status === 'leave') $row['stats']['leave']++;
                } elseif ($leaveInfo) {
                    $status = 'leave_mgmt';
                    $tooltip = $leaveInfo['type'] . ($leaveInfo['is_paid'] ? ' (Paid)' : ' (Unpaid)');
                    $row['stats']['leave_mgmt']++;
                } elseif (!$dayInfo['is_weekend']) {
                    $status = 'no_record';
                    $tooltip = 'No record';
                }

                $row['days'][] = [
                    'day' => $dayInfo['day'],
                    'is_weekend' => $dayInfo['is_weekend'],
                    'status' => $status,
                    'tooltip' => $tooltip,
                ];
            }

            $grid[] = $row;
        }

        return [
            'days' => $days,
            'grid' => $grid,
            'month_label' => Carbon::create($this->calendarYear, $this->calendarMonth, 1)->translatedFormat('F Y'),
        ];
    }

    public function getDepartmentsProperty()
    {
        return Department::orderBy('name')->get();
    }

    public function render()
    {
        $data = [
            'summary' => $this->summary,
            'departments' => $this->departments,
            'departmentBreakdown' => $this->departmentBreakdown,
        ];

        if ($this->viewMode === 'detailed') {
            $data['records'] = $this->attendanceRecords;
        } elseif ($this->viewMode === 'calendar') {
            $data['calendarData'] = $this->calendarData;
        } else {
            $data['employeeSummary'] = $this->employeeSummary;
        }

        return view('livewire.hr.reports.attendance-report', $data);
    }
}
