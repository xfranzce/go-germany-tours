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
    public const WIDTHS = [480, 768, 1280, 1920, 2560, 3840];

    public const QUALITY = 85;

    public const MAX_BYTES = 8 * 1024 * 1024;

    public function store(UploadedFile|string $file, string $alt = '', ?string $name = null): Media
    {
        $path = $file instanceof UploadedFile ? $file->getRealPath() : $file;
        $original = $name ?? ($file instanceof UploadedFile ? $file->getClientOriginalName() : basename($file));

        $stem = 'media/'.Str::slug(pathinfo($original, PATHINFO_FILENAME)).'-'.Str::lower(Str::random(6));
        $info = $this->encode($path, $stem);

        return Media::create([
            'path' => $stem, 'original_name' => $original, 'alt' => $alt,
            'width' => $info['width'], 'height' => $info['height'],
            'widths' => $info['widths'], 'bytes' => $info['bytes'],
        ]);
    }

    /** Re-encode an existing Media row in place from a (higher quality) source file; paths stay the same. */
    public function regenerate(Media $media, string $sourcePath): Media
    {
        $this->delete($media, keepRow: true);
        $info = $this->encode($sourcePath, $media->path);
        $media->update($info);

        return $media;
    }

    /** @return array{width:int,height:int,widths:array<int,int>,bytes:int} */
    private function encode(string $path, string $stem): array
    {
        $manager = new ImageManager(new Driver());
        $image = $manager->read($path);
        $origW = $image->width();
        $origH = $image->height();

        $widths = array_values(array_filter(self::WIDTHS, fn ($w) => $w < $origW));
        $widths[] = min($origW, max(self::WIDTHS));
        $widths = array_values(array_unique($widths));

        $bytes = 0;
        foreach ($widths as $w) {
            $encoded = (string) $manager->read($path)->scaleDown(width: $w)->toWebp(quality: self::QUALITY);
            Storage::disk('public')->put("{$stem}-{$w}.webp", $encoded);
            $bytes += strlen($encoded);
        }

        $largest = max($widths);

        return [
            'width' => $largest, 'height' => (int) round($origH * $largest / $origW),
            'widths' => $widths, 'bytes' => $bytes,
        ];
    }

    public function delete(Media $media, bool $keepRow = false): void
    {
        foreach ($media->widths as $w) {
            Storage::disk('public')->delete("{$media->path}-{$w}.webp");
        }
        if (! $keepRow) {
            $media->delete();
        }
    }
}
