<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Support\Seo;

/** About, Contact, Terms, Privacy: all editable in Admin > Pages. */
class PageController extends Controller
{
    private function render(string $key, string $view, string $routeName)
    {
        $page = Page::byKey($key);
        $content = str_replace('{company}', e(\App\Models\Setting::get('company_name')), (string) $page->content);

        return view($view, [
            'page' => $page, 'content' => $content,
            'seo' => Seo::make($page->seo_title ?: $page->title, $page->meta_description, [
                'image' => $page->og_image, 'noindex' => $page->noindex,
                'breadcrumbs' => [['name' => 'Home', 'url' => url('/')], ['name' => $page->title, 'url' => route($routeName)]],
            ]),
        ]);
    }

    public function about() { return $this->render('about', 'pages.about', 'about'); }

    public function contact() { return $this->render('contact', 'pages.contact', 'contact'); }

    public function terms() { return $this->render('terms', 'pages.legal', 'terms'); }

    public function privacy() { return $this->render('privacy', 'pages.legal', 'privacy'); }

    public function credits()
    {
        $credits = json_decode((string) @file_get_contents(base_path('database/seed-images/credits.json')), true) ?: [];

        return view('pages.credits', [
            'credits' => $credits,
            'seo' => Seo::make('Photo Credits', 'Photographers and licences for images used on this website.', ['canonical' => route('credits')]),
        ]);
    }
}
