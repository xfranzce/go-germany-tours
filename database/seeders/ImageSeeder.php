<?php

namespace Database\Seeders;

use App\Models\Destination;
use App\Models\Package;
use App\Models\Page;
use App\Models\PackageImage;
use App\Models\Setting;
use App\Models\TourType;
use App\Services\ImageService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

/**
 * Imports the openly licensed photos in database/seed-images (sources and
 * licences: docs/reference/IMAGE_CREDITS.md and credits.json) and attaches them.
 */
class ImageSeeder extends Seeder
{
    public const ALT = [
        'hero-quedlinburg' => 'Quedlinburg castle hill and abbey church towers above the red-roofed old town',
        'quedlinburg-lane' => 'Cobbled lane lined with half-timbered houses in Quedlinburg',
        'saxon-switzerland-bastei' => 'Sandstone rock towers rising from morning mist in Saxon Switzerland',
        'cochem-mosel' => 'Excursion boat on the Mosel river beside the riverside town of Cochem',
        'mosel-vineyard' => 'Vineyard slopes reflected in the Mosel river',
        'hiddensee-lighthouse' => 'Dornbusch lighthouse on a grassy hill on Hiddensee island',
        'hiddensee-beach' => 'Fishing boat on the sandy beach of Hiddensee with the Baltic Sea behind',
        'gengenbach-rathaus' => 'The pink baroque town hall on the market square in Gengenbach',
        'neuschwanstein-castle' => 'Neuschwanstein Castle on its wooded rock above the Bavarian lakes and meadows',
        'selfdrive-road' => 'A car driving along a winding road through vineyards above the Mosel river, with a village and castle in the distance',
        'chauffeur-private-driver' => 'A chauffeur holding the door of a black sedan on a cobbled square beside a pink baroque town hall',
        'coach-tour-cochem' => 'A touring coach on the Mosel riverside in Cochem with a group of travelers and the hilltop castle behind',
        'allgau-alps' => 'Autumn larches and a sunlit peak in the Allgäu Alps of Bavaria',
        'bacharach-rhine' => 'The Rhine valley town of Bacharach with its church tower, vineyards and castle',
    ];

    public function run(): void
    {
        $dir = database_path('seed-images');
        $alreadyDone = ! empty(Page::where('key', 'home')->first()?->data['hero_image']);
        if (! File::isDirectory($dir) || $alreadyDone) {
            return;
        }

        $svc = app(ImageService::class);
        $m = [];
        foreach (self::ALT as $slug => $alt) {
            $file = "{$dir}/{$slug}.jpg";
            if (File::exists($file)) {
                $m[$slug] = $svc->store($file, $alt, "{$slug}.jpg");
            }
        }

        $dest = fn (string $slug, string $img) => isset($m[$img]) && Destination::where('slug', $slug)->update(['image' => $m[$img]->path]);
        $dest('quedlinburg', 'quedlinburg-lane');
        $dest('saxon-switzerland', 'saxon-switzerland-bastei');
        $dest('cochem', 'cochem-mosel');
        $dest('hiddensee', 'hiddensee-lighthouse');
        $dest('gengenbach', 'gengenbach-rathaus');

        $gallery = [
            'quedlinburg-and-the-harz-self-drive' => ['quedlinburg-lane', 'hero-quedlinburg'],
            'saxon-switzerland-and-dresden-chauffeur' => ['saxon-switzerland-bastei'],
            'mosel-valley-and-cochem-coach' => ['cochem-mosel', 'mosel-vineyard'],
            'hiddensee-island-escape-self-drive' => ['hiddensee-lighthouse', 'hiddensee-beach'],
            'black-forest-and-gengenbach-chauffeur' => ['gengenbach-rathaus'],
            'germany-fairytale-towns-coach-tour' => ['hero-quedlinburg', 'quedlinburg-lane', 'gengenbach-rathaus'],
        ];
        foreach ($gallery as $pkgSlug => $slugs) {
            $pkg = Package::where('slug', $pkgSlug)->first();
            foreach ($slugs as $i => $s) {
                if ($pkg && isset($m[$s])) {
                    PackageImage::create(['package_id' => $pkg->id, 'path' => $m[$s]->path, 'alt' => $m[$s]->alt, 'sort' => $i]);
                }
            }
            if ($pkg && isset($m[$slugs[0]])) {
                $pkg->update(['og_image' => $m[$slugs[0]]->path]);
            }
        }

        // Tour type cards reuse a representative photo.
        foreach (['self-drive' => 'mosel-vineyard', 'chauffeur-driven' => 'gengenbach-rathaus', 'coach-tours' => 'cochem-mosel'] as $slug => $img) {
            isset($m[$img]) && TourType::where('slug', $slug)->update(['image' => $m[$img]->path]);
        }

        if (isset($m['hero-quedlinburg'])) {
            $home = Page::byKey('home');
            $home->update(['data' => array_merge($home->data ?? [], ['hero_image' => $m['hero-quedlinburg']->path]), 'og_image' => $m['hero-quedlinburg']->path]);
            Setting::put('default_og_image', $m['hero-quedlinburg']->path);
        }
    }
}
