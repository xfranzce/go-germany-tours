<?php

namespace Tests\Feature;

use App\Models\AdminUser;
use App\Models\Setting;
use App\Services\RecaptchaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class RecaptchaAdminTest extends TestCase
{
    use RefreshDatabase;

    private const SITE = '6LcAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA';

    private const SECRET = '6LcBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBB';

    protected function setUp(): void
    {
        parent::setUp();
        Setting::flush();
    }

    private function admin(string $role = 'admin'): AdminUser
    {
        return AdminUser::create(['name' => 'A', 'email' => $role.'@example.com', 'password' => 'correct-horse-battery', 'role' => $role]);
    }

    public function test_saved_keys_are_used_and_the_secret_is_encrypted_and_never_shown(): void
    {
        $this->actingAs($this->admin(), 'admin')->put('/admin/recaptcha', ['recaptcha_site_key' => self::SITE, 'recaptcha_secret_key' => self::SECRET])
            ->assertRedirect('/admin/recaptcha');
        Setting::flush();

        $captcha = app(RecaptchaService::class);
        $this->assertSame(self::SITE, $captcha->siteKey());
        $this->assertSame(self::SECRET, $captcha->secret());
        $this->assertSame('admin', $captcha->source());
        $this->assertStringNotContainsString(self::SECRET, (string) Setting::query()->where('key', 'recaptcha_secret_key_enc')->value('value'));

        $html = $this->get('/admin/recaptcha')->assertOk()->getContent();
        $this->assertStringNotContainsString(self::SECRET, $html);
        $this->assertStringContainsString('BBBB', $html, 'only the last 4 characters are hinted');
    }

    public function test_blank_secret_keeps_the_saved_one_and_remove_clears_it(): void
    {
        $this->actingAs($this->admin(), 'admin')->put('/admin/recaptcha', ['recaptcha_site_key' => self::SITE, 'recaptcha_secret_key' => self::SECRET]);
        $this->put('/admin/recaptcha', ['recaptcha_site_key' => self::SITE, 'recaptcha_secret_key' => '']);
        Setting::flush();
        $this->assertSame(self::SECRET, app(RecaptchaService::class)->secret());

        $this->put('/admin/recaptcha', ['recaptcha_site_key' => self::SITE, 'remove_secret' => '1']);
        Setting::flush();
        $this->assertSame('test-secret-key', app(RecaptchaService::class)->secret(), 'falls back to .env');
    }

    public function test_malformed_keys_are_rejected(): void
    {
        $this->actingAs($this->admin(), 'admin')->put('/admin/recaptcha', ['recaptcha_site_key' => 'too short', 'recaptcha_secret_key' => 'bad key!'])
            ->assertSessionHasErrors(['recaptcha_site_key', 'recaptcha_secret_key']);
    }

    public function test_google_test_site_key_is_flagged(): void
    {
        $this->actingAs($this->admin(), 'admin')->put('/admin/recaptcha', ['recaptcha_site_key' => RecaptchaService::TEST_SITE_KEY]);
        Setting::flush();

        $this->assertTrue(app(RecaptchaService::class)->usingTestKeys());
        $this->get('/admin/recaptcha')->assertSee('public TEST keys', false);
    }

    public function test_secret_check_distinguishes_a_valid_from_a_rejected_secret(): void
    {
        $this->actingAs($this->admin(), 'admin')->put('/admin/recaptcha', ['recaptcha_site_key' => self::SITE, 'recaptcha_secret_key' => self::SECRET]);
        Setting::flush();

        Http::fake(['www.google.com/recaptcha/api/siteverify' => Http::sequence()
            ->push(['success' => false, 'error-codes' => ['invalid-input-response']])
            ->push(['success' => false, 'error-codes' => ['invalid-input-secret']])]);
        $this->post('/admin/recaptcha/test')->assertSessionHas('status', 'Google accepted the secret key.');
        $this->post('/admin/recaptcha/test')->assertSessionHas('recaptcha_error');
    }

    public function test_editors_cannot_open_the_screen(): void
    {
        $this->actingAs($this->admin('editor'), 'admin')->get('/admin/recaptcha')->assertForbidden();
        $this->put('/admin/recaptcha', ['recaptcha_site_key' => self::SITE])->assertForbidden();
    }
}
