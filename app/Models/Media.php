<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Media extends Model
{
    protected $guarded = [];

    protected $casts = ['widths' => 'array'];

    private static ?array $byPath = null;

    /** Look up by stem path; one query per request. */
    public static function forPath(?string $path): ?self
    {
        if (! $path) {
            return null;
        }
        self::$byPath ??= static::query()->get()->keyBy('path')->all();

        return self::$byPath[$path] ?? null;
    }

    /** The photo whose stored path starts with "media/{stem}-" (stems are seeded as media/<stem>-<random>); uses the same one-query lookup as forPath(). */
    public static function byStem(string $stem): ?self
    {
        self::$byPath ??= static::query()->get()->keyBy('path')->all();
        $prefix = "media/{$stem}-";
        foreach (self::$byPath as $path => $media) {
            if (str_starts_with($path, $prefix)) {
                return $media;
            }
        }

        return null;
    }

    public function url(?int $width = null): string
    {
        $width ??= max($this->widths);

        return Storage::disk('public')->url("{$this->path}-{$width}.webp");
    }

    public function srcset(): string
    {
        return collect($this->widths)->sort()->map(fn ($w) => $this->url($w)." {$w}w")->implode(', ');
    }
}
