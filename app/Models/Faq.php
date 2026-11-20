<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Faq extends Model
{
    use \App\Concerns\LogsActivity;

    protected $guarded = [];

    protected $casts = ['is_active' => 'boolean'];

    public function scopeHome($q)
    {
        return $q->whereNull('package_id')->whereNull('tour_type_id')->where('is_active', true)->orderBy('sort');
    }

    public function scopeForTourType($q, int $id)
    {
        return $q->where('tour_type_id', $id)->where('is_active', true)->orderBy('sort');
    }

    public function tourType()
    {
        return $this->belongsTo(TourType::class);
    }
}
