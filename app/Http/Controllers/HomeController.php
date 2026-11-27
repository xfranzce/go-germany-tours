<?php

namespace App\Http\Controllers;

use App\Models\Package;
use App\Models\Page;
use App\Models\Post;
use App\Models\Testimonial;
use App\Models\TourType;
use App\Support\Seo;

class HomeController extends Controller
{
    public function __invoke()
    {
        $page = Page::byKey('home');

        return view('home', [
            'page' => $page,
            'posts' => Post::published()->take(6)->get(),
            'testimonials' => Testimonial::forPage(),
            'tourTypes' => TourType::active()->get(),
            'packages' => Package::published()->orderByDesc('is_featured')->orderBy('sort')->with(['tourType', 'destination', 'images'])->get(),
            'seo' => Seo::make($page->seo_title, $page->meta_description, [
                'image' => $page->og_image, 'noindex' => $page->noindex,
            ]),
        ]);
    }
}
