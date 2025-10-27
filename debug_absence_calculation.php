<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$employeeId = 14; // Ana Beatriz Lopes

echo "=== DEBUG: Comparação de Cálculo de Ausências ===\n\n";

$startDate = '2025-07-01';
$endDate = '2025-07-31';

echo "📅 PERÍODO: {$startDate} a {$endDate}\n\n";

// 1. Obter registros de presença
$attendanceRecords = \App\Models\HR\Attendance::where('employee_id', $employeeId)
    ->whereBetween('date', [$startDate, $endDate])
    ->orderBy('date')
    ->get();

echo "📊 ANÁLISE DOS REGISTROS:\n";
echo "   Total de registros de presença: " . $attendanceRecords->count() . "\n";

$presentDays = $attendanceRecords->where('status', 'present')->count();
$absentDays = $attendanceRecords->where('status', 'absent')->count();
$lateDays = $attendanceRecords->where('status', 'late')->count();
$halfDays = $attendanceRecords->where('status', 'half_day')->count();

echo "   Registros 'present': {$presentDays}\n";
echo "   Registros 'absent': {$absentDays}\n";
echo "   Registros 'late': {$lateDays}\n";
echo "   Registros 'half_day': {$halfDays}\n\n";

// 2. Calcular dias úteis do mês
$workingDaysInMonth = 0;
$currentDate = \Carbon\Carbon::parse($startDate);
$endDateCarbon = \Carbon\Carbon::parse($endDate);

while ($currentDate->lte($endDateCarbon)) {
    // Contar apenas dias úteis (segunda a sexta)
    if ($currentDate->isWeekday()) {
        $workingDaysInMonth++;
    }
    $currentDate->addDay();
}

echo "📅 CÁLCULO DE DIAS ÚTEIS:\n";
echo "   Total de dias úteis em julho 2025: {$workingDaysInMonth}\n";
echo "   Registros de presença encontrados: " . $attendanceRecords->count() . "\n";
echo "   Dias sem registo: " . ($workingDaysInMonth - $attendanceRecords->count()) . "\n\n";

// 3. Duas formas de calcular ausências
echo "🔍 DUAS FORMAS DE CALCULAR AUSÊNCIAS:\n\n";

echo "📝 MÉTODO 1 - NOSSA LÓGICA ATUAL (Recibo):\n";
echo "   Ausências = Registros marcados como 'absent'\n";
echo "   Resultado: {$absentDays} ausências\n\n";

echo "📝 MÉTODO 2 - LÓGICA DA INTERFACE (Folha de Pagamento):\n";
echo "   Ausências = Dias úteis - Registros de presença\n";
echo "   Resultado: {$workingDaysInMonth} - " . $attendanceRecords->count() . " = " . ($workingDaysInMonth - $attendanceRecords->count()) . " ausências\n\n";

// 4. Verificar que dias estão em falta
echo "📋 DIAS SEM REGISTO (considerados ausentes pela interface):\n";
$recordDates = $attendanceRecords->pluck('date')->map(fn($date) => $date->format('Y-m-d'))->toArray();

$currentDate = \Carbon\Carbon::parse($startDate);
$missingDays = [];

while ($currentDate->lte($endDateCarbon)) {
    if ($currentDate->isWeekday()) {
        $dateStr = $currentDate->format('Y-m-d');
        if (!in_array($dateStr, $recordDates)) {
            $missingDays[] = $currentDate->format('d/m/Y (D)');
        }
    }
    $currentDate->addDay();
}

foreach($missingDays as $day) {
    echo "   - {$day}\n";
}

echo "\n💡 CONCLUSÃO:\n";
echo "   A interface considera dias sem registo como ausências\n";
echo "   O recibo só conta registros explicitamente marcados como 'absent'\n";
echo "   Para alinhar os valores, precisamos escolher uma lógica consistente\n";

echo "\n=== DEBUG CONCLUÍDO ===\n";
