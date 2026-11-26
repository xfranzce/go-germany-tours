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
        $destinations = Destination::active()->withCount(['packages' => fn ($q) => $q->published()])->get();

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
        $packages = $destination->packages()->published()->with(['tourType', 'destination', 'images'])->get();

        return view('destinations.show', $this->shared() + [
            'destination' => $destination, 'packages' => $packages,
            'others' => Destination::active()->where('id', '!=', $destination->id)->withCount(['packages' => fn ($q) => $q->published()])->get(),
            'seo' => Seo::make($destination->seo_title ?: $destination->name.' Tours and Vacations', $destination->meta_description ?: $destination->summary, [
                'image' => $destination->image,
                'breadcrumbs' => [['name' => 'Home', 'url' => url('/')], ['name' => 'Destinations', 'url' => route('destinations.index')], ['name' => $destination->name, 'url' => route('destinations.show', $destination->slug)]],
            ]),
        ]);
    }

    /** Data the shared home sections (why, how, reviews, FAQ) need. */
    private function shared(): array
    {
        return ['page' => Page::byKey('home'), 'testimonials' => Testimonial::active()->get(), 'faqs' => Faq::home()->get()];
    }
}
