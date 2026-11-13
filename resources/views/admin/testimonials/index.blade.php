@extends('admin.layout')
@section('title', 'Testimonials')
@section('heading', 'Testimonials')
@section('actions')<a class="btn btn-primary btn-sm" href="{{ route('admin.testimonials.create') }}">Add testimonial</a>@endsection

@section('content')
<p class="mb-4 text-ink-soft max-w-2xl">Only publish real, permissioned guest reviews. Placeholder entries show a PLACEHOLDER label on the site until you replace them.</p>
<div class="bg-white rounded-xl shadow overflow-x-auto"><table class="w-full text-sm">
    <thead class="bg-gray-50 text-left"><tr><th class="p-3">Order</th><th class="p-3">Name</th><th class="p-3">Quote</th><th class="p-3">Status</th><th class="p-3"></th></tr></thead>
    <tbody>@forelse($items as $t)
        <tr class="border-t align-top"><td class="p-3">{{ $t->sort }}</td><td class="p-3 font-bold">{{ $t->name }}</td><td class="p-3 max-w-md">{{ \Illuminate\Support\Str::limit($t->quote, 90) }}</td>
            <td class="p-3">{{ $t->is_active ? 'Shown' : 'Hidden' }}{{ $t->is_placeholder ? ', placeholder' : '' }}</td>
            <td class="p-3 text-right"><a class="btn btn-outline btn-sm" href="{{ route('admin.testimonials.edit', $t) }}">Edit</a></td></tr>
    @empty<tr><td colspan="5" class="p-6 text-ink-soft">None yet.</td></tr>@endforelse</tbody></table></div>
@endsection
