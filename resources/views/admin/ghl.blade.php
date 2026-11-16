@extends('admin.layout')
@section('title', 'GoHighLevel')
@section('heading', 'GoHighLevel integration')

@section('content')
<div class="max-w-3xl space-y-6">

    {{-- Status --}}
    <div class="rounded-xl bg-white p-5 shadow">
        <div class="flex flex-wrap items-center gap-3">
            @if($enabled)
                <span class="inline-flex items-center gap-2 rounded-full bg-green-100 px-3 py-1 text-sm font-bold text-green-800"><span class="size-2 rounded-full bg-green-600"></span> On: enquiries are sent to GoHighLevel</span>
            @elseif($switchedOff)
                <span class="inline-flex items-center gap-2 rounded-full bg-gray-200 px-3 py-1 text-sm font-bold"><span class="size-2 rounded-full bg-gray-500"></span> Switched off</span>
            @else
                <span class="inline-flex items-center gap-2 rounded-full bg-amber-100 px-3 py-1 text-sm font-bold text-amber-900"><span class="size-2 rounded-full bg-amber-600"></span> Off: add the Location ID and API token</span>
            @endif
            @if($source === 'env')<span class="text-sm text-ink-soft">Credentials currently come from the server <code>.env</code> file. Saving values below overrides them.</span>@endif
            @if($source === 'admin')<span class="text-sm text-ink-soft">Credentials saved here (the token is stored encrypted).</span>@endif
        </div>
        @if($failed > 0)
            <p class="mt-3 text-sm font-bold text-brand-red-dark">{{ $failed }} enquiry sync(s) failed. Open Enquiries and press "Retry GHL" on them once the connection works.</p>
        @endif
        <p class="mt-3 text-sm text-ink-soft">Enquiries are always saved here first. If GoHighLevel is off or fails, nothing is lost and the enquiry is never blocked.</p>
    </div>

    @if($errors->has('ghl'))
        <p class="rounded-lg border border-brand-red bg-red-50 p-3 font-bold text-brand-red-dark" role="alert">{{ $errors->first('ghl') }}</p>
    @endif
    @if(session('ghl_messages'))
        <ul class="space-y-1 rounded-lg border border-brand-custard bg-brand-custard-light p-4 text-sm font-semibold" role="status">
            @foreach(session('ghl_messages') as $m)<li>{{ $m }}</li>@endforeach
        </ul>
    @endif

    {{-- Credentials --}}
    <form method="POST" action="{{ route('admin.ghl.update') }}" class="grid gap-4 rounded-xl bg-white p-5 shadow sm:grid-cols-2" autocomplete="off">
        @csrf @method('PUT')
        <h2 class="text-xl sm:col-span-2">Connection details</h2>

        <x-admin.field name="ghl_location_id" label="Location ID" :value="$locationId" placeholder="e.g. ve9EPM428h8vShlRW1KT"
            help="GoHighLevel > Settings > Business Profile (or the Location ID in the sub-account URL)."/>

        <div>
            <x-admin.field name="ghl_api_token" label="API token (Private Integration token)" type="password" :placeholder="$tokenHint ?: 'pit-...'"
                help="{{ $tokenHint ? 'A token is saved ('.$tokenHint.'). Leave blank to keep it, or paste a new one to replace it.' : 'Paste the token once; it is stored encrypted and never shown again.' }}"/>
            @if($tokenHint && $source === 'admin')
                <label class="mt-2 flex items-center gap-2 text-sm"><input type="checkbox" name="remove_token" value="1" class="size-4 accent-brand-red"> Remove the saved token</label>
            @endif
        </div>

        <div class="sm:col-span-2">
            <input type="hidden" name="ghl_enabled" value="0">
            <label class="flex items-center gap-3 font-bold"><input type="checkbox" name="ghl_enabled" value="1" class="size-5 accent-brand-red" @checked(! $switchedOff)> Send enquiries to GoHighLevel</label>
            <p class="mt-1 text-sm text-ink-soft">Untick to pause the sync without losing your credentials.</p>
        </div>

        <div class="sm:col-span-2"><button class="btn btn-primary">Save</button></div>
    </form>

    {{-- Test + fields --}}
    <div class="grid gap-4 rounded-xl bg-white p-5 shadow sm:grid-cols-2">
        <h2 class="text-xl sm:col-span-2">Check and prepare</h2>
        <form method="POST" action="{{ route('admin.ghl.test') }}" class="space-y-2">
            @csrf
            <button class="btn btn-outline btn-sm" @disabled(! $enabled)>Test connection</button>
            <p class="text-sm text-ink-soft">Checks the token and Location ID and that the {{ $fieldCount }} custom fields exist. Creates nothing and sends no contact.</p>
        </form>
        <form method="POST" action="{{ route('admin.ghl.setup') }}" class="space-y-2">
            @csrf
            <button class="btn btn-outline btn-sm" @disabled(! $enabled)>Create missing custom fields</button>
            <p class="text-sm text-ink-soft">Adds the {{ $fieldCount }} fields enquiries use (quiz answers, consent, tracking). Safe to repeat. Do not rename them in GoHighLevel.</p>
        </form>
    </div>

    {{-- Help --}}
    <details class="rounded-xl bg-white p-5 shadow">
        <summary class="cursor-pointer font-bold">How to get the Location ID and token</summary>
        <ol class="mt-3 list-decimal space-y-2 pl-5 text-sm text-ink-soft">
            <li>In GoHighLevel open the sub-account, then <b>Settings &rarr; Private Integrations &rarr; Create new integration</b>.</li>
            <li>Tick these scopes: <b>contacts.readonly, contacts.write, locations/customFields.readonly, locations/customFields.write</b> (and <b>locations.readonly</b> so "Test connection" can show the account name).</li>
            <li>Copy the token (starts with <code>pit-</code>) and paste it above. GoHighLevel shows it only once.</li>
            <li>Copy the <b>Location ID</b> from <b>Settings &rarr; Business Profile</b> and paste it above.</li>
            <li>Save, press <b>Test connection</b>, then <b>Create missing custom fields</b>.</li>
        </ol>
        <p class="mt-3 text-sm text-ink-soft">Full guide: <code>docs/reference/GHL_INTEGRATION.md</code>.</p>
    </details>
</div>
@endsection
