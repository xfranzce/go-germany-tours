<?php

namespace Tests\Feature;

use App\Models\AdminUser;
use App\Models\Setting;
use App\Services\Ghl\GhlClient;
use App\Services\Ghl\GhlFields;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GhlAdminTest extends TestCase
{
    use RefreshDatabase;

    private const TOKEN = 'pit-12345678-aaaa-bbbb-cccc-1234567890ab';

    private function admin(string $role = 'admin'): AdminUser
    {
        return AdminUser::create(['name' => 'A', 'email' => $role.'@example.com', 'password' => 'correct-horse-battery', 'role' => $role]);
    }

    protected function setUp(): void
    {
        parent::setUp();
        Setting::flush();
    }

    public function test_saving_credentials_turns_ghl_on_and_stores_the_token_encrypted_and_never_shows_it(): void
    {
        $this->assertFalse(app(GhlClient::class)->enabled(), 'off until configured');

        $this->actingAs($this->admin(), 'admin')->put('/admin/gohighlevel', [
            'ghl_location_id' => 've9EPM428h8vShlRW1KT', 'ghl_api_token' => self::TOKEN, 'ghl_enabled' => '1',
        ])->assertRedirect('/admin/gohighlevel');

        Setting::flush();
        $client = app(GhlClient::class);
        $this->assertTrue($client->enabled());
        $this->assertSame(self::TOKEN, $client->token());
        $this->assertSame('ve9EPM428h8vShlRW1KT', $client->locationId());
        $this->assertSame('admin', $client->source());

        $stored = Setting::query()->where('key', 'ghl_api_token_enc')->value('value');
        $this->assertNotSame(self::TOKEN, $stored);
        $this->assertStringNotContainsString('pit-1234', (string) $stored, 'token is encrypted at rest');

        $html = $this->get('/admin/gohighlevel')->assertOk()->getContent();
        $this->assertStringNotContainsString(self::TOKEN, $html, 'the token is never sent back to the browser');
        $this->assertStringContainsString('90ab', $html, 'only the last 4 characters are hinted');
    }

    public function test_blank_token_keeps_the_saved_one_and_the_switch_pauses_the_sync(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin, 'admin')->put('/admin/gohighlevel', ['ghl_location_id' => 've9EPM428h8vShlRW1KT', 'ghl_api_token' => self::TOKEN, 'ghl_enabled' => '1']);

        $this->put('/admin/gohighlevel', ['ghl_location_id' => 've9EPM428h8vShlRW1KT', 'ghl_api_token' => '', 'ghl_enabled' => '0']);
        Setting::flush();
        $client = app(GhlClient::class);
        $this->assertSame(self::TOKEN, $client->token(), 'blank keeps the existing token');
        $this->assertFalse($client->enabled(), 'switched off in the admin');

        $this->put('/admin/gohighlevel', ['ghl_location_id' => 've9EPM428h8vShlRW1KT', 'ghl_enabled' => '1', 'remove_token' => '1']);
        Setting::flush();
        $this->assertFalse(app(GhlClient::class)->enabled(), 'removed token = off');
    }

    public function test_bad_location_id_is_rejected(): void
    {
        $this->actingAs($this->admin(), 'admin')->put('/admin/gohighlevel', ['ghl_location_id' => 'not valid!', 'ghl_api_token' => self::TOKEN])->assertSessionHasErrors('ghl_location_id');
    }

    public function test_connection_test_reports_missing_fields_and_setup_creates_them(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin, 'admin')->put('/admin/gohighlevel', ['ghl_location_id' => 've9EPM428h8vShlRW1KT', 'ghl_api_token' => self::TOKEN, 'ghl_enabled' => '1']);
        Setting::flush();

        Http::fake([
            '*/locations/ve9EPM428h8vShlRW1KT/customFields' => Http::sequence()
                ->push(['customFields' => []])         // test: none exist
                ->push(['customFields' => []])         // setup: list
                ->push(['customField' => ['id' => 'x']]), // setup: create (repeated below)
            '*/locations/ve9EPM428h8vShlRW1KT' => Http::response(['location' => ['name' => 'Go Germany Sub-account']]),
        ]);

        $this->post('/admin/gohighlevel/test')->assertSessionHas('ghl_messages', fn ($m) => str_contains(implode(' ', $m), 'Go Germany Sub-account') && str_contains(implode(' ', $m), (string) count(GhlFields::all())));
        Http::assertSent(fn ($r) => $r->hasHeader('Authorization', 'Bearer '.self::TOKEN));
    }

    public function test_setup_button_creates_only_the_missing_custom_fields(): void
    {
        $this->actingAs($this->admin(), 'admin')->put('/admin/gohighlevel', ['ghl_location_id' => 've9EPM428h8vShlRW1KT', 'ghl_api_token' => self::TOKEN, 'ghl_enabled' => '1']);
        Setting::flush();

        $existing = array_slice(array_keys(GhlFields::all()), 0, 3);
        Http::fake([
            '*/locations/ve9EPM428h8vShlRW1KT/customFields' => fn ($request) => $request->method() === 'GET'
                ? Http::response(['customFields' => array_map(fn ($n) => ['id' => 'id-'.$n, 'name' => $n], $existing)])
                : Http::response(['customField' => ['id' => 'new']]),
        ]);

        $expected = count(GhlFields::all()) - 3;
        $this->post('/admin/gohighlevel/setup')->assertSessionHas('status', "{$expected} custom field(s) created, 3 already existed.");
        Http::assertSentCount(1 + $expected);
    }

    public function test_test_button_explains_when_ghl_is_off_and_editors_are_blocked(): void
    {
        $this->actingAs($this->admin(), 'admin')->post('/admin/gohighlevel/test')->assertSessionHasErrors('ghl');
        Http::assertNothingSent();

        $this->actingAs($this->admin('editor'), 'admin')->get('/admin/gohighlevel')->assertForbidden();
        $this->post('/admin/gohighlevel/test')->assertForbidden();
    }
}
