<?php

namespace App\Console\Commands;

use App\Models\Media;
use App\Services\ImageService;
use Database\Seeders\ImageSeeder;
use Illuminate\Console\Command;

class ImagesImportSeed extends Command
{
    protected $signature = 'images:import-seed';

    protected $description = 'Import photos from database/seed-images (listed in ImageSeeder::ALT) that have no Media row yet, and re-encode ones whose file has changed';

    public function handle(ImageService $svc): int
    {
        // md5 of each seed file as last encoded here, so a replaced file is re-encoded once (same Media row and paths, new ?v=)
        $manifestFile = storage_path('app/seed-images.json');
        $manifest = is_file($manifestFile) ? (json_decode((string) file_get_contents($manifestFile), true) ?: []) : [];

        foreach (ImageSeeder::ALT as $slug => $alt) {
            $file = database_path("seed-images/{$slug}.jpg");
            if (! is_file($file)) {
                continue;
            }
            $hash = md5_file($file);
            $media = Media::where('original_name', "{$slug}.jpg")->first();
            if (! $media) {
                $m = $svc->store($file, $alt, "{$slug}.jpg");
                $this->info("Imported {$m->path} ({$m->width}px)");
            } elseif (($manifest[$slug] ?? null) !== $hash) {
                $svc->regenerate($media, $file);
                $this->info("Re-encoded {$media->path} ({$media->fresh()->width}px)");
            }
            $manifest[$slug] = $hash;
        }
        file_put_contents($manifestFile, json_encode($manifest, JSON_PRETTY_PRINT));

        return self::SUCCESS;
    }
}
