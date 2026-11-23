<?php

namespace App\Http\Controllers;

use App\Models\Package;
use App\Models\Page;
use App\Models\Testimonial;
use App\Models\TourType;
use App\Support\Seo;

/** Unlisted snapshot of the home page ("Hero old"), kept so the home page can be restored. Not in menus, sitemap or search. */
class HeroOldController extends Controller
{
    public function __invoke()
    {
        $page = Page::byKey('home');

        return view('hero-old', [
            'page' => $page,
            'testimonials' => Testimonial::active()->get(),
            'tourTypes' => TourType::active()->get(),
            'packages' => Package::published()->orderByDesc('is_featured')->orderBy('sort')->with(['tourType', 'destination', 'images'])->get(),
            'seo' => Seo::make($page->seo_title, $page->meta_description, [
                'noindex' => true, 'canonical' => route('hero-old'),
            ]),
        ]);
    }
}
