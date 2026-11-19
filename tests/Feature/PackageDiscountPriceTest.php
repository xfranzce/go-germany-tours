<?php

namespace Tests\Feature;

use App\Models\AdminUser;
use App\Models\Package;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PackageDiscountPriceTest extends TestCase
{
    use RefreshDatabase;

    private function payload(Package $p, array $o): array
    {
        return $o + ['title' => $p->title, 'tour_type_id' => $p->tour_type_id, 'destination_id' => $p->destination_id, 'nights' => $p->nights, 'currency' => 'USD', 'price_from' => '3900'];
    }

    public function test_the_from_price_must_be_higher_than_the_current_price_and_the_error_says_so(): void
    {
        $this->seedContent();
        $admin = AdminUser::create(['name' => 'A', 'email' => 'a@example.com', 'password' => 'correct-horse-battery', 'role' => 'admin']);
        $p = Package::first();
        $this->actingAs($admin, 'admin');

        $this->put("/admin/packages/{$p->id}", $this->payload($p, ['price_was' => '2900']))->assertSessionHasErrors('price_was');
        $this->put("/admin/packages/{$p->id}", $this->payload($p, ['price_was' => '3900']))->assertSessionHasErrors('price_was');
        $this->put("/admin/packages/{$p->id}", $this->payload($p, ['price_from' => '', 'price_was' => '2900']))->assertSessionHasErrors('price_was');

        $this->put("/admin/packages/{$p->id}", $this->payload($p, ['price_was' => '4500']))->assertSessionHasNoErrors();
        $this->assertSame('From $4,500 pp', $p->fresh()->wasLabel());
        $this->assertSame('$3,900 pp', $p->fresh()->priceLabel());

        $this->put("/admin/packages/{$p->id}", $this->payload($p, ['price_was' => '']))->assertSessionHasNoErrors();
        $this->assertNull($p->fresh()->wasLabel());
    }
}
