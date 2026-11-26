<?php

namespace Database\Seeders;

use App\Models\Destination;
use App\Models\Media;
use App\Models\Package;
use App\Models\PackageImage;
use App\Models\TourType;
use App\Models\TravelStyle;
use Illuminate\Database\Seeder;

/**
 * Tops every destination up to five SAMPLE tours (2026-11-26, for the gobelgiumtrips.com-style Destinations pages).
 * Routes and day plans are realistic starting points, but prices and details are placeholders (is_sample = true,
 * "sample price" shown): replace them in Admin > Packages before launch. Only creates packages whose slug does not
 * exist yet, so it never overwrites admin edits. Run by migration 2026_11_26_000002 and by DatabaseSeeder.
 */
class FiveToursPerDestinationSeeder extends Seeder
{
    public function run(): void
    {
        // [slug, title, tour type, destination, nights, sample price, images, travel styles, summary, highlights, itinerary]
        $packages = [
            ['harz-castles-and-steam-railway-self-drive', 'Harz Castles and Steam Railway Self Drive', 'self-drive', 'quedlinburg', 4, 1490,
                ['hero-quedlinburg', 'medieval-castle-path'], ['cultural-heritage', 'scenic'],
                'A short self drive through the Harz: Quedlinburg, Wernigerode castle and the steam railway up the Brocken.',
                ['Quedlinburg\'s UNESCO-listed old town', 'Wernigerode castle and market square', 'Steam railway to the Brocken summit', 'Bode gorge walk at Thale'],
                [['Day 1: Arrive in Quedlinburg', 'Collect your rental car and settle into the old town.'], ['Day 2: Quedlinburg and Thale', 'Morning in the half-timbered lanes, afternoon walk in the Bode gorge.'],
                    ['Day 3: Wernigerode and the Brocken', 'Ride the narrow-gauge steam railway to the Harz summit.'], ['Day 4: Goslar', 'Explore the UNESCO-listed mining town.'], ['Day 5: Departure', 'Drive on or return the car.']]],

            ['elbe-sandstone-hiking-self-drive', 'Elbe Sandstone Hiking Self Drive', 'self-drive', 'saxon-switzerland', 6, 1690,
                ['saxon-switzerland-bastei', 'medieval-castle-path'], ['adventure', 'scenic'],
                'Hike the sandstone towers of Saxon Switzerland at your own pace, with comfortable stays near the trails.',
                ['Bastei bridge at sunrise', 'Schrammsteine viewpoint trail', 'Königstein fortress', 'A day in baroque Dresden'],
                [['Day 1: Arrive in Dresden', 'Pick up your car and spend the evening in the old town.'], ['Day 2: Bastei', 'Walk to the rock bridge and the Swiss Gorge.'],
                    ['Day 3: Schrammsteine', 'Viewpoint trails above Bad Schandau.'], ['Day 4: Königstein', 'Hilltop fortress and Elbe views.'], ['Day 5: Kirnitzsch valley', 'Easy walk to the Lichtenhain waterfall.'],
                    ['Day 6: Pirna', 'Old town on the Elbe.'], ['Day 7: Departure', 'Return the car in Dresden.']]],

            ['dresden-meissen-and-elbe-valley-chauffeur', 'Dresden, Meissen and the Elbe Valley by Private Driver', 'chauffeur-driven', 'saxon-switzerland', 5, 2490,
                ['saxon-switzerland-bastei', 'anniversary-dinner-terrace'], ['luxury', 'cultural-heritage'],
                'Baroque Dresden, porcelain town Meissen and the sandstone Elbe valley, with one private driver throughout.',
                ['Dresden\'s Zwinger and Frauenkirche', 'Meissen porcelain and castle hill', 'Bastei viewpoint without the driving', 'Elbe valley vineyards'],
                [['Day 1: Arrive in Dresden', 'Met by your driver and taken to your hotel.'], ['Day 2: Dresden', 'Old town, Zwinger and Frauenkirche.'],
                    ['Day 3: Meissen', 'Castle hill, cathedral and the porcelain works.'], ['Day 4: Saxon Switzerland', 'Bastei and Königstein with your driver.'],
                    ['Day 5: Elbe vineyards', 'Riverside villages and a relaxed afternoon.'], ['Day 6: Departure', 'Transfer to the airport or station.']]],

            ['ruegen-and-hiddensee-island-hopping-self-drive', 'Rügen and Hiddensee Island Hopping Self Drive', 'self-drive', 'hiddensee', 6, 1790,
                ['hiddensee-beach', 'hiddensee-lighthouse'], ['slow', 'scenic'],
                'Chalk cliffs on Rügen, car-free Hiddensee and Hanseatic Stralsund, with time to slow down by the sea.',
                ['Car-free Hiddensee by bike or on foot', 'Rügen\'s chalk cliffs', 'Seaside villas in Binz', 'Stralsund\'s UNESCO-listed old town'],
                [['Day 1: Stralsund', 'Collect your car and explore the old harbour town.'], ['Day 2: Rügen', 'Chalk cliffs and beech forest.'],
                    ['Day 3: Binz', 'Seaside promenade and resort architecture.'], ['Day 4: Hiddensee', 'Ferry over and cycle between the villages.'],
                    ['Day 5: Hiddensee', 'Dornbusch lighthouse and the dunes.'], ['Day 6: Back to the mainland', 'Slow day on the coast.'], ['Day 7: Departure', 'Return the car.']]],

            ['stralsund-and-baltic-islands-chauffeur', 'Stralsund and the Baltic Islands by Private Driver', 'chauffeur-driven', 'hiddensee', 5, 2690,
                ['hiddensee-lighthouse', 'hiddensee-beach'], ['luxury', 'slow'],
                'The Baltic coast at an easy pace: Stralsund, Rügen and a day on Hiddensee, with a private driver.',
                ['Private driver along the coast', 'A day on car-free Hiddensee', 'Rügen chalk cliffs', 'Hanseatic Stralsund'],
                [['Day 1: Arrive in Stralsund', 'Met by your driver.'], ['Day 2: Stralsund', 'Old town and harbour.'], ['Day 3: Rügen', 'Chalk cliffs and Binz.'],
                    ['Day 4: Hiddensee', 'Ferry crossing and island walks.'], ['Day 5: Coast', 'Fishing villages and beaches.'], ['Day 6: Departure', 'Transfer onward.']]],

            ['black-forest-villages-and-waterfalls-self-drive', 'Black Forest Villages and Triberg Waterfalls Self Drive', 'self-drive', 'gengenbach', 5, 1590,
                ['gengenbach-rathaus', 'medieval-castle-path'], ['scenic', 'adventure'],
                'Half-timbered villages, forest lakes and the Triberg waterfalls on a self drive through the Black Forest.',
                ['Gengenbach and Schiltach old towns', 'Triberg waterfalls', 'Titisee forest lake', 'Freiburg\'s cathedral square'],
                [['Day 1: Gengenbach', 'Collect your car and settle into the old town.'], ['Day 2: Kinzig valley', 'Schiltach and riverside villages.'],
                    ['Day 3: Triberg', 'Waterfalls and forest walks.'], ['Day 4: Titisee', 'Lakeside afternoon.'], ['Day 5: Freiburg', 'Cathedral square and market.'], ['Day 6: Departure', 'Return the car.']]],

            ['baden-baden-and-black-forest-spa-chauffeur', 'Baden-Baden and the Black Forest Spa Route by Private Driver', 'chauffeur-driven', 'gengenbach', 6, 2890,
                ['anniversary-dinner-terrace', 'gengenbach-rathaus'], ['luxury', 'food-wine'],
                'Spa town Baden-Baden, Black Forest villages and Baden wine country, with a private driver and time to unwind.',
                ['Baden-Baden thermal baths', 'Baden wine villages', 'Gengenbach and the Kinzig valley', 'Private driver throughout'],
                [['Day 1: Baden-Baden', 'Met by your driver and taken to your hotel.'], ['Day 2: Baden-Baden', 'Thermal baths and the Lichtentaler Allee.'],
                    ['Day 3: Wine country', 'Baden vineyards and tastings.'], ['Day 4: Gengenbach', 'Half-timbered old town.'], ['Day 5: Black Forest high road', 'Scenic drive and lakes.'],
                    ['Day 6: Freiburg', 'Cathedral and old town.'], ['Day 7: Departure', 'Transfer onward.']]],
        ];

        $sortBase = (int) Package::max('sort') + 1;
        foreach ($packages as $i => [$slug, $title, $type, $dest, $nights, $price, $imgs, $styles, $summary, $highlights, $days]) {
            $destination = Destination::where('slug', $dest)->first();
            $tour = TourType::where('slug', $type)->first();
            if (! $destination || ! $tour || Package::withTrashed()->where('slug', $slug)->exists()) {
                continue;
            }
            $package = Package::create([
                'slug' => $slug, 'tour_type_id' => $tour->id, 'destination_id' => $destination->id,
                'title' => $title, 'nights' => $nights, 'price_from' => $price, 'currency' => 'USD', 'rating' => 5.0,
                'summary' => $summary,
                'description' => "<p>{$summary}</p><p>[SAMPLE] This is a sample itinerary to start from: dates, hotels and price are placeholders. Replace them in the admin area with the real details.</p>",
                'highlights' => $highlights,
                'itinerary' => array_map(fn ($d) => ['title' => $d[0], 'body' => $d[1]], $days),
                'inclusions' => ['[SAMPLE] Hotels', '[SAMPLE] '.($type === 'self-drive' ? 'Rental car' : ($type === 'chauffeur-driven' ? 'Private driver' : 'Coach travel and guide')), '[SAMPLE] Itinerary planning'],
                'exclusions' => ['[SAMPLE] International flights'],
                'is_published' => true, 'is_featured' => false, 'is_sample' => true, 'sort' => $sortBase + $i,
                'seo_title' => $title,
                'meta_description' => "{$nights}-night ".strtolower($tour->name)." trip around {$destination->name}, Germany. Request a free quote.",
            ]);

            foreach ($imgs as $n => $stem) {
                if ($m = Media::where('path', 'like', "media/{$stem}-%")->first()) {
                    PackageImage::create(['package_id' => $package->id, 'path' => $m->path, 'alt' => $m->alt, 'sort' => $n]);
                }
            }
            if ($first = $package->images()->first()) {
                $package->update(['og_image' => $first->path]);
            }
            $package->travelStyles()->syncWithoutDetaching(TravelStyle::whereIn('slug', $styles)->pluck('id'));
        }
    }
}
