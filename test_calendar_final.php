<?php

require_once 'vendor/autoload.php';

use App\Models\HR\Attendance;
use Carbon\Carbon;

// Configurar a aplicação Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== TESTING FINAL CALENDAR FIX ===\n\n";

// Simular o método getCalendarData corrigido
$currentYear = Carbon::now()->year;
$currentMonth = Carbon::now()->month;
$startOfMonth = Carbon::createFromDate($currentYear, $currentMonth, 1);
$endOfMonth = $startOfMonth->copy()->endOfMonth();

echo "Testing calendar query for: {$startOfMonth->format('Y-m-d')} to {$endOfMonth->format('Y-m-d')}\n\n";

// Query corrigida
$attendances = Attendance::whereBetween('date', [$startOfMonth->format('Y-m-d'), $endOfMonth->format('Y-m-d')])
    ->selectRaw('DATE(date) as date_only, COUNT(*) as count, status')
    ->groupBy('date_only', 'status')
    ->get();

echo "Corrected query results:\n";
foreach ($attendances as $attendance) {
    echo "  Date_only: '{$attendance->date_only}' | Status: {$attendance->status} | Count: {$attendance->count}\n";
}

// Aplicar o mapeamento
$attendances = $attendances->map(function($attendance) {
    $attendance->date = $attendance->date_only;
    return $attendance;
});

echo "\nAfter mapping:\n";
foreach ($attendances as $attendance) {
    echo "  Date: '{$attendance->date}' | Status: {$attendance->status} | Count: {$attendance->count}\n";
}

// Agrupar por data
$groupedAttendances = $attendances->groupBy('date');

echo "\nGrouped by date:\n";
foreach ($groupedAttendances as $date => $dayAttendances) {
    echo "  Date: '{$date}' has {$dayAttendances->count()} status entries\n";
    
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

echo "\n=== END TEST ===\n";
