<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Services\ImageService;
use App\Support\Html;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

abstract class AdminController extends Controller
{
    /** The site name is added to every page title automatically, so drop it if someone typed it into an SEO title (it would also use up the 70 characters). */
    protected function stripBrandFromSeoTitle(Request $request): void
    {
        $title = $request->input('seo_title');
        $brand = trim((string) \App\Models\Setting::get('brand_name'));
        if (! is_string($title) || $brand === '') {
            return;
        }
        $clean = preg_replace('/\s*[|\-\x{2013}\x{2014}:]\s*'.preg_quote($brand, '/').'\s*$/iu', '', trim($title));
        $request->merge(['seo_title' => $clean]);
    }

    /** Validation rules for an <x-admin.image name="..."> field. */
    protected function imageRules(string $name = 'image'): array
    {
        return [
            "{$name}_file" => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:8192', 'dimensions:min_width=400,min_height=300'],
            "{$name}_alt" => ["required_with:{$name}_file", 'nullable', 'string', 'max:200'],
            "{$name}_remove" => ['nullable', 'boolean'],
        ];
    }

    /** Applies an upload/removal/alt edit and returns the media path to store (or null). */
    protected function handleImage(Request $request, string $name = 'image', ?string $existing = null): ?string
    {
        if ($request->boolean("{$name}_remove")) {
            return null;
        }
        if ($request->hasFile("{$name}_file")) {
            return app(ImageService::class)->store($request->file("{$name}_file"), (string) $request->input("{$name}_alt"))->path;
        }
        if ($existing && $request->filled("{$name}_alt") && ($media = Media::forPath($existing))) {
            $media->update(['alt' => $request->input("{$name}_alt")]);
        }

        return $existing;
    }

    protected function clean(?string $html): ?string
    {
        return Html::clean($html);
    }

    /** Unique slug for a model, ignoring the row being edited. */
    protected function slug(string $modelClass, ?string $given, string $fallback, ?int $ignoreId = null): string
    {
        $base = Str::slug($given ?: $fallback) ?: 'item';
        $slug = $base;
        $i = 2;
        $query = fn () => in_array(\Illuminate\Database\Eloquent\SoftDeletes::class, class_uses_recursive($modelClass)) ? $modelClass::withTrashed() : $modelClass::query();
        while ($query()->where('slug', $slug)->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }

    /** Textarea with one item per line -> array. */
    protected function lines(?string $text): array
    {
        return array_values(array_filter(array_map('trim', preg_split('/\R/', (string) $text))));
    }
}
