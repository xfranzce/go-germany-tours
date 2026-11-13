<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

/** Key/value site settings with config('gogermany.defaults') fallbacks. */
class Setting extends Model
{
    protected $guarded = [];

    private static ?array $memo = null;

    public static function all_(): array
    {
        if (self::$memo !== null) {
            return self::$memo;
        }
        try {
            $stored = Cache::remember('settings.all', 3600, fn () => static::query()->pluck('value', 'key')->all());
        } catch (\Throwable) {
            $stored = []; // database not migrated yet
        }

        return self::$memo = array_merge(config('gogermany.defaults'), array_filter($stored, fn ($v) => $v !== null && $v !== ''));
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        $all = static::all_();

        return ($all[$key] ?? '') !== '' ? $all[$key] : $default;
    }

    public static function put(string $key, ?string $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
        static::flush();
    }

    public static function flush(): void
    {
        Cache::forget('settings.all');
        self::$memo = null;
    }
}
