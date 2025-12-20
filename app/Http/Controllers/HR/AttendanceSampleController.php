<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AttendanceSampleExport;
use App\Exports\AttendanceZKTimeSampleExport;

class AttendanceSampleController extends Controller
{
    /**
     * Download ZKTime format sample file
     */
    public function downloadZKTimeSample()
    {
        return Excel::download(new AttendanceZKTimeSampleExport(), 'attendance_zktime_sample.xlsx');
    }

    /**
     * Download Standard format sample file
     */
    public function downloadStandardSample()
    {
        return Excel::download(new AttendanceSampleExport(), 'attendance_standard_sample.xlsx');
    }
}
