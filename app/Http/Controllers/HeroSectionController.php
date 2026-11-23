<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Support\Seo;

/** Unlisted page holding a copy of the home hero, for trying out changes. Not in menus, sitemap or search. */
class HeroSectionController extends Controller
{
    public function __invoke()
    {
        return view('hero-section', [
            'page' => Page::byKey('home'),
            'seo' => Seo::make('Hero section page', 'Hero section page.', ['noindex' => true, 'canonical' => route('hero-section')]),
        ]);
    }
}
