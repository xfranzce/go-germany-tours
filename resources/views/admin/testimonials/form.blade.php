@extends('admin.layout')
@php $isNew = ! $item->exists; @endphp
@section('title', $isNew ? 'New testimonial' : 'Edit testimonial')
@section('heading', $isNew ? 'New testimonial' : 'Edit testimonial')
@section('actions')<a class="btn btn-outline btn-sm" href="{{ route('admin.testimonials.index') }}">&larr; Back</a>@endsection

@section('content')
<form method="POST" action="{{ $isNew ? route('admin.testimonials.store') : route('admin.testimonials.update', $item) }}" class="bg-white rounded-xl shadow p-5 grid gap-4 sm:grid-cols-2 max-w-3xl">
    @csrf @if(! $isNew) @method('PUT') @endif
    <x-admin.field name="name" label="Guest name" :value="$item->name" required/>
    <x-admin.field name="location" label="Where they are from" :value="$item->location"/>
    <x-admin.field name="source" label="Where the review came from (e.g. Google review, March 2025)" :value="$item->source" class="sm:col-span-2"/>
    <x-admin.field name="quote" label="Review text" type="textarea" :rows="5" :value="$item->quote" required class="sm:col-span-2" maxlength="1000"/>
    <x-admin.field name="sort" label="Order" type="number" :value="$item->sort"/>
    <div class="space-y-2">
        <x-admin.field name="is_active" label="Show on the site" type="checkbox" :value="$item->is_active"/>
        <x-admin.field name="is_placeholder" label="This is placeholder text (shows a PLACEHOLDER label)" type="checkbox" :value="$item->is_placeholder"/>
    </div>
    <div class="sm:col-span-2 flex gap-3"><button class="btn btn-primary">Save</button></div>
</form>
@unless($isNew)
<form method="POST" action="{{ route('admin.testimonials.destroy', $item) }}" class="mt-6" x-data="{ sure: false }" @submit="if (!sure) { $event.preventDefault(); sure = true }">
    @csrf @method('DELETE')
    <button class="btn btn-outline btn-sm" x-text="sure ? 'Click again to delete' : 'Delete'">Delete</button>
</form>
@endunless
@endsection
