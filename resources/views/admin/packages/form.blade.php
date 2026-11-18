@extends('admin.layout')
@php $isNew = ! $package->exists; @endphp
@section('title', $isNew ? 'New package' : 'Edit package')
@section('heading', $isNew ? 'New package' : 'Edit: '.$package->title)
@section('actions')
    <a class="btn btn-outline btn-sm" href="{{ route('admin.packages.index') }}">&larr; All packages</a>
    @unless($isNew)@if($package->is_published)<a class="btn btn-outline btn-sm" target="_blank" rel="noopener" href="{{ $package->url() }}">View on site</a>@endif @endunless
    <button type="submit" form="package-form" class="btn btn-primary btn-sm">Save package</button>
    @unless($isNew)<a class="btn btn-primary btn-sm" href="{{ route('admin.packages.create') }}">+ Add new</a>@endunless
    @unless($isNew)
        <form method="POST" action="{{ route('admin.packages.destroy', $package) }}" class="inline" x-data
              @submit="if (!confirm('Permanently delete this package? This cannot be undone.')) $event.preventDefault()">
            @csrf @method('DELETE')
            <button class="btn btn-outline btn-sm !border-brand-red !text-brand-red hover:!bg-brand-red hover:!text-white">Delete package</button>
        </form>
    @endunless
@endsection

@section('content')
@php
    $itinerary = old('itinerary', $package->itinerary ?? []);
    $faqs = old('faqs', $package->exists ? $package->faqs->map(fn ($f) => ['question' => $f->question, 'answer' => $f->answer])->all() : []);
