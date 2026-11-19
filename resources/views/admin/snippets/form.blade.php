@extends('admin.layout')
@php $isNew = ! $item->exists; @endphp
@section('title', $isNew ? 'Add code' : 'Edit code')
@section('heading', $isNew ? 'Add code' : 'Edit code')
@section('actions')<a class="btn btn-outline btn-sm" href="{{ route('admin.snippets.index') }}">&larr; Back</a>@endsection

@section('content')
<form method="POST" action="{{ $isNew ? route('admin.snippets.store') : route('admin.snippets.update', $item) }}" class="bg-white rounded-xl shadow p-5 grid gap-4 max-w-3xl"
      x-data="{ scope: @js(old('scope', $item->scope)) }">
    @csrf @if(! $isNew) @method('PUT') @endif
    <x-admin.field name="name" label="Name" :value="$item->name" required maxlength="120" help="Only for you, e.g. Facebook Pixel or Chat widget."/>
    <div class="grid gap-4 sm:grid-cols-2">
        <x-admin.field name="type" label="Type" type="select" :options="\App\Models\CodeSnippet::TYPES" :value="$item->type" help="Bare code is wrapped in the right tag for you. Code that already starts with a tag (for example <script src=...>) is used exactly as pasted."/>
        <x-admin.field name="position" label="Place it" type="select" :options="\App\Models\CodeSnippet::POSITIONS" :value="$item->position" help="Most tracking and style code goes in <head>. Noscript fallbacks go at the start of <body>. Widgets can go at the end of <body>."/>
    </div>
    <div>
        <label for="f-code" class="field-label">Code <span class="text-brand-red">*</span></label>
        <textarea id="f-code" name="code" rows="14" required spellcheck="false" autocomplete="off" class="field-input font-mono text-sm" placeholder="&lt;script&gt;...&lt;/script&gt;">{{ old('code', $item->code) }}</textarea>
        @error('code')<p class="field-error">{{ $message }}</p>@enderror
    </div>
    <div class="grid gap-4 sm:grid-cols-2">
        <div>
            <label for="f-scope" class="field-label">Runs on</label>
            <select id="f-scope" name="scope" class="field-input" x-model="scope">
                @foreach(\App\Models\CodeSnippet::SCOPES as $k => $label)<option value="{{ $k }}">{{ $label }}</option>@endforeach
            </select>
        </div>
        <x-admin.field name="sort" label="Order" type="number" :value="$item->sort" help="Lower numbers are output first."/>
    </div>
    <div x-show="scope === 'specific'" x-cloak>
        <label for="f-pages" class="field-label">Pages</label>
        <textarea id="f-pages" name="pages" rows="5" spellcheck="false" class="field-input font-mono text-sm" placeholder="/&#10;/about&#10;/tours/*&#10;/thank-you">{{ old('pages', $item->pages) }}</textarea>
        <p class="text-xs text-ink-soft mt-1">One page per line. Use the path (or paste the full URL). <code>/</code> is the home page, and <code>*</code> matches anything, e.g. <code>/tours/*</code> or <code>/packages/*</code>. The Thank-you page is <code>/thank-you</code>.</p>
        @error('pages')<p class="field-error">{{ $message }}</p>@enderror
    </div>
    <x-admin.field name="is_active" label="Active (output on the site)" type="checkbox" :value="$item->is_active"/>
    <div><button class="btn btn-primary">Save</button></div>
</form>
@unless($isNew)
<form method="POST" action="{{ route('admin.snippets.destroy', $item) }}" class="mt-6" x-data="{ sure: false }" @submit="if (!sure) { $event.preventDefault(); sure = true }">
    @csrf @method('DELETE')<button class="btn btn-outline btn-sm" x-text="sure ? 'Click again to delete' : 'Delete'">Delete</button>
</form>
@endunless
@endsection
