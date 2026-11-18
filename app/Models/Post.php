<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    public const CATEGORIES = ['Self Drive', 'Chauffeur Driven', 'Coach Tours'];

    protected $guarded = [];

    protected $casts = ['is_published' => 'boolean', 'published_at' => 'datetime', 'noindex' => 'boolean'];

    public function readingMinutes(): int
    {
        return max(1, (int) ceil(str_word_count(strip_tags((string) $this->body)) / 200));
    }

    public function scopePublished($q)
    {
        return $q->where('is_published', true)->where('published_at', '<=', now())->orderByDesc('published_at');
    }
}
