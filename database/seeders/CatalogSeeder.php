<?php

namespace Database\Seeders;

use App\Models\Destination;
use App\Models\Package;
use App\Models\TourType;
use Illuminate\Database\Seeder;

/** Tour types, destinations and clearly-marked SAMPLE packages. */
class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['Self Drive', 'self-drive', 'car', 'Drive yourself at your own pace with pre-booked stays, a suggested route and local tips.',
                'Hire a car and follow a route planned for you. You choose where to linger; we arrange the accommodation and the day-by-day route notes.'],
            ['Chauffeur Driven', 'chauffeur-driven', 'steering', 'Sit back while a private driver handles the roads, parking and timings.',
                'A private driver takes you between towns so you can enjoy the scenery and the wine instead of the navigation.'],
            ['Coach Tours', 'coach-tours', 'bus', 'Travel with other guests on a guided small-group coach itinerary.',
                'A guided itinerary by coach with the logistics taken care of and other travelers to share it with.'],
        ];
        foreach ($types as $i => [$name, $slug, $icon, $summary, $desc]) {
            TourType::updateOrCreate(['slug' => $slug], [
                'name' => $name, 'icon' => $icon, 'summary' => $summary, 'description' => "<p>{$desc}</p>", 'sort' => $i,
                'seo_title' => preg_replace('/\s+Tours$/i', '', $name).' Tours of Germany', 'meta_description' => "{$name} vacations in Germany for US travelers. $summary",
            ]);
        }

        $dests = [
            ['Quedlinburg', 'quedlinburg', 'A UNESCO-listed half-timbered town at the edge of the Harz mountains.',
                'Quedlinburg is a small town in the Harz region, known for its half-timbered old town, cobbled streets, castle hill and abbey church. It makes a calm base for walking, history and Harz day trips.'],
            ['Saxon Switzerland', 'saxon-switzerland', 'Sandstone rock towers, hiking trails and Elbe river views near Dresden.',
                'Saxon Switzerland National Park, south-east of Dresden, is known for its sandstone rock formations, hiking trails and the Elbe valley. It is easy to combine with a stay in Dresden.'],
            ['Cochem', 'cochem', 'A Mosel river town under a hilltop castle, surrounded by vineyards.',
                'Cochem sits on the Mosel river beneath Reichsburg Cochem castle, surrounded by vineyards. Expect riverside walks, wine tasting and a lively old town square.'],
            ['Hiddensee', 'hiddensee', 'A car-free Baltic island with beaches, lighthouses and quiet walking and cycling paths.',
                'Hiddensee is a small, car-free island in the Baltic Sea near Rügen, reached by ferry. It suits slow travel: beaches, coastal walking, cycling and horse-drawn carriages.'],
            ['Gengenbach', 'gengenbach', 'A half-timbered Black Forest town in the Kinzig valley, known for its festive old town.',
                'Gengenbach is a half-timbered town in the Black Forest region, in the Kinzig valley. Its old town, wine slopes and nearby forest trails make a relaxed base.'],
        ];
        foreach ($dests as $i => [$name, $slug, $summary, $desc]) {
            Destination::updateOrCreate(['slug' => $slug], [
                'name' => $name, 'summary' => $summary, 'description' => "<p>{$desc}</p>", 'sort' => $i,
                'seo_title' => "{$name} Vacations and Tours", 'meta_description' => "Plan a trip to {$name}, Germany. {$summary}",
            ]);
        }

        // SAMPLE data: names, durations and prices are placeholders to be replaced (see CONTENT_TO_CONFIRM.md).
        $packages = [
            ['quedlinburg-and-the-harz-self-drive', 'Quedlinburg and the Harz Self Drive', 'self-drive', 'quedlinburg', 6, 1790, true],
            ['saxon-switzerland-and-dresden-chauffeur', 'Saxon Switzerland and Dresden by Private Driver', 'chauffeur-driven', 'saxon-switzerland', 5, 2490, true],
            ['mosel-valley-and-cochem-coach', 'Mosel Valley and Cochem Small-Group Coach Tour', 'coach-tours', 'cochem', 7, 1990, true],
            ['hiddensee-island-escape-self-drive', 'Hiddensee Baltic Island Escape', 'self-drive', 'hiddensee', 5, 1650, false],
            ['black-forest-and-gengenbach-chauffeur', 'Black Forest and Gengenbach by Private Driver', 'chauffeur-driven', 'gengenbach', 6, 2790, false],
            ['germany-fairytale-towns-coach-tour', 'Germany Fairytale Towns Coach Tour', 'coach-tours', 'quedlinburg', 10, 3290, false],
        ];
        foreach ($packages as $i => [$slug, $title, $type, $dest, $nights, $price, $featured]) {
            $destination = Destination::where('slug', $dest)->first();
            $tour = TourType::where('slug', $type)->first();
            $style = strtolower($tour->name);
            Package::updateOrCreate(['slug' => $slug], [
                'tour_type_id' => $tour->id, 'destination_id' => $destination->id,
                'title' => $title, 'nights' => $nights, 'price_from' => $price, 'currency' => 'USD',
                'summary' => "{$nights} nights, {$style}. SAMPLE package to be replaced with real itinerary and pricing.",
                'description' => "<p>[SAMPLE] {$title} is a placeholder package showing how a tour appears on the site. Replace this text with the real description, itinerary and price in the admin area.</p>"
                    ."<p>This {$nights}-night {$style} trip is based around {$destination->name}. {$destination->summary}</p>",
                'highlights' => ['[SAMPLE] Highlight one: replace in admin', '[SAMPLE] Highlight two: replace in admin', '[SAMPLE] Highlight three: replace in admin'],
                'itinerary' => [
                    ['title' => 'Day 1: Arrival', 'body' => '[SAMPLE] Arrive and settle in.'],
                    ['title' => 'Day 2: Explore', 'body' => '[SAMPLE] Explore the area.'],
                    ['title' => 'Final day: Departure', 'body' => '[SAMPLE] Depart.'],
                ],
                'inclusions' => ['[SAMPLE] Accommodation', '[SAMPLE] Itinerary planning'],
                'exclusions' => ['[SAMPLE] International flights'],
                'is_published' => true, 'is_featured' => $featured, 'is_sample' => true, 'sort' => $i,
                'seo_title' => "{$title} | Go Germany Tours",
                'meta_description' => "{$nights}-night {$style} vacation around {$destination->name}, Germany. Request a free quote.",
            ]);
        }
    }
}
