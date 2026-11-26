<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Starter travel styles (linked to the sample packages by slug) and the destination taglines / highlight places.
 * Only fills what is missing, so it is safe to run again and on the live site (the 2026_11_26 migration calls it).
 */
class TravelStyleSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedStyles();
        $this->seedPlaces();
    }

    private function image(string $stem): ?string
    {
        return DB::table('media')->where('path', 'like', "media/{$stem}-%")->value('path');
    }

    private function seedStyles(): void
    {
        $styles = [
            ['slow', 'Slow', 'Unhurried journeys', 'Fewer stops, longer stays. Settle into a small town, walk its lanes in the evening light and travel at a pace that leaves time for the unplanned.',
                "Two or more nights in each place\nShort, easy travel days\nTime for cafes, markets and evening walks\nQuiet islands and small towns", 'hiddensee-beach',
                ['hiddensee-island-escape-self-drive', 'hiddensee-baltic-coast-chauffeur', 'hiddensee-baltic-coast-coach', 'black-forest-and-gengenbach-self-drive', 'black-forest-and-gengenbach-chauffeur']],
            ['scenic', 'Scenic', 'Breathtaking views', 'Routes built around the views: sandstone towers above the Elbe, river bends lined with vineyards and quiet Baltic shores. Every mile brings a scene worth stopping for.',
                "Viewpoints planned into each day\nRiver valleys, rock towers and coastlines\nScenic drives and short walks\nGolden-hour stops where it matters", 'saxon-switzerland-bastei',
                ['saxon-switzerland-and-dresden-self-drive', 'saxon-switzerland-and-dresden-chauffeur', 'saxon-switzerland-and-dresden-coach', 'mosel-valley-and-cochem-self-drive', 'hiddensee-island-escape-self-drive']],
            ['luxury', 'Luxury', 'Travel in comfort', 'A private driver, hand-picked hotels and every detail arranged for you. Germany at its most relaxed, with nothing to organize but where to have dinner.',
                "Private driver for the whole trip\nHand-picked hotels\nDoor-to-door transfers\nA plan shaped entirely around you", 'chauffeur-private-driver',
                ['saxon-switzerland-and-dresden-chauffeur', 'black-forest-and-gengenbach-chauffeur', 'quedlinburg-and-the-harz-chauffeur', 'mosel-valley-and-cochem-chauffeur', 'rhine-and-mosel-wine-villages-chauffeur', 'hiddensee-baltic-coast-chauffeur']],
            ['food-wine', 'Food & Wine', 'Taste the regions', 'Riesling on the Mosel, wine villages on the Rhine and regional cooking in family-run inns. A journey planned around the table.',
                "Wine villages on the Mosel and Rhine\nTime for tastings and long lunches\nRegional dishes in family-run inns\nRiverside towns with vineyard walks", 'mosel-vineyard',
                ['rhine-and-mosel-wine-route-self-drive', 'rhine-and-mosel-wine-villages-chauffeur', 'mosel-valley-and-cochem-self-drive', 'mosel-valley-and-cochem-chauffeur', 'mosel-valley-and-cochem-coach']],
            ['cultural-heritage', 'Cultural & Heritage', 'Living history', 'UNESCO-listed old towns, half-timbered streets, castles and baroque cities. Walk through a thousand years of German history at your own pace.',
                "UNESCO-listed towns and old quarters\nCastles, cathedrals and palaces\nHalf-timbered streets and market squares\nTime to explore each place on foot", 'quedlinburg-lane',
                ['quedlinburg-and-the-harz-self-drive', 'quedlinburg-and-the-harz-chauffeur', 'harz-mountains-and-quedlinburg-coach', 'germany-fairytale-towns-coach-tour', 'saxon-switzerland-and-dresden-coach']],
            ['adventure', 'Adventure', 'Get outdoors', 'Hike among sandstone rock towers, walk Harz mountain trails and explore the Black Forest. Active days, with comfortable stays at night.',
                "Hiking and walking days\nMountain, forest and river scenery\nRoutes for different fitness levels\nComfortable hotels after active days", 'medieval-castle-path',
                ['saxon-switzerland-and-dresden-self-drive', 'quedlinburg-and-the-harz-self-drive', 'black-forest-and-gengenbach-self-drive', 'black-forest-small-group-coach']],
        ];

        foreach ($styles as $i => [$slug, $name, $tagline, $summary, $highlights, $stem, $packages]) {
            $id = DB::table('travel_styles')->where('slug', $slug)->value('id');
            if (! $id) {
                $id = DB::table('travel_styles')->insertGetId([
                    'name' => $name, 'slug' => $slug, 'tagline' => $tagline, 'summary' => $summary, 'highlights' => $highlights,
                    'image' => $this->image($stem), 'sort' => $i + 1, 'is_active' => true,
                    'seo_title' => "{$name} Tours of Germany", 'created_at' => now(), 'updated_at' => now(),
                ]);
            } elseif (DB::table('package_travel_style')->where('travel_style_id', $id)->exists()) {
                continue;   // links already set (possibly edited in the admin): leave them alone
            }
            if (! DB::table('travel_styles')->where('id', $id)->value('image') && ($img = $this->image($stem))) {
                DB::table('travel_styles')->where('id', $id)->update(['image' => $img]);
            }
            foreach (DB::table('packages')->whereIn('slug', $packages)->pluck('id') as $pid) {
                DB::table('package_travel_style')->insertOrIgnore(['package_id' => $pid, 'travel_style_id' => $id]);
            }
        }
    }

    private function seedPlaces(): void
    {
        $places = [
            'quedlinburg' => ['Half-timbered towns & Harz mountain views', "Quedlinburg: UNESCO-listed old town\nWernigerode: castle and colorful old town\nBrocken: Harz summit by steam railway\nThale: the Bode gorge\nGoslar: UNESCO-listed mining town\nHalberstadt: cathedral and treasury"],
            'saxon-switzerland' => ['Sandstone peaks & the Elbe valley', "Bastei: the sandstone rock bridge\nKönigstein: hilltop fortress\nDresden: baroque old town\nPirna: old town on the Elbe\nBad Schandau: spa town and Kirnitzsch valley\nSchrammsteine: viewpoint hikes"],
            'cochem' => ['Castles, river bends & vineyards', "Cochem: the Reichsburg castle\nBurg Eltz: forest castle\nBeilstein: tiny wine village\nBernkastel-Kues: half-timbered market square\nTraben-Trarbach: Art Nouveau riverside\nTrier: Roman monuments"],
            'hiddensee' => ['Car-free island & Baltic shores', "Hiddensee: car-free island\nKloster: village and Dornbusch lighthouse\nVitte: dunes and beach\nStralsund: UNESCO-listed Hanseatic town\nRügen: chalk cliffs\nBinz: seaside resort architecture"],
            'gengenbach' => ['Black Forest villages & valleys', "Gengenbach: half-timbered old town\nFreiburg: university city and cathedral\nTriberg: waterfalls and cuckoo clocks\nBaden-Baden: spa town\nSchiltach: riverside half-timbered town\nTitisee: forest lake"],
        ];
        foreach ($places as $slug => [$tagline, $list]) {
            DB::table('destinations')->where('slug', $slug)->whereNull('tagline')->update(['tagline' => $tagline]);
            DB::table('destinations')->where('slug', $slug)->whereNull('places')->update(['places' => $list]);
        }
    }
}
