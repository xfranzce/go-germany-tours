@extends('admin.layout')
@section('title', 'Pages')
@section('heading', 'Pages')

@section('content')
<p class="mb-4 text-ink-soft max-w-2xl">Edit the wording and search-engine details of each page. The Terms and Privacy pages are <strong>starter templates</strong>: have them reviewed by a lawyer before launch.</p>
<div class="bg-white rounded-xl shadow overflow-x-auto"><table class="w-full text-sm">
    <thead class="bg-gray-50 text-left"><tr><th class="p-3">Page</th><th class="p-3">Last updated</th><th class="p-3"></th></tr></thead>
    <tbody>@foreach($pages as $p)
        <tr class="border-t"><td class="p-3 font-bold">{{ $p->title }}</td><td class="p-3">{{ $p->updated_at->diffForHumans() }}</td><td class="p-3 text-right"><a class="btn btn-outline btn-sm" href="{{ route('admin.pages.edit', $p) }}">Edit</a></td></tr>
    @endforeach</tbody></table></div>
<p class="mt-4 text-sm">Also manage: <a class="underline font-bold" href="{{ route('admin.testimonials.index') }}">Testimonials</a>, <a class="underline font-bold" href="{{ route('admin.faqs.index') }}">Home page FAQs</a>.</p>
@endsection
