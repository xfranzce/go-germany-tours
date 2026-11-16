<?php

namespace Database\Seeders;

use App\Models\Destination;
use App\Models\Media;
use App\Models\Package;
use App\Models\PackageImage;
use App\Models\TourType;
use Illuminate\Database\Seeder;

/**
 * Brings the catalogue to six SAMPLE packages per tour type. Names, durations and prices are
 * placeholders (is_sample = true, "sample" labels shown): replace via Admin > Packages.
 * Safe to run repeatedly (matches on slug). Run: php artisan db:seed --class=MoreSamplePackagesSeeder
 */
class MoreSamplePackagesSeeder extends Seeder
{
    /** destination => image stems (see database/seed-images) */
    private const IMAGES = [
        'quedlinburg' => ['quedlinburg-lane', 'hero-quedlinburg'],
        'saxon-switzerland' => ['saxon-switzerland-bastei'],
        'cochem' => ['cochem-mosel', 'mosel-vineyard'],
        'hiddensee' => ['hiddensee-lighthouse', 'hiddensee-beach'],
        'gengenbach' => ['gengenbach-rathaus'],
    ];

    public function run(): void
    {
        // [slug, title, tour type, destination, nights, sample price, images override]
        $packages = [
            ['mosel-valley-and-cochem-self-drive', 'Mosel Valley and Cochem Self Drive', 'self-drive', 'cochem', 7, 1890, null],
            ['black-forest-and-gengenbach-self-drive', 'Black Forest and Gengenbach Self Drive', 'self-drive', 'gengenbach', 6, 1850, null],
            ['saxon-switzerland-and-dresden-self-drive', 'Saxon Switzerland and Dresden Self Drive', 'self-drive', 'saxon-switzerland', 5, 1590, null],
            ['rhine-and-mosel-wine-route-self-drive', 'Rhine and Mosel Wine Route Self Drive', 'self-drive', 'cochem', 8, 2150, ['bacharach-rhine', 'mosel-vineyard']],

            ['quedlinburg-and-the-harz-chauffeur', 'Quedlinburg and the Harz by Private Driver', 'chauffeur-driven', 'quedlinburg', 5, 2390, ['hero-quedlinburg', 'quedlinburg-lane']],
            ['mosel-valley-and-cochem-chauffeur', 'Mosel Valley and Cochem by Private Driver', 'chauffeur-driven', 'cochem', 6, 2690, ['mosel-vineyard', 'cochem-mosel']],
            ['hiddensee-baltic-coast-chauffeur', 'Hiddensee and the Baltic Coast by Private Driver', 'chauffeur-driven', 'hiddensee', 5, 2590, ['hiddensee-beach', 'hiddensee-lighthouse']],
            ['rhine-and-mosel-wine-villages-chauffeur', 'Rhine and Mosel Wine Villages by Private Driver', 'chauffeur-driven', 'cochem', 7, 2990, ['bacharach-rhine', 'cochem-mosel']],

            ['saxon-switzerland-and-dresden-coach', 'Saxon Switzerland and Dresden Small-Group Coach Tour', 'coach-tours', 'saxon-switzerland', 6, 1890, null],
            ['black-forest-small-group-coach', 'Black Forest Small-Group Coach Tour', 'coach-tours', 'gengenbach', 7, 2090, null],
            ['hiddensee-baltic-coast-coach', 'Baltic Coast and Hiddensee Small-Group Coach Tour', 'coach-tours', 'hiddensee', 6, 2190, ['hiddensee-beach', 'hiddensee-lighthouse']],
            ['harz-mountains-and-quedlinburg-coach', 'Harz Mountains and Quedlinburg Small-Group Coach Tour', 'coach-tours', 'quedlinburg', 5, 1790, ['hero-quedlinburg', 'quedlinburg-lane']],
        ];

        $sortBase = (int) Package::max('sort') + 1;
        foreach ($packages as $i => [$slug, $title, $type, $dest, $nights, $price, $imgs]) {
            $destination = Destination::where('slug', $dest)->first();
            $tour = TourType::where('slug', $type)->first();
            if (! $destination || ! $tour) {
                continue;
            }
            $style = strtolower($tour->name);
            $package = Package::updateOrCreate(['slug' => $slug], [
                'tour_type_id' => $tour->id, 'destination_id' => $destination->id,
                'title' => $title, 'nights' => $nights, 'price_from' => $price, 'currency' => 'USD',
                'rating' => 5.0,
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
                'is_published' => true, 'is_featured' => false, 'is_sample' => true, 'sort' => $sortBase + $i,
                'seo_title' => "{$title} | Go Germany Tours",
                'meta_description' => "{$nights}-night {$style} vacation around {$destination->name}, Germany. Request a free quote.",
            ]);

            if ($package->images()->exists()) {
                continue;
            }
            foreach (array_values($imgs ?? self::IMAGES[$dest]) as $n => $stem) {
                $m = Media::where('path', 'like', "media/{$stem}-%")->first();
                if ($m) {
                    PackageImage::create(['package_id' => $package->id, 'path' => $m->path, 'alt' => $m->alt, 'sort' => $n]);
                }
            }
            if (! $package->og_image && ($first = $package->images()->first())) {
                $package->update(['og_image' => $first->path]);
            }
        }
    }
}
