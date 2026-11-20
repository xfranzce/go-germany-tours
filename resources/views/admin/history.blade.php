@extends('admin.layout')
@section('title', 'History')
@section('heading', 'History')

@section('content')
<p class="mb-4 text-ink-soft max-w-3xl">Everything done in the admin: who added, changed, deleted or restored something, and when. Passwords, keys and tokens are never shown.</p>

<form method="get" class="mb-4 flex flex-wrap items-end gap-3 rounded-xl bg-white p-4 shadow">
    <label class="text-sm font-bold">Who
        <select name="user" class="field-input mt-1"><option value="">Everyone</option>@foreach($users as $email => $name)<option value="{{ $email }}" @selected(request('user') === $email)>{{ $name ?: $email }}</option>@endforeach</select></label>
    <label class="text-sm font-bold">What
        <select name="action" class="field-input mt-1"><option value="">Any action</option>@foreach(\App\Models\ActivityLog::ACTIONS as $k => $v)<option value="{{ $k }}" @selected(request('action') === $k)>{{ $v }}</option>@endforeach</select></label>
    <label class="text-sm font-bold">Area
        <select name="area" class="field-input mt-1"><option value="">All areas</option>@foreach($areas as $a)<option value="{{ $a }}" @selected(request('area') === $a)>{{ $a }}</option>@endforeach</select></label>
    <label class="text-sm font-bold">From <input type="date" name="from" value="{{ request('from') }}" class="field-input mt-1"></label>
    <label class="text-sm font-bold">To <input type="date" name="to" value="{{ request('to') }}" class="field-input mt-1"></label>
    <label class="text-sm font-bold">Search <input type="search" name="q" value="{{ request('q') }}" class="field-input mt-1" placeholder="Name or title"></label>
    <button class="btn btn-primary btn-sm">Filter</button>
    @if(request()->query())<a class="btn btn-outline btn-sm" href="{{ route('admin.history.index') }}">Clear</a>@endif
</form>

<div class="bg-white rounded-xl shadow overflow-x-auto"><table class="w-full text-sm">
    <thead class="bg-gray-50 text-left"><tr><th class="p-3 whitespace-nowrap">Date and time</th><th class="p-3">Who</th><th class="p-3">Action</th><th class="p-3">Details</th></tr></thead>
    <tbody>@forelse($logs as $l)
        <tr class="border-t align-top">
            <td class="p-3 whitespace-nowrap">{{ $l->created_at?->format('d M Y, H:i') }}</td>
            <td class="p-3"><span class="font-bold">{{ $l->user_name ?: ($l->user_email ?: 'Unknown') }}</span>@if($l->user_name && $l->user_email)<br><span class="text-xs text-ink-soft">{{ $l->user_email }}</span>@endif</td>
            <td class="p-3 whitespace-nowrap">{{ $l->actionLabel() }}@if($l->subject_type)<br><span class="text-xs text-ink-soft">{{ $l->subject_type }}</span>@endif</td>
            <td class="p-3">
                {{ $l->summary }}
                @if($l->changes)
                    <details class="mt-1 text-xs"><summary class="cursor-pointer font-bold underline">See what changed</summary>
                        <ul class="mt-1 space-y-1">@foreach($l->changes as $field => [$old, $new])
                            <li><span class="font-bold">{{ str_replace('_', ' ', $field) }}:</span> <span class="text-ink-soft line-through">{{ $old === null || $old === '' ? '(empty)' : $old }}</span> &rarr; {{ $new === null || $new === '' ? '(empty)' : $new }}</li>
                        @endforeach</ul></details>
                @endif
                @if($l->ip)<span class="mt-1 block text-xs text-ink-soft">IP {{ $l->ip }}</span>@endif
            </td>
        </tr>
    @empty<tr><td colspan="4" class="p-6 text-ink-soft">Nothing recorded yet. Changes made in the admin from now on will appear here.</td></tr>@endforelse</tbody></table></div>
<div class="mt-4">{{ $logs->links() }}</div>
@endsection
