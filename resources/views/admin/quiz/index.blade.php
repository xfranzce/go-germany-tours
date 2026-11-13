@extends('admin.layout')
@section('title', 'Hero quiz')
@section('heading', 'Hero quiz')

@section('content')
<p class="mb-5 text-ink-soft max-w-3xl">These are the questions shown in the "Start your adventure" box, in order. The contact-details form is always the final step. Tick answers go forward automatically; dropdowns have a Next button. Lower order numbers come first.
    The <strong>answer key</strong> names the answer in enquiries and in GoHighLevel, so it cannot be changed after creation.</p>

<div class="space-y-6">
@foreach($steps as $step)
    <form method="POST" action="{{ route('admin.quiz.update', $step) }}" class="bg-white rounded-xl shadow p-5 {{ $step->is_active ? '' : 'opacity-80' }}"
          x-data="repeater(@js($step->options->map(fn ($o) => ['id' => $o->id, 'label' => $o->label, 'icon' => $o->icon, 'sort' => $o->sort, 'is_active' => (bool) $o->is_active])->values()))">
        @csrf @method('PUT')
        <div class="grid gap-3 sm:grid-cols-[5rem_1fr_9rem] items-end">
            <div><label class="field-label" for="s{{ $step->id }}o">Order</label><input id="s{{ $step->id }}o" type="number" min="0" name="sort" value="{{ $step->sort }}" class="field-input"></div>
            <div><label class="field-label" for="s{{ $step->id }}q">Question <span class="text-ink-soft font-normal">(key: {{ $step->key }})</span></label><input id="s{{ $step->id }}q" name="question" value="{{ $step->question }}" class="field-input" maxlength="200" required></div>
            <div><label class="field-label" for="s{{ $step->id }}t">Answer style</label><select id="s{{ $step->id }}t" name="type" class="field-input"><option value="tiles" @selected($step->type === 'tiles')>Tiles</option><option value="dropdown" @selected($step->type === 'dropdown')>Dropdown</option></select></div>
        </div>
        <label class="inline-flex items-center gap-2 font-bold mt-3"><input type="checkbox" name="is_active" value="1" class="h-5 w-5 accent-brand-red" @checked($step->is_active)> Step is active</label>

        <h3 class="font-bold mt-4 mb-2">Answer options</h3>
        <template x-for="(o, i) in items" :key="i">
            <div class="flex flex-wrap items-end gap-2 mb-2">
                <input type="hidden" :name="'options[' + i + '][id]'" :value="o.id">
                <div class="w-16"><label class="field-label text-xs" :for="'o' + {{ $step->id }} + '-' + i + 's'">Order</label><input :id="'o' + {{ $step->id }} + '-' + i + 's'" type="number" min="0" class="field-input" :name="'options[' + i + '][sort]'" x-model="o.sort"></div>
                <div class="w-20"><label class="field-label text-xs" :for="'o' + {{ $step->id }} + '-' + i + 'i'">Icon</label><input :id="'o' + {{ $step->id }} + '-' + i + 'i'" class="field-input" :name="'options[' + i + '][icon]'" x-model="o.icon" maxlength="10" placeholder="emoji"></div>
                <div class="flex-1 min-w-40"><label class="field-label text-xs" :for="'o' + {{ $step->id }} + '-' + i + 'l'">Answer text</label><input :id="'o' + {{ $step->id }} + '-' + i + 'l'" class="field-input" :name="'options[' + i + '][label]'" x-model="o.label" maxlength="80"></div>
                <label class="inline-flex items-center gap-1 text-sm font-bold pb-3"><input type="checkbox" value="1" class="accent-brand-red" :name="'options[' + i + '][is_active]'" :checked="o.is_active" @change="o.is_active = $event.target.checked"> On</label>
                <button type="button" class="btn btn-outline btn-sm mb-1" @click="remove(i)">Remove</button>
            </div>
        </template>
        <button type="button" class="btn btn-outline btn-sm" @click="add({id: '', label: '', icon: '', sort: items.length, is_active: true})">+ Add an option</button>

        <div class="mt-5 flex flex-wrap gap-3"><button class="btn btn-primary btn-sm">Save step</button></div>
    </form>
    <form method="POST" action="{{ route('admin.quiz.destroy', $step) }}" x-data="{ sure: false }" @submit="if (!sure) { $event.preventDefault(); sure = true }" class="-mt-3 mb-2 text-right">
        @csrf @method('DELETE')
        <button class="text-sm underline" :class="sure && 'text-brand-red-dark font-bold'" x-text="sure ? 'Click again to delete this step' : 'Delete step {{ $loop->iteration }}'">Delete step</button>
    </form>
@endforeach

    <form method="POST" action="{{ route('admin.quiz.store') }}" class="bg-brand-custard rounded-xl p-5 grid gap-3 sm:grid-cols-[12rem_1fr_9rem_auto] items-end">
        @csrf
        <div><label class="field-label" for="nk">Answer key</label><input id="nk" name="key" class="field-input" placeholder="e.g. interests" pattern="[a-z][a-z0-9_]*" required></div>
        <div><label class="field-label" for="nq">New question</label><input id="nq" name="question" class="field-input" maxlength="200" required></div>
        <div><label class="field-label" for="nt">Style</label><select id="nt" name="type" class="field-input"><option value="tiles">Tiles</option><option value="dropdown">Dropdown</option></select></div>
        <button class="btn btn-primary">Add step</button>
    </form>
</div>
@endsection
