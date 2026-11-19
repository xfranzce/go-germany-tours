@extends('admin.layout')
@section('title', 'Packages')
@section('heading', 'Packages')
@section('actions')<a class="btn btn-primary btn-sm" href="{{ route('admin.packages.create') }}">Add package</a>@endsection

@section('content')
@php
    $bulk = fn (string $action) => route('admin.packages.bulk', $action);
    $btn = 'btn btn-outline btn-sm disabled:opacity-40 disabled:cursor-not-allowed';
@endphp
@php $keep = array_filter(['type' => $type?->slug]); @endphp
<nav class="mb-4 flex gap-2" aria-label="Package views">
    <a href="{{ route('admin.packages.index', $keep) }}" class="btn btn-sm {{ $view === 'all' ? 'btn-primary' : 'btn-outline' }}">All packages</a>
    <a href="{{ route('admin.packages.index', ['view' => 'trash'] + $keep) }}" class="btn btn-sm {{ $view === 'trash' ? 'btn-primary' : 'btn-outline' }}">Trash ({{ $trashCount }})</a>
</nav>
{{-- Filter by tour type: click one to see only its packages --}}
@php $viewKeep = $view === 'trash' ? ['view' => 'trash'] : []; @endphp
<nav class="mb-4 flex flex-wrap items-center gap-2" aria-label="Filter by tour type">
    <span class="text-sm font-bold">Tour type:</span>
    <a href="{{ route('admin.packages.index', $viewKeep) }}" class="btn btn-sm {{ ! $type ? 'btn-primary' : 'btn-outline' }}" @if(! $type) aria-current="true" @endif>All ({{ $typeCounts->sum() }})</a>
    @foreach($types as $t)
        <a href="{{ route('admin.packages.index', ['type' => $t->slug] + $viewKeep) }}" class="btn btn-sm {{ $type?->id === $t->id ? 'btn-primary' : 'btn-outline' }}" @if($type?->id === $t->id) aria-current="true" @endif>{{ $t->name }} ({{ $typeCounts[$t->id] ?? 0 }})</a>
    @endforeach
</nav>

