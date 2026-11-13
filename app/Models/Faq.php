<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Faq extends Model
{
    protected $guarded = [];

    protected $casts = ['is_active' => 'boolean'];

    public function scopeHome($q)
    {
        return $q->whereNull('package_id')->where('is_active', true)->orderBy('sort');
    }
}
