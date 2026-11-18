<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;

/** Shared CRUD for tour types and destinations (both feed the package filters and landing pages). */
abstract class TaxonomyController extends AdminController
{
    /** @var class-string<Model> */
    protected string $model;

    protected string $routeBase;     // admin.tour-types

    protected string $label;         // Tour type

    protected bool $hasIcon = false;

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
        return view('admin.taxonomy.form', ['item' => $item, 'routeBase' => $this->routeBase, 'label' => $this->label, 'hasIcon' => $this->hasIcon]);
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

    private function save(Request $request, Model $item): void
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'slug' => ['nullable', 'string', 'max:100'],
            'summary' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string', 'max:50000'],
            'sort' => ['nullable', 'integer', 'min:0'],
            'icon' => ['nullable', 'string', 'max:30'],
            'highlights' => ['nullable', 'string', 'max:2000'],
            'inclusions' => ['nullable', 'string', 'max:1000'],
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
        if ($this->hasIcon) {
            $item->icon = $data['icon'] ?? null;
            $item->highlights = $data['highlights'] ?? null;
            $item->inclusions = $data['inclusions'] ?? null;
        }
        $item->save();
    }
}
