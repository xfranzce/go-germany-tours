@extends('admin.layout')
@section('title', 'Packages')
@section('heading', 'Packages')
@section('actions')<a class="btn btn-primary btn-sm" href="{{ route('admin.packages.create') }}">Add package</a>@endsection

@section('content')
<form method="POST" action="{{ route('admin.packages.reorder') }}" class="bg-white rounded-xl shadow overflow-x-auto">
    @csrf @method('PUT')
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-left"><tr><th class="p-3 w-20">Order</th><th class="p-3">Package</th><th class="p-3">Type / destination</th><th class="p-3">Price</th><th class="p-3">Status</th><th class="p-3 text-right">Actions</th></tr></thead>
        <tbody>
        @forelse($packages as $p)
            <tr class="border-t align-top">
                <td class="p-3"><label class="sr-only" for="o{{ $p->id }}">Order for {{ $p->title }}</label><input id="o{{ $p->id }}" type="number" min="0" name="sort[{{ $p->id }}]" value="{{ $p->sort }}" class="field-input !min-h-9 !py-1 w-16"></td>
                <td class="p-3"><a class="font-bold underline" href="{{ route('admin.packages.edit', $p) }}">{{ $p->title }}</a>@if($p->is_sample) <span class="bg-brand-gold rounded px-1.5 text-xs font-bold">SAMPLE</span>@endif<br><span class="text-ink-soft">{{ $p->nights }} nights</span></td>
                <td class="p-3">{{ $p->tourType->name }}<br><span class="text-ink-soft">{{ $p->destination->name }}</span></td>
                <td class="p-3">{{ $p->price_from ? number_format($p->price_from).' '.$p->currency : '-' }}</td>
                <td class="p-3">{{ $p->is_published ? 'Published' : 'Draft' }}{{ $p->is_featured ? ', featured' : '' }}</td>
                <td class="p-3">
                    <div class="flex flex-wrap gap-1 justify-end">
                        <button type="submit" formaction="{{ route('admin.packages.toggle', [$p, 'is_published']) }}" formmethod="POST" class="btn btn-outline btn-sm" name="_method" value="POST">{{ $p->is_published ? 'Unpublish' : 'Publish' }}</button>
                        <button type="submit" formaction="{{ route('admin.packages.toggle', [$p, 'is_featured']) }}" formmethod="POST" class="btn btn-outline btn-sm" name="_method" value="POST">{{ $p->is_featured ? 'Unfeature' : 'Feature' }}</button>
                        <button type="submit" formaction="{{ route('admin.packages.duplicate', $p) }}" formmethod="POST" class="btn btn-outline btn-sm" name="_method" value="POST">Duplicate</button>
                        <a class="btn btn-primary btn-sm" href="{{ route('admin.packages.edit', $p) }}">Edit</a>
                    </div>
                </td>
            </tr>
        @empty
            <tr><td colspan="6" class="p-6 text-ink-soft">No packages yet.</td></tr>
        @endforelse
        </tbody>
    </table>
    @if($packages->isNotEmpty())<div class="p-3 border-t"><button class="btn btn-gold btn-sm">Save order</button> <span class="text-xs text-ink-soft">Lower numbers show first.</span></div>@endif
</form>
@endsection
