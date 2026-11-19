<?php

namespace Tests\Feature;

use App\Models\AdminUser;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MaintenanceModeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedContent();
    }

    private function admin(string $role = 'admin'): AdminUser
    {
        return AdminUser::create(['name' => 'A', 'email' => "$role@example.com", 'password' => 'correct-horse-battery', 'role' => $role]);
    }

    public function test_the_site_is_live_by_default(): void
    {
        $this->get('/')->assertOk()->assertDontSee('We will be back');
    }

    public function test_visitors_get_a_503_maintenance_page_while_it_is_on(): void
    {
        Setting::put('maintenance_enabled', '1');
        Setting::put('maintenance_message', 'Upgrading our booking system.');

        foreach (['/', '/about', '/packages', '/tours/self-drive'] as $path) {
            $this->get($path)->assertStatus(503)->assertSee('We will be back')->assertSee('Upgrading our booking system.')
                ->assertHeader('Retry-After')->assertHeader('X-Robots-Tag', 'noindex, nofollow');
        }
        $this->post('/enquiry', ['first_name' => 'x'], ['Accept' => 'application/json'])->assertStatus(503)->assertJson(['message' => 'Upgrading our booking system.']);
    }

    public function test_the_standard_message_is_used_when_none_is_set(): void
    {
        Setting::put('maintenance_enabled', '1');
        $this->get('/')->assertStatus(503)->assertSee('Please check back shortly');
    }

    public function test_admin_login_and_logged_in_admins_are_never_blocked(): void
    {
        Setting::put('maintenance_enabled', '1');

        $this->get('/admin/login')->assertOk();   // an administrator can always get back in

        $this->actingAs($this->admin(), 'admin');
        $this->get('/')->assertOk();               // and can preview the live site
        $this->get('/admin/maintenance')->assertOk()->assertSee('ON: visitors see the maintenance page');
        $this->get('/admin/packages')->assertOk()->assertSee('Maintenance mode is ON');
    }

    public function test_admin_can_turn_it_on_and_off_from_the_admin(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin, 'admin')->get('/admin/maintenance')->assertOk()->assertSee('OFF: the site is live');

        $this->put('/admin/maintenance', ['enabled' => '1', 'message' => 'Back at noon.'])->assertRedirect('/admin/maintenance');
        $this->assertSame('1', Setting::get('maintenance_enabled'));
        auth('admin')->logout();
        $this->get('/')->assertStatus(503)->assertSee('Back at noon.');

        $this->actingAs($admin, 'admin');
        $this->put('/admin/maintenance', ['enabled' => '0', 'message' => 'Back at noon.'])->assertRedirect('/admin/maintenance');
        auth('admin')->logout();
        $this->get('/')->assertOk()->assertDontSee('We will be back');
    }

    public function test_editors_cannot_change_maintenance_mode(): void
    {
        $this->actingAs($this->admin('editor'), 'admin')->get('/admin/maintenance')->assertForbidden();
        $this->put('/admin/maintenance', ['enabled' => '1'])->assertForbidden();
        $this->assertNotSame('1', Setting::get('maintenance_enabled'));
    }
}
