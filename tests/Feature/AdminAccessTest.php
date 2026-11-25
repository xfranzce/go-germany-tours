<?php

namespace Tests\Feature;

use App\Models\AdminUser;
use App\Models\Enquiry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_requires_login(): void
    {
        foreach (['/admin', '/admin/enquiries', '/admin/packages', '/admin/settings', '/admin/users', '/admin/quiz', '/admin/enquiries/export'] as $url) {
            $this->get($url)->assertRedirect('/admin/login');
        }
        $this->post('/admin/logout')->assertRedirect('/admin/login');
        $this->put('/admin/settings', [])->assertRedirect('/admin/login');
    }

    public function test_admin_can_log_in_and_out_and_wrong_passwords_are_rejected(): void
    {
        AdminUser::create(['name' => 'A', 'email' => 'a@example.com', 'password' => 'correct-horse-battery']);

        $this->post('/admin/login', ['email' => 'a@example.com', 'password' => 'wrong'])->assertSessionHasErrors('email');
        $this->assertGuest('admin');

        $this->post('/admin/login', ['email' => 'a@example.com', 'password' => 'correct-horse-battery'])->assertRedirect('/admin');
        $this->assertAuthenticated('admin');
        $this->get('/admin')->assertOk();

        $this->post('/admin/logout')->assertRedirect('/admin/login');
        $this->assertGuest('admin');
    }

    public function test_passwords_are_hashed(): void
    {
        $u = AdminUser::create(['name' => 'A', 'email' => 'h@example.com', 'password' => 'correct-horse-battery']);
        $this->assertNotSame('correct-horse-battery', $u->getRawOriginal('password'));
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('correct-horse-battery', $u->password));
    }

    public function test_admin_login_is_throttled(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->post('/admin/login', ['email' => 'x@example.com', 'password' => 'bad'])->assertStatus(302);
        }
        $this->post('/admin/login', ['email' => 'x@example.com', 'password' => 'bad'])->assertStatus(429);
    }

    public function test_admin_cannot_delete_themselves_or_the_last_admin(): void
    {
        $me = AdminUser::create(['name' => 'Me', 'email' => 'me@example.com', 'password' => 'correct-horse-battery']);
        $this->actingAs($me, 'admin')->delete("/admin/users/{$me->id}")->assertSessionHasErrors('delete');
        $this->assertSame(1, AdminUser::count());

        $other = AdminUser::create(['name' => 'Other', 'email' => 'o@example.com', 'password' => 'correct-horse-battery']);
        $this->actingAs($me, 'admin')->delete("/admin/users/{$other->id}")->assertRedirect('/admin/users');
        $this->assertSame(1, AdminUser::count());
    }

    public function test_admin_can_view_and_update_an_enquiry(): void
    {
        $this->seedContent();
        $admin = AdminUser::create(['name' => 'A', 'email' => 'a@example.com', 'password' => 'correct-horse-battery']);
        $e = Enquiry::create([
            'form_name' => 'contact', 'first_name' => 'T', 'last_name' => 'U', 'email' => 't@example.com', 'phone_country' => '+1', 'phone_e164' => '+15551234567',
            'consent_given' => true, 'consent_text' => 'x', 'terms_version' => 'v', 'consented_at' => now(), 'ghl_status' => 'off',
        ]);

        $this->actingAs($admin, 'admin')->get("/admin/enquiries/{$e->id}")->assertOk()->assertSee('Consent evidence');
        $this->actingAs($admin, 'admin')->put("/admin/enquiries/{$e->id}", ['status' => 'quoted', 'internal_notes' => 'hello'])->assertSessionHas('status');
        $this->assertSame('quoted', $e->fresh()->status);
    }

    public function test_dashboard_shows_period_numbers_sources_and_site_health(): void
    {
        $admin = AdminUser::create(['name' => 'A', 'email' => 'a@example.com', 'password' => 'correct-horse-battery']);
        $base = ['form_name' => 'hero_quiz', 'first_name' => 'T', 'last_name' => 'U', 'email' => 't@example.com', 'phone_country' => '+1', 'phone_e164' => '+15551234567',
            'consent_given' => true, 'consent_text' => 'x', 'terms_version' => 'v', 'consented_at' => now(), 'ghl_status' => 'off'];
        Enquiry::create($base + ['source' => 'google', 'status' => 'won', 'package_name' => 'Romantic Rhine', 'last_touch' => ['utm_source' => 'google', 'utm_medium' => 'cpc', 'utm_campaign' => 'spring-sale', 'gclid' => 'abc']]);
        Enquiry::create($base + ['source' => 'chatgpt.com', 'status' => 'new']);
        $old = Enquiry::create($base + ['source' => 'direct', 'status' => 'new']);
        $old->forceFill(['created_at' => now()->subDays(60)])->save();

        $this->actingAs($admin, 'admin')->get('/admin?days=30')->assertOk()
            ->assertSee('Enquiries, last 30 days')->assertSee('google / cpc')->assertSee('spring-sale')->assertSee('Romantic Rhine')
            ->assertSee('chatgpt.com')->assertSee('Site health')->assertSee('Needs your attention')
            ->assertViewHas('total', 2)->assertViewHas('fromAds', 1)->assertViewHas('won', 1);
        $this->actingAs($admin, 'admin')->get('/admin?days=90')->assertViewHas('total', 3);
        $this->actingAs($admin, 'admin')->get('/admin?days=999')->assertViewHas('days', 30);
    }
}
