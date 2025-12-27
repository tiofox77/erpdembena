<?php

use App\Models\HR\PayrollBatch;
use App\Models\HR\PayrollPeriod;
use App\Models\HR\Employee;
use App\Models\HR\OvertimeRecord;
use Carbon\Carbon;

// 1. Apagar payroll batches existentes
echo "🗑️  Apagando payroll batches existentes...\n";
PayrollBatch::query()->delete();
echo "✅ Payroll batches apagados!\n\n";

// 2. Buscar um funcionário ativo
echo "👤 Buscando funcionário ativo...\n";
$employee = Employee::where('status', 'active')->first();

if (!$employee) {
    echo "❌ Nenhum funcionário ativo encontrado!\n";
    exit(1);
}

echo "✅ Funcionário encontrado: {$employee->full_name} (ID: {$employee->id})\n\n";

// 3. Buscar período de payroll atual ou criar um
echo "📅 Buscando período de payroll...\n";
$period = PayrollPeriod::where('status', 'open')
    ->orWhere('status', 'active')
    ->latest()
    ->first();

if (!$period) {
    echo "⚠️  Nenhum período aberto encontrado. Buscando o mais recente...\n";
    $period = PayrollPeriod::latest()->first();
}

if (!$period) {
    echo "❌ Nenhum período de payroll encontrado!\n";
    exit(1);
}

echo "✅ Período encontrado: {$period->name}\n";
echo "   Data: {$period->start_date} até {$period->end_date}\n\n";

// 4. Apagar registros de overtime existentes deste funcionário no período
echo "🗑️  Limpando registros antigos de overtime...\n";
OvertimeRecord::where('employee_id', $employee->id)
    ->whereBetween('date', [$period->start_date, $period->end_date])
    ->delete();
echo "✅ Registros antigos apagados!\n\n";

// 5. Criar registro de Overtime Regular
echo "🕐 Criando registro de Overtime Regular...\n";
$overtimeDate = Carbon::parse($period->start_date)->addDays(5);

$overtime = OvertimeRecord::create([
    'employee_id' => $employee->id,
    'date' => $overtimeDate,
    'start_time' => '18:00',
    'end_time' => '22:00',
    'hours' => 4.0,
    'rate' => 500.00,
    'hourly_rate' => 500.00,
    'amount' => 2000.00,
    'description' => 'Horas extras projeto especial',
    'status' => 'approved',
    'input_type' => 'time_range',
    'period_type' => 'evening',
    'is_night_shift' => false,
    'approved_by' => 1,
    'approved_at' => now(),
    'created_by' => 1,
]);

echo "✅ Overtime Regular criado!\n";
echo "   Data: {$overtime->date->format('d/m/Y')}\n";
echo "   Horas: {$overtime->hours}h\n";
echo "   Valor: " . number_format($overtime->amount, 2) . " Kz\n\n";

// 6. Criar registro de Night Allowance
echo "🌙 Criando registro de Night Allowance...\n";
$nightDate = Carbon::parse($period->start_date)->addDays(10);

$nightAllowance = OvertimeRecord::create([
    'employee_id' => $employee->id,
    'date' => $nightDate,
    'direct_hours' => 5.0, // 5 dias trabalhados
    'rate' => 3000.00, // Taxa diária
    'hours' => 0,
    'hourly_rate' => 0,
    'amount' => 3000.00, // 20% de (5 dias × 3000 Kz) = 3000 Kz
    'description' => 'Subsídio noturno - 5 dias',
    'status' => 'approved',
    'input_type' => 'days',
    'period_type' => 'day',
    'is_night_shift' => true,
    'approved_by' => 1,
    'approved_at' => now(),
    'created_by' => 1,
]);

echo "✅ Night Allowance criado!\n";
echo "   Data: {$nightAllowance->date->format('d/m/Y')}\n";
echo "   Dias: {$nightAllowance->direct_hours}\n";
echo "   Taxa Diária: " . number_format($nightAllowance->rate, 2) . " Kz\n";
echo "   Valor: " . number_format($nightAllowance->amount, 2) . " Kz\n\n";

echo "🎉 Dados de teste criados com sucesso!\n";
echo "📊 Agora pode processar o payroll batch em: http://erpdembena.test/hr/payroll-batch\n";
echo "👤 Funcionário: {$employee->full_name}\n";
echo "📅 Período: {$period->name}\n";
