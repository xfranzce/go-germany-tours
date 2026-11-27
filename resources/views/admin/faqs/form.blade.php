@extends('admin.layout')
@php $isNew = ! $item->exists; @endphp
@section('title', $isNew ? 'New FAQ' : 'Edit FAQ')
@section('heading', $isNew ? 'New FAQ' : 'Edit FAQ')
@section('actions')<a class="btn btn-outline btn-sm" href="{{ route('admin.faqs.index') }}">&larr; Back</a>@endsection

@section('content')
<form method="POST" action="{{ $isNew ? route('admin.faqs.store') : route('admin.faqs.update', $item) }}" class="bg-white rounded-xl shadow p-5 grid gap-4 max-w-3xl">
    @csrf @if(! $isNew) @method('PUT') @endif
    <x-admin.field name="question" label="Question" :value="$item->question" required maxlength="250"/>
    <x-admin.field name="answer" label="Answer (plain text)" type="textarea" :rows="5" :value="$item->answer" required maxlength="3000"/>
    <x-admin.field name="place" label="Shows on" type="select" :options="$places" :value="$item->placeKey()" help="Home page (general) FAQs also show on the FAQs page, the Packages and Destinations overviews, and on any tour type, destination or travel style page that has no FAQs of its own."/>
    <div class="grid gap-4 sm:grid-cols-2"><x-admin.field name="sort" label="Order" type="number" :value="$item->sort"/><x-admin.field name="is_active" label="Show on the site" type="checkbox" :value="$item->is_active"/></div>
    <div><button class="btn btn-primary">Save</button></div>
</form>
@unless($isNew)
<form method="POST" action="{{ route('admin.faqs.destroy', $item) }}" class="mt-6" x-data="{ sure: false }" @submit="if (!sure) { $event.preventDefault(); sure = true }">
    @csrf @method('DELETE')<button class="btn btn-outline btn-sm" x-text="sure ? 'Click again to delete' : 'Delete'">Delete</button>
</form>
@endunless
@endsection
