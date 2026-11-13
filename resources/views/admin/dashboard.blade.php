@extends('admin.layout')
@section('title', 'Dashboard')
@section('heading', 'Dashboard')

@section('content')
<div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
    @foreach([['Enquiries this week', $thisWeek, 'bg-white'], ['New (not yet actioned)', $newCount, 'bg-brand-gold'], ['New for more than 24 hours', $unactioned, $unactioned ? 'bg-red-100' : 'bg-white'], ['GoHighLevel sync failures', $ghlFailed, $ghlFailed ? 'bg-red-100' : 'bg-white']] as [$label, $n, $bg])
        <div class="{{ $bg }} rounded-xl shadow p-5"><p class="text-sm font-bold">{{ $label }}</p><p class="font-display font-extrabold text-4xl mt-1">{{ $n }}</p></div>
    @endforeach
</div>
<p class="mt-3 text-sm text-ink-soft">GoHighLevel integration is <strong>{{ $ghlOn ? 'ON' : 'OFF' }}</strong> {{ $ghlOn ? '' : '(add GHL_API_TOKEN and GHL_LOCATION_ID to .env to switch it on)' }}.</p>

<div class="mt-8 grid gap-6 lg:grid-cols-[1fr_16rem]">
    <section class="bg-white rounded-xl shadow overflow-hidden" aria-labelledby="recent">
        <h2 id="recent" class="text-xl p-4">Recent enquiries</h2>
        <div class="overflow-x-auto"><table class="w-full text-sm">
            <thead class="bg-gray-50 text-left"><tr><th class="p-3">When</th><th class="p-3">Name</th><th class="p-3">Form</th><th class="p-3">Status</th></tr></thead>
            <tbody>
            @forelse($recent as $e)
                <tr class="border-t"><td class="p-3 whitespace-nowrap">{{ $e->created_at->format('M j, H:i') }}</td>
                    <td class="p-3"><a class="font-bold underline" href="{{ route('admin.enquiries.show', $e) }}">{{ $e->fullName() }}</a></td>
                    <td class="p-3">{{ $e->formLabel() }}</td><td class="p-3">{{ config('gogermany.statuses')[$e->status] ?? $e->status }}</td></tr>
            @empty
                <tr><td colspan="4" class="p-4 text-ink-soft">No enquiries yet.</td></tr>
            @endforelse
            </tbody></table></div>
    </section>
    <aside class="bg-white rounded-xl shadow p-4" aria-labelledby="quick">
        <h2 id="quick" class="text-xl">Quick links</h2>
        <ul class="mt-3 space-y-2 font-bold underline">
            <li><a href="{{ route('admin.enquiries.index', ['status' => 'new']) }}">New enquiries</a></li>
            <li><a href="{{ route('admin.packages.create') }}">Add a package</a></li>
            <li><a href="{{ route('admin.quiz.index') }}">Edit the hero quiz</a></li>
            <li><a href="{{ route('admin.pages.index') }}">Edit pages (home, about...)</a></li>
            <li><a href="{{ route('admin.settings.edit') }}">Site settings</a></li>
        </ul>
    </aside>
</div>
@endsection
