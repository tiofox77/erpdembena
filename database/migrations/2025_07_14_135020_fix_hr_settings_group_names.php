<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Corrige inconsistências de nomes nos grupos de configurações HR
     */
    public function up(): void
    {
        // Corrigir nome do grupo de "Regras Trabalhistas" para "labor_rules"
        DB::table('hr_settings')
            ->where('group', 'Regras Trabalhistas')
            ->update(['group' => 'labor_rules']);
            
        // Adicionar tradução do grupo no arquivo messages.php
        // Nota: Isso deve ser feito manualmente no arquivo resources/lang/pt/messages.php
        // Adicionar: 'hr_setting_group_labor_rules' => 'Regras Trabalhistas',
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restaurar nome anterior
        DB::table('hr_settings')
            ->where('group', 'labor_rules')
            ->update(['group' => 'Regras Trabalhistas']);
            
        // Nota: a tradução deve ser removida manualmente do arquivo messages.php
    }
};