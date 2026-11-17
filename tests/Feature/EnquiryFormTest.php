<?php

namespace Tests\Feature;

use App\Models\Enquiry;
use App\Models\Package;
use App\Models\Setting;
use App\Services\EnquiryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class EnquiryFormTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedContent();
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'form_id' => 'contact', 'form_name' => 'contact',
            'first_name' => 'Ada', 'last_name' => 'Lovelace', 'email' => 'ada@example.com',
            'phone_country' => 'US', 'phone' => '(555) 123-4567',
            'travel_date' => now()->addMonths(8)->format('Y-m-d'), 'second_quote' => 'No', 'special_request' => 'Window seat <b>please</b>',
            'consent' => '1', 'g-recaptcha-response' => 'token',
        ], $overrides);
    }

    private function captcha(bool $success): void
    {
        Http::fake(['www.google.com/recaptcha/api/siteverify' => Http::response(['success' => $success])]);
    }

    public function test_the_phone_country_is_saved_as_the_enquiry_country(): void
    {
        $this->captcha(true);
        $this->postJson('/enquiry', $this->payload(['phone_country' => 'GB', 'phone' => '7911 123456']))->assertSuccessful();
        $this->assertSame('GB', Enquiry::latest('id')->first()->country_code);
    }

    public function test_travel_date_must_be_a_real_date_that_is_not_in_the_past(): void
    {
        $this->captcha(true);

        $this->postJson('/enquiry', $this->payload(['travel_date' => 'September 2027']))->assertStatus(422)->assertJsonValidationErrors('travel_date');
        $this->postJson('/enquiry', $this->payload(['travel_date' => now()->subDays(10)->format('Y-m-d')]))->assertStatus(422)->assertJsonValidationErrors('travel_date');
        $this->postJson('/enquiry', $this->payload(['travel_date' => null]))->assertSuccessful();
        $this->assertSame(1, Enquiry::count());
    }

    public function test_the_travel_date_field_is_a_date_picker_that_blocks_past_days(): void
    {
        $html = $this->get('/contact')->assertOk()->getContent();
        $this->assertMatchesRegularExpression('/type="date"[^>]*name="travel_date"[^>]*min="'.now()->format('Y-m-d').'"/', $html);
    }

    public function test_enquiry_fails_without_consent(): void
    {
        $this->captcha(true);

        $this->postJson('/enquiry', $this->payload(['consent' => null]))
            ->assertStatus(422)->assertJsonValidationErrors('consent');

        $this->assertSame(0, Enquiry::count());
    }

    public function test_consent_checkbox_is_unticked_by_default_and_uses_the_exact_wording(): void
    {
        Setting::put('company_name', 'Acme Travel GmbH');
        $html = $this->get('/contact')->assertOk()->getContent();

        $this->assertMatchesRegularExpression('/<input id="contact-consent"[^>]*>/', $html);
        $this->assertDoesNotMatchRegularExpression('/<input id="contact-consent"[^>]*checked/', $html);
        $this->assertStringContainsString('provided by Acme Travel GmbH. By providing my phone number, I consent to receive SMS messages from Acme Travel GmbH regarding my enquiry, booking updates, itinerary updates, reminders, and customer support. Message and data rates may apply. Reply STOP to unsubscribe or HELP for assistance. Consent is not a condition of purchase.', strip_tags($html));
    }

    public function test_enquiry_fails_without_a_valid_recaptcha(): void
    {
        $this->captcha(false);
        $this->postJson('/enquiry', $this->payload())->assertStatus(422)->assertJsonValidationErrors('g-recaptcha-response');

        $this->postJson('/enquiry', $this->payload(['g-recaptcha-response' => null]))->assertStatus(422)->assertJsonValidationErrors('g-recaptcha-response');
        $this->assertSame(0, Enquiry::count());
    }

    public function test_enquiry_fails_closed_when_google_is_unreachable(): void
    {
        Http::fake(['www.google.com/*' => fn () => throw new \Illuminate\Http\Client\ConnectionException('down')]);

        $this->postJson('/enquiry', $this->payload())->assertStatus(422)->assertJsonValidationErrors('g-recaptcha-response');
        $this->assertSame(0, Enquiry::count());
    }

    public function test_valid_enquiry_is_saved_with_consent_evidence(): void
    {
        $this->captcha(true);
        Setting::put('company_name', 'Acme Travel GmbH');
        $package = Package::first();

        $this->withHeaders(['User-Agent' => 'PHPUnit-Agent/1.0'])
            ->postJson('/enquiry', $this->payload(['form_name' => 'package_quote', 'package_id' => $package->id,
                'page_url' => 'https://example.test/packages/x', 'quiz' => ['destination' => 'Cochem', 'bogus_key' => 'x']]))
            ->assertOk()->assertJson(['ok' => true]);

        $e = Enquiry::sole();
        $this->assertSame('package_quote', $e->form_name);
        $this->assertSame($package->id, $e->package_id);
        $this->assertSame($package->title, $e->package_name);
        $this->assertSame('+15551234567', $e->phone_e164);
        $this->assertSame('Window seat please', $e->special_request);   // sanitised
        $this->assertSame(['destination' => 'Cochem'], $e->quiz_answers); // unknown keys dropped

        // Consent / chargeback evidence
        $this->assertTrue($e->consent_given);
        $this->assertSame(EnquiryService::consentText(), $e->consent_text);
        $this->assertStringContainsString('Acme Travel GmbH', $e->consent_text);
        $this->assertSame(config('gogermany.terms_version'), $e->terms_version);
        $this->assertNotNull($e->consented_at);
        $this->assertSame('127.0.0.1', $e->ip_address);
        $this->assertSame('PHPUnit-Agent/1.0', $e->user_agent);
        $this->assertSame('https://example.test/packages/x', $e->page_url);
        $this->assertSame('off', $e->ghl_status); // GHL credentials are blank in phpunit.xml
    }

    public function test_landing_on_a_tagged_url_sets_30_day_first_and_last_touch_cookies(): void
    {
        $res = $this->get('/?utm_source=google&utm_medium=cpc&utm_campaign=spring&gclid=ABC123&gbraid=GB1');

        $first = json_decode($res->getCookie('ggt_ft')->getValue(), true);
        $last = json_decode($res->getCookie('ggt_lt')->getValue(), true);
        foreach ([$first, $last] as $touch) {
            $this->assertSame('google', $touch['utm_source']);
            $this->assertSame('ABC123', $touch['gclid']);
            $this->assertSame('GB1', $touch['gbraid']);
            $this->assertStringContainsString('gclid=ABC123', $touch['landing_page']);
        }
        $this->assertEqualsWithDelta(30 * 24 * 3600, $res->getCookie('ggt_ft')->getExpiresTime() - time(), 120);
    }

    public function test_the_saved_enquiry_carries_the_first_and_last_touch_from_the_request(): void
    {
        $request = \Illuminate\Http\Request::create('/enquiry', 'POST', [], [
            'ggt_ft' => json_encode(['utm_source' => 'google', 'gclid' => 'ABC123', 'landing_page' => 'https://x.test/?gclid=ABC123']),
            'ggt_lt' => json_encode(['utm_source' => 'bing', 'utm_campaign' => 'later']),
        ]);
        $data = $this->payload(['form_name' => 'hero_quiz']);
        $data['phone_country'] = 'US';

        $e = app(EnquiryService::class)->create($data, $request);

        $this->assertSame('google', $e->first_touch['utm_source']);
        $this->assertSame('ABC123', $e->first_touch['gclid']);
        $this->assertSame('bing', $e->last_touch['utm_source']);
        $this->assertSame('bing', $e->source);
    }

    public function test_honeypot_discards_bot_submissions_silently(): void
    {
        $this->captcha(true);
        $this->postJson('/enquiry', $this->payload(['contact_url' => 'http://spam.example']))->assertOk();
        $this->assertSame(0, Enquiry::count());
    }

    public function test_invalid_phone_and_email_are_rejected(): void
    {
        $this->captcha(true);
        $this->postJson('/enquiry', $this->payload(['phone' => '12', 'email' => 'nope']))->assertStatus(422)->assertJsonValidationErrors(['phone', 'email']);
    }

    public function test_non_js_submission_redirects_to_thank_you_and_errors_return_to_the_form(): void
    {
        $this->captcha(true);
        $this->post('/enquiry', $this->payload())->assertRedirect(route('thank-you'));

        $this->from('/contact')->post('/enquiry', $this->payload(['consent' => null]))
            ->assertRedirect('/contact#contact')->assertSessionHasErrors('consent', null, 'contact');
    }

    public function test_enquiries_are_rate_limited_per_ip(): void
    {
        $this->captcha(true);
        for ($i = 0; $i < 3; $i++) {
            $this->postJson('/enquiry', $this->payload(['email' => "u{$i}@example.com"]))->assertOk();
        }
        $this->postJson('/enquiry', $this->payload(['email' => 'u9@example.com']))->assertStatus(429);
    }

    public function test_thank_you_page_is_noindex_and_only_fires_the_conversion_after_a_submission(): void
    {
        $this->get('/thank-you')->assertOk()->assertSee('noindex', false)->assertDontSee('enquiry_thank_you', false);

        $this->captcha(true);
        $this->post('/enquiry', $this->payload());
        $this->followingRedirects()->get('/thank-you')->assertSee('enquiry_thank_you', false);
    }
}
