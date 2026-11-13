@extends('admin.layout')
@section('title', $label.'s')
@section('heading', $label.'s')
@section('actions')<a class="btn btn-primary btn-sm" href="{{ route($routeBase.'.create') }}">Add {{ strtolower($label) }}</a>@endsection

@section('content')
<p class="mb-4 text-ink-soft max-w-2xl">These feed the package filters, the navigation menu and the {{ strtolower($label) }} landing pages. Lower order numbers show first.</p>
<div class="bg-white rounded-xl shadow overflow-x-auto">
<table class="w-full text-sm">
    <thead class="bg-gray-50 text-left"><tr><th class="p-3">Name</th><th class="p-3">URL</th><th class="p-3">Packages</th><th class="p-3">Order</th><th class="p-3">Status</th><th class="p-3"></th></tr></thead>
    <tbody>
    @foreach($items as $i)
        <tr class="border-t"><td class="p-3 font-bold">{{ $i->name }}</td><td class="p-3 text-ink-soft">{{ $i->slug }}</td><td class="p-3">{{ $i->packages_count }}</td><td class="p-3">{{ $i->sort }}</td>
            <td class="p-3">{{ $i->is_active ? 'Active' : 'Hidden' }}</td>
            <td class="p-3 text-right"><a class="btn btn-outline btn-sm" href="{{ route($routeBase.'.edit', $i) }}">Edit</a></td></tr>
    @endforeach
    </tbody>
</table></div>
@endsection
