@extends('admin.layout')
@section('title', 'Header & Footer')
@section('heading', 'Header & Footer')
@section('actions')<a class="btn btn-primary btn-sm" href="{{ route('admin.snippets.create') }}">Add code</a>@endsection

@section('content')
<p class="mb-4 text-ink-soft max-w-3xl">Add scripts (tracking, chat widgets, pixels), styles or any HTML to the public pages. Choose where it goes (in &lt;head&gt;, the start or the end of &lt;body&gt;) and whether it runs on the entire website or only on specific pages. This code runs in every visitor's browser, so only paste code from sources you trust.</p>
<div class="bg-white rounded-xl shadow overflow-x-auto"><table class="w-full text-sm">
    <thead class="bg-gray-50 text-left"><tr><th class="p-3">Name</th><th class="p-3">Type</th><th class="p-3">Placed</th><th class="p-3">Runs on</th><th class="p-3">Status</th><th class="p-3"></th></tr></thead>
    <tbody>@forelse($items as $s)
        <tr class="border-t">
            <td class="p-3 font-bold">{{ $s->name }}</td>
            <td class="p-3">{{ explode(' (', \App\Models\CodeSnippet::TYPES[$s->type] ?? $s->type)[0] }}</td>
            <td class="p-3">{{ \App\Models\CodeSnippet::POSITIONS[$s->position] ?? $s->position }}</td>
            <td class="p-3">@if($s->scope === 'all')Entire website @else {{ implode(', ', $s->pagePatterns()) }}@endif</td>
            <td class="p-3">{{ $s->is_active ? 'Active' : 'Off' }}</td>
            <td class="p-3 text-right"><a class="btn btn-outline btn-sm" href="{{ route('admin.snippets.edit', $s) }}">Edit</a></td>
        </tr>
    @empty<tr><td colspan="6" class="p-6 text-ink-soft">Nothing added yet.</td></tr>@endforelse</tbody></table></div>
@endsection
