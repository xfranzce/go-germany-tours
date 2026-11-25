@extends('admin.layout')
@section('title', 'Dashboard')
@section('heading', 'Dashboard')

@section('actions')
    {{-- period switch --}}
    <nav class="inline-flex overflow-hidden rounded-full border border-gray-300 bg-white text-sm" aria-label="Period">
        @foreach(\App\Http\Controllers\Admin\DashboardController::RANGES as $d)
            <a href="{{ route('admin.dashboard', ['days' => $d]) }}" class="px-4 py-2 {{ $days === $d ? 'bg-brand-navy font-bold text-white' : 'hover:bg-gray-100' }}" @if($days === $d) aria-current="page" @endif>{{ $d }} days</a>
        @endforeach
    </nav>
@endsection

@section('content')
@php
    $statusPill = ['new' => 'bg-amber-100 text-amber-900', 'contacted' => 'bg-sky-100 text-sky-900', 'quoted' => 'bg-indigo-100 text-indigo-900', 'won' => 'bg-green-100 text-green-900', 'lost' => 'bg-gray-200 text-gray-700'];
    $pct = fn ($n) => $total ? round($n / $total * 100) : 0;
    $change = $total - $prevCount;
    $attention = array_filter([
        $waiting ? [$waiting.' new '.Str::plural('enquiry', $waiting).' '.($waiting === 1 ? 'has' : 'have').' waited more than 24 hours.', route('admin.enquiries.index', ['status' => 'new']), 'Open them'] : null,
        $ghlFailed ? [$ghlFailed.' '.Str::plural('enquiry', $ghlFailed).' failed to reach GoHighLevel.', route('admin.enquiries.index'), 'Retry from the enquiry'] : null,
        $sheetFailed ? [$sheetFailed.' '.Str::plural('enquiry', $sheetFailed).' failed to reach the Google Sheet.', route('admin.enquiries.index'), 'Check them'] : null,
    ]);
    $max = max(1, $perDay->max());
    $card = 'rounded-xl bg-white p-5 shadow-sm ring-1 ring-black/5';
@endphp

@if($attention)
    <section class="mb-6 rounded-xl border border-amber-300 bg-amber-50 p-4" aria-labelledby="attn">
        <h2 id="attn" class="font-sans text-sm font-bold">Needs your attention</h2>
        <ul class="mt-1 space-y-1 text-sm">
            @foreach($attention as [$text, $url, $link])<li>{{ $text }} <a href="{{ $url }}" class="font-bold underline">{{ $link }}</a></li>@endforeach
        </ul>
    </section>
@endif

{{-- KPI cards --}}
<div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
    <div class="{{ $card }}">
        <p class="text-sm">Enquiries, last {{ $days }} days</p>
        <p class="mt-1 font-display text-4xl">{{ $total }}</p>
        <p class="mt-1 text-sm {{ $change > 0 ? 'text-green-700' : ($change < 0 ? 'text-brand-red-dark' : 'text-ink-soft') }}">
            {{ $change > 0 ? '+' : '' }}{{ $change }} vs the previous {{ $days }} days</p>
    </div>
    <div class="{{ $card }}">
        <p class="text-sm">New, not yet actioned</p>
        <p class="mt-1 font-display text-4xl">{{ $newCount }}</p>
        <a href="{{ route('admin.enquiries.index', ['status' => 'new']) }}" class="mt-1 inline-block text-sm underline">Open the inbox &rarr;</a>
    </div>
    <div class="{{ $card }}">
        <p class="text-sm">From Google Ads</p>
        <p class="mt-1 font-display text-4xl">{{ $fromAds }}</p>
        <p class="mt-1 text-sm text-ink-soft">{{ $pct($fromAds) }}% of enquiries (gclid / paid Google)</p>
    </div>
    <div class="{{ $card }}">
        <p class="text-sm">Won</p>
        <p class="mt-1 font-display text-4xl">{{ $won }}</p>
        <p class="mt-1 text-sm text-ink-soft">{{ $pct($won) }}% of enquiries in this period</p>
    </div>
</div>

<div class="mt-6 grid gap-6 xl:grid-cols-[2fr_1fr]">
    {{-- enquiries per day: plain bars, one per day --}}
    <section class="{{ $card }}" aria-labelledby="perday">
        <div class="flex items-baseline justify-between gap-4">
            <h2 id="perday" class="text-2xl">Enquiries per day</h2>
            <p class="text-sm text-ink-soft">max {{ $perDay->max() }} / day</p>
        </div>
        <div class="relative mt-4 h-48 border-b border-gray-200 bg-[repeating-linear-gradient(to_top,transparent_0,transparent_calc(25%-1px),#eef0f2_calc(25%-1px),#eef0f2_25%)]">
            <div class="absolute inset-0 flex items-end {{ $days > 30 ? 'gap-px' : 'gap-1' }}">
                @foreach($perDay as $date => $n)
                    <div class="group relative flex h-full flex-1 items-end">
                        <div class="w-full rounded-t bg-brand-navy transition group-hover:bg-brand-gold-dark" style="height: {{ $n ? max(3, $n / $max * 100) : 0 }}%"></div>
                        <span class="pointer-events-none absolute bottom-full left-1/2 z-10 mb-1 hidden -translate-x-1/2 whitespace-nowrap rounded bg-brand-navy px-2 py-1 text-xs text-white group-hover:block">{{ \Carbon\Carbon::parse($date)->format('D j M') }}: {{ $n }}</span>
                    </div>
                @endforeach
            </div>
        </div>
        <div class="mt-2 flex justify-between text-sm text-ink-soft"><span>{{ $from->format('D j M') }}</span><span>{{ now()->format('D j M') }}</span></div>
        <p class="sr-only">{{ $perDay->filter()->map(fn ($n, $d) => \Carbon\Carbon::parse($d)->format('j M').': '.$n)->implode(', ') ?: 'No enquiries in this period.' }}</p>
    </section>

    {{-- pipeline by status (this period) --}}
    <section class="{{ $card }}" aria-labelledby="pipe">
        <h2 id="pipe" class="text-2xl">Pipeline</h2>
        <p class="text-sm text-ink-soft">Status of the enquiries from this period</p>
        <ul class="mt-4 space-y-4">
            @foreach($pipeline as $key => $row)
                <li>
                    <div class="flex justify-between text-sm"><a href="{{ route('admin.enquiries.index', ['status' => $key]) }}" class="underline">{{ $row['label'] }}</a><span class="font-bold">{{ $row['count'] }}</span></div>
                    <div class="mt-1 h-2 rounded-full bg-gray-100"><div class="h-2 rounded-full {{ $key === 'won' ? 'bg-green-600' : ($key === 'lost' ? 'bg-gray-400' : 'bg-brand-navy') }}" style="width: {{ $pct($row['count']) }}%"></div></div>
                </li>
            @endforeach
        </ul>
    </section>
