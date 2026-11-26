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
        'hl-quedlinburg-market' => 'Market square of Quedlinburg with the ivy-covered Renaissance town hall and half-timbered houses',
        'hl-wernigerode-castle' => 'Wernigerode Castle on a wooded hill above the town in autumn',
        'hl-brocken-railway' => 'Narrow-gauge Harz steam train climbing through spruce forest towards the Brocken',
        'hl-bode-gorge' => 'Riverside hiking path through the granite Bode gorge near Thale',
        'hl-goslar' => 'Market square of Goslar with slate-roofed half-timbered houses and a fountain',
        'hl-harz-trail' => 'Hiking trail with a wooden signpost through mossy spruce forest in the Harz',
        'hl-koenigstein' => 'Königstein Fortress on its sandstone plateau above a bend of the Elbe',
        'hl-dresden' => 'Dresden skyline with the Frauenkirche dome lit up over the Elbe at blue hour',
        'hl-meissen' => 'Albrechtsburg castle and Meissen cathedral above the Elbe at sunset',
        'hl-schrammsteine' => 'Sandstone rock towers rising from forest at the Schrammsteine in Saxon Switzerland',
        'hl-elbe-steamer' => 'Historic paddle steamer cruising the Elbe below sandstone cliffs',
        'hl-burg-eltz' => 'Burg Eltz castle with its turrets in a forested valley in autumn',
        'hl-beilstein' => 'Wine village of Beilstein on the Mosel below a castle ruin and vineyards',
        'hl-bernkastel' => 'Half-timbered market square of Bernkastel-Kues with a fountain and cafe tables',
        'hl-wine-cellar' => 'Riesling tasting by candlelight on an oak barrel in a vaulted Mosel wine cellar',
        'hl-rhine-loreley' => 'The Rhine bending past the Loreley rock, vineyards and a hilltop castle',
        'hl-trier' => 'The Roman Porta Nigra city gate in Trier with cafes in front',
        'hl-hiddensee-cycling' => 'Two cyclists on a sandy path through dunes and heather on Hiddensee, lighthouse in the distance',
        'hl-ruegen-cliffs' => 'White chalk cliffs of Rügen above the turquoise Baltic Sea framed by beech trees',
        'hl-stralsund' => 'Red-brick Gothic gables and a sailing ship in the harbour of Stralsund at sunset',
        'hl-binz' => 'Seaside promenade of Binz with white resort villas and the pier',
        'hl-beach-chairs' => 'Striped wicker beach chairs on a sandy Baltic Sea beach',
        'hl-triberg' => 'Triberg waterfalls cascading over mossy rocks in a Black Forest fir forest',
        'hl-titisee' => 'Rowing boats at a wooden jetty on Lake Titisee in the Black Forest',
        'hl-freiburg' => 'Freiburg minster above the market on the cathedral square',
        'hl-baden-baden' => 'The Kurhaus of Baden-Baden with its columns and flower gardens',
        'hl-schiltach' => 'Half-timbered houses on the sloping market square of Schiltach',
        'hl-black-forest-cake' => 'A slice of Black Forest cherry cake with coffee in a Black Forest cafe',
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
        'bacharach-view' => 'The Rhine river, vineyards and the church tower of Bacharach under a summer sky',
        'couple-rhine-vineyard' => 'A couple holding hands on a vineyard path above the Rhine river with a castle on the far hillside at golden hour',
        'family-old-town' => 'A family with two young children walking along a cobbled street lined with half-timbered houses',
        'medieval-castle-path' => 'A cobbled path leading up to a medieval stone castle with towers on a forested hill at sunset',
        'koenigssee-hero' => 'Königssee in the Bavarian Alps with the red-domed St. Bartholomä chapel, a wooden tour boat and the Watzmann mountains',
        'koenigssee' => 'Bright green Königssee in the Bavarian Alps with the red-domed St. Bartholomä chapel, a wooden tour boat and the Watzmann mountains',
        'bavaria-village' => 'A sunny Bavarian village with a white onion-domed church, painted farmhouses and flower meadows below the snow-capped Alps',
        'neuschwanstein-autumn' => 'Neuschwanstein Castle glowing in golden light above an autumn forest in the Bavarian Alps',
        'anniversary-dinner-terrace' => 'A group raising champagne glasses at a candle-lit terrace dinner with a fairytale castle and mountains behind',
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
