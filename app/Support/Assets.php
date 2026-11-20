<?php

namespace App\Support;

/** Helpers for the built (Vite) assets. */
class Assets
{
    /** @var array<string, string|null> */
    private static array $css = [];

    /**
     * The compiled stylesheet as a string, so public pages can put it straight into the HTML instead of waiting for a
     * separate render-blocking request (faster first paint, mostly on first visits from ads). Null when there is no
     * production build (npm run dev / no build): the layout then falls back to the normal linked file.
     * The font URLs inside are absolute (/build/assets/...), so inlining is safe.
     */
    public static function inlineCss(string $entry = 'resources/css/app.css'): ?string
    {
        if (is_file(public_path('hot'))) {
            return null;
        }

        return self::$css[$entry] ??= (function () use ($entry): ?string {
            $manifest = public_path('build/manifest.json');
            $file = is_file($manifest) ? (json_decode((string) file_get_contents($manifest), true)[$entry]['file'] ?? null) : null;
            $path = $file ? public_path('build/'.$file) : null;

            return $path && is_file($path) ? trim((string) file_get_contents($path)) : null;
        })();
    }
}
