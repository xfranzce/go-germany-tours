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

    public function test_destinations_overview_shows_testimonials_between_how_it_works_and_faq(): void
    {
        $this->seedContent();
        \App\Models\Testimonial::forceCreate(['name' => 'Test Traveler', 'quote' => 'Test quote for the destinations page.', 'is_active' => true, 'is_placeholder' => false, 'sort' => 0]);

        $this->get('/destinations')->assertOk()->assertSeeInOrder(['id="how-title"', 'id="reviews-title"', 'Test quote for the destinations page.', 'id="faq-title"'], false);
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

    public function test_a_package_can_be_featured_on_extra_destinations_from_the_package_form(): void
    {
        $this->seedContent();
        $admin = AdminUser::create(['name' => 'A', 'email' => 'a@example.com', 'password' => 'correct-horse-battery', 'role' => 'admin']);
        $this->actingAs($admin, 'admin');
        $p = Package::published()->first();
        $other = Destination::where('id', '!=', $p->destination_id)->first();
        $style = TravelStyle::first();
        $base = ['title' => $p->title, 'tour_type_id' => $p->tour_type_id, 'destination_id' => $p->destination_id, 'nights' => $p->nights, 'currency' => 'USD', 'is_published' => '1'];

        $this->get("/admin/packages/{$p->id}/edit")->assertOk()->assertSee('Where this package is featured');
        // the main destination is ignored if ticked; only extra ones are stored
        $this->put("/admin/packages/{$p->id}", $base + ['featured_destinations' => [$p->destination_id, $other->id], 'travel_styles' => [$style->id]])->assertSessionHasNoErrors();
        $this->assertSame([$other->id], $p->fresh()->featuredDestinations->pluck('id')->all());

        $this->get("/destinations/{$other->slug}")->assertViewHas('packages', fn ($l) => $l->contains('id', $p->id));
        $this->get('/packages?destination='.$other->slug)->assertViewHas('packages', fn ($l) => $l->contains('id', $p->id));
        $this->get('/destinations')->assertViewHas('destinations', fn ($ds) => $ds->firstWhere('id', $other->id)->packages_count === Package::published()->listedOn($other)->count());

        // unticking removes it again
        $this->put("/admin/packages/{$p->id}", $base)->assertSessionHasNoErrors();
        $this->assertCount(0, $p->fresh()->featuredDestinations);
        $this->assertCount(0, $p->fresh()->travelStyles);
        $this->get("/destinations/{$other->slug}")->assertViewHas('packages', fn ($l) => ! $l->contains('id', $p->id));
    }

    public function test_destination_and_travel_style_forms_tick_packages_in_and_out(): void
    {
        $this->seedContent();
        $admin = AdminUser::create(['name' => 'A', 'email' => 'a@example.com', 'password' => 'correct-horse-battery', 'role' => 'admin']);
        $this->actingAs($admin, 'admin');

        // travel style: the ticks are exactly its packages
        $style = TravelStyle::where('slug', 'adventure')->first();
        [$a, $b] = Package::orderBy('id')->take(2)->get()->all();
        $this->get("/admin/travel-styles/{$style->id}/edit")->assertOk()->assertSee('Packages on this travel style')->assertSee($a->title);
        $this->put("/admin/travel-styles/{$style->id}", ['name' => $style->name, 'is_active' => '1', 'package_picker' => '1', 'package_ids' => [$a->id, $b->id]])->assertSessionHasNoErrors();
        $this->assertEqualsCanonicalizing([$a->id, $b->id], $style->fresh()->packages->pluck('id')->all());
        $this->put("/admin/travel-styles/{$style->id}", ['name' => $style->name, 'is_active' => '1', 'package_picker' => '1'])->assertSessionHasNoErrors();
        $this->assertCount(0, $style->fresh()->packages);

        // destination: own packages are always listed; only extra ones are stored as featured
        $dest = Destination::where('slug', 'cochem')->first();
        $own = Package::where('destination_id', $dest->id)->first();
        $extra = Package::where('destination_id', '!=', $dest->id)->first();
        $this->get("/admin/destinations/{$dest->id}/edit")->assertOk()->assertSee('(main destination)');
        $this->put("/admin/destinations/{$dest->id}", ['name' => $dest->name, 'is_active' => '1', 'package_picker' => '1', 'package_ids' => [$own->id, $extra->id]])->assertSessionHasNoErrors();
        $this->assertSame([$extra->id], $dest->fresh()->featuredPackages->pluck('id')->all());
        $this->get('/destinations/cochem')->assertViewHas('packages', fn ($l) => $l->contains('id', $extra->id) && $l->contains('id', $own->id));
    }
}
