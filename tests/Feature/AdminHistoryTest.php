<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\AdminUser;
use App\Models\Package;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminHistoryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedContent();
    }

    private function user(string $role = 'admin'): AdminUser
    {
        return AdminUser::create(['name' => ucfirst($role).' Person', 'email' => "$role@example.com", 'password' => 'correct-horse-battery', 'role' => $role]);
    }

    public function test_changes_by_a_signed_in_user_are_recorded_with_who_and_what(): void
    {
        $admin = $this->user();
        $this->actingAs($admin, 'admin');
        $package = Package::first();
        $package->update(['title' => 'Brand new name']);

        $log = ActivityLog::where('action', 'updated')->where('subject_type', 'Package')->latest('id')->first();
        $this->assertNotNull($log);
        $this->assertSame('Admin Person', $log->user_name);
        $this->assertSame('admin@example.com', $log->user_email);
        $this->assertSame('Brand new name', $log->changes['title'][1]);

        $package->delete();
        $this->assertTrue(ActivityLog::where('action', 'trashed')->where('subject_id', $package->id)->exists());
        $package->restore();
        $this->assertTrue(ActivityLog::where('action', 'restored')->where('subject_id', $package->id)->exists());
    }

    public function test_nothing_is_recorded_for_visitors_or_seeding(): void
    {
        Package::first()->update(['title' => 'Changed by nobody']);
        $this->assertSame(0, ActivityLog::count());
    }

    public function test_secrets_are_never_stored(): void
    {
        $this->actingAs($this->user(), 'admin');
        Setting::put('ghl_api_token_enc', 'super-secret-value');
        Setting::put('ghl_api_token_enc', 'another-secret');
        $json = json_encode(ActivityLog::all()->toArray());
        $this->assertStringNotContainsString('super-secret-value', $json);
        $this->assertStringNotContainsString('another-secret', $json);
        $this->assertStringContainsString('(hidden)', $json);
    }

    public function test_login_and_logout_are_recorded(): void
    {
        $this->user();
        $this->post('/admin/login', ['email' => 'admin@example.com', 'password' => 'wrong']);
        $this->post('/admin/login', ['email' => 'admin@example.com', 'password' => 'correct-horse-battery']);
        $this->post('/admin/logout');

        $this->assertEqualsCanonicalizing(['login_failed', 'login', 'logout'], ActivityLog::pluck('action')->all());
    }

    public function test_history_page_is_for_administrators_only_and_lists_entries(): void
    {
        $admin = $this->user();
        $this->actingAs($admin, 'admin');
        Package::first()->update(['title' => 'Visible in history']);

        $this->get('/admin/history')->assertOk()->assertSee('Visible in history')->assertSee('Admin Person');
        $this->get('/admin/history?user=nobody@example.com')->assertOk()->assertDontSee('Visible in history');

        auth('admin')->logout();
        $this->actingAs($this->user('editor'), 'admin');
        $this->get('/admin/history')->assertForbidden();
    }
}
