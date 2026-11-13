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
