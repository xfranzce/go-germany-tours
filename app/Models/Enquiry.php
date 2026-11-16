<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Enquiry extends Model
{
    protected $guarded = [];

    protected $casts = [
        'quiz_answers' => 'array', 'first_touch' => 'array', 'last_touch' => 'array',
        'consent_given' => 'boolean', 'consented_at' => 'datetime', 'email_sent_at' => 'datetime',
        'ghl_synced_at' => 'datetime',
    ];

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class)->withTrashed();
    }

    public function fullName(): string
    {
        return trim($this->first_name.' '.$this->last_name);
    }

    public function formLabel(): string
    {
        return config('gogermany.form_names.'.$this->form_name, $this->form_name);
    }
}
