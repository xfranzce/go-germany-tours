<?php

namespace App\Support;

use App\Models\CodeSnippet;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/** Renders the admin-managed header/footer code for one position on the current page. */
class Snippets
{
    public static function render(string $position, ?string $path = null): string
    {
        try {
            $all = Cache::rememberForever(CodeSnippet::CACHE_KEY, fn () => Schema::hasTable('code_snippets')
                ? CodeSnippet::where('is_active', true)->orderBy('sort')->orderBy('id')->get()
                    ->map(fn (CodeSnippet $s) => ['position' => $s->position, 'scope' => $s->scope, 'patterns' => $s->pagePatterns(), 'markup' => $s->markup()])->all()
                : []);
        } catch (\Throwable) {
            return '';   // never break a public page because of this feature
        }

        $path = '/'.trim($path ?? request()->path(), '/');
        $out = [];
        foreach ($all as $s) {
            if ($s['position'] !== $position || $s['markup'] === '') {
                continue;
            }
            if ($s['scope'] === 'specific' && ! collect($s['patterns'])->contains(fn ($p) => Str::is($p, $path))) {
                continue;
            }
            $out[] = $s['markup'];
        }

        return $out ? "\n".implode("\n", $out)."\n" : '';
    }
}
