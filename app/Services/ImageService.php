<?php

namespace App\Services;

use App\Models\Media;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

/**
 * Turns an uploaded/local image into responsive WebP files on the public disk:
 *   media/{stem}-{width}.webp  for each width up to the original size.
 * The Media row records the stem, dimensions and widths so templates can
 * output srcset plus width/height (no layout shift).
 */
class ImageService
{
    public const WIDTHS = [480, 768, 1280, 1920];

    public const MAX_BYTES = 8 * 1024 * 1024;

    public function store(UploadedFile|string $file, string $alt = '', ?string $name = null): Media
    {
        $path = $file instanceof UploadedFile ? $file->getRealPath() : $file;
        $original = $name ?? ($file instanceof UploadedFile ? $file->getClientOriginalName() : basename($file));

        $manager = new ImageManager(new Driver());
        $image = $manager->read($path);
        $origW = $image->width();
        $origH = $image->height();

        $stem = 'media/'.Str::slug(pathinfo($original, PATHINFO_FILENAME)).'-'.Str::lower(Str::random(6));
        $widths = array_values(array_filter(self::WIDTHS, fn ($w) => $w < $origW));
        $widths[] = min($origW, max(self::WIDTHS));
        $widths = array_values(array_unique($widths));

        $bytes = 0;
        foreach ($widths as $w) {
            $encoded = $manager->read($path)->scaleDown(width: $w)->toWebp(quality: 80);
            Storage::disk('public')->put("{$stem}-{$w}.webp", (string) $encoded);
            $bytes += strlen((string) $encoded);
        }

        $largest = max($widths);

        return Media::create([
            'path' => $stem, 'original_name' => $original, 'alt' => $alt,
            'width' => $largest, 'height' => (int) round($origH * $largest / $origW),
            'widths' => $widths, 'bytes' => $bytes,
        ]);
    }

    public function delete(Media $media): void
    {
        foreach ($media->widths as $w) {
            Storage::disk('public')->delete("{$media->path}-{$w}.webp");
        }
        $media->delete();
    }
}
