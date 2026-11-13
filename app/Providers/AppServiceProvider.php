<?php

namespace App\Providers;

use App\Models\Setting;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Enquiry forms: per-IP limits (minute and hour). Tune in config/gogermany.php.
        RateLimiter::for('enquiries', fn (Request $request) => [
            Limit::perMinute((int) config('gogermany.rate_limit_per_minute'))->by($request->ip()),
            Limit::perHour((int) config('gogermany.rate_limit_per_hour'))->by($request->ip()),
        ]);

        // Admin login: 5 attempts per minute per email+IP.
        RateLimiter::for('admin-login', fn (Request $request) => Limit::perMinute(5)->by(strtolower((string) $request->input('email')).'|'.$request->ip()));

        // $site = all site settings (brand, contact details, tracking IDs...) in every view.
        View::composer('*', function ($view) {
            $view->with('site', Setting::all_());
        });
    }
}
