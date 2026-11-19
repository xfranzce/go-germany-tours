<?php

namespace App\Http\Controllers;

use App\Models\Package;
use App\Models\Page;
use App\Models\TourType;
use App\Support\Seo;

/** Unlisted design test page (layout modelled on goscottishtours.com, our palette). Not in menus, sitemap or search. */
class TestHomeController extends Controller
{
    public function __invoke()
    {
        $page = Page::byKey('home');

        return view('test-home', [
            'page' => $page,
            'tourTypes' => TourType::active()->get(),
            'packages' => Package::published()->orderByDesc('is_featured')->orderBy('sort')->with(['tourType', 'destination', 'images'])->get(),
            'seo' => Seo::make('Design test page', 'Design test page.', ['noindex' => true, 'canonical' => route('test-home')]),
        ]);
    }
}
