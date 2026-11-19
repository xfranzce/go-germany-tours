@extends('admin.layout')
@section('title', 'Edit '.$page->title)
@section('heading', 'Edit page: '.$page->title)
@section('actions')<a class="btn btn-outline btn-sm" href="{{ route('admin.pages.index') }}">&larr; All pages</a>@endsection

@section('content')
<form method="POST" enctype="multipart/form-data" action="{{ route('admin.pages.update', $page) }}" class="space-y-6">
    @csrf @method('PUT')
    <section class="bg-white rounded-xl shadow p-5 space-y-4">
        <x-admin.field name="title" label="Page title" :value="$page->title" required/>
        @if($page->key === 'home')
            <p class="text-sm text-ink-soft">Leave a box empty to use the default wording shown inside it.</p>
            <div class="grid gap-4 sm:grid-cols-2">
                @foreach($homeText as $key => [$label, $default])
                    <x-admin.field :name="'text['.$key.']'" :label="$label" :value="$page->data[$key] ?? ''" :placeholder="$default" :type="in_array($key, ['hero_sub', 'why_intro', 'featured_intro', 'tours_intro', 'cta_text']) ? 'textarea' : 'text'" :rows="2" maxlength="500"/>
                @endforeach
            </div>
            <x-admin.image name="hero" label="Hero background image" :current="$page->data['hero_image'] ?? null"/>
        @else
            <x-admin.rich name="content" label="Page text" :value="$page->content" help="{{ in_array($page->key, ['terms', 'privacy']) ? 'Type {company} wherever the legal company name should appear.' : '' }}"/>
        @endif
    </section>
    <section class="bg-white rounded-xl shadow p-5 grid gap-4 sm:grid-cols-2">
        <h2 class="text-xl sm:col-span-2">SEO</h2>
        <x-admin.field name="seo_title" label="SEO title (max 70)" :value="$page->seo_title" maxlength="70" help="Do not add the site name: it is added automatically."/>
        <x-admin.field name="meta_description" label="Meta description (max 320)" type="textarea" :rows="3" :value="$page->meta_description" maxlength="320"/>
        <x-admin.field name="noindex" label="Hide from search engines (noindex)" type="checkbox" :value="$page->noindex"/>
    </section>
    <button class="btn btn-primary">Save page</button>
</form>
@endsection
