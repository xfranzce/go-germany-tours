<?php

namespace App\Http\Controllers;

use App\Models\Destination;
use App\Models\Faq;
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
        $faqs = Faq::home()->get();

        return view('home', [
            'page' => $page,
            'tourTypes' => TourType::active()->get(),
            'destinations' => Destination::active()->get(),
            'featured' => Package::published()->orderByDesc('is_featured')->orderBy('sort')->with(['tourType', 'destination', 'images'])->take(24)->get(),
            'testimonials' => Testimonial::active()->get(),
            'posts' => Post::published()->take(6)->get(),
            'faqs' => $faqs,
            'seo' => Seo::make($page->seo_title, $page->meta_description, [
                'image' => $page->og_image, 'noindex' => $page->noindex, 'schema' => [Seo::faqSchema($faqs)],
            ]),
        ]);
    }
}
