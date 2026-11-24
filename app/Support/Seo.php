<?php

namespace App\Support;

use App\Models\Media;
use App\Models\Setting;
use Illuminate\Support\Facades\Vite;

/**
 * Builds the per-page SEO array consumed by layouts/app.blade.php:
 * title, description, canonical, image, noindex, breadcrumbs, schema[].
 */
class Seo
{
    public static function make(?string $title = null, ?string $description = null, array $opts = []): array
    {
        $brand = Setting::get('brand_name');
        $image = $opts['image'] ?? Setting::get('default_og_image');
        $media = Media::forPath(is_string($image) ? $image : null);

        return [
            'title' => $title ?: Setting::get('default_meta_title'),
            'description' => $description ?: Setting::get('default_meta_description'),
            'canonical' => $opts['canonical'] ?? url()->current(),
            'image' => $media?->url(min(1280, max($media->widths))),
            'image_alt' => $media?->alt,
            'noindex' => (bool) ($opts['noindex'] ?? false),
            'type' => $opts['type'] ?? 'website',
            'breadcrumbs' => $opts['breadcrumbs'] ?? [],
            'schema' => $opts['schema'] ?? [],
            'brand' => $brand,
        ];
    }

    /** Page title with brand suffix unless it already contains the brand. */
    public static function titleWithBrand(string $title): string
    {
        $brand = Setting::get('brand_name');

        return str_contains($title, $brand) ? $title : "{$title} | {$brand}";
    }

    public static function breadcrumbSchema(array $crumbs): ?array
    {
        if (count($crumbs) < 2) {
            return null;
        }

        return [
            '@context' => 'https://schema.org', '@type' => 'BreadcrumbList',
            'itemListElement' => collect($crumbs)->values()->map(fn ($c, $i) => [
                '@type' => 'ListItem', 'position' => $i + 1, 'name' => $c['name'], 'item' => $c['url'],
            ])->all(),
        ];
    }

    public static function faqSchema($faqs): ?array
    {
        if ($faqs->isEmpty()) {
            return null;
        }

        return [
            '@context' => 'https://schema.org', '@type' => 'FAQPage',
            'mainEntity' => $faqs->map(fn ($f) => [
                '@type' => 'Question', 'name' => $f->question,
                'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f->answer],
            ])->values()->all(),
        ];
    }

    public static function organizationSchema(): array
    {
        $logo = Media::forPath(Setting::get('logo'));
        $schema = [
            '@context' => 'https://schema.org', '@type' => 'TravelAgency',
            'name' => Setting::get('brand_name'), 'url' => url('/'),
            'description' => Setting::get('default_meta_description'),
        ];
        if ($logo) {
            $schema['logo'] = $logo->url();
        }
        foreach (['phone' => 'telephone', 'email' => 'email', 'address' => 'address'] as $k => $prop) {
            if ($v = Setting::get($k)) {
                $schema[$prop] = $v;
            }
        }
        $sameAs = array_values(array_filter([Setting::get('facebook_url'), Setting::get('instagram_url'), Setting::get('youtube_url')]));
        if ($sameAs) {
            $schema['sameAs'] = $sameAs;
        }

        return $schema;
    }

    /** Font preload URLs (guarded so a missing build never breaks the page). */
    public static function fontPreloads(): array
    {
        $urls = [];
        foreach (['manrope/files/manrope-latin-wght-normal.woff2', 'bodoni-moda/files/bodoni-moda-latin-wght-normal.woff2'] as $f) {
            try {
                $urls[] = Vite::asset("node_modules/@fontsource-variable/{$f}");
            } catch (\Throwable) {
            }
        }

        return $urls;
    }
}
