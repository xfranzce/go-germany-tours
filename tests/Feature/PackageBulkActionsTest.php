<?php

namespace Tests\Feature;

use App\Models\AdminUser;
use App\Models\Package;
use Database\Seeders\CatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PackageBulkActionsTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): AdminUser
    {
        return AdminUser::create(['name' => 'A', 'email' => 'a@example.com', 'password' => 'correct-horse-battery']);
    }

    public function test_bulk_unpublish_feature_sample_trash_and_restore(): void
    {
        $this->seed(CatalogSeeder::class);
        $admin = $this->admin();
        $ids = Package::take(2)->pluck('id')->all();
        $other = Package::whereNotIn('id', $ids)->first();
        $before = $other->only(['is_published', 'is_featured', 'is_sample']);

        $this->actingAs($admin, 'admin')->post('/admin/packages/bulk/unpublish', ['ids' => $ids])->assertSessionHas('status', '2 packages unpublished.');
        $this->assertSame(0, Package::whereIn('id', $ids)->where('is_published', true)->count());

        $this->post('/admin/packages/bulk/feature', ['ids' => $ids]);
        $this->post('/admin/packages/bulk/unsample', ['ids' => $ids]);
        $this->assertSame(2, Package::whereIn('id', $ids)->where('is_featured', true)->where('is_sample', false)->count());
        $this->post('/admin/packages/bulk/sample', ['ids' => $ids]);
        $this->assertSame(2, Package::whereIn('id', $ids)->where('is_sample', true)->count());

        // Trash hides them from the public site and the main list; Restore brings them back.
        $this->post('/admin/packages/bulk/trash', ['ids' => $ids])->assertSessionHas('status', '2 packages moved to trash.');
        $this->assertSame(2, Package::onlyTrashed()->count());
        $this->get('/admin/packages')->assertDontSee(Package::onlyTrashed()->first()->title);
        $this->get('/admin/packages?view=trash')->assertSee(Package::onlyTrashed()->first()->title);

        $this->post('/admin/packages/bulk/restore', ['ids' => $ids])->assertSessionHas('status', '2 packages restored.');
        $this->assertSame(0, Package::onlyTrashed()->count());

        $this->assertSame($before, $other->fresh()->only(['is_published', 'is_featured', 'is_sample']), 'unticked packages are untouched');
    }

    public function test_bulk_requires_a_selection_and_a_login(): void
    {
        $this->seed(CatalogSeeder::class);
        $this->post('/admin/packages/bulk/trash', ['ids' => [1]])->assertRedirect();
        $this->assertSame(0, Package::onlyTrashed()->count());

        $this->actingAs($this->admin(), 'admin')->post('/admin/packages/bulk/trash', [])->assertSessionHasErrors('ids');
        $this->assertSame(0, Package::onlyTrashed()->count());
    }
}
