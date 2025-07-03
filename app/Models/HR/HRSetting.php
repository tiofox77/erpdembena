<?php

declare(strict_types=1);

namespace App\Models\HR;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HRSetting extends Model
{
    use HasFactory;
    
    /**
     * @var string
     */
    protected $table = 'hr_settings';
    
    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'key',
        'value',
        'group',
        'description',
        'is_system'
    ];
    
    /**
     * @var array<string, string>
     */
    protected $casts = [
        'is_system' => 'boolean',
    ];
    
    /**
     * Grupos de configurações disponíveis
     * 
     * @return array<string, string>
     */
    public static function getGroups(): array
    {
        return [
            'labor_rules' => 'Regras Laborais',
            'tax' => 'Impostos e Contribuições',
            'leave' => 'Férias e Licenças',
            'employment' => 'Emprego e Contratos',
            'benefits' => 'Benefícios e Subsídios',
            'general' => 'Configurações Gerais'
        ];
    }
    
    /**
     * Recupera uma configuração específica pelo seu nome
     * 
     * @param string $key
     * @param mixed|null $default
     * @return mixed
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $setting = self::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }
    
    /**
     * Atualiza ou cria uma configuração
     * 
     * @param string $key
     * @param mixed $value
     * @param string $group
     * @param string|null $description
     * @return void
     */
    public static function set(string $key, mixed $value, string $group = 'general', ?string $description = null): void
    {
        self::updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'group' => $group,
                'description' => $description
            ]
        );
    }
    
    /**
     * Recupera todas as configurações em formato de array agrupado
     * 
     * @return array<string, array<int, array<string, mixed>>>
     */
    public static function getAllGrouped(): array
    {
        $settings = self::all();
        $grouped = [];
        
        foreach ($settings as $setting) {
            $grouped[$setting->group][] = [
                'id' => $setting->id,
                'key' => $setting->key,
                'value' => $setting->value,
                'description' => $setting->description,
                'is_system' => $setting->is_system,
            ];
        }
        
        return $grouped;
    }
}
