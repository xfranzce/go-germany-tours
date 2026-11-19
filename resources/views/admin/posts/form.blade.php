@extends('admin.layout')
@php $isNew = ! $item->exists; @endphp
@section('title', $isNew ? 'New guide' : 'Edit guide')
@section('heading', $isNew ? 'New guide' : 'Edit: '.$item->title)
@section('actions')<a class="btn btn-outline btn-sm" href="{{ route('admin.posts.index') }}">&larr; Back</a>@endsection

@section('content')
<form method="POST" enctype="multipart/form-data" class="space-y-6" action="{{ $isNew ? route('admin.posts.store') : route('admin.posts.update', $item) }}">
    @csrf @if(! $isNew) @method('PUT') @endif
    <section class="bg-white rounded-xl shadow p-5 grid gap-4 sm:grid-cols-2">
        <x-admin.field name="title" label="Title" :value="$item->title" required class="sm:col-span-2"/>
        <x-admin.field name="slug" label="URL slug" :value="$item->slug" help="Blank builds it from the title."/>
        <x-admin.field name="published_at" label="Publish date" type="date" :value="$item->published_at?->format('Y-m-d')"/>
        <x-admin.field name="category" label="Category (tag on the blog card)" type="select" :options="['' => 'None'] + array_combine(\App\Models\Post::CATEGORIES, \App\Models\Post::CATEGORIES)" :value="$item->category"/>
        <x-admin.field name="excerpt" label="Short intro" type="textarea" :rows="2" :value="$item->excerpt" maxlength="500" class="sm:col-span-2"/>
        <div class="sm:col-span-2"><x-admin.rich name="body" label="Article" :value="$item->body"/></div>
        <x-admin.image :current="$item->image" label="Featured image (shown on blog cards, the home page and the top of the post; also used when the post is shared)" class="sm:col-span-2"/>
        <x-admin.field name="is_published" label="Published" type="checkbox" :value="$item->is_published"/>
        <x-admin.field name="noindex" label="Hide from search engines" type="checkbox" :value="$item->noindex"/>
        <x-admin.field name="seo_title" label="SEO title (max 70)" :value="$item->seo_title" maxlength="70" help="Do not add the site name: it is added automatically."/>
        <x-admin.field name="meta_description" label="Meta description (max 320)" :value="$item->meta_description" maxlength="320"/>
    </section>
    <button class="btn btn-primary">Save guide</button>
</form>
@unless($isNew)
<form method="POST" action="{{ route('admin.posts.destroy', $item) }}" class="mt-6" x-data="{ sure: false }" @submit="if (!sure) { $event.preventDefault(); sure = true }">
    @csrf @method('DELETE')<button class="btn btn-outline btn-sm" x-text="sure ? 'Click again to delete' : 'Delete'">Delete</button>
</form>
@endunless
@endsection
