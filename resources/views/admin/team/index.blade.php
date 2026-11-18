@extends('admin.layout')
@section('title', 'Team')
@section('heading', 'Team')
@section('actions')<a class="btn btn-primary btn-sm" href="{{ route('admin.team.create') }}">Add team member</a>@endsection

@section('content')
<p class="mb-4 text-ink-soft max-w-2xl">These are the cards on the Meet Our Team page, shown three across. The text above the cards is edited in Pages > Meet Our Team.</p>
<div class="bg-white rounded-xl shadow overflow-x-auto"><table class="w-full text-sm">
    <thead class="bg-gray-50 text-left"><tr><th class="p-3">Order</th><th class="p-3">Name</th><th class="p-3">Role</th><th class="p-3">Status</th><th class="p-3"></th></tr></thead>
    <tbody>@forelse($items as $m)
        <tr class="border-t"><td class="p-3">{{ $m->sort }}</td><td class="p-3 font-bold">{{ $m->name }}</td><td class="p-3">{{ $m->role }}</td><td class="p-3">{{ $m->is_active ? 'Shown' : 'Hidden' }}</td>
            <td class="p-3 text-right"><a class="btn btn-outline btn-sm" href="{{ route('admin.team.edit', $m) }}">Edit</a></td></tr>
    @empty<tr><td colspan="5" class="p-6 text-ink-soft">None yet.</td></tr>@endforelse</tbody></table></div>
@endsection
