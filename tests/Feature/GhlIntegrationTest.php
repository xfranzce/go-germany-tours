<?php

namespace Tests\Feature;

use App\Jobs\SyncEnquiryToGhl;
use App\Models\Enquiry;
use App\Services\Ghl\GhlClient;
use App\Services\Ghl\GhlFields;
use App\Services\Ghl\GhlPayloadBuilder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class GhlIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedContent();
        Cache::flush();
    }

    private function enquiry(string $form, array $extra = []): Enquiry
    {
        return Enquiry::create($extra + [
            'form_name' => $form, 'first_name' => 'Grace', 'last_name' => 'Hopper', 'email' => 'grace@example.com',
            'phone_country' => '+1', 'phone_e164' => '+15551234567', 'travel_date' => now()->addMonths(6)->format('Y-m-d'), 'second_quote' => 'Yes',
            'special_request' => 'Vegetarian', 'package_name' => 'Mosel Valley', 'quiz_answers' => ['destination' => 'Cochem', 'budget' => '$2K per person'],
            'consent_given' => true, 'consent_text' => 'x', 'terms_version' => 'v1', 'consented_at' => now(), 'source' => 'google',
            'first_touch' => ['utm_source' => 'google', 'utm_medium' => 'cpc', 'utm_campaign' => 'first-camp', 'utm_term' => 'germany tours', 'utm_content' => 'ad1',
                'gclid' => 'GCLID-1', 'gbraid' => 'GB-1', 'wbraid' => 'WB-1', 'referrer' => 'https://google.com/', 'landing_page' => 'https://x.test/?a=1'],
            'last_touch' => ['utm_source' => 'bing', 'utm_campaign' => 'last-camp', 'gclid' => 'GCLID-2', 'landing_page' => 'https://x.test/?b=2'],
            'ghl_status' => 'pending',
        ]);
    }

    public function test_plain_utm_and_form_source_fields_use_the_latest_touch_then_the_first(): void
    {
        $e = $this->enquiry('hero_quiz', [
            'page_url' => 'https://x.test/packages/mosel-valley?utm_source=x',
            'last_touch' => ['utm_source' => 'bing', 'utm_campaign' => 'last-camp', 'gclid' => 'GCLID-2', 'utm_device' => 'm', 'utm_adcreative' => '777', 'utm_adposition' => '1t2'],
        ]);
        $v = (new GhlPayloadBuilder())->fieldValues($e);

        $this->assertSame('bing', $v['utm_source']);          // latest touch wins
        $this->assertSame('cpc', $v['utm_medium']);           // missing in last touch: falls back to first touch
        $this->assertSame('last-camp', $v['utm_campaign']);
        $this->assertSame('ad1', $v['utm_content']);
        $this->assertSame('germany tours', $v['utm_term']);
        $this->assertSame('GCLID-2', $v['utm_gclid']);
        $this->assertSame('m', $v['utm_device']);
        $this->assertSame('777', $v['utm_adcreative']);
        $this->assertSame('1t2', $v['utm_adposition']);
        $this->assertSame(config('gogermany.form_names')['hero_quiz'].' (/packages/mosel-valley)', $v['form_source']);
        $this->assertArrayHasKey('utm_device', GhlFields::all());
        $this->assertArrayHasKey('form_source', GhlFields::all());
    }

    public function test_google_ads_value_track_parameters_are_captured_into_the_tracking_cookie(): void
    {
        $this->assertContains('utm_device', config('gogermany.tracking_keys'));
        $response = $this->get('/?utm_source=google&utm_device=m&utm_adcreative=123&utm_adposition=1t1');
        $touch = json_decode(\Illuminate\Support\Facades\Cookie::getQueuedCookies()[0]->getValue() ?? '', true) ?: [];
        $this->assertSame('m', $touch['utm_device'] ?? null);
        $this->assertSame('123', $touch['utm_adcreative'] ?? null);
        $response->assertOk();
    }

    public function test_contact_country_follows_the_phone_country_chosen_on_the_form(): void
    {
        $b = new GhlPayloadBuilder();
        $this->assertSame('GB', $b->contact($this->enquiry('contact', ['country_code' => 'GB']))['country']);
        $this->assertSame('US', $b->contact($this->enquiry('contact', ['country_code' => 'US']))['country']);
        $this->assertArrayNotHasKey('country', $b->contact($this->enquiry('contact')), 'unknown country is never guessed');
    }

    #[DataProvider('forms')]
    public function test_payload_includes_utm_and_tracking_fields_for_every_form_type(string $form): void
    {
        $values = (new GhlPayloadBuilder())->fieldValues($this->enquiry($form));

        foreach (['utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content', 'gclid', 'gbraid', 'wbraid', 'referrer', 'landing_page'] as $key) {
            $this->assertArrayHasKey(GhlFields::tracking($key, 'first'), $values, "{$form}: {$key} first");
        }
        $this->assertSame('google', $values['UTM Source First']);
        $this->assertSame('bing', $values['UTM Source Last']);          // latest touch kept separately
        $this->assertSame('GCLID-1', $values['GCLID First']);
        $this->assertSame('GCLID-2', $values['GCLID Last']);
        $this->assertSame(config('gogermany.form_names')[$form], $values[GhlFields::FORM_NAME]);
        $this->assertSame('Yes', $values[GhlFields::CONSENT_GIVEN]);
        $this->assertSame('v1', $values[GhlFields::CONSENT_VERSION]);
        $this->assertSame('Cochem', $values['Quiz: Destination']);
        $this->assertStringContainsString('budget: $2K per person', $values[GhlFields::QUIZ_FULL]);
        $this->assertSame('Mosel Valley', $values[GhlFields::PACKAGE]);
        $this->assertContains(config('gogermany.form_names')[$form], (new GhlPayloadBuilder())->tags($this->enquiry($form)));
    }

    public static function forms(): array
    {
        return [['hero_quiz'], ['header_modal'], ['package_quote'], ['package_question'], ['contact']];
    }

    public function test_integration_is_off_without_credentials_and_makes_no_http_calls(): void
    {
        Http::fake();
        $e = $this->enquiry('contact');
        $this->assertFalse(app(GhlClient::class)->enabled());

        SyncEnquiryToGhl::dispatchSync($e->id);

        Http::assertNothingSent();
        $this->assertSame('off', $e->fresh()->ghl_status);
    }

    private function enableGhl(): void
    {
        config(['services.ghl.token' => 'pit-test-token', 'services.ghl.location_id' => 'LOC123']);
    }

    private function fakeGhl(array $existingContact = null): void
    {
        $fields = collect(GhlFields::all())->keys()->map(fn ($n, $i) => ['id' => "fid{$i}", 'name' => $n])->all();
        Http::fake([
            '*/locations/LOC123/customFields' => Http::response(['customFields' => $fields]),
            '*/contacts/search/duplicate*' => $existingContact ? Http::response(['contact' => $existingContact]) : Http::response(['message' => 'none'], 404),
            '*/contacts/upsert' => Http::response(['contact' => ['id' => 'CONTACT-9'], 'new' => ! $existingContact]),
        ]);
    }

    private function fieldId(string $name): string
    {
        return 'fid'.array_search($name, array_keys(GhlFields::all()));
    }

    public function test_sync_upserts_contact_with_tracking_fields_and_records_the_contact_id(): void
    {
        $this->enableGhl();
        $this->fakeGhl();
        $e = $this->enquiry('hero_quiz');

        SyncEnquiryToGhl::dispatchSync($e->id);

        Http::assertSent(function (Request $r) {
            if (! str_ends_with($r->url(), '/contacts/upsert')) {
                return false;
            }
            $this->assertSame('Bearer pit-test-token', $r->header('Authorization')[0]);
            $this->assertSame('2021-07-28', $r->header('Version')[0]);
            $body = $r->data();
            $this->assertSame('+15551234567', $body['phone']);
            $this->assertSame('LOC123', $body['locationId']);
            $this->assertSame('grace@example.com', $body['email']);
            $byId = collect($body['customFields'])->pluck('field_value', 'id');
            $this->assertSame('google', $byId[$this->fieldId('UTM Source First')]);
            $this->assertSame('bing', $byId[$this->fieldId('UTM Source Last')]);
            $this->assertSame('GCLID-2', $byId[$this->fieldId('GCLID Last')]);
            $this->assertContains('Hero quiz', $body['tags']);

            return true;
        });

        $e->refresh();
        $this->assertSame('synced', $e->ghl_status);
        $this->assertSame('CONTACT-9', $e->ghl_contact_id);
        $this->assertNull($e->ghl_error);
    }

    public function test_returning_contact_keeps_first_touch_fields_but_gets_new_last_touch(): void
    {
        $this->enableGhl();
        $this->fakeGhl(['id' => 'CONTACT-9', 'customFields' => [['id' => $this->fieldId('UTM Source First'), 'value' => 'ORIGINAL-FIRST']]]);

        SyncEnquiryToGhl::dispatchSync($this->enquiry('contact')->id);

        Http::assertSent(function (Request $r) {
            if (! str_ends_with($r->url(), '/contacts/upsert')) {
                return false;
            }
            $ids = collect($r->data()['customFields'])->pluck('id')->all();
            $this->assertNotContains($this->fieldId('UTM Source First'), $ids);   // not overwritten
            $this->assertContains($this->fieldId('UTM Campaign First'), $ids);    // empty before, so filled
            $this->assertContains($this->fieldId('UTM Source Last'), $ids);       // latest touch updated

            return true;
        });
    }

    public function test_ghl_failure_never_blocks_the_enquiry_and_never_leaks_the_token(): void
    {
        $this->enableGhl();
        Http::fake(['*' => Http::response(['message' => 'Forbidden'], 403)]);
        $e = $this->enquiry('contact');

        SyncEnquiryToGhl::dispatchSync($e->id);

        $e->refresh();
        $this->assertSame('failed', $e->ghl_status);
        $this->assertStringContainsString('403', $e->ghl_error);
        $this->assertStringNotContainsString('pit-test-token', (string) $e->ghl_error);
        $this->assertNotNull(Enquiry::find($e->id)); // enquiry itself is untouched
    }

    public function test_form_submission_succeeds_even_when_ghl_is_down(): void
    {
        $this->enableGhl();
        Http::fake([
            'www.google.com/recaptcha/*' => Http::response(['success' => true]),
            '*leadconnectorhq.com*' => Http::response('boom', 500),
        ]);

        $this->postJson('/enquiry', [
            'form_id' => 'contact', 'form_name' => 'contact', 'first_name' => 'A', 'last_name' => 'B', 'email' => 'a@example.com',
            'phone_country' => 'US', 'phone' => '5551234567', 'consent' => '1', 'g-recaptcha-response' => 't',
        ])->assertOk()->assertJson(['ok' => true]);

        $e = Enquiry::sole();
        $this->assertSame('failed', $e->ghl_status);
    }

    public function test_field_definitions_cover_every_required_field_and_each_quiz_step(): void
    {
        $names = array_keys(GhlFields::all());
        foreach (['Preferred Travel Date', 'Second Quote Preference', 'Special Request', 'Package', 'Quiz Answers (Full)', 'Consent Given',
            'Consent Timestamp', 'Consent Text Version', 'Form Name', 'UTM Source First', 'UTM Source Last', 'GCLID First', 'WBRAID Last',
            'Referrer First', 'Landing Page URL Last', 'Quiz: Destination', 'Quiz: Time Of Year', 'Quiz: Likelihood'] as $n) {
            $this->assertContains($n, $names);
        }
        $this->assertSame(count($names), count(array_unique($names)));
    }

    public function test_ghl_setup_creates_only_missing_fields(): void
    {
        $this->enableGhl();
        $all = array_keys(GhlFields::all());
        $existing = array_slice($all, 0, 5);
        Http::fake([
            '*/locations/LOC123/customFields' => fn (Request $r) => $r->method() === 'GET'
                ? Http::response(['customFields' => collect($existing)->map(fn ($n, $i) => ['id' => "e{$i}", 'name' => $n])->all()])
                : Http::response(['customField' => ['id' => 'new']]),
        ]);

        $this->artisan('ghl:setup')->expectsOutputToContain((count($all) - 5).' created, 5 already existed')->assertSuccessful();

        $created = Http::recorded(fn (Request $r) => $r->method() === 'POST')->count();
        $this->assertSame(count($all) - 5, $created);
    }

    public function test_ghl_commands_refuse_to_run_when_off(): void
    {
        Http::fake();
        $this->artisan('ghl:setup')->assertFailed();
        $this->artisan('ghl:test')->assertFailed();
        Http::assertNothingSent();
    }
}
