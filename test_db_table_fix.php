<?php

require_once 'vendor/autoload.php';

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

// Configurar a aplicação Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== TESTING DB::TABLE FIX ===\n\n";

// Simular o método getCalendarData corrigido
$currentYear = Carbon::now()->year;
$currentMonth = Carbon::now()->month;
$startOfMonth = Carbon::createFromDate($currentYear, $currentMonth, 1);
$endOfMonth = $startOfMonth->copy()->endOfMonth();

echo "Testing calendar query for: {$startOfMonth->format('Y-m-d')} to {$endOfMonth->format('Y-m-d')}\n\n";

// Query usando DB::table
$attendances = DB::table('attendances')
    ->whereBetween('date', [$startOfMonth->format('Y-m-d'), $endOfMonth->format('Y-m-d')])
    ->selectRaw('DATE(date) as date, COUNT(*) as count, status')
    ->groupBy('date', 'status')
    ->get();

echo "DB::table query results:\n";
foreach ($attendances as $attendance) {
    echo "  Date: '{$attendance->date}' | Status: {$attendance->status} | Count: {$attendance->count}\n";
    echo "  Date type: " . gettype($attendance->date) . "\n";
}

// Agrupar por data
$groupedAttendances = $attendances->groupBy('date');

echo "\nGrouped by date:\n";
foreach ($groupedAttendances as $date => $dayAttendances) {
    echo "  Date: '{$date}' (type: " . gettype($date) . ") has {$dayAttendances->count()} status entries\n";
    
    $stats = [
        'total_attendances' => $dayAttendances->sum('count'),
        'present' => $dayAttendances->where('status', 'present')->sum('count'),
        'absent' => $dayAttendances->where('status', 'absent')->sum('count'),
        'late' => $dayAttendances->where('status', 'late')->sum('count'),
    ];
    
    echo "    - Total: {$stats['total_attendances']}\n";
    echo "    - Present: {$stats['present']}\n";
    echo "    - Absent: {$stats['absent']}\n";
    echo "    - Late: {$stats['late']}\n";
}

echo "\n=== TESTING CALENDAR VIEW LOGIC ===\n\n";

// Testar a lógica da view
$testDate = '2025-07-18';
$dayStats = $groupedAttendances->get($testDate);

echo "Testing calendar view for date: {$testDate}\n";

if ($dayStats) {
    $stats = [
        'total_attendances' => $dayStats->sum('count'),
        'present' => $dayStats->where('status', 'present')->sum('count'),
    ];
    
    echo "Day stats found:\n";
    echo "  - Total attendances: {$stats['total_attendances']}\n";
    echo "  - Present: {$stats['present']}\n";
    echo "  - Should show data: " . ($stats['total_attendances'] > 0 ? "YES ✅" : "NO ❌") . "\n";
} else {
    echo "No day stats found - would show 'No data available' ❌\n";
}

echo "\n=== CALENDAR COLLECTION TEST ===\n\n";

// Simular a coleção do calendário
$calendarData = collect();

foreach ($groupedAttendances as $date => $dayAttendances) {
    $stats = [
        'date' => $date,
        'total_attendances' => $dayAttendances->sum('count'),
        'present' => $dayAttendances->where('status', 'present')->sum('count'),
        'absent' => $dayAttendances->where('status', 'absent')->sum('count'),
        'late' => $dayAttendances->where('status', 'late')->sum('count'),
    ];
    
    $calendarData->put($date, $stats);
}

echo "Calendar data collection:\n";
foreach ($calendarData as $date => $stats) {
    echo "  Date: '{$date}' => Total: {$stats['total_attendances']}, Present: {$stats['present']}\n";
}

echo "\n=== FINAL VIEW TEST ===\n\n";

// Testar como a view veria os dados
$testDate = '2025-07-18';
$dayStats = $calendarData->get($testDate);

echo "Final test for date: {$testDate}\n";
if ($dayStats && $dayStats['total_attendances'] > 0) {
    echo "✅ SUCCESS: Calendar will show attendance data\n";
    echo "   - Total attendances: {$dayStats['total_attendances']}\n";
    echo "   - Present: {$dayStats['present']}\n";
} else {
    echo "❌ FAIL: Calendar will still show 'No data available'\n";
}

echo "\n=== TESTING ALL DATES IN CALENDAR ===\n\n";

// Testar todas as datas que temos dados
echo "All dates with data:\n";
foreach ($calendarData as $date => $stats) {
    echo "  Testing date: '{$date}'\n";
    $testResult = $calendarData->get($date);
    if ($testResult && $testResult['total_attendances'] > 0) {
        echo "    ✅ SUCCESS: Will show {$testResult['total_attendances']} attendances\n";
    } else {
        echo "    ❌ FAIL: Will show 'No data available'\n";
    }
}

echo "\n=== END TEST ===\n";
