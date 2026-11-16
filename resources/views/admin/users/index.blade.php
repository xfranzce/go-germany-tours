@extends('admin.layout')
@section('title', 'Admin users')
@section('heading', 'Admin users')
@section('actions')<a class="btn btn-primary btn-sm" href="{{ route('admin.users.create') }}">Add admin</a>@endsection

@section('content')
<div class="bg-white rounded-xl shadow overflow-x-auto"><table class="w-full text-sm">
    <thead class="bg-gray-50 text-left"><tr><th class="p-3">Name</th><th class="p-3">Email</th><th class="p-3">Role</th><th class="p-3">Last sign-in</th><th class="p-3"></th></tr></thead>
    <tbody>@foreach($users as $u)
        <tr class="border-t"><td class="p-3 font-bold">{{ $u->name }}@if($u->is(auth('admin')->user())) <span class="text-ink-soft font-normal">(you)</span>@endif</td><td class="p-3">{{ $u->email }}</td><td class="p-3">{{ \App\Models\AdminUser::ROLES[$u->role] ?? $u->role }}</td><td class="p-3">{{ $u->last_login_at?->diffForHumans() ?? 'Never' }}</td>
            <td class="p-3 text-right"><a class="btn btn-outline btn-sm" href="{{ route('admin.users.edit', $u) }}">Edit</a></td></tr>
    @endforeach</tbody></table></div>
@endsection
