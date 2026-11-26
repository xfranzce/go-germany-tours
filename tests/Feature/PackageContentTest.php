<?php

namespace Tests\Feature;

use App\Models\AdminUser;
use App\Models\Media;
use App\Models\Package;
use Database\Seeders\PackageContentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PackageContentTest extends TestCase
{
    use RefreshDatabase;

    private function media(string $stem): Media
    {
        return Media::create(['path' => "media/{$stem}-abc123", 'original_name' => "{$stem}.jpg", 'alt' => $stem, 'width' => 1200, 'height' => 800, 'widths' => [480, 1200]]);
    }

    public function test_content_seeder_fills_placeholder_packages_with_highlights_itinerary_and_inclusions(): void
    {
        $this->seedContent();
        $this->media('hl-quedlinburg-market');   // prevents the image import in the test
        $p = Package::where('slug', 'quedlinburg-and-the-harz-self-drive')->first();

        (new PackageContentSeeder)->run();
        $p->refresh();

        $this->assertStringNotContainsString('[SAMPLE]', $p->description);
        $this->assertCount($p->nights + 1, $p->itinerary);
        $this->assertSame('Day 1: Arrive in Quedlinburg', $p->itinerary[0]['title']);
        $this->assertCount(4, $p->highlightItems());
        $this->assertSame('media/hl-quedlinburg-market-abc123', $p->highlightItems()[0]['image']);
        $this->assertContains('Rental car with unlimited mileage', $p->inclusions);
        $this->assertContains('International flights', $p->exclusions);

        $this->get($p->url())->assertOk()->assertSee('Highlights')->assertSee('Quedlinburg&#039;s UNESCO old town', false)
            ->assertSee('Day 2: Quedlinburg old town')->assertSee('Not included');

        // edited packages are left alone on a second run
        $p->update(['summary' => 'Edited']);
        (new PackageContentSeeder)->run();
        $this->assertSame('Edited', $p->fresh()->summary);
    }

    public function test_admin_saves_highlights_with_photos_and_old_string_highlights_still_show(): void
    {
        $this->seedContent();
        $m = $this->media('hl-goslar');
        $admin = AdminUser::create(['name' => 'A', 'email' => 'a@example.com', 'password' => 'correct-horse-battery', 'role' => 'admin']);
        $p = Package::first();
        $p->update(['highlights' => ['Old plain highlight']]);

        $this->actingAs($admin, 'admin')->get("/admin/packages/{$p->id}/edit")->assertOk()->assertSee('+ Add a highlight');
        $this->get($p->url())->assertSee('Old plain highlight');

        $this->actingAs($admin, 'admin')->put("/admin/packages/{$p->id}", [
            'title' => $p->title, 'tour_type_id' => $p->tour_type_id, 'destination_id' => $p->destination_id, 'nights' => $p->nights, 'currency' => 'USD', 'is_published' => '1',
            'highlights' => [['title' => 'Goslar', 'text' => 'Slate roofs', 'image' => $m->path], ['title' => '', 'text' => 'dropped']],
        ])->assertSessionHasNoErrors();
        $this->assertSame([['title' => 'Goslar', 'text' => 'Slate roofs', 'image' => $m->path]], $p->fresh()->highlights);

        $this->actingAs($admin, 'admin')->put("/admin/packages/{$p->id}", [
            'title' => $p->title, 'tour_type_id' => $p->tour_type_id, 'destination_id' => $p->destination_id, 'nights' => $p->nights, 'currency' => 'USD',
            'highlights' => [['title' => 'X', 'image' => 'media/not-a-real-file']],
        ])->assertSessionHasErrors('highlights.0.image');
    }

    public function test_every_package_gets_its_own_cover_photo_but_uploaded_galleries_are_kept(): void
    {
        $this->seedContent();
        $data = require database_path('seeders/data/package-content.php');
        foreach (array_keys($data['hl']) as $stem) {
            $this->media($stem);
        }
        $custom = Package::orderBy('id')->first();
        $custom->images()->delete();
        $custom->images()->create(['path' => 'media/my-own-upload-zz9', 'alt' => 'Mine', 'sort' => 0]);

        (new PackageContentSeeder)->run();
        (new \Database\Seeders\UniquePackageImagesSeeder)->run();

        $covers = Package::with('images')->get()->map(fn ($p) => $p->images->first()?->path)->filter();
        $this->assertSame($covers->count(), $covers->unique()->count());
        $this->assertSame('media/my-own-upload-zz9', $custom->fresh()->images->first()->path);
    }
}