@endphp
<form id="package-form" method="POST" enctype="multipart/form-data" action="{{ $isNew ? route('admin.packages.store') : route('admin.packages.update', $package) }}" class="space-y-6">
    @csrf @if(! $isNew) @method('PUT') @endif

    <section class="bg-white rounded-xl shadow p-5 grid gap-4 sm:grid-cols-2">
        <h2 class="text-xl sm:col-span-2">Basics</h2>
        <x-admin.field name="title" label="Title" :value="$package->title" required class="sm:col-span-2"/>
        <x-admin.field name="slug" label="URL slug" :value="$package->slug" help="Leave blank to build it from the title."/>
        <x-admin.field name="sort" label="Order" type="number" :value="$package->sort" help="Lower numbers show first."/>
        <x-admin.field name="tour_type_id" label="Tour type" type="select" :options="$types" :value="$package->tour_type_id" required/>
        <x-admin.field name="destination_id" label="Destination" type="select" :options="$destinations" :value="$package->destination_id" required/>
        <x-admin.field name="nights" label="Nights" type="number" :value="$package->nights" required/>
        <div class="grid grid-cols-[1fr_1fr_6rem] gap-3">
            <x-admin.field name="price_was" label="'From' price per person (optional, shown crossed out; must be higher than the current price)" type="number" step="0.01" :value="$package->price_was"/>
            <x-admin.field name="price_from" label="Current price per person" type="number" step="0.01" :value="$package->price_from"/>
            <x-admin.field name="currency" label="Currency" type="select" :options="['USD' => 'USD', 'EUR' => 'EUR', 'GBP' => 'GBP', 'CAD' => 'CAD', 'AUD' => 'AUD']" :value="$package->currency"/>
        </div>
        <div class="grid grid-cols-2 gap-3">
            <x-admin.field name="rating" label="Guest rating, 0 to 5 (REAL ratings only; blank = no stars)" type="number" step="0.1" min="0" max="5" :value="$package->rating"/>
            <x-admin.field name="review_count" label="Number of reviews (optional)" type="number" :value="$package->review_count"/>
        </div>
        <x-admin.field name="summary" label="Short summary (shown on cards, max 500 characters)" type="textarea" :rows="2" :value="$package->summary" class="sm:col-span-2" maxlength="500"/>
        <div class="sm:col-span-2 flex flex-wrap gap-x-8 gap-y-2">
            <x-admin.field name="is_published" label="Published (visible on the site)" type="checkbox" :value="$package->is_published"/>
            <x-admin.field name="is_featured" label="Featured on the home page" type="checkbox" :value="$package->is_featured"/>
            <x-admin.field name="is_sample" label="Sample / placeholder (shows a SAMPLE badge)" type="checkbox" :value="$package->is_sample"/>
        </div>
    </section>

    <section class="bg-white rounded-xl shadow p-5 space-y-4">
        <h2 class="text-xl">Content</h2>
        <x-admin.rich name="description" label="Full description" :value="$package->description"/>
        <x-admin.field name="highlights" label="Highlights (one per line)" type="textarea" :rows="5" :value="implode(PHP_EOL, $package->highlights ?? [])"/>
        <div class="grid gap-4 sm:grid-cols-2">
            <x-admin.field name="inclusions" label="Included (one per line)" type="textarea" :rows="5" :value="implode(PHP_EOL, $package->inclusions ?? [])"/>
            <x-admin.field name="exclusions" label="Not included (one per line)" type="textarea" :rows="5" :value="implode(PHP_EOL, $package->exclusions ?? [])"/>
        </div>
    </section>

    <section class="bg-white rounded-xl shadow p-5" x-data="repeater(@js(array_values($itinerary)))">
        <h2 class="text-xl mb-3">Day-by-day itinerary</h2>
        <template x-for="(d, i) in items" :key="i">
            <div class="border rounded-lg p-3 mb-3 grid gap-2">
                <div class="flex gap-2 items-end">
                    <div class="flex-1"><label class="field-label" :for="'it-t-' + i">Day title</label><input :id="'it-t-' + i" class="field-input" :name="'itinerary[' + i + '][title]'" x-model="d.title" maxlength="150"></div>
                    <button type="button" class="btn btn-outline btn-sm" @click="up(i)" aria-label="Move up">&uarr;</button>
                    <button type="button" class="btn btn-outline btn-sm" @click="down(i)" aria-label="Move down">&darr;</button>
                    <button type="button" class="btn btn-outline btn-sm" @click="remove(i)">Remove</button>
                </div>
                <div><label class="field-label" :for="'it-b-' + i">Description</label><textarea :id="'it-b-' + i" rows="3" class="field-input" :name="'itinerary[' + i + '][body]'" x-model="d.body" maxlength="3000"></textarea></div>
            </div>
        </template>
        <button type="button" class="btn btn-outline btn-sm" @click="add({title: '', body: ''})">+ Add a day</button>
    </section>

    <section class="bg-white rounded-xl shadow p-5" x-data="repeater(@js(array_values($faqs)))">
        <h2 class="text-xl mb-3">FAQs for this package</h2>
        <template x-for="(f, i) in items" :key="i">
            <div class="border rounded-lg p-3 mb-3 grid gap-2">
                <div class="flex gap-2 items-end">
                    <div class="flex-1"><label class="field-label" :for="'fq-' + i">Question</label><input :id="'fq-' + i" class="field-input" :name="'faqs[' + i + '][question]'" x-model="f.question" maxlength="250"></div>
                    <button type="button" class="btn btn-outline btn-sm" @click="remove(i)">Remove</button>
                </div>
                <div><label class="field-label" :for="'fa-' + i">Answer</label><textarea :id="'fa-' + i" rows="3" class="field-input" :name="'faqs[' + i + '][answer]'" x-model="f.answer" maxlength="3000"></textarea></div>
            </div>
        </template>
        <button type="button" class="btn btn-outline btn-sm" @click="add({question: '', answer: ''})">+ Add a FAQ</button>
    </section>

    <section class="bg-white rounded-xl shadow p-5">
        <h2 class="text-xl mb-3">Gallery images</h2>
        <p class="text-sm text-ink-soft mb-3">The first image (lowest order) is the cover. Alt text is required: describe what the picture shows.</p>
        @if($package->exists && $package->images->isNotEmpty())
            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3 mb-5">
                @foreach($package->images as $img)
                    @php $m = \App\Models\Media::forPath($img->path); @endphp
                    <div class="border rounded-lg p-3 space-y-2">
                        @if($m)<img src="{{ $m->url(min(480, max($m->widths))) }}" alt="{{ $img->alt }}" width="240" height="{{ max(1, (int) round(240 * $m->height / $m->width)) }}" class="rounded w-full">@endif
                        <label class="field-label" for="ia{{ $img->id }}">Alt text</label>
                        <input id="ia{{ $img->id }}" class="field-input" name="images[{{ $img->id }}][alt]" value="{{ old("images.{$img->id}.alt", $img->alt) }}" required maxlength="200">
                        <div class="flex items-center gap-3">
                            <label class="text-sm font-bold">Order <input type="number" class="field-input !w-20 !min-h-9 !py-1 inline-block" name="images[{{ $img->id }}][sort]" value="{{ $img->sort }}"></label>
                            <label class="text-sm font-bold inline-flex items-center gap-1"><input type="checkbox" name="images[{{ $img->id }}][delete]" value="1" class="accent-brand-red"> Remove</label>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
        <h3 class="font-bold mb-2">Add images</h3>
        @for($i = 0; $i < 3; $i++)
            <div class="grid gap-2 sm:grid-cols-2 mb-3">
                <div><label class="field-label" for="ni{{ $i }}">Image file</label><input id="ni{{ $i }}" type="file" name="new_images[{{ $i }}][file]" accept="image/jpeg,image/png,image/webp" class="block text-sm"></div>
                <div><label class="field-label" for="na{{ $i }}">Alt text</label><input id="na{{ $i }}" name="new_images[{{ $i }}][alt]" class="field-input" maxlength="200" value="{{ old("new_images.{$i}.alt") }}"></div>
            </div>
        @endfor
        <p class="text-xs text-ink-soft">JPG, PNG or WebP, up to 8 MB each, at least 400 x 300 pixels. Save to add more.</p>
        @error('new_images.*.file')<p class="field-error">{{ $message }}</p>@enderror
    </section>

    <section class="bg-white rounded-xl shadow p-5 grid gap-4 sm:grid-cols-2">
        <h2 class="text-xl sm:col-span-2">SEO and sharing</h2>
        <x-admin.field name="seo_title" label="SEO title (max 70)" :value="$package->seo_title" maxlength="70" help="Blank uses the package title."/>
        <x-admin.field name="og_image" label="Social share image" type="select" :options="['' => 'First gallery image'] + ($package->exists ? $package->images->pluck('alt', 'path')->all() : [])" :value="$package->og_image"/>
        <x-admin.field name="meta_description" label="Meta description (max 320)" type="textarea" :rows="3" :value="$package->meta_description" maxlength="320" class="sm:col-span-2" help="Blank uses the short summary."/>
        <x-admin.field name="noindex" label="Hide from search engines (noindex)" type="checkbox" :value="$package->noindex"/>
    </section>

    <div class="flex flex-wrap gap-3 items-center">
        <button class="btn btn-primary">Save package</button>
    </div>
</form>

@unless($isNew)
<form method="POST" action="{{ route('admin.packages.destroy', $package) }}" class="mt-6" x-data="{ sure: false }" @submit="if (!sure) { $event.preventDefault(); sure = true }">
    @csrf @method('DELETE')
    <button class="btn btn-outline btn-sm" :class="sure && '!bg-brand-red !text-white !border-brand-red'" x-text="sure ? 'Click again to permanently delete this package' : 'Delete package'">Delete package</button>
</form>
@endunless
@endsection
