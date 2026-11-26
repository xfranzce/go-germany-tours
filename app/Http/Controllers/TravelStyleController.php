<?php

namespace App\Http\Controllers;

use App\Models\Faq;
use App\Models\Page;
use App\Models\Testimonial;
use App\Models\TravelStyle;
use App\Support\Seo;

/** /travel-styles overview and one page per style (Slow, Scenic, Luxury...) listing its packages. */
class TravelStyleController extends Controller
{
    public function index()
    {
        $styles = TravelStyle::active()->withCount(['packages' => fn ($q) => $q->published()])->get();

        return view('travel-styles.index', $this->shared() + [
            'styles' => $styles,
            'seo' => Seo::make('Germany Travel Styles: Find the Trip That Suits You',
                'Slow, scenic, luxury, food and wine, cultural or active: choose how you like to travel and see Germany tours planned around it.', [
                    'image' => $styles->first()?->image,
                    'breadcrumbs' => [['name' => 'Home', 'url' => url('/')], ['name' => 'Travel styles', 'url' => route('travel-styles.index')]],
                ]),
        ]);
    }

    public function show(string $slug)
    {
        $style = TravelStyle::where('slug', $slug)->where('is_active', true)->firstOrFail();
        $packages = $style->packages()->published()->with(['tourType', 'destination', 'images'])->orderBy('sort')->get();

        return view('travel-styles.show', $this->shared() + [
            'style' => $style, 'packages' => $packages,
            'others' => TravelStyle::active()->where('id', '!=', $style->id)->withCount(['packages' => fn ($q) => $q->published()])->get(),
            'seo' => Seo::make($style->seo_title ?: "{$style->name} Tours of Germany", $style->meta_description ?: $style->summary, [
                'image' => $style->image,
                'breadcrumbs' => [['name' => 'Home', 'url' => url('/')], ['name' => 'Travel styles', 'url' => route('travel-styles.index')], ['name' => $style->name, 'url' => route('travel-styles.show', $style->slug)]],
            ]),
        ]);
    }

    private function shared(): array
    {
        return ['page' => Page::byKey('home'), 'testimonials' => Testimonial::active()->get(), 'faqs' => Faq::home()->get()];
    }
}
