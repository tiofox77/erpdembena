<?php

require_once 'vendor/autoload.php';

use App\Models\HR\Attendance;
use Carbon\Carbon;

// Configurar a aplicação Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== DEBUG ATTENDANCE CALENDAR DATA ===\n\n";

// Verificar total de registos
$totalAttendances = Attendance::count();
echo "Total attendances in database: {$totalAttendances}\n\n";

// Verificar registos recentes
echo "Recent attendance records:\n";
$recentAttendances = Attendance::orderBy('created_at', 'desc')->take(10)->get();
foreach ($recentAttendances as $attendance) {
    echo "ID: {$attendance->id} | Employee: {$attendance->employee_id} | Date: {$attendance->date} | Status: {$attendance->status}\n";
}

echo "\n=== TESTING CALENDAR QUERY ===\n\n";

// Testar a query do calendário para o mês atual
$currentYear = Carbon::now()->year;
$currentMonth = Carbon::now()->month;
$startOfMonth = Carbon::createFromDate($currentYear, $currentMonth, 1);
$endOfMonth = $startOfMonth->copy()->endOfMonth();

echo "Testing calendar query for: {$startOfMonth->format('Y-m-d')} to {$endOfMonth->format('Y-m-d')}\n\n";

// Query similar ao método getCalendarData
$attendances = Attendance::whereBetween('date', [$startOfMonth->format('Y-m-d'), $endOfMonth->format('Y-m-d')])
    ->selectRaw('DATE(date) as date, COUNT(*) as count, status')
    ->groupBy('date', 'status')
    ->get();

echo "Calendar query returned {$attendances->count()} records:\n";
foreach ($attendances as $attendance) {
    echo "Date: {$attendance->date} | Status: {$attendance->status} | Count: {$attendance->count}\n";
}

echo "\n=== CHECKING DATA TYPES ===\n\n";

// Verificar tipos de dados
$sampleRecord = Attendance::first();
if ($sampleRecord) {
    echo "Sample record date type: " . gettype($sampleRecord->date) . "\n";
    echo "Sample record date value: {$sampleRecord->date}\n";
    echo "Sample record date formatted: " . Carbon::parse($sampleRecord->date)->format('Y-m-d') . "\n";
}

echo "\n=== TESTING SPECIFIC DATE ===\n\n";

// Testar uma data específica
$testDate = Carbon::today()->format('Y-m-d');
echo "Testing for today: {$testDate}\n";

$todayAttendances = Attendance::whereDate('date', $testDate)->get();
echo "Today's attendances: {$todayAttendances->count()}\n";

foreach ($todayAttendances as $attendance) {
    echo "  - Employee: {$attendance->employee_id} | Status: {$attendance->status}\n";
}

echo "\n=== END DEBUG ===\n";
