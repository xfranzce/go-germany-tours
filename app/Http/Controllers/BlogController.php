<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Support\Seo;

class BlogController extends Controller
{
    public function index()
    {
        return view('blog.index', [
            'posts' => Post::published()->paginate(9),
            'seo' => Seo::make('Germany Travel Guides', 'Travel guides and ideas for planning a Germany trip: fairytale towns, river valleys and island escapes.', [
                'canonical' => route('blog.index'),
                'breadcrumbs' => [['name' => 'Home', 'url' => url('/')], ['name' => 'Travel guides', 'url' => route('blog.index')]],
            ]),
        ]);
    }

    public function show(Post $post)
    {
        abort_unless($post->is_published && $post->published_at?->isPast(), 404);

        return view('blog.show', [
            'post' => $post,
            'seo' => Seo::make($post->seo_title ?: $post->title, $post->meta_description ?: $post->excerpt, [
                'image' => $post->image, 'noindex' => $post->noindex, 'type' => 'article',
                'breadcrumbs' => [['name' => 'Home', 'url' => url('/')], ['name' => 'Travel guides', 'url' => route('blog.index')], ['name' => $post->title, 'url' => route('blog.show', $post->slug)]],
                'schema' => [['@context' => 'https://schema.org', '@type' => 'Article', 'headline' => $post->title, 'datePublished' => $post->published_at?->toIso8601String(), 'dateModified' => $post->updated_at->toIso8601String()]],
            ]),
        ]);
    }
}
