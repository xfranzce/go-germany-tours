<?php

namespace Database\Seeders;

use App\Models\Media;
use App\Models\Package;
use App\Models\PackageImage;
use Illuminate\Database\Seeder;

/**
 * Gives every package its own cover photo (2026-11-26: many packages in the same region shared the same 1-2 seed photos).
 * The cover is the first of the package's highlight photos that no other package uses as its cover yet; the rest of
 * its highlight photos follow in the gallery. Packages whose gallery holds a photo uploaded in the admin (not a seed
 * image) are left alone. Run by migration 2026_11_26_000005.
 */
class UniquePackageImagesSeeder extends Seeder
{
    public function run(): void
    {
        $seedNames = array_map(fn ($slug) => "{$slug}.jpg", array_keys(ImageSeeder::ALT));
        $seedPaths = Media::whereIn('original_name', $seedNames)->pluck('alt', 'path');   // path => alt
        if ($seedPaths->isEmpty()) {
            return;
        }

        $packages = Package::with('images')->orderBy('sort')->orderBy('id')->get();
        $editable = $packages->filter(fn (Package $p) => $p->images->every(fn ($img) => $seedPaths->has($img->path)));

        // covers already taken by packages we must not touch
        $used = $packages->diff($editable)->map(fn ($p) => $p->images->first()?->path)->filter()->values()->all();

        foreach ($editable as $package) {
            $candidates = collect($package->highlightItems())->pluck('image')->filter(fn ($p) => $p && $seedPaths->has($p))->unique()->values();
            $cover = $candidates->first(fn ($p) => ! in_array($p, $used, true));
            if (! $cover) {
                continue;   // nothing unused left for this one: keep its current photos
            }
            $used[] = $cover;

            $gallery = collect([$cover])->merge($candidates)->unique()->take(4)->values();
            $package->images()->delete();
            foreach ($gallery as $i => $path) {
                PackageImage::create(['package_id' => $package->id, 'path' => $path, 'alt' => $seedPaths[$path] ?: $package->title, 'sort' => $i]);
            }
            $package->update(['og_image' => $cover]);
        }
    }
}
