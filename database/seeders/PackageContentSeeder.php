<?php

namespace Database\Seeders;

use App\Models\Media;
use App\Models\Package;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

/**
 * Fills the sample packages with an overview, highlights (with photos), a day-by-day itinerary and included / not
 * included lists, from database/seeders/data/package-content.php. Only touches packages that still hold placeholder
 * text ("[SAMPLE]" in the description or highlights), so admin edits are never overwritten.
 * Run by migration 2026_11_26_000004 and by DatabaseSeeder.
 */
class PackageContentSeeder extends Seeder
{
    public function run(): void
    {
        $data = require database_path('seeders/data/package-content.php');
        $targets = Package::withTrashed()->whereIn('slug', array_keys($data['packages']))->get()
            ->filter(fn (Package $p) => str_contains((string) $p->description, '[SAMPLE]') || str_contains(json_encode($p->highlights ?? []), '[SAMPLE]'));
        if ($targets->isEmpty()) {
            return;
        }

        // the highlight photos are seed images: make sure they are imported before linking them
        if (Media::where('original_name', 'like', 'hl-%')->doesntExist()) {
            Artisan::call('images:import-seed');
        }

        foreach ($targets as $package) {
            [$base, $intro, $dayKeys, $stems, $extra] = $data['packages'][$package->slug];
            $type = $package->tourType?->slug;
            $nights = (int) $package->nights;

            $package->update([
                'summary' => $intro,
                'description' => '<p>'.e($intro).'</p><p>'.e($this->typeText($type)).'</p>'
                    .'<p>This is a sample itinerary to start from. Dates, hotels, pace and price can all be changed to suit you.</p>',
                'highlights' => array_map(fn ($stem) => [
                    'title' => $data['hl'][$stem][0], 'text' => $data['hl'][$stem][1],
                    'image' => Media::where('path', 'like', "media/{$stem}-%")->value('path'),
                ], $stems),
                'itinerary' => $this->itinerary($type, $base, $nights, $dayKeys, $data['days']),
                'inclusions' => $this->inclusions($type, $nights, $extra),
                'exclusions' => $this->exclusions($type),
            ]);
        }
    }

    private function typeText(?string $type): string
    {
        return match ($type) {
            'self-drive' => 'You drive at your own pace with the hotels booked and the route planned for you. Every day has suggested stops, walks and places to eat, and there is always room to change the plan.',
            'chauffeur-driven' => 'A private driver takes care of the roads, parking and timings, so you can simply enjoy the views. Your driver meets you on arrival and shapes each day around your pace.',
            default => 'Travel in a small group with a tour leader who handles the route, the luggage and the hotel check-ins, with free time in every town.',
        };
    }

    /** Arrival day, one day per middle stop (nights - 1 of them), departure day. */
    private function itinerary(?string $type, string $base, int $nights, array $keys, array $pool): array
    {
        $arrive = match ($type) {
            'self-drive' => ["Arrive in {$base}", "Collect your rental car and drive to {$base}. Check in and spend the evening exploring the old town."],
            'chauffeur-driven' => ["Arrive in {$base}", "Your private driver meets you at the airport or station and takes you to your hotel in {$base}. The rest of the day is yours."],
            default => ["Arrive in {$base}", "Meet your tour leader and fellow travelers at the hotel in {$base}, followed by a welcome dinner."],
        };
        $leave = match ($type) {
            'self-drive' => ['Departure', 'After breakfast, drive on or return your rental car.'],
            'chauffeur-driven' => ['Departure', 'Your driver takes you to the airport or station for your onward journey.'],
            default => ['Departure', 'The tour ends after breakfast. Your tour leader helps with onward travel.'],
        };

        $days = [$arrive];
        $keys = array_slice(array_pad($keys, max(0, $nights - 1), null), 0, max(0, $nights - 1));
        foreach ($keys as $k) {
            $days[] = $k && isset($pool[$k]) ? $pool[$k] : ['Free day', 'A free day to explore at your own pace, or to rest before the next stop.'];
        }
        $days[] = $leave;

        return array_map(fn ($d, $i) => ['title' => 'Day '.($i + 1).': '.$d[0], 'body' => $d[1]], $days, array_keys($days));
    }

    private function inclusions(?string $type, int $nights, string $extra): array
    {
        return match ($type) {
            'self-drive' => ["{$nights} nights in hand-picked hotels with daily breakfast", 'Rental car with unlimited mileage', 'Route plan and road book', $extra, 'Local tips for stops and restaurants', 'Phone support during your trip'],
            'chauffeur-driven' => ["{$nights} nights in hand-picked hotels with daily breakfast", 'Private driver and comfortable vehicle on touring days', 'Arrival and departure transfers', $extra, 'Fuel, parking and road tolls', 'Phone support during your trip'],
            default => ["{$nights} nights in hotels with daily breakfast", 'Small-group coach travel', 'English-speaking tour leader', $extra, 'Guided walks in the main towns', 'Welcome dinner'],
        };
    }

    private function exclusions(?string $type): array
    {
        return array_values(array_filter([
            'International flights',
            'Travel insurance',
            $type === 'coach-tours' ? 'Lunches and dinners not listed' : 'Lunches and dinners',
            $type === 'self-drive' ? 'Fuel, parking and road tolls' : null,
            'Entrance fees not listed as included',
            'Tips and personal expenses',
        ]));
    }
}
