<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class Testimonial extends Model
{
    use \App\Concerns\LogsActivity;

    /** Columns that tie a review to one page type. None set = a general review. */
    public const LINKS = ['destination_id', 'tour_type_id', 'travel_style_id'];

    protected $guarded = [];

    protected $casts = ['is_active' => 'boolean', 'is_placeholder' => 'boolean',
        'destination_id' => 'integer', 'tour_type_id' => 'integer', 'travel_style_id' => 'integer'];

    public function scopeActive($q)
    {
        return $q->where('is_active', true)->orderBy('sort');
    }

    /** Reviews not tied to any destination, tour type or travel style. */
    public function scopeGeneral(Builder $q): Builder
    {
        foreach (self::LINKS as $col) {
            $q->whereNull($col);
        }

        return $q;
    }

    public function destination()
    {
        return $this->belongsTo(Destination::class);
    }

    public function tourType()
    {
        return $this->belongsTo(TourType::class);
    }

    public function travelStyle()
    {
        return $this->belongsTo(TravelStyle::class);
    }

    /**
     * Reviews for a page. With a link column + id (e.g. 'destination_id', 5): that page's own reviews; if it has none,
     * the general ones. Without: the general reviews (home, packages, overviews), or every active review if no
     * general ones exist yet, so the section never disappears just because all reviews are page-specific.
     */
    public static function forPage(?string $column = null, ?int $id = null): Collection
    {
        if ($column && in_array($column, self::LINKS, true) && $id) {
            $own = static::active()->where($column, $id)->get();
            if ($own->isNotEmpty()) {
                return $own;
            }
        }

        $general = static::active()->general()->get();

        return $general->isNotEmpty() ? $general : static::active()->get();
    }

    /** Short label for the admin list: where this review shows. */
    public function placeLabel(): string
    {
        $parts = array_filter([$this->destination?->name, $this->tourType?->name, $this->travelStyle?->name]);

        return $parts ? implode(', ', $parts) : 'General';
    }
}
