<?php

namespace Tests\Feature;

use App\Models\AdminUser;
use App\Models\Destination;
use App\Models\Faq;
use App\Models\TravelStyle;
use Database\Seeders\PageFaqSeeder;
use Database\Seeders\TourTypeFaqSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Destination, travel style and tour type pages show their own FAQs; everything else shows the general (home) ones. */
class PageFaqTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedContent();
        $this->seed([TourTypeFaqSeeder::class, PageFaqSeeder::class]);
    }

    public function test_every_destination_and_travel_style_gets_its_own_starter_faqs_once(): void
    {
        foreach (Destination::all() as $d) {
            $this->assertGreaterThan(0, Faq::where('destination_id', $d->id)->count(), "No FAQs for destination {$d->slug}");
        }
        foreach (TravelStyle::all() as $s) {
            $this->assertGreaterThan(0, Faq::where('travel_style_id', $s->id)->count(), "No FAQs for travel style {$s->slug}");
        }

        $count = Faq::count();
        $this->seed(PageFaqSeeder::class);
        $this->assertSame($count, Faq::count(), 'Re-running the seeder must not duplicate FAQs');
    }

    public function test_pages_show_their_own_faqs_and_general_pages_do_not(): void
    {
        $this->get('/destinations/cochem')->assertOk()->assertSee('Questions about Cochem')->assertSee('Is Burg Eltz near Cochem?')
            ->assertDontSee('Is Hiddensee really car-free?')->assertSee('"@type":"FAQPage"', false);
        $this->get('/travel-styles/food-wine')->assertOk()->assertSee('Questions about food &amp; wine trips', false)->assertSee('When are the wine festivals?');

        foreach (['/', '/faqs', '/destinations'] as $url) {
            $this->get($url)->assertOk()->assertDontSee('Is Burg Eltz near Cochem?')->assertDontSee('When are the wine festivals?');
        }
    }

    public function test_a_page_without_its_own_faqs_falls_back_to_the_general_ones(): void
    {
        $cochem = Destination::where('slug', 'cochem')->firstOrFail();
        Faq::where('destination_id', $cochem->id)->delete();
        $general = Faq::home()->firstOrFail();

        $this->get('/destinations/cochem')->assertOk()->assertSee($general->question)->assertDontSee('Questions about Cochem')
            ->assertDontSee('"@type":"FAQPage"', false);
    }

    public function test_admin_can_put_an_faq_on_a_destination_or_style_page(): void
    {
        $admin = AdminUser::create(['name' => 'A', 'email' => 'a@example.com', 'password' => 'correct-horse-battery']);
        $style = TravelStyle::where('slug', 'slow')->firstOrFail();

        $this->actingAs($admin, 'admin')->get('/admin/faqs/create')->assertOk()->assertSee('Destination: Cochem')->assertSee('Travel style: Slow');
        $this->actingAs($admin, 'admin')->get('/admin/faqs')->assertOk()->assertSee('Destination: Cochem');

        $this->actingAs($admin, 'admin')->post('/admin/faqs', ['question' => 'Slow Q?', 'answer' => 'A.', 'place' => "travel_style_id:{$style->id}", 'is_active' => '1'])
            ->assertRedirect('/admin/faqs');
        $faq = Faq::where('question', 'Slow Q?')->firstOrFail();
        $this->assertSame($style->id, $faq->travel_style_id);
        $this->assertNull($faq->destination_id);
        $this->actingAs($admin, 'admin')->get("/admin/faqs/{$faq->id}/edit")->assertOk()->assertSee('value="travel_style_id:'.$style->id.'" selected', false);

        // Back to the home page.
        $this->actingAs($admin, 'admin')->put("/admin/faqs/{$faq->id}", ['question' => 'Slow Q?', 'answer' => 'A.', 'place' => '', 'is_active' => '1'])->assertRedirect();
        $this->assertNull($faq->fresh()->travel_style_id);

        $this->actingAs($admin, 'admin')->post('/admin/faqs', ['question' => 'X', 'answer' => 'Y', 'place' => 'destination_id:99999'])->assertSessionHasErrors('place');
    }
}
