<?php

require_once 'vendor/autoload.php';

use App\Models\HR\Attendance;
use Carbon\Carbon;

// Configurar a aplicação Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== TESTING CALENDAR FIX ===\n\n";

// Simular o método getCalendarData corrigido
$currentYear = Carbon::now()->year;
$currentMonth = Carbon::now()->month;
$startOfMonth = Carbon::createFromDate($currentYear, $currentMonth, 1);
$endOfMonth = $startOfMonth->copy()->endOfMonth();

echo "Testing calendar query for: {$startOfMonth->format('Y-m-d')} to {$endOfMonth->format('Y-m-d')}\n\n";

// Query original (com problema)
$attendances = Attendance::whereBetween('date', [$startOfMonth->format('Y-m-d'), $endOfMonth->format('Y-m-d')])
    ->selectRaw('DATE(date) as date, COUNT(*) as count, status')
    ->groupBy('date', 'status')
    ->get();

echo "Original query results:\n";
foreach ($attendances as $attendance) {
    echo "  Date: '{$attendance->date}' | Status: {$attendance->status} | Count: {$attendance->count}\n";
}

// Aplicar a correção de formato
$attendances = $attendances->map(function($attendance) {
    $attendance->date = Carbon::parse($attendance->date)->format('Y-m-d');
    return $attendance;
});

echo "\nAfter format correction:\n";
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
    echo "  - Should show data: " . ($stats['total_attendances'] > 0 ? "YES" : "NO") . "\n";
} else {
    echo "No day stats found - would show 'No data available'\n";
}

echo "\n=== END TEST ===\n";
