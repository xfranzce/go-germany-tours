<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    use \App\Concerns\LogsActivity;

    protected $guarded = [];

    protected $casts = ['data' => 'array', 'noindex' => 'boolean'];

    public static function byKey(string $key): self
    {
        return static::where('key', $key)->firstOrFail();
    }

    /** Fetch an editable text value from the page's JSON "data" column. */
    public function text(string $key, string $default = ''): string
    {
        return (string) (($this->data[$key] ?? '') !== '' ? $this->data[$key] : $default);
    }
}
