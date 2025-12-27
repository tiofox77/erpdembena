<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\HR\PayrollBatch;
use App\Models\HR\PayrollPeriod;
use App\Models\HR\Employee;
use App\Models\HR\OvertimeRecord;
use Carbon\Carbon;

class SetupPayrollTest extends Command
{
    protected $signature = 'payroll:setup-test';
    protected $description = 'Apaga payroll batches e cria registros de overtime e night allowance para teste';

    public function handle()
    {
        $this->info('🗑️  Apagando payroll batches existentes...');
        PayrollBatch::query()->delete();
        $this->info('✅ Payroll batches apagados!');
        $this->newLine();

        $this->info('👤 Buscando funcionário ativo...');
        $employee = Employee::where('employment_status', 'active')->first();

        if (!$employee) {
            $this->error('❌ Nenhum funcionário ativo encontrado!');
            return 1;
        }

        $this->info("✅ Funcionário encontrado: {$employee->full_name} (ID: {$employee->id})");
        $this->newLine();

        $this->info('📅 Buscando período de payroll...');
        $period = PayrollPeriod::where('status', 'open')
            ->orWhere('status', 'active')
            ->latest()
            ->first();

        if (!$period) {
            $this->warn('⚠️  Nenhum período aberto encontrado. Buscando o mais recente...');
            $period = PayrollPeriod::latest()->first();
        }

        if (!$period) {
            $this->error('❌ Nenhum período de payroll encontrado!');
            return 1;
        }

        $this->info("✅ Período encontrado: {$period->name}");
        $this->info("   Data: {$period->start_date->format('d/m/Y')} até {$period->end_date->format('d/m/Y')}");
        $this->newLine();

        $this->info('🗑️  Limpando registros antigos de overtime...');
        OvertimeRecord::where('employee_id', $employee->id)
            ->whereBetween('date', [$period->start_date, $period->end_date])
            ->delete();
        $this->info('✅ Registros antigos apagados!');
        $this->newLine();

        $this->info('🕐 Criando registro de Overtime Regular...');
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

        $this->info('✅ Overtime Regular criado!');
        $this->info("   Data: {$overtime->date->format('d/m/Y')}");
        $this->info("   Horas: {$overtime->hours}h");
        $this->info("   Valor: " . number_format($overtime->amount, 2) . " Kz");
        $this->newLine();

        $this->info('🌙 Criando registro de Night Allowance...');
        $nightDate = Carbon::parse($period->start_date)->addDays(10);

        $nightAllowance = OvertimeRecord::create([
            'employee_id' => $employee->id,
            'date' => $nightDate,
            'direct_hours' => 5.0,
            'rate' => 3000.00,
            'hours' => 0,
            'hourly_rate' => 0,
            'amount' => 3000.00,
            'description' => 'Subsídio noturno - 5 dias',
            'status' => 'approved',
            'input_type' => 'days',
            'period_type' => 'day',
            'is_night_shift' => true,
            'approved_by' => 1,
            'approved_at' => now(),
            'created_by' => 1,
        ]);

        $this->info('✅ Night Allowance criado!');
        $this->info("   Data: {$nightAllowance->date->format('d/m/Y')}");
        $this->info("   Dias: {$nightAllowance->direct_hours}");
        $this->info("   Taxa Diária: " . number_format($nightAllowance->rate, 2) . " Kz");
        $this->info("   Valor: " . number_format($nightAllowance->amount, 2) . " Kz");
        $this->newLine();

        $this->info('🎉 Dados de teste criados com sucesso!');
        $this->info('📊 Agora pode processar o payroll batch em: http://erpdembena.test/hr/payroll-batch');
        $this->info("👤 Funcionário: {$employee->full_name}");
        $this->info("📅 Período: {$period->name}");

        return 0;
    }
}
