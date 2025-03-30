<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;

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
     * The cache prefix for settings
     */
    protected static $cachePrefix = 'settings_';

    /**
     * Cache duration in seconds (1 day)
     */
    protected static $cacheDuration = 86400;

    /**
     * Get a setting value by key
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function get(string $key, $default = null)
    {
        // Try to get from cache first
        $cacheKey = static::$cachePrefix . $key;
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        // If not in cache, get from database
        try {
            $setting = static::where('key', $key)->first();

            if ($setting) {
                $value = static::castValue($setting->value, $setting->type);
                // Cache the result
                Cache::put($cacheKey, $value, static::$cacheDuration);
                return $value;
            }
        } catch (\Exception $e) {
            Log::error("Error getting setting {$key}: " . $e->getMessage());
        }

        return $default;
    }

    /**
     * Set a setting value
     *
     * @param string $key
     * @param mixed $value
     * @param string $group
     * @param string $type
     * @param string|null $description
     * @param bool $isPublic
     * @return bool
     */
    public static function set(string $key, $value, string $group = 'general', string $type = 'string', ?string $description = null, bool $isPublic = false)
    {
        try {
            // Check if the setting already exists
            $setting = static::where('key', $key)->first();

            if ($setting) {
                // Update existing setting
                $setting->value = $value;
                $setting->group = $group;
                $setting->type = $type;

                if ($description !== null) {
                    $setting->description = $description;
                }

                $setting->is_public = $isPublic;
                $setting->save();
            } else {
                // Create new setting
                $setting = static::create([
                    'key' => $key,
                    'value' => $value,
                    'group' => $group,
                    'type' => $type,
                    'description' => $description,
                    'is_public' => $isPublic,
                ]);
            }

            // Update cache
            $cacheKey = static::$cachePrefix . $key;
            $castedValue = static::castValue($value, $type);
            Cache::put($cacheKey, $castedValue, static::$cacheDuration);

            // Special handling for app_version
            if ($key === 'app_version') {
                Config::set('app.version', $castedValue);
                Log::info("App version updated to: {$castedValue}");
            }

            return true;
        } catch (\Exception $e) {
            Log::error("Error setting {$key}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Clear the settings cache
     *
     * @return void
     */
    public static function clearCache()
    {
        try {
            // Get all settings
            $settings = static::all();

            // Clear each setting cache
            foreach ($settings as $setting) {
                Cache::forget(static::$cachePrefix . $setting->key);

                // Special handling for app_version to update config
                if ($setting->key === 'app_version') {
                    Config::set('app.version', $setting->value);
                    Log::info("App version updated in runtime config: {$setting->value}");
                }
            }

            // Also clear the whole settings cache pattern if possible
            if (method_exists(Cache::getStore(), 'flush')) {
                Cache::getStore()->flush();
            }

            // Sync app version in config after cache flush
            try {
                $appVersion = static::where('key', 'app_version')->first();
                if ($appVersion) {
                    Config::set('app.version', $appVersion->value);
                }
            } catch (\Exception $e) {
                Log::error("Error syncing app version after cache clear: " . $e->getMessage());
            }

            Log::info("Settings cache cleared");
        } catch (\Exception $e) {
            Log::error("Error clearing settings cache: " . $e->getMessage());
        }
    }

    /**
     * Cast value according to type
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
                return is_string($value) ? json_decode($value, true) : $value;
            case 'string':
            default:
                return (string) $value;
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
}
