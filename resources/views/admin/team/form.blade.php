@extends('admin.layout')
@php $isNew = ! $item->exists; @endphp
@section('title', $isNew ? 'New team member' : 'Edit team member')
@section('heading', $isNew ? 'New team member' : 'Edit team member')
@section('actions')<a class="btn btn-outline btn-sm" href="{{ route('admin.team.index') }}">&larr; Back</a>@endsection

@section('content')
<form method="POST" enctype="multipart/form-data" action="{{ $isNew ? route('admin.team.store') : route('admin.team.update', $item) }}" class="bg-white rounded-xl shadow p-5 grid gap-4 max-w-3xl">
    @csrf @if(! $isNew) @method('PUT') @endif
    <x-admin.field name="name" label="Name" :value="$item->name" required maxlength="120"/>
    <x-admin.field name="role" label="Role" :value="$item->role" maxlength="120"/>
    <x-admin.field name="bio" label="Short bio (plain text)" type="textarea" :rows="3" :value="$item->bio" maxlength="600"/>
    <x-admin.image :current="$item->image" label="Photo (square or portrait works best)"/>
    <div class="grid gap-4 sm:grid-cols-2"><x-admin.field name="sort" label="Order" type="number" :value="$item->sort"/><x-admin.field name="is_active" label="Show on the site" type="checkbox" :value="$item->is_active"/></div>
    <div><button class="btn btn-primary">Save</button></div>
</form>
@unless($isNew)
<form method="POST" action="{{ route('admin.team.destroy', $item) }}" class="mt-6" x-data="{ sure: false }" @submit="if (!sure) { $event.preventDefault(); sure = true }">
    @csrf @method('DELETE')<button class="btn btn-outline btn-sm" x-text="sure ? 'Click again to delete' : 'Delete'">Delete</button>
</form>
@endunless
@endsection
