<?php

namespace Database\Seeders;

use App\Models\Destination;
use App\Models\Faq;
use App\Models\TravelStyle;
use Illuminate\Database\Seeder;

/**
 * Starter FAQs for each destination and travel style page (copy in data/page-faqs.php; [CONFIRM] items need the
 * business's real answers). Only fills pages that have no FAQs of their own yet, so it is safe to repeat and never
 * overwrites admin edits.
 */
class PageFaqSeeder extends Seeder
{
    public function run(): void
    {
        $data = require __DIR__.'/data/page-faqs.php';

        $this->fill(Destination::class, 'destination_id', $data['destinations']);
        $this->fill(TravelStyle::class, 'travel_style_id', $data['travel-styles']);
    }

    /** @param  class-string  $model */
    private function fill(string $model, string $column, array $sets): void
    {
        foreach ($sets as $slug => $faqs) {
            $owner = $model::where('slug', $slug)->first();
            if (! $owner || Faq::where($column, $owner->id)->exists()) {
                continue;
            }
            foreach ($faqs as $i => [$q, $a]) {
                Faq::create([$column => $owner->id, 'question' => $q, 'answer' => $a, 'sort' => $i, 'is_active' => true]);
            }
        }
    }
}
