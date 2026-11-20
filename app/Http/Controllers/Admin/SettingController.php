<?php

namespace App\Http\Controllers\Admin;

use App\Models\Setting;
use App\Services\Ghl\GhlClient;
use Illuminate\Http\Request;

class SettingController extends AdminController
{
    /** Plain text settings saved from the form. */
    private const TEXT = [
        'brand_name' => 'max:80', 'company_name' => 'max:150', 'tagline' => 'max:200',
        'phone' => 'max:30', 'phone_display' => 'max:40', 'email' => 'max:120', 'address' => 'max:300',
        'facebook_url' => 'max:250', 'instagram_url' => 'max:250', 'youtube_url' => 'max:250',
        'notification_emails' => 'max:500', 'gtm_id' => 'max:20',
        'modal_discount_percent' => 'max:3', 'footer_text' => 'max:300', 'google_rating' => 'max:4', 'google_reviews_url' => 'max:500',
        'default_meta_title' => 'max:70', 'default_meta_description' => 'max:320', 'terms_version' => 'max:32',
    ];

    public function edit(GhlClient $ghl)
    {
        return view('admin.settings', ['ghlOn' => $ghl->enabled()]);
    }

    public function update(Request $request)
    {
        $rules = $this->imageRules('logo') + $this->imageRules('favicon') + $this->imageRules('og');
        foreach (self::TEXT as $key => $max) {
            $rules[$key] = ['nullable', 'string', $max];
        }
        $rules['email'] = ['nullable', 'email', 'max:120'];
        $rules['gtm_id'] = ['nullable', 'regex:/^GTM-[A-Z0-9]{4,12}$/i'];
        $rules['facebook_url'] = $rules['instagram_url'] = $rules['youtube_url'] = ['nullable', 'url', 'max:250'];
        $rules['google_rating'] = ['nullable', 'numeric', 'min:1', 'max:5'];
        $rules['google_reviews_url'] = ['nullable', 'url', 'max:500'];
        $rules['modal_discount_percent'] = ['nullable', 'integer', 'min:1', 'max:99'];
        $rules['notification_emails'] = ['nullable', 'string', 'max:500', function ($attr, $value, $fail) {
            foreach (preg_split('/[,;\s]+/', (string) $value, -1, PREG_SPLIT_NO_EMPTY) as $e) {
                if (! filter_var($e, FILTER_VALIDATE_EMAIL)) {
                    $fail("'{$e}' is not a valid email address.");
                }
            }
        }];
        $data = $request->validate($rules, ['gtm_id.regex' => 'The GTM ID should look like GTM-XXXXXXX.']);

        foreach (array_keys(self::TEXT) as $key) {
            // A blank brand/company name would break the consent text: keep the previous value.
            if (in_array($key, ['brand_name', 'company_name'], true) && blank($data[$key] ?? null)) {
                continue;
            }
            Setting::put($key, $data[$key] ?? null);
        }
        Setting::put('google_rating_enabled', $request->boolean('google_rating_enabled') && filled($data['google_rating'] ?? null) ? '1' : '0');   // never on without a rating
        foreach (['logo' => 'logo', 'favicon' => 'favicon', 'og' => 'default_og_image'] as $field => $key) {
            Setting::put($key, $this->handleImage($request, $field, Setting::get($key)));
        }

        return redirect()->route('admin.settings.edit')->with('status', 'Settings saved.');
    }
}
