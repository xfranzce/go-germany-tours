<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Admin > Maintenance mode: while switched on, visitors get a 503 maintenance page. The admin area and logged-in admin users are never blocked. */
class MaintenanceMode
{
    public const DEFAULT_MESSAGE = 'We are making a few improvements to the site. Please check back shortly. If you need us in the meantime, get in touch below.';

    public static function enabled(): bool
    {
        try {
            return Setting::get('maintenance_enabled') === '1';
        } catch (\Throwable) {
            return false;   // never take the site down because of a settings problem
        }
    }

    public function handle(Request $request, Closure $next): Response
    {
        if (! static::enabled() || $request->is('admin', 'admin/*') || auth('admin')->check()) {
            return $next($request);
        }

        $message = Setting::get('maintenance_message') ?: self::DEFAULT_MESSAGE;
        $headers = ['Retry-After' => '3600', 'X-Robots-Tag' => 'noindex, nofollow', 'Cache-Control' => 'no-store, private'];

        if ($request->expectsJson() || $request->isMethod('POST')) {
            return response()->json(['message' => $message], 503, $headers);
        }

        return response()->view('maintenance', ['message' => $message], 503, $headers);
    }
}
