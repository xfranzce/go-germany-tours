<?php

namespace App\Services;

use App\Http\Middleware\CaptureTracking;
use App\Jobs\SendEnquiryNotification;
use App\Jobs\SyncEnquiryToGhl;
use App\Models\Enquiry;
use App\Models\Package;
use App\Models\QuizStep;
use App\Models\Setting;
use App\Services\Ghl\GhlClient;
use App\Support\Countries;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

/** One place that validates rules, builds the consent evidence and saves every enquiry, whatever form it came from. */
class EnquiryService
{
    public static function rules(): array
    {
        return [
            'form_name' => ['required', Rule::in(array_keys(config('gogermany.form_names')))],
            'first_name' => ['required', 'string', 'max:60'],
            'last_name' => ['required', 'string', 'max:60'],
            'email' => ['required', 'email:rfc', 'max:150'],
            'phone_country' => ['required', Rule::in(array_keys(Countries::all()))],
            'phone' => ['required', 'string', 'max:30', 'regex:/^[0-9+\-\s().]{5,30}$/'],
            'travel_date' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:yesterday'],
            'second_quote' => ['nullable', Rule::in(['Yes', 'No', 'Maybe'])],
            'special_request' => ['nullable', 'string', 'max:2000'],
            'package_id' => ['nullable', 'integer', 'exists:packages,id'],
            'time_of_year' => ['nullable', 'string', 'max:40'],
            'group_size' => ['nullable', 'string', 'max:40'],
            'nights' => ['nullable', 'string', 'max:40'],
            'quiz' => ['nullable', 'array', 'max:20'],
            'quiz.*' => ['nullable', 'string', 'max:120'],
            'page_url' => ['nullable', 'string', 'max:500'],
            'consent' => ['accepted'],
            'g-recaptcha-response' => ['required', 'string'],
        ];
    }

    public static function messages(): array
    {
        return [
            'consent.accepted' => 'Please tick the box to agree to the Terms and Conditions, Privacy Policy and SMS consent.',
            'g-recaptcha-response.required' => 'Please tick "I\'m not a robot".',
            'phone.regex' => 'Please enter a valid phone number.',
            'package_id.exists' => 'That package is no longer available.',
        ];
    }

    /** The exact consent wording shown beside every checkbox, with the legal company name filled in. */
    public static function consentText(): string
    {
        return str_replace('{company}', Setting::get('company_name'), config('gogermany.consent_text'));
    }

    public function create(array $data, Request $request): Enquiry
    {
        $e164 = Countries::e164($data['phone_country'], $data['phone']);
        if (! $e164) {
            throw \Illuminate\Validation\ValidationException::withMessages(['phone' => 'Please enter a valid phone number.']);
        }

        $package = ! empty($data['package_id']) ? Package::find($data['package_id']) : null;
        $touch = CaptureTracking::read($request);
        $clean = fn (?string $v, int $max = 255) => $v === null ? null : (mb_substr(trim(preg_replace('/\s+/u', ' ', strip_tags($v))), 0, $max) ?: null);

        $enquiry = Enquiry::create([
            'form_name' => $data['form_name'],
            'first_name' => $clean($data['first_name'], 60),
            'last_name' => $clean($data['last_name'], 60),
            'email' => mb_strtolower(trim($data['email'])),
            'phone_country' => Countries::dial($data['phone_country']),
            'country_code' => $data['phone_country'], // ISO code picked next to the phone number (validated against Countries::all())
            'phone_e164' => $e164,
            'travel_date' => $clean($data['travel_date'] ?? null, 60),
            'second_quote' => $data['second_quote'] ?? null,
            'special_request' => isset($data['special_request']) ? trim(strip_tags($data['special_request'])) ?: null : null,
            'package_id' => $package?->id,
            'package_name' => $package?->title,
            'time_of_year' => $clean($data['time_of_year'] ?? null, 40),
            'group_size' => $clean($data['group_size'] ?? null, 40),
            'nights' => $clean($data['nights'] ?? null, 40),
            'quiz_answers' => $this->cleanQuiz($data['quiz'] ?? []),

            // Consent / chargeback evidence
            'consent_given' => true,
            'consent_text' => self::consentText(),
            'terms_version' => Setting::get('terms_version', config('gogermany.terms_version')),
            'consented_at' => now(),
            'ip_address' => $request->ip(),
            'user_agent' => mb_substr((string) $request->userAgent(), 0, 1000),
            'page_url' => $clean($data['page_url'] ?? $request->headers->get('referer'), 500),

            'source' => $this->source($touch),
            'first_touch' => $touch['first'] ?: null,
            'last_touch' => $touch['last'] ?: null,
            'ghl_status' => app(GhlClient::class)->enabled() ? 'pending' : 'off',
        ]);

        $this->dispatchAfterSave($enquiry);

        return $enquiry;
    }

    /** Keep only answers for known quiz steps; stored as {question key: answer}. */
    private function cleanQuiz(array $answers): ?array
    {
        if (! $answers) {
            return null;
        }
        $keys = QuizStep::pluck('key')->all();
        $clean = [];
        foreach ($answers as $key => $value) {
            if (in_array($key, $keys, true) && is_string($value) && $value !== '') {
                $clean[$key] = mb_substr(strip_tags($value), 0, 120);
            }
        }

        return $clean ?: null;
    }

    private function source(array $touch): string
    {
        foreach (['last', 'first'] as $which) {
            if (! empty($touch[$which]['utm_source'])) {
                return mb_substr($touch[$which]['utm_source'], 0, 100);
            }
            if (! empty($touch[$which]['gclid'])) {
                return 'google-ads';
            }
        }
        $ref = $touch['first']['referrer'] ?? null;

        return $ref ? (parse_url($ref, PHP_URL_HOST) ?: 'referral') : 'direct';
    }

    /** Email and CRM sync never block or fail the enquiry. */
    private function dispatchAfterSave(Enquiry $enquiry): void
    {
        try {
            SendEnquiryNotification::dispatch($enquiry->id);
        } catch (\Throwable $e) {
            Log::error('Enquiry email dispatch failed: '.$e->getMessage(), ['enquiry' => $enquiry->id]);
        }
        if ($enquiry->ghl_status === 'pending') {
            try {
                SyncEnquiryToGhl::dispatch($enquiry->id);
            } catch (\Throwable $e) {
                Log::error('GHL dispatch failed: '.$e->getMessage(), ['enquiry' => $enquiry->id]);
            }
        }
    }
}
