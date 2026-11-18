<?php

namespace App\Http\Controllers;

use App\Models\Faq;
use App\Models\Page;
use App\Models\Testimonial;
use App\Support\Seo;

/** About, Contact, Terms, Privacy: all editable in Admin > Pages. */
class PageController extends Controller
{
    private function render(string $key, string $view, string $routeName, array $extra = [])
    {
        $page = Page::byKey($key);
        $content = str_replace('{company}', e(\App\Models\Setting::get('company_name')), (string) $page->content);

        return view($view, $extra + [
            'page' => $page, 'content' => $content,
            'seo' => Seo::make($page->seo_title ?: $page->title, $page->meta_description, [
                'image' => $page->og_image, 'noindex' => $page->noindex,
                'breadcrumbs' => [['name' => 'Home', 'url' => url('/')], ['name' => $page->title, 'url' => route($routeName)]],
            ]),
        ]);
    }

    public function about() { return $this->render('about', 'pages.about', 'about'); }

    /** Meet Our Team: rich-text page editable in Admin > Pages. */
    public function team() { return $this->render('team', 'pages.about', 'team', ['members' => \App\Models\TeamMember::active()->get()]); }

    public function testimonials()
    {
        return view('pages.testimonials', [
            'testimonials' => Testimonial::active()->get(),
            'seo' => Seo::make('Traveler Testimonials', 'What travelers say about planning their Germany vacation with us.', [
                'canonical' => route('testimonials'),
                'breadcrumbs' => [['name' => 'Home', 'url' => url('/')], ['name' => 'Testimonials', 'url' => route('testimonials')]],
            ]),
        ]);
    }

    public function faqs()
    {
        $faqs = Faq::home()->get();

        return view('pages.faqs', [
            'faqs' => $faqs,
            'seo' => Seo::make('Frequently Asked Questions', 'Answers about our self drive, chauffeur driven and coach tours of Germany, quotes and planning.', [
                'canonical' => route('faqs'), 'schema' => [Seo::faqSchema($faqs)],
                'breadcrumbs' => [['name' => 'Home', 'url' => url('/')], ['name' => 'FAQs', 'url' => route('faqs')]],
            ]),
        ]);
    }

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
