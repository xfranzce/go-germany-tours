<?php

namespace App\Http\Controllers\Admin;

use App\Models\Setting;
use App\Services\RecaptchaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

/** Admin > reCAPTCHA: site key + secret key for the enquiry forms (secret stored encrypted, never shown). */
class RecaptchaController extends AdminController
{
    public function edit(RecaptchaService $captcha)
    {
        $secret = $captcha->secret();

        return view('admin.recaptcha', [
            'siteKey' => Setting::get('recaptcha_site_key', ''),
            'secretHint' => filled($secret) ? '••••••••'.substr($secret, -4) : null,
            'source' => $captcha->source(),
            'testKeys' => $captcha->usingTestKeys(),
            'domain' => parse_url((string) config('app.url'), PHP_URL_HOST),
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'recaptcha_site_key' => ['nullable', 'string', 'regex:/^[A-Za-z0-9_-]{30,60}$/'],
            'recaptcha_secret_key' => ['nullable', 'string', 'regex:/^[A-Za-z0-9_-]{30,60}$/'],
        ], [
            'recaptcha_site_key.regex' => 'The site key is about 40 letters, numbers, "-" and "_" with no spaces.',
            'recaptcha_secret_key.regex' => 'The secret key is about 40 letters, numbers, "-" and "_" with no spaces.',
        ]);

        Setting::put('recaptcha_site_key', $data['recaptcha_site_key'] ?? null);
        if (filled($data['recaptcha_secret_key'] ?? null)) {
            Setting::put('recaptcha_secret_key_enc', Crypt::encryptString(trim($data['recaptcha_secret_key'])));
            Setting::put('recaptcha_secret_key', null); // drop any older plain-text copy
        }
        if ($request->boolean('remove_secret')) {
            Setting::put('recaptcha_secret_key_enc', null);
            Setting::put('recaptcha_secret_key', null);
        }

        return redirect()->route('admin.recaptcha.edit')->with('status', 'reCAPTCHA keys saved.');
    }

    public function test(RecaptchaService $captcha)
    {
        $r = $captcha->checkSecret();

        return back()->with($r['ok'] ? 'status' : 'recaptcha_error', $r['message']);
    }
}
