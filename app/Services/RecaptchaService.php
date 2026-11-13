<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/** Server-side reCAPTCHA v2 verification. Fails CLOSED on any problem. */
class RecaptchaService
{
    public function siteKey(): string
    {
        return (string) (Setting::get('recaptcha_site_key') ?: config('services.recaptcha.site_key'));
    }

    private function secret(): string
    {
        return (string) (Setting::get('recaptcha_secret_key') ?: config('services.recaptcha.secret_key'));
    }

    public function verify(?string $token, ?string $ip = null): bool
    {
        if (! $token || ! $this->secret()) {
            return false;
        }

        try {
            $response = Http::asForm()->timeout(6)->post('https://www.google.com/recaptcha/api/siteverify', array_filter([
                'secret' => $this->secret(), 'response' => $token, 'remoteip' => $ip,
            ]));

            return $response->successful() && $response->json('success') === true;
        } catch (\Throwable $e) {
            Log::warning('reCAPTCHA verification unreachable: '.$e->getMessage());

            return false; // fail closed
        }
    }
}
