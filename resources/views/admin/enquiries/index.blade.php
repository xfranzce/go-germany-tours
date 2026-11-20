@extends('admin.layout')
@section('title', 'Enquiries')
@section('heading', 'Enquiries')
@section('actions')<a class="btn btn-outline btn-sm" href="#export" onclick="document.getElementById('export').open = true">Export CSV</a>@endsection

@section('content')
@if(session('export_error'))<div class="mb-4 rounded-lg border border-brand-red bg-red-50 p-3 font-bold text-brand-red-dark" role="alert">{{ session('export_error') }}</div>@endif
@php $picked = array_filter((array) request('form')); @endphp
<details id="export" class="mb-4 rounded-xl bg-white shadow" @if($errors->any() || session('export_error')) open @endif>
    <summary class="cursor-pointer select-none p-4 font-bold">Export to CSV&hellip; <span class="font-normal text-ink-soft">(choose the dates and forms)</span></summary>
    <form method="GET" action="{{ route('admin.enquiries.export') }}" class="grid gap-4 border-t p-4 sm:grid-cols-2 lg:grid-cols-4">
        <div><label class="field-label" for="x-from">From date</label><input id="x-from" type="date" name="from" value="{{ request('from') }}" class="field-input"></div>
        <div><label class="field-label" for="x-to">To date</label><input id="x-to" type="date" name="to" value="{{ request('to') }}" class="field-input"></div>
        <div><label class="field-label" for="x-status">Status</label>
            <select id="x-status" name="status" class="field-input"><option value="">All statuses</option>@foreach(config('gogermany.statuses') as $k => $v)<option value="{{ $k }}" @selected(request('status') === $k)>{{ $v }}</option>@endforeach</select></div>
        <div><label class="field-label" for="x-source">Source</label>
            <select id="x-source" name="source" class="field-input"><option value="">All sources</option>@foreach($sources as $s)<option @selected(request('source') === $s)>{{ $s }}</option>@endforeach</select></div>
        <fieldset class="sm:col-span-2 lg:col-span-4">
            <legend class="field-label">Forms to include</legend>
            <div class="mt-1 flex flex-wrap gap-x-6 gap-y-2">
                @foreach($forms as $key => $label)
                    <label class="inline-flex items-center gap-2 font-semibold"><input type="checkbox" name="form[]" value="{{ $key }}" class="h-5 w-5 accent-brand-red" @checked(! $picked || in_array($key, $picked, true))> {{ $label }}</label>
                @endforeach
            </div>
            <p class="mt-1 text-xs text-ink-soft">Leave all ticked to include every form. Dates are inclusive; leave them empty for no limit.</p>
            @error('to')<p class="field-error">{{ $message }}</p>@enderror
        </fieldset>
        <div class="sm:col-span-2 lg:col-span-4"><button class="btn btn-primary btn-sm">Download CSV</button></div>
    </form>
</details>
<form method="GET" class="bg-white rounded-xl shadow p-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-6 items-end">
    <div class="lg:col-span-2"><label class="field-label" for="q">Search</label><input id="q" name="q" value="{{ request('q') }}" class="field-input" placeholder="Name, email, phone, package"></div>
    <div><label class="field-label" for="status">Status</label>
        <select id="status" name="status" class="field-input"><option value="">All</option>@foreach(config('gogermany.statuses') as $k => $v)<option value="{{ $k }}" @selected(request('status') === $k)>{{ $v }}</option>@endforeach</select></div>
    <div><label class="field-label" for="source">Source</label>
        <select id="source" name="source" class="field-input"><option value="">All</option>@foreach($sources as $s)<option @selected(request('source') === $s)>{{ $s }}</option>@endforeach</select></div>
    <div><label class="field-label" for="form">Form</label>
        <select id="form" name="form[]" class="field-input"><option value="">All forms</option>@foreach($forms as $k => $label)<option value="{{ $k }}" @selected(in_array($k, $picked, true))>{{ $label }}</option>@endforeach</select></div>
    <div><label class="field-label" for="from">From</label><input id="from" type="date" name="from" value="{{ request('from') }}" class="field-input"></div>
    <div><label class="field-label" for="to">To</label><input id="to" type="date" name="to" value="{{ request('to') }}" class="field-input"></div>
    <div class="sm:col-span-2 lg:col-span-6 flex gap-2"><button class="btn btn-primary btn-sm">Filter</button><a class="btn btn-outline btn-sm" href="{{ route('admin.enquiries.index') }}">Reset</a></div>
</form>

<div class="mt-5 bg-white rounded-xl shadow overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-left"><tr><th class="p-3">Received</th><th class="p-3">Name</th><th class="p-3">Contact</th><th class="p-3">Form / package</th><th class="p-3">Source</th><th class="p-3">Status</th><th class="p-3">GHL</th></tr></thead>
        <tbody>
        @forelse($enquiries as $e)
            <tr class="border-t align-top">
                <td class="p-3 whitespace-nowrap">{{ $e->created_at->format('M j, Y H:i') }}</td>
                <td class="p-3"><a class="font-bold underline" href="{{ route('admin.enquiries.show', $e) }}">{{ $e->fullName() }}</a></td>
                <td class="p-3">{{ $e->email }}<br>{{ $e->phone_e164 }}</td>
                <td class="p-3">{{ $e->formLabel() }}@if($e->package_name)<br><span class="text-ink-soft">{{ $e->package_name }}</span>@endif</td>
                <td class="p-3">{{ $e->source }}</td>
                <td class="p-3"><span class="inline-block rounded-full px-2 py-0.5 font-bold {{ $e->status === 'new' ? 'bg-brand-gold' : 'bg-gray-200' }}">{{ config('gogermany.statuses')[$e->status] ?? $e->status }}</span></td>
                <td class="p-3">{{ $e->ghl_status }}</td>
            </tr>
        @empty
            <tr><td colspan="7" class="p-6 text-ink-soft">No enquiries match.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $enquiries->links() }}</div>
@endsection
