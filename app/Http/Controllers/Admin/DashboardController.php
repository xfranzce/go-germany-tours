<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Middleware\MaintenanceMode;
use App\Models\Enquiry;
use App\Models\Setting;
use App\Services\Ghl\GhlClient;
use App\Services\RecaptchaService;
use App\Services\SheetsService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

/**
 * Admin dashboard: enquiry numbers for a chosen period (7/30/90 days), a per-day chart, the pipeline,
 * where enquiries came from (source/medium, campaign, form, package), recent enquiries and a site health checklist.
 * Everything is computed from the enquiries table and settings; nothing is estimated.
 */
class DashboardController extends Controller
{
    public const RANGES = [7, 30, 90];

    public function __invoke(Request $request, GhlClient $ghl, SheetsService $sheets, RecaptchaService $recaptcha)
    {
        if (auth('admin')->user()->isEditor()) {
            return redirect()->route('admin.packages.index');
        }

        $days = in_array((int) $request->query('days'), self::RANGES, true) ? (int) $request->query('days') : 30;
        $from = now()->startOfDay()->subDays($days - 1);
        $prevFrom = $from->copy()->subDays($days);

        $period = Enquiry::where('created_at', '>=', $from)->get(['id', 'created_at', 'status', 'form_name', 'package_name', 'source', 'first_touch', 'last_touch']);
        $prevCount = Enquiry::whereBetween('created_at', [$prevFrom, $from])->count();

        // per-day counts, every day in the range (zero days included)
        $byDay = $period->groupBy(fn ($e) => $e->created_at->toDateString())->map->count();
        $perDay = collect(range(0, $days - 1))->mapWithKeys(fn ($i) => [$k = $from->copy()->addDays($i)->toDateString() => $byDay[$k] ?? 0]);

        $statuses = config('gogermany.statuses');
        $pipeline = collect($statuses)->map(fn ($label, $key) => ['label' => $label, 'count' => $period->where('status', $key)->count()]);

        $fromAds = $period->filter(fn ($e) => $this->isGoogleAds($e))->count();
        $won = $pipeline['won']['count'] ?? 0;

        return view('admin.dashboard', [
            'days' => $days,
            'from' => $from,
            'total' => $period->count(),
            'prevCount' => $prevCount,
            'newCount' => Enquiry::where('status', 'new')->count(),
            'waiting' => Enquiry::where('status', 'new')->where('created_at', '<', now()->subDay())->count(),
            'ghlFailed' => Enquiry::where('ghl_status', 'failed')->count(),
            'sheetFailed' => Enquiry::where('sheet_status', 'failed')->count(),
            'fromAds' => $fromAds,
            'won' => $won,
            'perDay' => $perDay,
            'pipeline' => $pipeline,
            'sources' => $this->top($period, fn ($e) => $this->sourceMedium($e)),
            'campaigns' => $this->top($period, fn ($e) => $this->touch($e, 'utm_campaign')),
            'forms' => $this->top($period, fn ($e) => $e->formLabel()),
            'journeys' => $this->top($period, fn ($e) => $e->package_name ?: null),
            'recent' => Enquiry::latest()->take(8)->get(),
            'health' => $this->health($ghl, $sheets, $recaptcha),
        ]);
    }

    /** Latest touch first, else first touch. */
    private function touch(Enquiry $e, string $key): ?string
    {
        return ($e->last_touch[$key] ?? null) ?: (($e->first_touch[$key] ?? null) ?: null);
    }

    private function isGoogleAds(Enquiry $e): bool
    {
        foreach (['gclid', 'gbraid', 'wbraid'] as $k) {
            if ($this->touch($e, $k)) {
                return true;
            }
        }

        return strtolower((string) $this->touch($e, 'utm_source')) === 'google' && in_array(strtolower((string) $this->touch($e, 'utm_medium')), ['cpc', 'ppc', 'paid'], true);
    }

    private function sourceMedium(Enquiry $e): string
    {
        $source = $this->touch($e, 'utm_source') ?: ($e->source ?: 'direct');
        $medium = $this->touch($e, 'utm_medium');

        return $medium ? "{$source} / {$medium}" : $source;
    }

    /** Top 6 values with their counts. */
    private function top(Collection $rows, callable $key): Collection
    {
        return $rows->map($key)->filter()->countBy()->sortDesc()->take(6);
    }

    /** Setup checklist: [ok, title, detail when not ok, fix route]. */
    private function health(GhlClient $ghl, SheetsService $sheets, RecaptchaService $recaptcha): array
    {
        $placeholders = collect(Setting::all_())->filter(fn ($v) => is_string($v) && str_contains($v, '[CONFIRM'))->count();

        return [
            [! $recaptcha->usingTestKeys(), 'Real reCAPTCHA keys', 'The public TEST keys are live, so spam is not blocked.', 'admin.recaptcha.edit'],
            [$ghl->enabled(), 'GoHighLevel connected', 'Enquiries are not being sent to your CRM.', 'admin.ghl.edit'],
            [$sheets->enabled(), 'Google Sheet log connected', 'Enquiries are not copied to the Google Sheet.', 'admin.sheets.edit'],
            [filled(Setting::get('phone')), 'Phone number set', 'No phone number is shown on the site.', 'admin.settings.edit'],
            [filled(Setting::get('gtm_id')), 'Google Tag Manager ID set', 'Conversion events (enquiry_submitted, enquiry_thank_you) are not being sent.', 'admin.settings.edit'],
            [filled(Setting::get('notification_emails')), 'Notification email set', 'Nobody is emailed when an enquiry arrives.', 'admin.settings.edit'],
            [$placeholders === 0, 'No [CONFIRM] placeholders in settings', "{$placeholders} setting(s) still hold a placeholder.", 'admin.settings.edit'],
            [! MaintenanceMode::enabled(), 'Site is live', 'Maintenance mode is on: visitors cannot see the site.', 'admin.maintenance.edit'],
        ];
    }
}
