<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Destination extends Model
{
    use \App\Concerns\LogsActivity;

    protected $guarded = [];

    protected $casts = ['is_active' => 'boolean'];

    public function packages(): HasMany
    {
        return $this->hasMany(Package::class);
    }

    /** Packages ticked to also feature on this destination (see Package::scopeListedOn for the full list). */
    public function featuredPackages(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Package::class);
    }

    /** Set packages_count on each destination = published packages listed on its page (main + featured). */
    public static function withListedCounts(\Illuminate\Support\Collection $destinations): \Illuminate\Support\Collection
    {
        $packages = Package::published()->with('featuredDestinations:id')->get(['id', 'destination_id']);
        foreach ($destinations as $d) {
            $d->packages_count = $packages->filter(fn ($p) => $p->destination_id === $d->id || $p->featuredDestinations->contains('id', $d->id))->count();
        }

        return $destinations;
    }

    /** Highlight places, one per line as "Name: short note", returned as [name, note] pairs. */
    public function placeList(): array
    {
        return collect(preg_split('/\R/', (string) $this->places))->map(fn ($l) => trim($l))->filter()
            ->map(fn ($l) => array_map('trim', explode(':', $l, 2)) + [1 => ''])->values()->all();
    }

    public function scopeActive($q)
    {
        return $q->where('is_active', true)->orderBy('sort')->orderBy('name');
    }
}
