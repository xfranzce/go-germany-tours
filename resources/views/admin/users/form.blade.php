@extends('admin.layout')
@php $isNew = ! $user->exists; @endphp
@section('title', $isNew ? 'New admin' : 'Edit admin')
@section('heading', $isNew ? 'New admin user' : 'Edit: '.$user->name)
@section('actions')<a class="btn btn-outline btn-sm" href="{{ route('admin.users.index') }}">&larr; Back</a>@endsection

@section('content')
<form method="POST" action="{{ $isNew ? route('admin.users.store') : route('admin.users.update', $user) }}" class="bg-white rounded-xl shadow p-5 grid gap-4 sm:grid-cols-2 max-w-3xl">
    @csrf @if(! $isNew) @method('PUT') @endif
    <x-admin.field name="name" label="Name" :value="$user->name" required/>
    <x-admin.field name="email" label="Email" type="email" :value="$user->email" required/>
    <x-admin.field name="role" label="Role" type="select" :options="\App\Models\AdminUser::ROLES" :value="old('role', $user->role ?? 'admin')" required
        help="Editors can only manage Packages, Pages, Travel guides, Media, Tour types and Destinations. Administrators can do everything."/>
    <x-admin.field name="password" label="{{ $isNew ? 'Password' : 'New password (leave blank to keep)' }}" type="password" :required="$isNew" help="At least 12 characters."/>
    <x-admin.field name="password_confirmation" label="Confirm password" type="password" :required="$isNew"/>
    <div class="sm:col-span-2"><button class="btn btn-primary">Save</button></div>
</form>
@unless($isNew)
<form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="mt-6" x-data="{ sure: false }" @submit="if (!sure) { $event.preventDefault(); sure = true }">
    @csrf @method('DELETE')
    @error('delete')<p class="field-error mb-2">{{ $message }}</p>@enderror
    <button class="btn btn-outline btn-sm" x-text="sure ? 'Click again to remove this admin' : 'Remove admin'">Remove admin</button>
</form>
@endunless
@endsection
