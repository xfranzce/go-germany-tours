<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/** A way of travelling (Slow, Scenic, Luxury...). Packages can have several; each style has a page at /travel-styles/{slug}. */
class TravelStyle extends Model
{
    use \App\Concerns\LogsActivity;

    protected $guarded = [];

    protected $casts = ['is_active' => 'boolean'];

    public function packages(): BelongsToMany
    {
        return $this->belongsToMany(Package::class);
    }

    public function scopeActive($q)
    {
        return $q->where('is_active', true)->orderBy('sort')->orderBy('name');
    }

    /** "What to expect" points, one per line. */
    public function points(): array
    {
        return array_values(array_filter(array_map('trim', preg_split('/\R/', (string) $this->highlights))));
    }
}
