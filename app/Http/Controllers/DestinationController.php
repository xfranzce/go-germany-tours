<?php

namespace App\Http\Controllers;

use App\Models\Destination;
use App\Models\Faq;
use App\Models\Page;
use App\Models\Testimonial;
use App\Support\Seo;

/** /destinations overview and one page per destination (layout modelled on gobelgiumtrips.com/destinations). */
class DestinationController extends Controller
{
    public function index()
    {
        $destinations = Destination::withListedCounts(Destination::active()->get());

        return view('destinations.index', $this->shared() + [
            'destinations' => $destinations,
            'seo' => Seo::make('Germany Destinations: Regions and Towns to Visit',
                'Explore Germany by region: '.$destinations->pluck('name')->join(', ', ' and ').'. See the highlights of each and request a free, tailored quote.', [
                    'image' => $destinations->first()?->image,
                    'breadcrumbs' => [['name' => 'Home', 'url' => url('/')], ['name' => 'Destinations', 'url' => route('destinations.index')]],
                ]),
        ]);
    }

    public function show(string $slug)
    {
        $destination = Destination::where('slug', $slug)->where('is_active', true)->firstOrFail();
        $packages = \App\Models\Package::published()->listedOn($destination)->with(['tourType', 'destination', 'images'])->get();

        [$faqs, $ownFaqs] = Faq::forPage('destination_id', $destination->id);

        return view('destinations.show', ['faqs' => $faqs, 'ownFaqs' => $ownFaqs] + $this->shared('destination_id', $destination->id) + [
            'destination' => $destination, 'packages' => $packages,
            'others' => Destination::withListedCounts(Destination::active()->where('id', '!=', $destination->id)->get()),
            'seo' => Seo::make($destination->seo_title ?: $destination->name.' Tours and Vacations', $destination->meta_description ?: $destination->summary, [
                'image' => $destination->image, 'schema' => $ownFaqs ? [Seo::faqSchema($faqs)] : [],
                'breadcrumbs' => [['name' => 'Home', 'url' => url('/')], ['name' => 'Destinations', 'url' => route('destinations.index')], ['name' => $destination->name, 'url' => route('destinations.show', $destination->slug)]],
            ]),
        ]);
    }

    /** Data the shared home sections (why, how, reviews, FAQ) need. Reviews: this destination's own, else general. */
    private function shared(?string $reviewsFor = null, ?int $id = null): array
    {
        return ['page' => Page::byKey('home'), 'testimonials' => Testimonial::forPage($reviewsFor, $id), 'faqs' => Faq::home()->get()];
    }
}
