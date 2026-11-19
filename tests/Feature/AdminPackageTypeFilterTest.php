<?php

namespace Tests\Feature;

use App\Models\AdminUser;
use App\Models\Package;
use App\Models\TourType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPackageTypeFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_filter_packages_by_tour_type(): void
    {
        $this->seedContent();
        $admin = AdminUser::create(['name' => 'A', 'email' => 'a@example.com', 'password' => 'correct-horse-battery', 'role' => 'admin']);
        $selfDrive = TourType::where('slug', 'self-drive')->firstOrFail();
        $mine = Package::where('tour_type_id', $selfDrive->id)->firstOrFail();
        $other = Package::where('tour_type_id', '!=', $selfDrive->id)->firstOrFail();

        $this->actingAs($admin, 'admin')->get('/admin/packages')->assertOk()
            ->assertSee($mine->title)->assertSee($other->title)->assertSee('Filter by tour type');

        $this->get('/admin/packages?type=self-drive')->assertOk()
            ->assertSee($mine->title)->assertDontSee($other->title);

        // an unknown type is ignored, not an error
        $this->get('/admin/packages?type=nope')->assertOk()->assertSee($other->title);
    }
}
