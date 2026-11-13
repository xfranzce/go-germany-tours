<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected $guarded = [];

    protected $casts = ['is_published' => 'boolean', 'published_at' => 'datetime', 'noindex' => 'boolean'];

    public function scopePublished($q)
    {
        return $q->where('is_published', true)->where('published_at', '<=', now())->orderByDesc('published_at');
    }
}