<form method="POST" action="{{ route('admin.packages.reorder') }}" class="bg-white rounded-xl shadow overflow-x-auto"
      x-data="{ ids: [], all: @js($packages->pluck('id')->all()), toggleAll(on) { this.ids = on ? [...this.all] : [] } }">
    @csrf @method('PUT')

    @if($packages->isNotEmpty())
    <div class="flex flex-wrap items-center gap-2 border-b bg-gray-50 p-3" role="toolbar" aria-label="Bulk actions">
        <span class="mr-1 text-sm font-bold" aria-live="polite" x-text="ids.length ? ids.length + ' selected' : 'Tick packages to use bulk actions'"></span>
        @if($view === 'trash')
            <button type="submit" formaction="{{ $bulk('restore') }}" name="_method" value="POST" class="{{ $btn }}" :disabled="!ids.length">Restore</button>
        @else
            <button type="submit" formaction="{{ $bulk('publish') }}" name="_method" value="POST" class="{{ $btn }}" :disabled="!ids.length">Publish</button>
            <button type="submit" formaction="{{ $bulk('unpublish') }}" name="_method" value="POST" class="{{ $btn }}" :disabled="!ids.length">Unpublish</button>
            <button type="submit" formaction="{{ $bulk('feature') }}" name="_method" value="POST" class="{{ $btn }}" :disabled="!ids.length">Feature</button>
            <button type="submit" formaction="{{ $bulk('unfeature') }}" name="_method" value="POST" class="{{ $btn }}" :disabled="!ids.length">Unfeature</button>
            <button type="submit" formaction="{{ $bulk('sample') }}" name="_method" value="POST" class="{{ $btn }}" :disabled="!ids.length">Mark sample</button>
            <button type="submit" formaction="{{ $bulk('unsample') }}" name="_method" value="POST" class="{{ $btn }}" :disabled="!ids.length">Not sample</button>
            <button type="submit" formaction="{{ $bulk('trash') }}" name="_method" value="POST" :disabled="!ids.length"
                    @click="if (!confirm('Move ' + ids.length + ' package(s) to the trash? You can restore them from the Trash tab.')) $event.preventDefault()"
                    class="btn btn-outline btn-sm !border-brand-red !text-brand-red hover:!bg-brand-red hover:!text-white disabled:opacity-40 disabled:cursor-not-allowed">Trash</button>
        @endif
    </div>
    @endif

    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-left"><tr>
            <th class="p-3 w-10"><label class="sr-only" for="pk-all">Select all packages</label><input id="pk-all" type="checkbox" class="size-4 accent-[var(--color-brand-navy)]" :checked="ids.length && ids.length === all.length" @change="toggleAll($event.target.checked)"></th>
            @if($view !== 'trash')<th class="p-3 w-20">Order</th>@endif
            <th class="p-3">Package</th><th class="p-3">Type / destination</th><th class="p-3">Price</th><th class="p-3">Status</th>
            @if($view !== 'trash')<th class="p-3 text-right">Actions</th>@endif
        </tr></thead>
        <tbody>
        @forelse($packages as $p)
            <tr class="border-t align-top" :class="ids.includes({{ $p->id }}) && 'bg-brand-custard-light'">
                <td class="p-3"><label class="sr-only" for="pk{{ $p->id }}">Select {{ $p->title }}</label><input id="pk{{ $p->id }}" type="checkbox" name="ids[]" value="{{ $p->id }}" x-model.number="ids" class="size-4 accent-[var(--color-brand-navy)]"></td>
                @if($view !== 'trash')
                <td class="p-3"><label class="sr-only" for="o{{ $p->id }}">Order for {{ $p->title }}</label><input id="o{{ $p->id }}" type="number" min="0" name="sort[{{ $p->id }}]" value="{{ $p->sort }}" class="field-input !min-h-9 !py-1 w-16"></td>
                @endif
                <td class="p-3">
                    @if($view === 'trash')<span class="font-bold">{{ $p->title }}</span>@else<a class="font-bold underline" href="{{ route('admin.packages.edit', $p) }}">{{ $p->title }}</a>@endif
                    @if($p->is_sample) <span class="bg-brand-gold rounded px-1.5 text-xs font-bold">SAMPLE</span>@endif<br><span class="text-ink-soft">{{ $p->nights }} nights</span></td>
                <td class="p-3">{{ $p->tourType->name }}<br><span class="text-ink-soft">{{ $p->destination->name }}</span></td>
                <td class="p-3">{{ $p->price_from ? number_format($p->price_from).' '.$p->currency : '-' }}</td>
                <td class="p-3">@if($view === 'trash')<span class="rounded bg-gray-200 px-1.5 text-xs font-bold">In trash since {{ $p->deleted_at->format('j M Y') }}</span>@else{{ $p->is_published ? 'Published' : 'Draft' }}{{ $p->is_featured ? ', featured' : '' }}@endif</td>
                @if($view !== 'trash')
                <td class="p-3">
                    <div class="flex flex-wrap gap-1 justify-end">
                        <button type="submit" formaction="{{ route('admin.packages.toggle', [$p, 'is_published']) }}" formmethod="POST" class="btn btn-outline btn-sm" name="_method" value="POST">{{ $p->is_published ? 'Unpublish' : 'Publish' }}</button>
                        <button type="submit" formaction="{{ route('admin.packages.toggle', [$p, 'is_featured']) }}" formmethod="POST" class="btn btn-outline btn-sm" name="_method" value="POST">{{ $p->is_featured ? 'Unfeature' : 'Feature' }}</button>
                        <button type="submit" formaction="{{ route('admin.packages.duplicate', $p) }}" formmethod="POST" class="btn btn-outline btn-sm" name="_method" value="POST">Duplicate</button>
                        <a class="btn btn-primary btn-sm" href="{{ route('admin.packages.edit', $p) }}">Edit</a>
                    </div>
                </td>
                @endif
            </tr>
        @empty
            <tr><td colspan="7" class="p-6 text-ink-soft">{{ $view === 'trash' ? 'The trash is empty.' : 'No packages yet.' }}</td></tr>
        @endforelse
        </tbody>
    </table>
    @if($packages->isNotEmpty() && $view !== 'trash')<div class="p-3 border-t"><button class="btn btn-primary btn-sm">Save order</button> <span class="text-xs text-ink-soft">Lower numbers show first.</span></div>@endif
</form>
@endsection
