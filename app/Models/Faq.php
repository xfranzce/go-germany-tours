<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class Faq extends Model
{
    use \App\Concerns\LogsActivity;

    /** Columns that put an FAQ on one page instead of the home page (package FAQs live inside each package). */
    public const PAGES = ['tour_type_id' => 'Tour type', 'destination_id' => 'Destination', 'travel_style_id' => 'Travel style'];

    protected $guarded = [];

    protected $casts = ['is_active' => 'boolean', 'tour_type_id' => 'integer', 'destination_id' => 'integer', 'travel_style_id' => 'integer'];

    /** General FAQs: home page, /faqs, overview pages and any page without FAQs of its own. */
    public function scopeHome($q)
    {
        $q->whereNull('package_id');
        foreach (array_keys(self::PAGES) as $col) {
            $q->whereNull($col);
        }

        return $q->where('is_active', true)->orderBy('sort');
    }

    public function scopeForTourType($q, int $id)
    {
        return $q->where('tour_type_id', $id)->where('is_active', true)->orderBy('sort');
    }

    /**
     * FAQs for one page: its own (e.g. 'destination_id', 5) or, if it has none, the general ones.
     * Returns [faqs, isOwn] so the view can title them and the controller only emits FAQ schema for page-specific ones.
     */
    public static function forPage(string $column, int $id): array
    {
        $own = in_array($column, array_keys(self::PAGES), true)
            ? static::where($column, $id)->where('is_active', true)->orderBy('sort')->get()
            : new Collection;

        return $own->isNotEmpty() ? [$own, true] : [static::home()->get(), false];
    }

    public function tourType()
    {
        return $this->belongsTo(TourType::class);
    }

    public function destination()
    {
        return $this->belongsTo(Destination::class);
    }

    public function travelStyle()
    {
        return $this->belongsTo(TravelStyle::class);
    }

    /** "Home page" or e.g. "Destination: Cochem" for the admin list. */
    public function placeLabel(): string
    {
        return match (true) {
            (bool) $this->tour_type_id => 'Tour type: '.$this->tourType?->name,
            (bool) $this->destination_id => 'Destination: '.$this->destination?->name,
            (bool) $this->travel_style_id => 'Travel style: '.$this->travelStyle?->name,
            default => 'Home page',
        };
    }

    /** Value for the admin "Shows on" dropdown, e.g. "destination_id:5", or "" for the home page. */
    public function placeKey(): string
    {
        foreach (array_keys(self::PAGES) as $col) {
            if ($this->{$col}) {
                return $col.':'.$this->{$col};
            }
        }

        return '';
    }
}
