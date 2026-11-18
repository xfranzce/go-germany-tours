@extends('admin.layout')
@php $isNew = ! $item->exists; @endphp
@section('title', ($isNew ? 'New ' : 'Edit ').strtolower($label))
@section('heading', $isNew ? 'New '.strtolower($label) : 'Edit: '.$item->name)
@section('actions')<a class="btn btn-outline btn-sm" href="{{ route($routeBase.'.index') }}">&larr; Back</a>@endsection

@section('content')
<form method="POST" enctype="multipart/form-data" class="space-y-6" action="{{ $isNew ? route($routeBase.'.store') : route($routeBase.'.update', $item) }}">
    @csrf @if(! $isNew) @method('PUT') @endif
    <section class="bg-white rounded-xl shadow p-5 grid gap-4 sm:grid-cols-2">
        <x-admin.field name="name" label="Name" :value="$item->name" required/>
        <x-admin.field name="slug" label="URL slug" :value="$item->slug" help="Blank builds it from the name. Changing it changes the page address."/>
        @if($hasIcon)<x-admin.field name="icon" label="Icon" type="select" :options="['car' => 'Car', 'steering' => 'Compass / guided', 'bus' => 'Coach']" :value="$item->icon"/>@endif
        <x-admin.field name="sort" label="Order" type="number" :value="$item->sort"/>
        <x-admin.field name="summary" label="Short summary" type="textarea" :rows="2" :value="$item->summary" maxlength="500" class="sm:col-span-2"/>
        @if($hasIcon)<x-admin.field name="inclusions" label="Inclusions (one per line, shown on the home page card)" type="textarea" :rows="4" :value="$item->inclusions" maxlength="1000" class="sm:col-span-2" help="Short ticks such as Premium Rental Car or 3-4 Star Hotels."/>@endif
        @if($hasIcon)<x-admin.field name="highlights" label="What to expect (one point per line)" type="textarea" :rows="6" :value="$item->highlights" maxlength="2000" class="sm:col-span-2" help="Shown as a checklist under the About text on the tour type page."/>@endif
        <div class="sm:col-span-2"><x-admin.rich name="description" label="Page text" :value="$item->description"/></div>
        <x-admin.image :current="$item->image" label="Picture" class="sm:col-span-2"/>
        <x-admin.field name="seo_title" label="SEO title (max 70)" :value="$item->seo_title" maxlength="70"/>
        <x-admin.field name="meta_description" label="Meta description (max 320)" :value="$item->meta_description" maxlength="320"/>
        <x-admin.field name="is_active" label="Active (shown on the site)" type="checkbox" :value="$item->is_active"/>
    </section>
    <button class="btn btn-primary">Save</button>
</form>
@unless($isNew)
<form method="POST" action="{{ route($routeBase.'.destroy', $item) }}" class="mt-6" x-data="{ sure: false }" @submit="if (!sure) { $event.preventDefault(); sure = true }">
    @csrf @method('DELETE')
    <button class="btn btn-outline btn-sm" :class="sure && '!bg-brand-red !text-white !border-brand-red'" x-text="sure ? 'Click again to delete' : 'Delete'">Delete</button>
</form>
@endunless
@endsection
