<?php

namespace Tests\Feature;

use App\Models\AdminUser;
use Database\Seeders\CatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminRolesTest extends TestCase
{
    use RefreshDatabase;

    private function user(string $role): AdminUser
    {
        return AdminUser::create(['name' => ucfirst($role), 'email' => $role.'@example.com', 'password' => 'correct-horse-battery', 'role' => $role]);
    }

    public function test_editor_can_use_packages_posts_pages_media_and_categories_and_tags(): void
    {
        $this->seed(CatalogSeeder::class);
        $editor = $this->user('editor');

        foreach (['/admin/packages', '/admin/packages/create', '/admin/posts', '/admin/pages', '/admin/media', '/admin/tour-types', '/admin/destinations'] as $url) {
            $this->actingAs($editor, 'admin')->get($url)->assertOk();
        }
    }

    public function test_editor_is_blocked_from_everything_else(): void
    {
        $editor = $this->user('editor');

        foreach (['/admin/enquiries', '/admin/settings', '/admin/users', '/admin/users/create', '/admin/testimonials', '/admin/faqs', '/admin/quiz'] as $url) {
            $this->actingAs($editor, 'admin')->get($url)->assertForbidden();
        }
        $this->actingAs($editor, 'admin')->get('/admin/enquiries/export')->assertForbidden();
        $this->actingAs($editor, 'admin')->post('/admin/users', ['name' => 'X', 'email' => 'x@example.com', 'password' => 'correct-horse-battery', 'password_confirmation' => 'correct-horse-battery', 'role' => 'admin'])->assertForbidden();
        $this->assertSame(1, AdminUser::count());
    }

    public function test_editor_lands_on_packages_and_only_sees_allowed_menu_items(): void
    {
        $editor = $this->user('editor');

        $this->actingAs($editor, 'admin')->get('/admin')->assertRedirect('/admin/packages');
        $html = $this->actingAs($editor, 'admin')->get('/admin/packages')->getContent();
        foreach (['Packages', 'Pages', 'Travel guides', 'Media', 'Tour types', 'Destinations'] as $label) {
            $this->assertStringContainsString($label, $html);
        }
        foreach (['Enquiries', 'Site settings', 'Admin users', 'Testimonials', 'FAQs', 'Hero quiz'] as $label) {
            $this->assertStringNotContainsString($label, $html);
        }
    }

    public function test_administrator_keeps_full_access_and_can_create_an_editor(): void
    {
        $admin = $this->user('admin');

        foreach (['/admin', '/admin/enquiries', '/admin/settings', '/admin/users', '/admin/testimonials', '/admin/faqs', '/admin/quiz', '/admin/packages'] as $url) {
            $this->actingAs($admin, 'admin')->get($url)->assertOk();
        }

        $this->actingAs($admin, 'admin')->post('/admin/users', [
            'name' => 'New Editor', 'email' => 'new-editor@example.com', 'role' => 'editor',
            'password' => 'correct-horse-battery', 'password_confirmation' => 'correct-horse-battery',
        ])->assertRedirect('/admin/users');
        $this->assertTrue(AdminUser::where('email', 'new-editor@example.com')->first()->isEditor());
    }

    public function test_the_last_administrator_cannot_be_demoted_and_roles_are_validated(): void
    {
        $admin = $this->user('admin');

        $this->actingAs($admin, 'admin')->put("/admin/users/{$admin->id}", ['name' => 'Admin', 'email' => $admin->email, 'role' => 'editor'])->assertSessionHasErrors('role');
        $this->assertSame('admin', $admin->fresh()->role);

        $this->put("/admin/users/{$admin->id}", ['name' => 'Admin', 'email' => $admin->email, 'role' => 'superuser'])->assertSessionHasErrors('role');
    }
}
