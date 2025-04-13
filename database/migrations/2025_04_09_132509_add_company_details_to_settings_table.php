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
        // Não precisamos modificar a estrutura da tabela, pois já temos as colunas key e value
        // Vamos inserir novos registros para os detalhes da empresa
        $settings = [
            [
                'key' => 'company_address',
                'value' => 'R. Principal, 123 - Centro, São Paulo - SP, 01234-567',
                'group' => 'company',
                'type' => 'string',
                'description' => 'Endereço completo da empresa',
                'is_public' => true,
            ],
            [
                'key' => 'company_phone',
                'value' => '+55 11 1234-5678',
                'group' => 'company',
                'type' => 'string',
                'description' => 'Telefone de contato da empresa',
                'is_public' => true,
            ],
            [
                'key' => 'company_email',
                'value' => 'contato@erpdembena.com',
                'group' => 'company',
                'type' => 'string',
                'description' => 'Email de contato da empresa',
                'is_public' => true,
            ],
            [
                'key' => 'company_website',
                'value' => 'www.erpdembena.com',
                'group' => 'company',
                'type' => 'string',
                'description' => 'Site da empresa',
                'is_public' => true,
            ],
            [
                'key' => 'company_tax_id',
                'value' => '12.345.678/0001-90',
                'group' => 'company',
                'type' => 'string',
                'description' => 'CNPJ da empresa',
                'is_public' => true,
            ]
        ];

        foreach ($settings as $setting) {
            // Verifica se o registro já existe para evitar duplicação
            if (!DB::table('settings')->where('key', $setting['key'])->exists()) {
                DB::table('settings')->insert($setting);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove os registros adicionados
        DB::table('settings')
            ->whereIn('key', [
                'company_address',
                'company_phone',
                'company_email',
                'company_website',
                'company_tax_id',
            ])
            ->delete();
    }
};
