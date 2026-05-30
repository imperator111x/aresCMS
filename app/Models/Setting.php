<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    /**
     * In-request cache to avoid repeated setting queries.
     *
     * @var array<string, mixed>
     */
    protected static array $runtimeCache = [];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'key',
        'value',
    ];

    /**
     * Get a setting value by key.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function getValue(string $key, $default = null)
    {
        if (array_key_exists($key, self::$runtimeCache)) {
            $cached = self::$runtimeCache[$key];

            return $cached === null ? $default : $cached;
        }

        $setting = self::where('key', $key)->latest('id')->first();
        $value = $setting ? $setting->value : null;
        self::$runtimeCache[$key] = $value;

        return $value ?? $default;
    }

    /**
     * Get a setting as boolean with robust normalization.
     */
    public static function getBoolValue(string $key, bool $default = false): bool
    {
        $value = self::getValue($key, $default);

        if (is_bool($value)) {
            return $value;
        }

        if (is_int($value) || is_float($value)) {
            return (bool) $value;
        }

        if (is_string($value)) {
            $normalized = strtolower(trim($value));

            if (in_array($normalized, ['1', 'true', 'on', 'yes'], true)) {
                return true;
            }

            if (in_array($normalized, ['0', 'false', 'off', 'no', ''], true)) {
                return false;
            }
        }

        return (bool) $value;
    }

    /**
     * Set a setting value by key.
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public static function setValue(string $key, $value): void
    {
        $query = self::where('key', $key);

        if ($query->exists()) {
            // Keep duplicate rows in sync if they exist.
            $query->update(['value' => $value]);
            self::$runtimeCache[$key] = $value;

            return;
        }

        self::create([
            'key' => $key,
            'value' => $value,
        ]);

        self::$runtimeCache[$key] = $value;
    }
}
