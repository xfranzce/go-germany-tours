<?php

namespace App\Http\Controllers;

use App\Models\Package;
use App\Models\Page;
use App\Models\Post;
use App\Models\Testimonial;
use App\Models\TourType;
use App\Support\Seo;

/** Unlisted snapshot of the home page as of 2026-11-24 (Bavarian video hero, bright sections) ("Home old 2"), kept so the home page can be restored. Not in menus, sitemap or search. */
class HomeOld2Controller extends Controller
{
    public function __invoke()
    {
        $page = Page::byKey('home');

        return view('home-old-2', [
            'page' => $page,
            'posts' => Post::published()->take(6)->get(),
            'testimonials' => Testimonial::active()->get(),
            'tourTypes' => TourType::active()->get(),
            'packages' => Package::published()->orderByDesc('is_featured')->orderBy('sort')->with(['tourType', 'destination', 'images'])->get(),
            'seo' => Seo::make($page->seo_title, $page->meta_description, [
                'noindex' => true, 'canonical' => route('home-old-2'),
            ]),
        ]);
    }
}
