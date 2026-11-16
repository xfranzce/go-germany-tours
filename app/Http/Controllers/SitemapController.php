<?php

namespace App\Http\Controllers;

use App\Models\Destination;
use App\Models\Package;
use App\Models\Page;
use App\Models\Post;
use App\Models\TourType;

class SitemapController extends Controller
{
    public function sitemap()
    {
        $urls = [['loc' => url('/'), 'mod' => Page::where('key', 'home')->value('updated_at'), 'pri' => '1.0']];

        foreach (['about' => 'about', 'team' => 'team', 'contact' => 'contact', 'terms' => 'terms', 'privacy' => 'privacy'] as $key => $route) {
            $page = Page::where('key', $key)->first();
            if ($page && ! $page->noindex) {
                $urls[] = ['loc' => route($route), 'mod' => $page->updated_at, 'pri' => in_array($key, ['terms', 'privacy']) ? '0.3' : '0.6'];
            }
        }
        $urls[] = ['loc' => route('packages.index'), 'mod' => null, 'pri' => '0.9'];
        $urls[] = ['loc' => route('testimonials'), 'mod' => null, 'pri' => '0.5'];
        $urls[] = ['loc' => route('faqs'), 'mod' => null, 'pri' => '0.6'];
        $urls[] = ['loc' => route('blog.index'), 'mod' => null, 'pri' => '0.5'];

        foreach (TourType::active()->get() as $t) {
            $urls[] = ['loc' => route('tours.show', $t->slug), 'mod' => $t->updated_at, 'pri' => '0.8'];
        }
        foreach (Destination::active()->get() as $d) {
            $urls[] = ['loc' => route('destinations.show', $d->slug), 'mod' => $d->updated_at, 'pri' => '0.8'];
        }
        foreach (Package::published()->where('noindex', false)->get() as $p) {
            $urls[] = ['loc' => $p->url(), 'mod' => $p->updated_at, 'pri' => '0.7'];
        }
        foreach (Post::published()->where('noindex', false)->get() as $p) {
            $urls[] = ['loc' => route('blog.show', $p->slug), 'mod' => $p->updated_at, 'pri' => '0.5'];
        }

        return response()->view('sitemap', ['urls' => $urls])->header('Content-Type', 'application/xml; charset=utf-8');
    }

    public function robots()
    {
        $body = "User-agent: *\nDisallow: /admin\nDisallow: /thank-you\nDisallow: /enquiry\n\nSitemap: ".url('/sitemap.xml')."\n";

        return response($body, 200, ['Content-Type' => 'text/plain; charset=utf-8']);
    }
}