</div>

{{-- breakdowns --}}
<div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
    @foreach([
        ['Where they came from', 'Source / medium (latest touch)', $sources],
        ['Campaigns', 'utm_campaign', $campaigns],
        ['Forms', 'Which form was used', $forms],
        ['Popular journeys', 'Enquiries naming a package', $journeys],
    ] as [$title, $sub, $rows])
        <section class="{{ $card }}">
            <h2 class="text-xl">{{ $title }}</h2>
            <p class="text-xs text-ink-soft">{{ $sub }}</p>
            @if($rows->isEmpty())
                <p class="mt-4 text-sm text-ink-soft">Nothing yet.</p>
            @else
                @php $top = $rows->max(); @endphp
                <ul class="mt-4 space-y-3">
                    @foreach($rows as $label => $n)
                        <li class="text-sm">
                            <div class="flex justify-between gap-3"><span class="truncate" title="{{ $label }}">{{ $label }}</span><span class="font-bold">{{ $n }}</span></div>
                            <div class="mt-1 h-1.5 rounded-full bg-gray-100"><div class="h-1.5 rounded-full bg-brand-gold-dark" style="width: {{ round($n / $top * 100) }}%"></div></div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>
    @endforeach
</div>

<div class="mt-8 grid gap-6 xl:grid-cols-[2fr_1fr]">
    <section aria-labelledby="recent">
        <div class="mb-3 flex items-baseline justify-between"><h2 id="recent" class="text-2xl">Recent enquiries</h2><a href="{{ route('admin.enquiries.index') }}" class="text-sm underline">View all</a></div>
        <div class="overflow-x-auto rounded-xl bg-white shadow-sm ring-1 ring-black/5"><table class="w-full text-sm">
            <thead class="bg-gray-50 text-left"><tr><th class="p-3">When</th><th class="p-3">Name</th><th class="p-3">Form</th><th class="p-3">Source</th><th class="p-3">Status</th></tr></thead>
            <tbody>
            @forelse($recent as $e)
                <tr class="border-t">
                    <td class="whitespace-nowrap p-3" title="{{ $e->created_at->format('M j, Y H:i') }}">{{ $e->created_at->diffForHumans() }}</td>
                    <td class="p-3"><a class="font-bold underline" href="{{ route('admin.enquiries.show', $e) }}">{{ $e->fullName() }}</a><span class="block text-xs text-ink-soft">{{ $e->email }}</span></td>
                    <td class="p-3">{{ $e->formLabel() }}</td>
                    <td class="p-3">{{ $e->source ?: 'direct' }}</td>
                    <td class="p-3"><span class="rounded-full px-2.5 py-0.5 text-xs font-bold {{ $statusPill[$e->status] ?? 'bg-gray-100' }}">{{ config('gogermany.statuses')[$e->status] ?? $e->status }}</span></td>
                </tr>
            @empty
                <tr><td colspan="5" class="p-4 text-ink-soft">No enquiries yet.</td></tr>
            @endforelse
            </tbody></table></div>
    </section>

    <section aria-labelledby="health">
        <h2 id="health" class="mb-3 text-2xl">Site health</h2>
        <ul class="divide-y rounded-xl bg-white shadow-sm ring-1 ring-black/5">
            @foreach($health as [$ok, $title, $detail, $fix])
                <li class="flex gap-3 p-3 text-sm">
                    <span class="mt-0.5 grid size-5 shrink-0 place-items-center rounded-full text-xs font-bold {{ $ok ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-800' }}" aria-hidden="true">{{ $ok ? '✓' : '!' }}</span>
                    <div><p class="{{ $ok ? '' : 'font-bold' }}">{{ $title }}<span class="sr-only">: {{ $ok ? 'OK' : 'needs attention' }}</span></p>
                        @unless($ok)<p class="text-ink-soft">{{ $detail }} <a href="{{ route($fix) }}" class="font-bold text-brand-navy underline">Fix</a></p>@endunless</div>
                </li>
            @endforeach
        </ul>
        <div class="mt-4 flex flex-wrap gap-2">
            <a href="{{ route('admin.packages.create') }}" class="btn btn-outline btn-sm rounded-full">New package</a>
            <a href="{{ route('admin.posts.create') }}" class="btn btn-outline btn-sm rounded-full">New travel guide</a>
            <a href="{{ url('/') }}" target="_blank" rel="noopener" class="btn btn-outline btn-sm rounded-full">View site</a>
        </div>
    </section>
</div>
@endsection
