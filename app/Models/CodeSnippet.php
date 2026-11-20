<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/** A script, style or HTML block an administrator injects into the public pages (Admin > Header & Footer). */
class CodeSnippet extends Model
{
    use \App\Concerns\LogsActivity;

    public const CACHE_KEY = 'code_snippets.active';

    public const TYPES = ['script' => 'Script (JavaScript)', 'style' => 'Style (CSS)', 'html' => 'HTML / tag (pasted as is)'];

    public const POSITIONS = ['head' => 'In <head>', 'body_start' => 'Start of <body>', 'body_end' => 'End of <body>'];

    public const SCOPES = ['all' => 'Entire website', 'specific' => 'Specific pages only'];

    protected $guarded = [];

    protected $casts = ['is_active' => 'boolean'];

    protected static function booted(): void
    {
        $flush = fn () => Cache::forget(self::CACHE_KEY);
        static::saved($flush);
        static::deleted($flush);
    }

    /** Page paths from the "pages" box, normalised to "/path" (the home page is "/"). */
    public function pagePatterns(): array
    {
        $out = [];
        foreach (preg_split('/\R/', (string) $this->pages) as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }
            $path = parse_url($line, PHP_URL_PATH) ?: $line;   // accept a full URL too
            $out[] = '/'.trim($path, '/');
        }

        return $out;
    }

    /** The markup to output: pasted tags go out untouched, bare code is wrapped for its type. */
    public function markup(): string
    {
        $code = trim($this->code);
        if ($code === '' || $this->type === 'html' || str_starts_with($code, '<')) {
            return $code;
        }

        return $this->type === 'style' ? "<style>\n{$code}\n</style>" : "<script>\n{$code}\n</script>";
    }
}
