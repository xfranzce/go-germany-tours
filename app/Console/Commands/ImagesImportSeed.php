<?php

namespace App\Console\Commands;

use App\Models\Media;
use App\Services\ImageService;
use Database\Seeders\ImageSeeder;
use Illuminate\Console\Command;

class ImagesImportSeed extends Command
{
    protected $signature = 'images:import-seed';

    protected $description = 'Import any photo from database/seed-images (listed in ImageSeeder::ALT) that has no Media row yet';

    public function handle(ImageService $svc): int
    {
        foreach (ImageSeeder::ALT as $slug => $alt) {
            $file = database_path("seed-images/{$slug}.jpg");
            if (! is_file($file) || Media::where('original_name', "{$slug}.jpg")->exists()) {
                continue;
            }
            $m = $svc->store($file, $alt, "{$slug}.jpg");
            $this->info("Imported {$m->path} ({$m->width}px)");
        }

        return self::SUCCESS;
    }
}
