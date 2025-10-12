<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\HR\HRSetting;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Remove a chave duplicada 'working_days_per_month'
        // Mantenha apenas 'monthly_working_days'
        HRSetting::where('key', 'working_days_per_month')->delete();
        
        // Garantir que 'monthly_working_days' existe
        $exists = HRSetting::where('key', 'monthly_working_days')->exists();
        
        if (!$exists) {
            HRSetting::create([
                'key' => 'monthly_working_days',
                'value' => '22',
                'group' => 'labor_rules',
                'description' => 'Número de dias úteis de trabalho por mês (padrão Angola: 22 dias)'
            ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Recriar a chave antiga se necessário (rollback)
        HRSetting::updateOrCreate(
            ['key' => 'working_days_per_month'],
            [
                'value' => '22',
                'group' => 'labor_rules',
                'description' => 'Dias de trabalho por mês (deprecated - use monthly_working_days)'
            ]
        );
    }
};
