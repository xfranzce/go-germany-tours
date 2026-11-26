<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;

/** Shared CRUD for tour types, destinations and travel styles (they feed the package filters, menus and landing pages). */
abstract class TaxonomyController extends AdminController
{
    /** @var class-string<Model> */
    protected string $model;

    protected string $routeBase;     // admin.tour-types

    protected string $label;         // Tour type

    protected bool $hasIcon = false;

    /** Extra text fields this taxonomy has: icon, highlights, inclusions, tagline, places. */
    protected array $extra = [];

    /**
     * Package tick list on the edit form: null (none), 'featured' (destinations: extra packages featured on the page,
     * packages whose main destination this is are always included) or 'members' (travel styles: the style's packages).
     */
    protected ?string $packagePicker = null;

    public function index()
    {
        return view('admin.taxonomy.index', [
            'items' => ($this->model)::withCount('packages')->orderBy('sort')->orderBy('name')->get(),
            'routeBase' => $this->routeBase, 'label' => $this->label,
        ]);
    }

    public function create()
    {
        return $this->form(new ($this->model)(['is_active' => true, 'sort' => 0]));
    }

    public function store(Request $request)
    {
        $item = new ($this->model)();
        $this->save($request, $item);

        return redirect()->route($this->routeBase.'.index')->with('status', "{$this->label} created.");
    }

    protected function form(Model $item)
    {
        $picker = null;
        if ($this->packagePicker) {
            $packages = \App\Models\Package::with(['destination:id,name', 'tourType:id,name'])->orderBy('title')->get(['id', 'title', 'destination_id', 'tour_type_id', 'is_published']);
            $relation = $this->packagePicker === 'featured' ? 'featuredPackages' : 'packages';
            $picker = [
                'packages' => $packages,
                'ticked' => $item->exists ? $item->{$relation}()->pluck('packages.id')->all() : [],
                'locked' => $this->packagePicker === 'featured' && $item->exists ? $packages->where('destination_id', $item->id)->pluck('id')->all() : [],
            ];
        }

        return view('admin.taxonomy.form', ['item' => $item, 'routeBase' => $this->routeBase, 'label' => $this->label, 'hasIcon' => $this->hasIcon, 'extra' => $this->extraFields(), 'picker' => $picker]);
    }

    protected function update_(Request $request, Model $item)
    {
        $this->save($request, $item);

        return redirect()->route($this->routeBase.'.index')->with('status', "{$this->label} saved.");
    }

    protected function destroy_(Model $item)
    {
        if ($item->packages()->exists()) {
            return back()->withErrors(['delete' => "This {$this->label} is used by packages. Move or delete those packages first."]);
        }
        $item->delete();

        return redirect()->route($this->routeBase.'.index')->with('status', "{$this->label} deleted.");
    }

    protected function extraFields(): array
    {
        return $this->extra ?: ($this->hasIcon ? ['icon', 'highlights', 'inclusions'] : []);
    }

    private function save(Request $request, Model $item): void
    {
        $this->stripBrandFromSeoTitle($request);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'slug' => ['nullable', 'string', 'max:100'],
            'summary' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string', 'max:50000'],
            'sort' => ['nullable', 'integer', 'min:0'],
            'icon' => ['nullable', 'string', 'max:30'],
            'highlights' => ['nullable', 'string', 'max:2000'],
            'inclusions' => ['nullable', 'string', 'max:1000'],
            'tagline' => ['nullable', 'string', 'max:120'],
            'places' => ['nullable', 'string', 'max:3000'],
            'package_ids' => ['nullable', 'array'],
            'package_ids.*' => ['integer', 'exists:packages,id'],
            'seo_title' => ['nullable', 'string', 'max:70'],
            'meta_description' => ['nullable', 'string', 'max:320'],
        ] + $this->imageRules());

        $item->fill([
            'name' => $data['name'],
            'slug' => $this->slug($this->model, $data['slug'] ?? null, $data['name'], $item->id),
            'summary' => $data['summary'] ?? null,
            'description' => $this->clean($data['description'] ?? null),
            'sort' => $data['sort'] ?? 0,
            'is_active' => $request->boolean('is_active'),
            'seo_title' => $data['seo_title'] ?? null,
            'meta_description' => $data['meta_description'] ?? null,
            'image' => $this->handleImage($request, 'image', $item->image),
        ]);
        foreach ($this->extraFields() as $field) {
            $item->{$field} = $data[$field] ?? null;
        }
        $item->save();

        if ($this->packagePicker && $request->boolean('package_picker')) {
            $ids = array_map('intval', $data['package_ids'] ?? []);
            if ($this->packagePicker === 'featured') {
                // packages whose main destination is this one are listed anyway, so only store the extra ones
                $own = \App\Models\Package::where('destination_id', $item->id)->pluck('id')->all();
                $item->featuredPackages()->sync(array_values(array_diff($ids, $own)));
            } else {
                $item->packages()->sync($ids);
            }
        }
    }
}
