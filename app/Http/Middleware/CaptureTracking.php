<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Symfony\Component\HttpFoundation\Response;

/**
 * Stores first-touch and last-touch attribution (UTMs, click ids, referrer,
 * landing page) in cookies for 30 days so every enquiry can carry them.
 *   ggt_ft: first touch, written once.   ggt_lt: latest touch, overwritten.
 */
class CaptureTracking
{
    public const FIRST = 'ggt_ft';

    public const LAST = 'ggt_lt';

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! $request->isMethod('GET') || $request->is('admin*', 'up', 'storage/*') || $request->expectsJson()) {
            return $response;
        }

        $params = [];
        foreach (config('gogermany.tracking_keys') as $key) {
            $value = $request->query($key);
            if (is_string($value) && $value !== '') {
                $params[$key] = mb_substr(strip_tags($value), 0, 255);
            }
        }

        $referrer = $request->headers->get('referer');
        $refHost = $referrer ? parse_url($referrer, PHP_URL_HOST) : null;
        $external = $refHost && $refHost !== $request->getHost();

        $touch = null;
        if ($params || $external) {
            $touch = $params + [
                'referrer' => $external ? mb_substr($referrer, 0, 500) : null,
                'landing_page' => mb_substr($request->fullUrl(), 0, 500),
                'captured_at' => now()->toIso8601String(),
            ];
        }

        $minutes = (int) config('gogermany.tracking_days') * 1440;

        if (! $request->cookie(self::FIRST)) {
            $first = $touch ?? ['referrer' => null, 'landing_page' => mb_substr($request->fullUrl(), 0, 500), 'captured_at' => now()->toIso8601String()];
            Cookie::queue(self::FIRST, json_encode($first), $minutes);
        }
        if ($touch) {
            Cookie::queue(self::LAST, json_encode($touch), $minutes);
        }

        return $response;
    }

    /** @return array{first: array, last: array} */
    public static function read(Request $request): array
    {
        $decode = fn (?string $raw) => is_string($raw) && is_array($d = json_decode($raw, true)) ? $d : [];
        $first = $decode($request->cookie(self::FIRST));
        $last = $decode($request->cookie(self::LAST)) ?: $first;

        return ['first' => $first, 'last' => $last];
    }
}
