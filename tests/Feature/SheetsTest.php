<?php

namespace Tests\Feature;

use App\Models\AdminUser;
use App\Models\Setting;
use App\Services\SheetsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SheetsTest extends TestCase
{
    use RefreshDatabase;

    private const URL = 'https://script.google.com/macros/s/AKfycbxTEST123/exec';

    private function admin(): void
    {
        $a = AdminUser::create(['name' => 'A', 'email' => 'a@example.com', 'password' => 'correct-horse-battery', 'role' => 'admin']);
        $this->actingAs($a, 'admin');
        Setting::flush();
    }

    public function test_admin_saves_url_encrypted_and_enables(): void
    {
        $this->admin();
        $this->get('/admin/google-sheets')->assertOk()->assertSee('const TOKEN');
        $this->put('/admin/google-sheets', ['sheets_webhook_url' => self::URL, 'sheets_enabled' => '1'])->assertRedirect('/admin/google-sheets');
        Setting::flush();

        $this->assertStringNotContainsString('AKfycbx', (string) Setting::get('sheets_webhook_url_enc'));
        $this->assertTrue(app(SheetsService::class)->enabled());
        $this->assertSame(self::URL, app(SheetsService::class)->url());
    }

    public function test_rejects_non_google_url(): void
    {
        $this->admin();
        $this->put('/admin/google-sheets', ['sheets_webhook_url' => 'https://evil.example.com/exec'])->assertSessionHasErrors('sheets_webhook_url');
    }

    public function test_test_row_posts_token_and_reports_result(): void
    {
        $this->admin();
        $this->put('/admin/google-sheets', ['sheets_webhook_url' => self::URL, 'sheets_enabled' => '1']);
        Setting::flush();

        Http::fake(['script.google.com/*' => Http::sequence()->push(['ok' => true])->push(['ok' => false, 'error' => 'Wrong token'])]);
        $this->post('/admin/google-sheets/test')->assertSessionHas('status');
        Http::assertSent(fn ($r) => $r['token'] === app(SheetsService::class)->token() && count($r['row']) === count(SheetsService::HEADERS));

        $this->post('/admin/google-sheets/test')->assertSessionHas('sheets_error');
    }

    public function test_editors_cannot_open_sheets_settings(): void
    {
        $e = AdminUser::create(['name' => 'E', 'email' => 'e@example.com', 'password' => 'correct-horse-battery', 'role' => 'editor']);
        $this->actingAs($e, 'admin')->get('/admin/google-sheets')->assertForbidden();
    }
}
