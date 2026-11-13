<?php

namespace App\Http\Controllers\Admin;

use App\Models\Post;
use Illuminate\Http\Request;

class PostController extends AdminController
{
    public function index()
    {
        return view('admin.posts.index', ['items' => Post::latest()->get()]);
    }

    public function create()
    {
        return view('admin.posts.form', ['item' => new Post(['is_published' => false])]);
    }

    public function store(Request $request)
    {
        $post = new Post();
        $this->save($request, $post);

        return redirect()->route('admin.posts.index')->with('status', 'Guide created.');
    }

    public function edit(Post $post)
    {
        return view('admin.posts.form', ['item' => $post]);
    }

    public function update(Request $request, Post $post)
    {
        $this->save($request, $post);

        return redirect()->route('admin.posts.index')->with('status', 'Guide saved.');
    }

    public function destroy(Post $post)
    {
        $post->delete();

        return back()->with('status', 'Guide deleted.');
    }

    private function save(Request $request, Post $post): void
    {
        $d = $request->validate([
            'title' => ['required', 'string', 'max:150'], 'slug' => ['nullable', 'string', 'max:150'],
            'excerpt' => ['nullable', 'string', 'max:500'], 'body' => ['nullable', 'string', 'max:100000'],
            'published_at' => ['nullable', 'date'],
            'seo_title' => ['nullable', 'string', 'max:70'], 'meta_description' => ['nullable', 'string', 'max:320'],
        ] + $this->imageRules());

        $published = $request->boolean('is_published');
        $post->fill([
            'title' => $d['title'], 'slug' => $this->slug(Post::class, $d['slug'] ?? null, $d['title'], $post->id),
            'excerpt' => $d['excerpt'] ?? null, 'body' => $this->clean($d['body'] ?? null),
            'is_published' => $published, 'noindex' => $request->boolean('noindex'),
            'published_at' => $published ? ($d['published_at'] ?? $post->published_at ?? now()) : ($d['published_at'] ?? null),
            'seo_title' => $d['seo_title'] ?? null, 'meta_description' => $d['meta_description'] ?? null,
            'image' => $this->handleImage($request, 'image', $post->image),
        ])->save();
    }
}
