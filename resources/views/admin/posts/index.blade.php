@extends('admin.layout')
@section('title', 'Travel guides')
@section('heading', 'Travel guides')
@section('actions')<a class="btn btn-primary btn-sm" href="{{ route('admin.posts.create') }}">Add guide</a>@endsection

@section('content')
<div class="bg-white rounded-xl shadow overflow-x-auto"><table class="w-full text-sm">
    <thead class="bg-gray-50 text-left"><tr><th class="p-3">Title</th><th class="p-3">Status</th><th class="p-3">Published</th><th class="p-3"></th></tr></thead>
    <tbody>@forelse($items as $p)
        <tr class="border-t"><td class="p-3 font-bold">{{ $p->title }}</td><td class="p-3">{{ $p->is_published ? 'Published' : 'Draft' }}</td><td class="p-3">{{ $p->published_at?->format('M j, Y') }}</td>
            <td class="p-3 text-right"><a class="btn btn-outline btn-sm" href="{{ route('admin.posts.edit', $p) }}">Edit</a></td></tr>
    @empty<tr><td colspan="4" class="p-6 text-ink-soft">None yet.</td></tr>@endforelse</tbody></table></div>
@endsection
