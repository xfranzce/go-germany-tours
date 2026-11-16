<?php

namespace App\Http\Controllers\Admin;

use App\Models\Page;
use Illuminate\Http\Request;

class PageController extends AdminController
{
    /** Editable home page text (key => [label, default shown when blank]). */
    public const HOME_TEXT = [
        'hero_h1' => ['Hero headline (H1). Put *stars* around the words to highlight, e.g. Tours Through *Fairytale Towns*', 'Tailor-Made Tours of *Germany\'s Fairytale Towns*'],
        'hero_sub' => ['Hero sub-heading', 'Self drive, chauffeur driven or small-group coach. Explore Quedlinburg, Saxon Switzerland, Cochem, Hiddensee and Gengenbach, planned around you.'],
        'why_title' => ['"Why choose us" title', 'Why travelers choose us'],
        'why_intro' => ['"Why choose us" intro', 'Germany is easier to enjoy when the planning is done for you.'],
        'tours_title' => ['Tour types title', 'Pick the way you like to travel'],
        'tours_intro' => ['Tour types intro', 'The same beautiful places, three very different experiences.'],
        'featured_title' => ['Featured packages title', 'Popular ways to see Germany'],
        'featured_intro' => ['Featured packages intro', 'A few ideas to start from. Every trip can be adjusted to suit you.'],
        'how_title' => ['"How it works" title', 'Your trip in three simple steps'],
        'testimonials_title' => ['Testimonials title', 'What travelers say'],
        'faq_title' => ['FAQ title', 'Frequently asked questions'],
        'cta_title' => ['Final call-to-action title', 'Ready to see Germany your way?'],
        'cta_text' => ['Final call-to-action text', 'Get a free, no-obligation quote in under a minute.'],
    ];

    public function index()
    {
        return view('admin.pages.index', ['pages' => Page::whereNot('key', 'thank-you')->orderBy('id')->get()]);
    }

    public function edit(Page $page)
    {
        return view('admin.pages.edit', ['page' => $page, 'homeText' => self::HOME_TEXT]);
    }

    public function update(Request $request, Page $page)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'content' => ['nullable', 'string', 'max:100000'],
            'seo_title' => ['nullable', 'string', 'max:70'],
            'meta_description' => ['nullable', 'string', 'max:320'],
            'text' => ['nullable', 'array'],
            'text.*' => ['nullable', 'string', 'max:500'],
        ] + $this->imageRules('hero'));

        $pageData = $page->data ?? [];
        if ($page->key === 'home') {
            foreach (array_keys(self::HOME_TEXT) as $k) {
                $pageData[$k] = $data['text'][$k] ?? '';
            }
            $pageData['hero_image'] = $this->handleImage($request, 'hero', $pageData['hero_image'] ?? null);
        }

        $page->update([
            'title' => $data['title'],
            'content' => $page->key === 'home' ? null : $this->clean($data['content'] ?? null),
            'data' => $pageData,
            'seo_title' => $data['seo_title'] ?? null,
            'meta_description' => $data['meta_description'] ?? null,
            'noindex' => $request->boolean('noindex'),
        ]);

        return redirect()->route('admin.pages.edit', $page)->with('status', 'Page saved.');
    }
}
