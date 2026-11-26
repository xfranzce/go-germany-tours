@extends('admin.layout')
@php $isNew = ! $item->exists; @endphp
@section('title', ($isNew ? 'New ' : 'Edit ').strtolower($label))
@section('heading', $isNew ? 'New '.strtolower($label) : 'Edit: '.$item->name)
@section('actions')<a class="btn btn-outline btn-sm" href="{{ route($routeBase.'.index') }}">&larr; Back</a>@endsection

@section('content')
<form method="POST" enctype="multipart/form-data" class="space-y-6" action="{{ $isNew ? route($routeBase.'.store') : route($routeBase.'.update', $item) }}">
    @csrf @if(! $isNew) @method('PUT') @endif
    <section class="bg-white rounded-xl shadow p-5 grid gap-4 sm:grid-cols-2">
        <x-admin.field name="name" label="Name" :value="$item->name" required/>
        <x-admin.field name="slug" label="URL slug" :value="$item->slug" help="Blank builds it from the name. Changing it changes the page address."/>
        @if(in_array('icon', $extra))<x-admin.field name="icon" label="Icon" type="select" :options="['car' => 'Car', 'steering' => 'Compass / guided', 'bus' => 'Coach']" :value="$item->icon"/>@endif
        <x-admin.field name="sort" label="Order" type="number" :value="$item->sort"/>
        @if(in_array('tagline', $extra))<x-admin.field name="tagline" label="Tagline (small line above the name)" :value="$item->tagline" maxlength="120" help="For example: Breathtaking views. Shown in the menu and on the overview page."/>@endif
        <x-admin.field name="summary" label="Short summary" type="textarea" :rows="2" :value="$item->summary" maxlength="500" class="sm:col-span-2"/>
        @if(in_array('inclusions', $extra))<x-admin.field name="inclusions" label="Inclusions (one per line, shown on the home page card)" type="textarea" :rows="4" :value="$item->inclusions" maxlength="1000" class="sm:col-span-2" help="Short ticks such as Premium Rental Car or 3-4 Star Hotels."/>@endif
        @if(in_array('highlights', $extra))<x-admin.field name="highlights" label="What to expect (one point per line)" type="textarea" :rows="6" :value="$item->highlights" maxlength="2000" class="sm:col-span-2" help="Shown as a checklist under the About text on the tour type page."/>@endif
        @if(in_array('places', $extra))<x-admin.field name="places" label="Highlight places (one per line, Name: short note)" type="textarea" :rows="6" :value="$item->places" maxlength="3000" class="sm:col-span-2" help="For example: Wernigerode: castle and colorful old town. Shown on the Destinations page and this destination's page."/>@endif
        <div class="sm:col-span-2"><x-admin.rich name="description" label="Page text" :value="$item->description"/></div>
        <x-admin.image :current="$item->image" label="Picture" class="sm:col-span-2"/>
        <x-admin.field name="seo_title" label="SEO title (max 70)" :value="$item->seo_title" maxlength="70" help="Do not add the site name: it is added automatically."/>
        <x-admin.field name="meta_description" label="Meta description (max 320)" :value="$item->meta_description" maxlength="320"/>
        <x-admin.field name="is_active" label="Active (shown on the site)" type="checkbox" :value="$item->is_active"/>
    </section>
    @if($picker)
        {{-- Package tick list (destinations: featured packages; travel styles: member packages). package_picker=1 tells the controller to sync. --}}
        <section class="bg-white rounded-xl shadow p-5" aria-labelledby="pkg-picker"
                 x-data="{ q: '', all(on) { $el.querySelectorAll('label:not([hidden]) input[type=checkbox]:not(:disabled)').forEach(c => c.checked = on) } }">
            <input type="hidden" name="package_picker" value="1">
            <div class="flex flex-wrap items-end justify-between gap-3">
                <div>
                    <h2 id="pkg-picker" class="text-xl">Packages on this {{ strtolower($label) }}</h2>
                    <p class="text-sm text-ink-soft">
                        @if(! empty($picker['locked']) || ! $item->exists && $routeBase === 'admin.destinations')Packages whose main destination is {{ $item->name ?: 'this one' }} are always included (greyed out). Tick any other package to feature it here too.
                        @else Tick every package that belongs to this {{ strtolower($label) }}. Untick to remove it; the package itself is not changed.@endif
                    </p>
                </div>
                <div class="flex items-center gap-3 text-sm">
                    <input type="search" x-model="q" placeholder="Search packages" class="field-input !min-h-9 !py-1 w-56" aria-label="Search packages">
                    <button type="button" class="underline" @click="all(true)">Tick all shown</button>
                    <button type="button" class="underline" @click="all(false)">Untick all shown</button>
                </div>
            </div>
            @if($picker['packages']->isEmpty())<p class="mt-4 text-sm text-ink-soft">No packages yet.</p>@endif
            <div class="mt-4 grid gap-x-6 gap-y-1 md:grid-cols-2">
                @php $ticked = array_map('intval', (array) old('package_ids', array_merge($picker['ticked'], $picker['locked']))); @endphp
                @foreach($picker['packages'] as $pk)
                    @php $locked = in_array($pk->id, $picker['locked']); @endphp
                    <label class="flex items-start gap-2 rounded-lg px-2 py-1.5 text-sm hover:bg-gray-50" data-search="{{ strtolower($pk->title.' '.$pk->destination?->name.' '.$pk->tourType?->name) }}"
                           x-bind:hidden="q !== '' && ! $el.dataset.search.includes(q.toLowerCase())">
                        <input type="checkbox" name="package_ids[]" value="{{ $pk->id }}" class="mt-0.5" @checked($locked || in_array($pk->id, $ticked)) @disabled($locked)>
                        <span><span class="font-semibold">{{ $pk->title }}</span>
                            <span class="block text-xs text-ink-soft">{{ $pk->tourType?->name }} &middot; {{ $pk->destination?->name }}@if($locked) (main destination)@endif @unless($pk->is_published)&middot; <strong>draft</strong>@endunless</span></span>
                    </label>
                @endforeach
            </div>
        </section>
    @endif
    <button class="btn btn-primary">Save</button>
</form>
@unless($isNew)
<form method="POST" action="{{ route($routeBase.'.destroy', $item) }}" class="mt-6" x-data="{ sure: false }" @submit="if (!sure) { $event.preventDefault(); sure = true }">
    @csrf @method('DELETE')
    <button class="btn btn-outline btn-sm" :class="sure && '!bg-brand-red !text-white !border-brand-red'" x-text="sure ? 'Click again to delete' : 'Delete'">Delete</button>
</form>
@endunless
@endsection
