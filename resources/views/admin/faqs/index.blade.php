@extends('admin.layout')
@section('title', 'FAQs')
@section('heading', 'FAQs')
@section('actions')<a class="btn btn-primary btn-sm" href="{{ route('admin.faqs.create') }}">Add FAQ</a>@endsection

@section('content')
<p class="mb-4 text-ink-soft max-w-2xl">These appear on the home page or on one tour type, destination or travel style page (see Shows on; a page with none of its own shows the home page ones) and are also sent to Google as FAQ structured data. Package FAQs are edited inside each package.</p>
<div class="bg-white rounded-xl shadow overflow-x-auto"><table class="w-full text-sm">
    <thead class="bg-gray-50 text-left"><tr><th class="p-3">Order</th><th class="p-3">Question</th><th class="p-3">Shows on</th><th class="p-3">Status</th><th class="p-3"></th></tr></thead>
    <tbody>@forelse($items as $f)
        <tr class="border-t"><td class="p-3">{{ $f->sort }}</td><td class="p-3 font-bold">{{ $f->question }}</td><td class="p-3">{{ $f->placeLabel() }}</td><td class="p-3">{{ $f->is_active ? 'Shown' : 'Hidden' }}</td>
            <td class="p-3 text-right"><a class="btn btn-outline btn-sm" href="{{ route('admin.faqs.edit', $f) }}">Edit</a></td></tr>
    @empty<tr><td colspan="5" class="p-6 text-ink-soft">None yet.</td></tr>@endforelse</tbody></table></div>
@endsection
