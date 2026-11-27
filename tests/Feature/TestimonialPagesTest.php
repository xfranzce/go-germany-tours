<?php

namespace Tests\Feature;

use App\Models\AdminUser;
use App\Models\Destination;
use App\Models\Testimonial;
use App\Models\TourType;
use App\Models\TravelStyle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Testimonials tied to a destination / tour type / travel style show on that page; everything else shows general ones. */
class TestimonialPagesTest extends TestCase
{
    use RefreshDatabase;

    private function review(string $quote, array $links = []): Testimonial
    {
        return Testimonial::forceCreate(['name' => 'Guest', 'quote' => $quote, 'is_active' => true, 'is_placeholder' => false, 'sort' => 0] + $links);
    }

    public function test_each_page_shows_its_own_reviews_and_general_pages_show_general_ones(): void
    {
        $this->seedContent();
        Testimonial::query()->delete();
        $cochem = Destination::where('slug', 'cochem')->firstOrFail();
        $type = TourType::where('is_active', true)->firstOrFail();
        $style = TravelStyle::where('is_active', true)->firstOrFail();

        $this->review('General review text.');
        $this->review('Cochem review text.', ['destination_id' => $cochem->id]);
        $this->review('Tour type review text.', ['tour_type_id' => $type->id]);
        $this->review('Style review text.', ['travel_style_id' => $style->id]);

        $this->get('/destinations/cochem')->assertOk()->assertSee('Cochem review text.')->assertSee('What travelers say about Cochem')
            ->assertDontSee('General review text.')->assertDontSee('Style review text.');
        $this->get(route('tours.show', $type->slug))->assertOk()->assertSee('Tour type review text.')->assertDontSee('General review text.')->assertDontSee('Cochem review text.');
        $this->get(route('travel-styles.show', $style->slug))->assertOk()->assertSee('Style review text.')->assertDontSee('General review text.');

        foreach (['/', '/packages', '/destinations'] as $url) {
            $this->get($url)->assertOk()->assertSee('General review text.')->assertDontSee('Cochem review text.')->assertDontSee('Style review text.');
        }

        // A destination with no reviews of its own falls back to the general ones, with the normal heading.
        $other = Destination::where('is_active', true)->where('id', '!=', $cochem->id)->firstOrFail();
        $this->get(route('destinations.show', $other->slug))->assertOk()->assertSee('General review text.')->assertDontSee('Cochem review text.')
            ->assertDontSee('What travelers say about '.$other->name);
    }

    public function test_general_pages_show_every_review_when_none_are_general(): void
    {
        $this->seedContent();
        Testimonial::query()->delete();
        $this->review('Only a Cochem review.', ['destination_id' => Destination::where('slug', 'cochem')->value('id')]);

        $this->get('/')->assertOk()->assertSee('Only a Cochem review.');
    }

    public function test_admin_can_tie_a_review_to_a_destination_tour_type_and_style(): void
    {
        $this->seedContent();
        $admin = AdminUser::create(['name' => 'A', 'email' => 'a@example.com', 'password' => 'correct-horse-battery']);
        $cochem = Destination::where('slug', 'cochem')->firstOrFail();

        $this->actingAs($admin, 'admin')->get('/admin/testimonials/create')->assertOk()->assertSee('What is this review about?')->assertSee($cochem->name);

        $this->actingAs($admin, 'admin')->post('/admin/testimonials', [
            'name' => 'Real Guest', 'quote' => 'Loved Cochem.', 'destination_id' => $cochem->id, 'tour_type_id' => '', 'travel_style_id' => '', 'is_active' => '1',
        ])->assertRedirect('/admin/testimonials');

        $t = Testimonial::where('name', 'Real Guest')->firstOrFail();
        $this->assertSame($cochem->id, $t->destination_id);
        $this->assertNull($t->tour_type_id);
        $this->actingAs($admin, 'admin')->get('/admin/testimonials')->assertOk()->assertSee($cochem->name);

        // Clearing the dropdown makes it general again.
        $this->actingAs($admin, 'admin')->put("/admin/testimonials/{$t->id}", ['name' => 'Real Guest', 'quote' => 'Loved Cochem.', 'destination_id' => '', 'is_active' => '1'])->assertRedirect();
        $this->assertNull($t->fresh()->destination_id);

        $this->actingAs($admin, 'admin')->post('/admin/testimonials', ['name' => 'X', 'quote' => 'Y', 'destination_id' => 99999])->assertSessionHasErrors('destination_id');
    }
}
