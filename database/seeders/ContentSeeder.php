<?php

namespace Database\Seeders;

use App\Models\Faq;
use App\Models\Post;
use App\Models\Testimonial;
use Illuminate\Database\Seeder;

/** FAQs, dummy (PLACEHOLDER-badged) testimonials and starter blog posts. All draft copy: review before launch. */
class ContentSeeder extends Seeder
{
    public function run(): void
    {
        if (! Faq::whereNull('package_id')->exists()) {
            $faqs = [
                ['How does the quote process work?', 'Answer a few quick questions or fill in the enquiry form. We review your dates, group size and preferences and send you a tailored quote. There is no obligation to book.'],
                ['What is the difference between self drive, chauffeur driven and coach tours?', 'On a self drive you drive yourself between pre-arranged stays. With a chauffeur a private driver takes you. A coach tour is a guided itinerary with other travelers.'],
                ['Can I combine more than one destination?', 'Yes, you can tell us which of Quedlinburg, Saxon Switzerland, Cochem, Hiddensee and Gengenbach interest you and we will quote a route. [CONFIRM: combination options and minimum stays]'],
                ['Are flights included?', 'Flights are not included in the sample packages. Tell us if you have already booked flights and we will plan around your arrival and departure. [CONFIRM: flight policy]'],
                ['What does a quote cost?', 'Quotes are free. [CONFIRM: deposit, payment and cancellation terms]'],
                ['Can I get a second quote from a partner?', 'If you tick that option on the form we may share your request with a partner so you can compare. [CONFIRM: partner arrangement]'],
            ];
            foreach ($faqs as $i => [$q, $a]) {
                Faq::create(['question' => $q, 'answer' => $a, 'sort' => $i]);
            }
        }

        // DUMMY testimonials shown with a PLACEHOLDER badge while the site is new. Replace with real,
        // permissioned guest reviews (name, town, source) BEFORE running ads: remove the placeholder flag only for real ones.
        if (! Testimonial::exists()) {
            $drafts = [
                ['Sample Guest A.', 'Sample town', 'We wanted the fairytale towns without the stress of planning it all. The route was sorted before we landed and every stay was in the heart of the old town. Quedlinburg felt like walking into a storybook.'],
                ['Sample Guest B.', 'Sample town', 'Having a private driver along the Mosel meant we could enjoy the wine villages and the views instead of watching the road. Everything ran on time and nothing felt rushed.'],
                ['Sample Guest C.', 'Sample town', 'I had a few questions about dates and group size and got a clear, tailored reply, with no pressure. The self-drive route through the Black Forest was exactly the pace we wanted.'],
            ];
            foreach ($drafts as $i => [$name, $loc, $quote]) {
                Testimonial::create(['name' => $name, 'location' => $loc, 'quote' => $quote, 'source' => 'Sample review: replace before launch', 'is_placeholder' => true, 'is_active' => true, 'sort' => $i + 1]);
            }
        }

        $posts = [
            ['first-time-in-germany-fairytale-towns', 'Germany\'s fairytale towns: where to start', 'A short guide to choosing between Quedlinburg, Cochem and Gengenbach.',
                '<p>[SAMPLE ARTICLE: replace with real content] Quedlinburg, Cochem and Gengenbach all have half-timbered old towns, but each has a different setting: the Harz mountains, the Mosel river and the Black Forest.</p>'],
            ['self-drive-or-chauffeur-germany', 'Self drive or chauffeur in Germany?', 'How to decide which way of travelling suits you.',
                '<p>[SAMPLE ARTICLE: replace with real content] Self drive suits travelers who want freedom; a chauffeur suits those who want to relax. Coach tours suit those who like a guided pace.</p>'],
        ];
        foreach ($posts as [$slug, $title, $excerpt, $body]) {
            Post::firstOrCreate(['slug' => $slug], [
                'title' => $title, 'excerpt' => $excerpt, 'body' => $body, 'is_published' => true, 'published_at' => now()->subDay(),
                'seo_title' => $title, 'meta_description' => $excerpt,
            ]);
        }
    }
}
