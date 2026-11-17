<?php

namespace Database\Seeders;

use App\Models\Faq;
use App\Models\TourType;
use Illuminate\Database\Seeder;

/** Starter FAQs for each tour type page (draft copy: [CONFIRM] items need the business's real answers). Safe to repeat. */
class TourTypeFaqSeeder extends Seeder
{
    public function run(): void
    {
        $sets = [
            'self-drive' => [
                ['How does a self drive tour work?', 'We plan the route and book your stays, then you drive yourself from place to place at your own pace. You get a suggested route and local tips for each stop.'],
                ['Do I need to be comfortable driving on the right?', 'Germany drives on the right, like most of Europe. Tell us about your experience and we can keep the routes simple. [CONFIRM: driver requirements, minimum age]'],
                ['Is the rental car included?', 'Tell us whether you would like us to include a car or arrange it yourself and we will quote accordingly. [CONFIRM: whether car hire is included or arranged separately]'],
                ['Can the route be changed to suit me?', 'Yes. Share your dates, group size and the places you would like to see and we will adjust the route and the length of each stay.'],
                ['What if I want to stay longer somewhere?', 'Just ask. Because the trip is built around you, we can add nights where you want more time. [CONFIRM: change and amendment policy]'],
            ],
            'chauffeur-driven' => [
                ['How does a chauffeur driven tour work?', 'A private driver takes you between your stays so you can relax and enjoy the views. We plan the route and the stays around your dates and interests.'],
                ['Does the driver stay with us for the whole trip?', 'Tell us how you would like to travel and we will quote it. [CONFIRM: driver arrangements and what the driver does each day]'],
                ['Is a chauffeur driven tour private?', 'Yes. The tour is arranged just for your party, not shared with other travelers. [CONFIRM: vehicle size and capacity]'],
                ['Can we stop wherever we like?', 'Your route is planned around the places you want to see, and we can add stops and free time. [CONFIRM: flexibility on the day]'],
                ['Is this a good choice for older travelers or families?', 'Many travelers choose a chauffeur for comfort and ease. Tell us who is travelling and we will shape the pace to suit everyone.'],
            ],
            'coach-tours' => [
                ['How big are the coach groups?', 'These are small-group coach tours, shared with other travelers. [CONFIRM: typical and maximum group size]'],
                ['Is a guide included?', 'Tell us what you would like and we will explain what each tour includes. [CONFIRM: guide arrangements and languages]'],
                ['Are the dates fixed?', 'Send us your preferred dates and we will tell you what is available. [CONFIRM: departure dates and availability]'],
                ['Can I travel solo on a coach tour?', 'Yes, solo travelers are welcome. [CONFIRM: single supplement and solo traveler policy]'],
                ['What is included in the price?', 'Ask for a free quote and we will list exactly what is included for your chosen tour. [CONFIRM: inclusions and exclusions]'],
            ],
        ];

        foreach ($sets as $slug => $faqs) {
            $type = TourType::where('slug', $slug)->first();
            if (! $type || Faq::where('tour_type_id', $type->id)->exists()) {
                continue;
            }
            foreach ($faqs as $i => [$q, $a]) {
                Faq::create(['tour_type_id' => $type->id, 'question' => $q, 'answer' => $a, 'sort' => $i]);
            }
        }
    }
}
