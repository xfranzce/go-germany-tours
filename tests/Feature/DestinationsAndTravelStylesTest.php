<?php

namespace Tests\Feature;

use App\Models\AdminUser;
use App\Models\Destination;
use App\Models\Package;
use App\Models\TravelStyle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DestinationsAndTravelStylesTest extends TestCase
{
    use RefreshDatabase;

    public function test_destinations_overview_and_page_show_taglines_places_and_packages(): void
    {
        $this->seedContent();
        $d = Destination::where('slug', 'cochem')->first();

        $this->get('/destinations')->assertOk()->assertSee('Destinations worth slowing down for')->assertSee('All our')
            ->assertSee('Castles, river bends &amp; vineyards', false)->assertSee('Explore Cochem')->assertSee(route('destinations.show', 'cochem'));
        $this->get('/destinations/cochem')->assertOk()->assertSee('Explore Cochem')->assertSee('Trips that visit')->assertSee('Bernkastel-Kues')
            ->assertSee('More destinations to')->assertSee('Ready for')->assertSee(route('packages.index', ['destination' => 'cochem']), false)
            ->assertSee($d->packages()->published()->first()->title);
    }

    public function test_every_destination_has_five_sample_tours_after_a_full_seed(): void
    {
        $this->seedContent();
        $this->seed([\Database\Seeders\MoreSamplePackagesSeeder::class, \Database\Seeders\FiveToursPerDestinationSeeder::class]);
        foreach (Destination::all() as $d) {
            $this->assertSame(5, $d->packages()->published()->count(), $d->name);
        }
        $this->assertTrue(Package::where('slug', 'elbe-sandstone-hiking-self-drive')->first()->travelStyles->pluck('slug')->contains('adventure'));
    }

    public function test_travel_style_pages_list_only_their_packages(): void
    {
        $this->seedContent();
        $style = TravelStyle::where('slug', 'food-wine')->first();
        $in = $style->packages()->published()->first();
        $out = Package::published()->whereDoesntHave('travelStyles', fn ($q) => $q->whereKey($style->id))->first();

        $this->get('/travel-styles')->assertOk()->assertSee('Travel styles for Germany')->assertSee('Not sure which')->assertSee('Food &amp; Wine', false)->assertSee('Adventure');
        $this->get('/travel-styles/food-wine')->assertOk()->assertSee('Food &amp; Wine Tours of Germany', false)->assertSee('Other ways to see')->assertSee('Ready for a')
            ->assertSee($in->title)->assertDontSee('&amp;amp;', false)
            // (the header's Tours menu lists every package, so check the page's own list rather than the HTML)
            ->assertViewHas('packages', fn ($list) => $list->contains('id', $in->id) && ! $list->contains('id', $out->id));
        $this->get('/travel-styles/nope')->assertNotFound();
    }

    public function test_menu_and_sitemap_link_the_new_pages(): void
    {
        $this->seedContent();
        $this->get('/')->assertOk()->assertSee('mega-destinations', false)->assertSee('mega-styles', false)->assertSee(route('travel-styles.show', 'luxury'));
        $this->get('/sitemap.xml')->assertOk()->assertSee(route('destinations.index'))->assertSee(route('travel-styles.show', 'scenic'));
    }

    public function test_admin_manages_travel_styles_and_ticks_packages_into_them(): void
    {
        $this->seedContent();
        $editor = AdminUser::create(['name' => 'E', 'email' => 'e@example.com', 'password' => 'correct-horse-battery', 'role' => 'editor']);
        $this->actingAs($editor, 'admin');

        $this->get('/admin/travel-styles')->assertOk()->assertSee('Food &amp; Wine', false);
        $this->post('/admin/travel-styles', ['name' => 'Family', 'tagline' => 'All ages', 'highlights' => "Kid-friendly stops\nShorter drives", 'is_active' => '1'])
            ->assertRedirect('/admin/travel-styles');
        $family = TravelStyle::where('slug', 'family')->first();
        $this->assertSame('All ages', $family->tagline);

        $p = Package::first();
        $this->put("/admin/packages/{$p->id}", ['title' => $p->title, 'tour_type_id' => $p->tour_type_id, 'destination_id' => $p->destination_id,
            'nights' => $p->nights, 'currency' => 'USD', 'is_published' => '1', 'travel_styles' => [$family->id]])->assertSessionHasNoErrors();
        $this->assertSame([$family->id], $p->fresh()->travelStyles->pluck('id')->all());
        $this->get('/travel-styles/family')->assertOk()->assertSee($p->title);

        $this->put("/admin/destinations/{$p->destination_id}", ['name' => $p->destination->name, 'tagline' => 'New line', 'places' => "Somewhere: nice", 'is_active' => '1'])->assertSessionHasNoErrors();
        $this->assertSame('New line', $p->destination->fresh()->tagline);

        $this->delete("/admin/travel-styles/{$family->id}")->assertRedirect('/admin/travel-styles');
        $this->assertNotNull($p->fresh());
        $this->assertCount(0, $p->fresh()->travelStyles);
    }
}
