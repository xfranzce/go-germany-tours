@extends('admin.layout')
@section('title', 'Maintenance mode')
@section('heading', 'Maintenance mode')

@section('content')
<div class="max-w-3xl space-y-6">
    <div class="rounded-xl bg-white p-5 shadow">
        @if($enabled)
            <span class="inline-flex items-center gap-2 rounded-full bg-red-100 px-3 py-1 text-sm font-bold text-brand-red-dark"><span class="size-2 rounded-full bg-brand-red"></span> ON: visitors see the maintenance page</span>
        @else
            <span class="inline-flex items-center gap-2 rounded-full bg-green-100 px-3 py-1 text-sm font-bold text-green-800"><span class="size-2 rounded-full bg-green-600"></span> OFF: the site is live</span>
        @endif
        <p class="mt-3 text-sm text-ink-soft">While it is on, everyone except logged-in admin users sees a "back soon" page. Search engines are told (503) that it is temporary, so rankings are not harmed. The admin area keeps working, and because you are logged in you can still browse the live site to check your changes.</p>
    </div>

    <form method="POST" action="{{ route('admin.maintenance.update') }}" class="grid gap-4 rounded-xl bg-white p-5 shadow">
        @csrf @method('PUT')
        <x-admin.field name="message" label="Message shown to visitors (optional)" type="textarea" :rows="3" :value="$message" maxlength="600"
            help="Leave empty to use the standard wording. Plain text only."/>
        <div class="flex flex-wrap items-center gap-3">
            @if($enabled)
                <button class="btn btn-primary" name="enabled" value="0">Turn maintenance mode OFF</button>
                <button class="btn btn-outline" name="enabled" value="1">Save message only (keep ON)</button>
            @else
                <button class="btn btn-primary" name="enabled" value="1" onclick="return confirm('Turn maintenance mode ON? Visitors will no longer see the site.')">Turn maintenance mode ON</button>
                <button class="btn btn-outline" name="enabled" value="0">Save message only (keep OFF)</button>
            @endif
        </div>
    </form>
</div>
@endsection
