<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('group')->default('general');
            $table->string('type')->default('string');
            $table->text('description')->nullable();
            $table->boolean('is_public')->default(false);
            $table->timestamps();
        });

        // Inserir configurações iniciais
        $this->seedDefaultSettings();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }

    /**
     * Seed the settings table with default values
     */
    private function seedDefaultSettings(): void
    {
        $defaultSettings = [
            [
                'key' => 'company_name',
                'value' => 'ERPDEMBENA',
                'group' => 'general',
                'type' => 'string',
                'description' => 'Nome da empresa',
                'is_public' => true,
            ],
            [
                'key' => 'app_timezone',
                'value' => 'UTC',
                'group' => 'general',
                'type' => 'string',
                'description' => 'Fuso horário padrão do sistema',
                'is_public' => true,
            ],
            [
                'key' => 'date_format',
                'value' => 'd/m/Y',
                'group' => 'general',
                'type' => 'string',
                'description' => 'Formato de data padrão',
                'is_public' => true,
            ],
            [
                'key' => 'currency',
                'value' => 'BRL',
                'group' => 'general',
                'type' => 'string',
                'description' => 'Moeda padrão',
                'is_public' => true,
            ],
            [
                'key' => 'language',
                'value' => 'pt',
                'group' => 'general',
                'type' => 'string',
                'description' => 'Idioma padrão',
                'is_public' => true,
            ],
            [
                'key' => 'github_repository',
                'value' => 'your-username/your-repository',
                'group' => 'updates',
                'type' => 'string',
                'description' => 'Repositório GitHub para atualizações',
                'is_public' => false,
            ],
            [
                'key' => 'app_version',
                'value' => '1.0.0',
                'group' => 'updates',
                'type' => 'string',
                'description' => 'Versão atual do sistema',
                'is_public' => true,
            ],
            [
                'key' => 'maintenance_mode',
                'value' => '0',
                'group' => 'maintenance',
                'type' => 'boolean',
                'description' => 'Modo de manutenção',
                'is_public' => true,
            ],
            [
                'key' => 'debug_mode',
                'value' => '0',
                'group' => 'maintenance',
                'type' => 'boolean',
                'description' => 'Modo de debug',
                'is_public' => false,
            ],
        ];

        foreach ($defaultSettings as $setting) {
            DB::table('settings')->insert($setting);
        }
    }
};
