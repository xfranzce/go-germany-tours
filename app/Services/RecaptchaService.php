<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/** Server-side reCAPTCHA v2 verification. Fails CLOSED on any problem. Keys: Admin > reCAPTCHA, then .env. */
class RecaptchaService
{
    /** Google's public test keys: every check passes, so they must never be live. */
    public const TEST_SITE_KEY = '6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI';

    public function siteKey(): string
    {
        return (string) (Setting::get('recaptcha_site_key') ?: config('services.recaptcha.site_key'));
    }

    /** Secret saved in the admin (encrypted), else a legacy plain value, else .env. */
    public function secret(): string
    {
        $stored = Setting::get('recaptcha_secret_key_enc');
        if (filled($stored)) {
            try {
                return Crypt::decryptString($stored);
            } catch (\Throwable) {
                // APP_KEY changed or value damaged: fall through
            }
        }

        return (string) (Setting::get('recaptcha_secret_key') ?: config('services.recaptcha.secret_key'));
    }

    /** 'admin', 'env' or null. */
    public function source(): ?string
    {
        if (filled(Setting::get('recaptcha_site_key')) && (filled(Setting::get('recaptcha_secret_key_enc')) || filled(Setting::get('recaptcha_secret_key')))) {
            return 'admin';
        }

        return filled(config('services.recaptcha.site_key')) && filled(config('services.recaptcha.secret_key')) ? 'env' : null;
    }

    public function usingTestKeys(): bool
    {
        return $this->siteKey() === self::TEST_SITE_KEY;
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

    /**
     * Checks the SECRET key with Google using a dummy token: a valid secret is answered with
     * "invalid-input-response", a wrong one with "invalid-input-secret". Cannot check the site key.
     *
     * @return array{ok: bool, message: string}
     */
    public function checkSecret(): array
    {
        if (! $this->secret()) {
            return ['ok' => false, 'message' => 'No secret key is set.'];
        }

        try {
            $r = Http::asForm()->timeout(8)->post('https://www.google.com/recaptcha/api/siteverify', ['secret' => $this->secret(), 'response' => 'admin-key-check']);
        } catch (\Throwable $e) {
            return ['ok' => false, 'message' => 'Could not reach Google to check the key ('.$e->getMessage().').'];
        }
        $codes = (array) $r->json('error-codes', []);
        if (in_array('invalid-input-secret', $codes, true) || in_array('missing-input-secret', $codes, true)) {
            return ['ok' => false, 'message' => 'Google rejected the secret key. Check you copied the SECRET key (not the site key) for a reCAPTCHA v2 checkbox.'];
        }

        return ['ok' => true, 'message' => 'Google accepted the secret key.'];
    }
}
