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
