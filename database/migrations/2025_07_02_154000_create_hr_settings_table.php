<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('hr_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('group')->default('general');
            $table->text('description')->nullable();
            $table->boolean('is_system')->default(false);
            $table->timestamps();
            
            // Índices para melhorar a performance
            $table->index(['key', 'group']);
        });
        
        // Inserir configurações padrão
        $this->seedSettings();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hr_settings');
    }
    
    /**
     * Seed the HR settings with Angola-specific defaults
     */
    private function seedSettings(): void
    {
        $settings = [
            // Configurações de horário de trabalho
            [
                'key' => 'working_hours_per_week', 
                'value' => '44', 
                'group' => 'labor_rules',
                'description' => 'Número de horas de trabalho semanal padrão em Angola',
                'is_system' => true
            ],
            [
                'key' => 'working_days_per_week', 
                'value' => '5', 
                'group' => 'labor_rules',
                'description' => 'Número de dias de trabalho semanal padrão',
                'is_system' => true
            ],
            [
                'key' => 'overtime_multiplier_weekday', 
                'value' => '1.5', 
                'group' => 'labor_rules',
                'description' => 'Multiplicador para horas extras em dias úteis',
                'is_system' => true
            ],
            [
                'key' => 'overtime_multiplier_weekend', 
                'value' => '2.0', 
                'group' => 'labor_rules',
                'description' => 'Multiplicador para horas extras em fins de semana',
                'is_system' => true
            ],
            [
                'key' => 'overtime_multiplier_holiday', 
                'value' => '2.5', 
                'group' => 'labor_rules',
                'description' => 'Multiplicador para horas extras em feriados',
                'is_system' => true
            ],
            
            // Configurações INSS/IRT (Segurança Social e Impostos em Angola)
            [
                'key' => 'inss_employee_rate', 
                'value' => '3', 
                'group' => 'tax',
                'description' => 'Percentual de contribuição do funcionário para INSS',
                'is_system' => true
            ],
            [
                'key' => 'inss_employer_rate', 
                'value' => '8', 
                'group' => 'tax',
                'description' => 'Percentual de contribuição do empregador para INSS',
                'is_system' => true
            ],
            [
                'key' => 'min_salary_tax_exempt', 
                'value' => '70000', 
                'group' => 'tax',
                'description' => 'Valor mínimo de salário isento de IRT em Kwanzas',
                'is_system' => true
            ],
            
            // Férias e licenças
            [
                'key' => 'annual_leave_days', 
                'value' => '22', 
                'group' => 'leave',
                'description' => 'Dias de férias anuais conforme Lei Geral do Trabalho',
                'is_system' => true
            ],
            [
                'key' => 'maternity_leave_days', 
                'value' => '90', 
                'group' => 'leave',
                'description' => 'Dias de licença maternidade',
                'is_system' => true
            ],
            [
                'key' => 'paternity_leave_days', 
                'value' => '5', 
                'group' => 'leave',
                'description' => 'Dias de licença paternidade',
                'is_system' => true
            ],
            
            // Configurações gerais
            [
                'key' => 'probation_period_months', 
                'value' => '3', 
                'group' => 'employment',
                'description' => 'Período de experiência padrão em meses',
                'is_system' => true
            ],
            [
                'key' => 'notice_period_days', 
                'value' => '30', 
                'group' => 'employment',
                'description' => 'Período de aviso prévio em dias',
                'is_system' => true
            ],
            [
                'key' => 'subsidy_transport', 
                'value' => '15000', 
                'group' => 'benefits',
                'description' => 'Subsídio de transporte mensal padrão em Kwanzas',
                'is_system' => true
            ],
            [
                'key' => 'subsidy_meal', 
                'value' => '30000', 
                'group' => 'benefits',
                'description' => 'Subsídio de alimentação mensal padrão em Kwanzas',
                'is_system' => true
            ],
        ];
        
        DB::table('hr_settings')->insert($settings);
    }
};
