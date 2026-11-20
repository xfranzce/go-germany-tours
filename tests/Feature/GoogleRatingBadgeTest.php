<?php

namespace Tests\Feature;

use App\Models\AdminUser;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GoogleRatingBadgeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedContent();
    }

    private function admin(): AdminUser
    {
        return AdminUser::create(['name' => 'A', 'email' => 'a@example.com', 'password' => 'correct-horse-battery', 'role' => 'admin']);
    }

    public function test_the_badge_is_hidden_until_a_real_rating_is_entered_and_switched_on(): void
    {
        $this->get('/')->assertOk()->assertDontSee('Traveler Rating');

        Setting::put('google_rating_enabled', '1');   // switched on but no rating: still hidden
        $this->get('/')->assertDontSee('Traveler Rating');
    }

    public function test_admin_can_show_the_badge_with_their_rating_and_it_links_to_the_reviews(): void
    {
        $this->actingAs($this->admin(), 'admin')
            ->put('/admin/settings', ['google_rating_enabled' => '1', 'google_rating' => '4.8', 'google_reviews_url' => 'https://g.page/r/example'])
            ->assertRedirect('/admin/settings');

        $this->get('/')->assertOk()->assertSee('4.8/5')->assertSee('Traveler Rating')->assertSee('Google')
            ->assertSee('href="https://g.page/r/example"', false);

        $this->put('/admin/settings', ['google_rating_enabled' => '0', 'google_rating' => '4.8']);
        $this->get('/')->assertDontSee('Traveler Rating');
    }

    public function test_turning_it_on_without_a_rating_is_ignored_and_bad_ratings_are_rejected(): void
    {
        $this->actingAs($this->admin(), 'admin')->put('/admin/settings', ['google_rating_enabled' => '1', 'google_rating' => ''])->assertRedirect('/admin/settings');
        $this->assertSame('0', Setting::get('google_rating_enabled'));

        $this->put('/admin/settings', ['google_rating_enabled' => '1', 'google_rating' => '7'])->assertSessionHasErrors('google_rating');
        $this->put('/admin/settings', ['google_rating_enabled' => '1', 'google_rating' => '4.9', 'google_reviews_url' => 'not a url'])->assertSessionHasErrors('google_reviews_url');
    }
}
