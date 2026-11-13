@extends('admin.layout')
@section('title', 'Enquiry #'.$e->id)
@section('heading', 'Enquiry from '.$e->fullName())
@section('actions')<a class="btn btn-outline btn-sm" href="{{ route('admin.enquiries.index') }}">&larr; All enquiries</a>@endsection

@section('content')
@php $touchLabels = ['utm_source' => 'UTM source', 'utm_medium' => 'UTM medium', 'utm_campaign' => 'UTM campaign', 'utm_term' => 'UTM term', 'utm_content' => 'UTM content', 'gclid' => 'gclid', 'gbraid' => 'gbraid', 'wbraid' => 'wbraid', 'referrer' => 'Referrer', 'landing_page' => 'Landing page']; @endphp
<div class="grid gap-6 lg:grid-cols-[1fr_22rem]">
<div class="space-y-6 min-w-0">
    <section class="bg-white rounded-xl shadow p-5" aria-labelledby="d">
        <h2 id="d" class="text-xl mb-3">Details</h2>
        <dl class="grid sm:grid-cols-2 gap-x-6 gap-y-3 text-sm">
            @foreach([['Received', $e->created_at->format('M j, Y H:i')], ['Form', $e->formLabel()], ['Email', $e->email], ['Phone (E.164)', $e->phone_e164], ['Package', $e->package_name ?: 'None'],
                ['Preferred travel date', $e->travel_date], ['Second quote from partner?', $e->second_quote], ['Time of year', $e->time_of_year], ['Group size', $e->group_size], ['Nights', $e->nights]] as [$l, $v])
                @if($v)<div><dt class="font-bold">{{ $l }}</dt><dd class="break-words">{{ $v }}</dd></div>@endif
            @endforeach
            @if($e->special_request)<div class="sm:col-span-2"><dt class="font-bold">Special request</dt><dd class="whitespace-pre-line">{{ $e->special_request }}</dd></div>@endif
        </dl>
    </section>

    @if($e->quiz_answers)
    <section class="bg-white rounded-xl shadow p-5" aria-labelledby="q"><h2 id="q" class="text-xl mb-3">Quiz answers</h2>
        <dl class="grid sm:grid-cols-2 gap-3 text-sm">@foreach($e->quiz_answers as $k => $a)<div><dt class="font-bold">{{ \Illuminate\Support\Str::headline($k) }}</dt><dd>{{ $a }}</dd></div>@endforeach</dl>
    </section>
    @endif

    <section class="bg-white rounded-xl shadow p-5" aria-labelledby="c">
        <h2 id="c" class="text-xl mb-3">Consent evidence</h2>
        <dl class="grid sm:grid-cols-2 gap-x-6 gap-y-3 text-sm">
            <div><dt class="font-bold">Consent ticked</dt><dd>{{ $e->consent_given ? 'Yes' : 'No' }}</dd></div>
            <div><dt class="font-bold">Consented at</dt><dd>{{ $e->consented_at->format('Y-m-d H:i:s') }} (server time {{ config('app.timezone') }})</dd></div>
            <div><dt class="font-bold">Terms version</dt><dd>{{ $e->terms_version }}</dd></div>
            <div><dt class="font-bold">IP address</dt><dd>{{ $e->ip_address }}</dd></div>
            <div class="sm:col-span-2"><dt class="font-bold">Page URL</dt><dd class="break-all">{{ $e->page_url }}</dd></div>
            <div class="sm:col-span-2"><dt class="font-bold">User agent</dt><dd class="break-all">{{ $e->user_agent }}</dd></div>
            <div class="sm:col-span-2"><dt class="font-bold">Exact consent text shown</dt><dd class="bg-gray-50 rounded p-3 mt-1">{{ $e->consent_text }}</dd></div>
        </dl>
    </section>

    <section class="bg-white rounded-xl shadow p-5" aria-labelledby="t">
        <h2 id="t" class="text-xl mb-3">Tracking</h2>
        <div class="grid md:grid-cols-2 gap-6 text-sm">
            @foreach(['First touch' => $e->first_touch, 'Latest touch' => $e->last_touch] as $title => $touch)
                <div><h3 class="font-bold mb-2">{{ $title }}</h3>
                    @if($touch)<dl class="space-y-1">@foreach($touchLabels as $k => $l)@if(!empty($touch[$k]))<div><dt class="inline font-bold">{{ $l }}:</dt> <dd class="inline break-all">{{ $touch[$k] }}</dd></div>@endif @endforeach</dl>
                    @else<p class="text-ink-soft">Nothing recorded.</p>@endif</div>
            @endforeach
        </div>
    </section>
</div>

<aside class="space-y-6">
    <form method="POST" action="{{ route('admin.enquiries.update', $e) }}" class="bg-white rounded-xl shadow p-5 space-y-4">
        @csrf @method('PUT')
        <h2 class="text-xl">Status and notes</h2>
        <x-admin.field name="status" label="Status" type="select" :options="config('gogermany.statuses')" :value="$e->status"/>
        <x-admin.field name="internal_notes" label="Internal notes" type="textarea" :value="$e->internal_notes" :rows="6" help="Only staff see these."/>
        <button class="btn btn-primary w-full">Save</button>
    </form>

    <section class="bg-white rounded-xl shadow p-5 text-sm space-y-2" aria-labelledby="g">
        <h2 id="g" class="text-xl">GoHighLevel</h2>
        <p>Status: <strong>{{ $e->ghl_status }}</strong> ({{ $e->ghl_attempts }} attempts)</p>
        @if($e->ghl_contact_id)<p class="break-all">Contact ID: {{ $e->ghl_contact_id }}</p>@endif
        @if($e->ghl_error)<p class="text-brand-red-dark break-words">Last error: {{ $e->ghl_error }}</p>@endif
        @if($ghlOn)
            <form method="POST" action="{{ route('admin.enquiries.ghl-retry', $e) }}">@csrf<button class="btn btn-outline btn-sm">Retry sync</button></form>
        @else
            <p class="text-ink-soft">Integration is off (no token in .env).</p>
        @endif
        <p class="text-ink-soft">Staff email sent: {{ $e->email_sent_at ? $e->email_sent_at->format('M j, H:i') : 'no' }}</p>
    </section>

    <form method="POST" action="{{ route('admin.enquiries.destroy', $e) }}" x-data="{ sure: false }" @submit="if (!sure) { $event.preventDefault(); sure = true }">
        @csrf @method('DELETE')
        <button class="btn btn-outline btn-sm w-full" :class="sure && '!bg-brand-red !text-white !border-brand-red'" x-text="sure ? 'Click again to permanently delete' : 'Delete enquiry'">Delete enquiry</button>
    </form>
</aside>
</div>
@endsection
