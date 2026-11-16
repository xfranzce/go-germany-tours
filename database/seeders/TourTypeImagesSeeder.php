<?php

namespace Database\Seeders;

use App\Models\Media;
use App\Models\TourType;
use Illuminate\Database\Seeder;

/**
 * Gives each tour type a photo that matches how you travel (shown on the home page cards and the Tours mega menu).
 * Run after `php artisan images:import-seed`: php artisan db:seed --class=TourTypeImagesSeeder
 */
class TourTypeImagesSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['self-drive' => 'selfdrive-road', 'chauffeur-driven' => 'chauffeur-private-driver', 'coach-tours' => 'coach-tour-cochem'] as $slug => $stem) {
            $media = Media::where('path', 'like', "media/{$stem}-%")->first();
            if ($media) {
                TourType::where('slug', $slug)->update(['image' => $media->path]);
            }
        }
    }
}
