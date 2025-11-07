<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=================================================\n";
echo "📅 VERIFICAÇÃO DE DATAS DE ATTENDANCE\n";
echo "=================================================\n\n";

// Verificar estrutura da tabela
echo "🔍 ESTRUTURA DA TABELA 'attendances':\n";
echo "-------------------------------------------------\n";
$columns = DB::select("SHOW COLUMNS FROM attendances");
foreach ($columns as $col) {
    echo "  • {$col->Field} ({$col->Type})\n";
}

echo "\n📊 TOTAL DE REGISTROS: " . DB::table('attendances')->count() . "\n\n";

// Verificar distribuição por data
echo "📅 REGISTROS POR DATA (Setembro/Outubro 2025):\n";
echo "-------------------------------------------------\n";
$dates = DB::table('attendances')
    ->whereBetween('date', ['2025-09-01', '2025-10-31'])
    ->selectRaw('DATE_FORMAT(date, "%Y-%m-%d") as day, COUNT(*) as total, COUNT(DISTINCT employee_id) as employees')
    ->groupBy('day')
    ->orderBy('day')
    ->get();

if ($dates->isEmpty()) {
    echo "❌ Nenhum registro encontrado em setembro/outubro 2025!\n\n";
    
    // Verificar em quais meses existem registros
    echo "📅 VERIFICANDO TODOS OS MESES COM REGISTROS:\n";
    echo "-------------------------------------------------\n";
    $allMonths = DB::table('attendances')
        ->selectRaw('DATE_FORMAT(date, "%Y-%m") as month, COUNT(*) as total')
        ->groupBy('month')
        ->orderBy('month', 'desc')
        ->limit(12)
        ->get();
    
    foreach ($allMonths as $m) {
        echo "  • {$m->month}: {$m->total} registros\n";
    }
} else {
    foreach ($dates as $date) {
        $indicator = "✅";
        // Destacar se está no período do payroll (20 set a 20 out)
        if ($date->day >= '2025-09-20' && $date->day <= '2025-10-20') {
            $indicator = "✅ [NO PERÍODO]";
        } else {
            $indicator = "⚠️  [FORA DO PERÍODO]";
        }
        echo "{$indicator} {$date->day}: {$date->total} registros ({$date->employees} funcionários)\n";
    }
}

echo "\n";
echo "🔍 REGISTROS NO PERÍODO ESPECÍFICO (2025-09-20 a 2025-10-20):\n";
echo "-------------------------------------------------\n";
$inPeriod = DB::table('attendances')
    ->whereBetween('date', ['2025-09-20', '2025-10-20'])
    ->count();

echo "Total: {$inPeriod} registros\n";

if ($inPeriod == 0) {
    echo "\n❌ PROBLEMA: Não há registros no período do payroll!\n";
    echo "\n💡 SOLUÇÃO:\n";
    echo "  1. Ajustar as datas do período de payroll para incluir as datas com registros, OU\n";
    echo "  2. Reimportar os registros de attendance com as datas corretas\n";
}

echo "\n=================================================\n";
echo "FIM\n";
echo "=================================================\n";
