@extends('admin.layout')
@section('title', 'Media')
@section('heading', 'Media library')

@section('content')
<form method="POST" enctype="multipart/form-data" action="{{ route('admin.media.store') }}" class="bg-white rounded-xl shadow p-5 grid gap-3 sm:grid-cols-[1fr_1fr_auto] items-end">
    @csrf
    <div><label class="field-label" for="file">Image file</label><input id="file" type="file" name="file" required accept="image/jpeg,image/png,image/webp" class="block text-sm"></div>
    <div><label class="field-label" for="alt">Alt text (required)</label><input id="alt" name="alt" class="field-input" maxlength="200" required></div>
    <button class="btn btn-primary">Upload</button>
    <p class="text-xs text-ink-soft sm:col-span-3">JPG, PNG or WebP, up to 8 MB, at least 400 x 300 pixels. Images are resized and converted to WebP automatically. Use the pickers on package, page and settings screens to put an image on the site.</p>
</form>

@error('delete')<p class="field-error mt-3">{{ $message }}</p>@enderror
<div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
    @forelse($items as $m)
        <div class="bg-white rounded-xl shadow p-3 space-y-2">
            <img src="{{ $m->url(min(480, max($m->widths))) }}" alt="{{ $m->alt }}" width="240" height="{{ max(1, (int) round(240 * $m->height / $m->width)) }}" class="rounded w-full" loading="lazy">
            <p class="text-xs text-ink-soft break-all">{{ $m->original_name }} &middot; {{ $m->width }}x{{ $m->height }} &middot; {{ round($m->bytes / 1024) }} KB</p>
            <form method="POST" action="{{ route('admin.media.update', $m) }}" class="flex gap-1">@csrf @method('PUT')
                <label class="sr-only" for="a{{ $m->id }}">Alt text</label><input id="a{{ $m->id }}" name="alt" value="{{ $m->alt }}" class="field-input !min-h-9 !py-1 text-sm" required maxlength="200">
                <button class="btn btn-outline btn-sm">Save</button></form>
            <form method="POST" action="{{ route('admin.media.destroy', $m) }}" x-data="{ sure: false }" @submit="if (!sure) { $event.preventDefault(); sure = true }">@csrf @method('DELETE')
                <button class="text-sm underline" x-text="sure ? 'Click again to delete' : 'Delete'">Delete</button></form>
        </div>
    @empty
        <p class="text-ink-soft">No images yet.</p>
    @endforelse
</div>
<div class="mt-4">{{ $items->links() }}</div>
@endsection
