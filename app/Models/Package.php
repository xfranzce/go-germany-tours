<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Package extends Model
{
    use \App\Concerns\LogsActivity;

    use SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'highlights' => 'array', 'itinerary' => 'array', 'inclusions' => 'array', 'exclusions' => 'array',
        'is_published' => 'boolean', 'is_featured' => 'boolean', 'is_sample' => 'boolean', 'noindex' => 'boolean',
        'price_from' => 'decimal:2',
        'price_was' => 'decimal:2',
        'rating' => 'decimal:1',
    ];

    public function tourType(): BelongsTo
    {
        return $this->belongsTo(TourType::class);
    }

    public function destination(): BelongsTo
    {
        return $this->belongsTo(Destination::class);
    }

    /** Extra destinations this package is also featured on (besides its main destination_id). */
    public function featuredDestinations(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Destination::class);
    }

    /** Published packages listed on a destination's page: its own packages plus the ones ticked to feature there. */
    public function scopeListedOn($q, Destination $destination)
    {
        return $q->where(fn ($w) => $w->where('destination_id', $destination->id)
            ->orWhereHas('featuredDestinations', fn ($f) => $f->whereKey($destination->id)));
    }

    public function travelStyles(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(TravelStyle::class);
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

    /** True when a higher "was" price is set above the current price. */
    public function hasSale(): bool
    {
        return $this->price_from && $this->price_was && (float) $this->price_was > (float) $this->price_from;
    }

    public function wasLabel(): ?string
    {
        return $this->hasSale() ? 'From '.($this->currency === 'USD' ? '$' : $this->currency.' ').number_format((float) $this->price_was).' pp' : null;
    }

    /** The current price, never prefixed with "From": that word only appears on the crossed-out old price when there is a discount (see wasLabel). */
    public function priceLabel(): string
    {
        return $this->price_from
            ? ($this->currency === 'USD' ? '$' : $this->currency.' ').number_format((float) $this->price_from).' pp'
            : 'Price on request';
    }
}
