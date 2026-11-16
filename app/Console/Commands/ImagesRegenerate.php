<?php

namespace App\Console\Commands;

use App\Models\Media;
use App\Services\ImageService;
use Illuminate\Console\Command;

class ImagesRegenerate extends Command
{
    protected $signature = 'images:regenerate {--dir= : Folder holding the original files (default database/seed-images)}';

    protected $description = 'Re-encode Media images at full resolution (up to 3840px) from their original files, keeping the same paths';

    public function handle(ImageService $svc): int
    {
        $dir = $this->option('dir') ?: database_path('seed-images');
        foreach (Media::all() as $m) {
            $src = $dir.'/'.$m->original_name;
            if (! is_file($src)) {
                $this->warn("Skipped {$m->path}: original {$m->original_name} not found (re-upload it in Admin > Media).");

                continue;
            }
            $svc->regenerate($m, $src);
            $this->info("Regenerated {$m->path} ({$m->fresh()->width}px)");
        }

        return self::SUCCESS;
    }
}
