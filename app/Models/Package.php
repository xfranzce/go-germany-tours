<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Package extends Model
{
    protected $guarded = [];

    protected $casts = [
        'highlights' => 'array', 'itinerary' => 'array', 'inclusions' => 'array', 'exclusions' => 'array',
        'is_published' => 'boolean', 'is_featured' => 'boolean', 'is_sample' => 'boolean', 'noindex' => 'boolean',
        'price_from' => 'decimal:2',
    ];

    public function tourType(): BelongsTo
    {
        return $this->belongsTo(TourType::class);
    }

    public function destination(): BelongsTo
    {
        return $this->belongsTo(Destination::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(PackageImage::class)->orderBy('sort');
    }

    public function faqs(): HasMany
    {
        return $this->hasMany(Faq::class)->where('is_active', true)->orderBy('sort');
    }

    public function scopePublished($q)
    {
        return $q->where('is_published', true)->orderBy('sort')->orderBy('title');
    }

    public function url(): string
    {
        return route('packages.show', $this->slug);
    }

    public function coverPath(): ?string
    {
        return $this->images->first()?->path;
    }

    public function priceLabel(): string
    {
        return $this->price_from
            ? 'From '.($this->currency === 'USD' ? '$' : $this->currency.' ').number_format((float) $this->price_from).' pp'
            : 'Price on request';
    }
}
