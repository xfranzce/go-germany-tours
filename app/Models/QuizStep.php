<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuizStep extends Model
{
    use \App\Concerns\LogsActivity;

    protected $guarded = [];

    protected $casts = ['is_active' => 'boolean'];

    public function options(): HasMany
    {
        return $this->hasMany(QuizOption::class)->orderBy('sort');
    }

    public function activeOptions(): HasMany
    {
        return $this->hasMany(QuizOption::class)->where('is_active', true)->orderBy('sort');
    }

    /** Active steps with active options, shaped for the Alpine quiz. */
    public static function forQuiz(): array
    {
        return static::where('is_active', true)->orderBy('sort')->with('activeOptions')->get()
            ->filter(fn ($s) => $s->activeOptions->isNotEmpty())
            ->map(fn ($s) => [
                'key' => $s->key, 'question' => $s->question, 'type' => $s->type,
                'options' => $s->activeOptions->map(fn ($o) => ['label' => $o->label, 'icon' => $o->icon])->values()->all(),
            ])->values()->all();
    }
}
