<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'group',
        'type',
        'description',
        'is_public',
    ];

    /**
     * Get a setting value by key
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function get(string $key, $default = null)
    {
        $cacheKey = "setting_{$key}";

        // Try to get from cache first
        return Cache::remember($cacheKey, 86400, function () use ($key, $default) {
            $setting = self::where('key', $key)->first();

            if (!$setting) {
                return $default;
            }

            // Cast value according to type
            return self::castValue($setting->value, $setting->type);
        });
    }

    /**
     * Set a setting value
     *
     * @param string $key
     * @param mixed $value
     * @param string|null $group
     * @param string|null $type
     * @param string|null $description
     * @param bool|null $isPublic
     * @return Setting
     */
    public static function set(string $key, $value, string $group = null, string $type = null, string $description = null, bool $isPublic = null)
    {
        $setting = self::updateOrCreate(
            ['key' => $key],
            array_filter([
                'value' => $value,
                'group' => $group,
                'type' => $type ?: 'string',
                'description' => $description,
                'is_public' => $isPublic,
            ])
        );

        // Clear the cache for this key
        Cache::forget("setting_{$key}");

        return $setting;
    }

    /**
     * Cast the value based on type
     *
     * @param mixed $value
     * @param string $type
     * @return mixed
     */
    protected static function castValue($value, string $type)
    {
        switch ($type) {
            case 'boolean':
                return (bool) $value;
            case 'integer':
                return (int) $value;
            case 'float':
            case 'double':
                return (float) $value;
            case 'array':
            case 'json':
                return json_decode($value, true);
            case 'object':
                return json_decode($value);
            default:
                return $value;
        }
    }

    /**
     * Get all settings as a key-value array
     *
     * @param string|null $group Filter by group
     * @return array
     */
    public static function getAllAsArray(string $group = null)
    {
        $query = self::query();

        if ($group) {
            $query->where('group', $group);
        }

        return $query->get()->mapWithKeys(function ($setting) {
            return [$setting->key => self::castValue($setting->value, $setting->type)];
        })->toArray();
    }

    /**
     * Get all public settings
     *
     * @return array
     */
    public static function getPublicSettings()
    {
        return Cache::remember('public_settings', 86400, function () {
            return self::where('is_public', true)->get()->mapWithKeys(function ($setting) {
                return [$setting->key => self::castValue($setting->value, $setting->type)];
            })->toArray();
        });
    }

    /**
     * Clear all settings cache
     *
     * @return void
     */
    public static function clearCache()
    {
        Cache::forget('public_settings');

        // Clear individual setting caches
        foreach (self::all() as $setting) {
            Cache::forget("setting_{$setting->key}");
        }
    }
}
