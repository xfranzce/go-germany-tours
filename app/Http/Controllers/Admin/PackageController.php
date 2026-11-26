<?php

namespace App\Http\Controllers\Admin;

use App\Models\Destination;
use App\Models\Faq;
use App\Models\Package;
use App\Models\PackageImage;
use App\Models\TourType;
use App\Services\ImageService;
use Illuminate\Http\Request;

class PackageController extends AdminController
{
    public function index(Request $request)
    {
        $view = $request->input('view') === 'trash' ? 'trash' : 'all';
        $types = TourType::orderBy('sort')->get();
        $type = $types->firstWhere('slug', $request->input('type'));   // optional filter: show one tour type only
        $typeCounts = Package::query()->when($view === 'trash', fn ($q) => $q->onlyTrashed())->selectRaw('tour_type_id, count(*) as n')->groupBy('tour_type_id')->pluck('n', 'tour_type_id');
        $packages = Package::with(['tourType', 'destination'])
            ->when($view === 'trash', fn ($q) => $q->onlyTrashed())
            ->when($type, fn ($q) => $q->where('tour_type_id', $type->id))
            ->when($request->filled('q'), fn ($q) => $q->where('title', 'like', '%'.$request->input('q').'%'))
            ->orderBy('sort')->orderBy('title')->get();
        $trashCount = Package::onlyTrashed()->count();

        return view('admin.packages.index', compact('packages', 'view', 'trashCount', 'types', 'type', 'typeCounts'));
    }

    public function create()
    {
        return view('admin.packages.form', $this->formData(new Package(['currency' => 'USD', 'nights' => 5, 'is_published' => false, 'sort' => 0])));
    }

    public function store(Request $request)
    {
        $package = new Package();
        $this->save($request, $package);

        return redirect()->route('admin.packages.edit', $package)->with('status', 'Package created. Add gallery images below.');
    }

    public function edit(Package $package)
    {
        return view('admin.packages.form', $this->formData($package->load(['images', 'faqs'])));
    }

    public function update(Request $request, Package $package)
    {
        $this->save($request, $package);

        return redirect()->route('admin.packages.edit', $package)->with('status', 'Package saved.');
    }

    public function destroy(Package $package)
    {
        $package->delete();

        return redirect()->route('admin.packages.index')->with('status', 'Package moved to trash. You can restore it from the Trash tab.');
    }

    /** Bulk actions on the ticked packages: trash, restore, publish, unpublish, sample, unsample, feature, unfeature. */
    public function bulk(Request $request, string $action)
    {
        $ids = $request->validate(['ids' => ['required', 'array', 'min:1'], 'ids.*' => ['integer']], ['ids.required' => 'Tick at least one package first.'])['ids'];
        $packages = Package::withTrashed()->whereIn('id', $ids);

        $changes = [
            'publish' => ['is_published' => true], 'unpublish' => ['is_published' => false],
            'sample' => ['is_sample' => true], 'unsample' => ['is_sample' => false],
            'feature' => ['is_featured' => true], 'unfeature' => ['is_featured' => false],
        ];
        if ($action === 'trash') {
            $count = Package::whereIn('id', $ids)->get()->each->delete()->count();
            $done = 'moved to trash';
        } elseif ($action === 'restore') {
            $count = $packages->onlyTrashed()->get()->each->restore()->count();
            $done = 'restored';
        } else {
            $count = Package::whereIn('id', $ids)->get()->each->update($changes[$action])->count();   // one by one, so each change is recorded in the History
            $done = ['publish' => 'published', 'unpublish' => 'unpublished', 'sample' => 'marked as sample', 'unsample' => 'marked as not sample', 'feature' => 'featured on the home page', 'unfeature' => 'unfeatured'][$action];
        }

        return back()->with('status', $count.' '.($count === 1 ? 'package' : 'packages').' '.$done.'.');
    }

    public function toggle(Package $package, string $field)
    {
        $package->update([$field => ! $package->$field]);

        return back()->with('status', $field === 'is_published' ? ($package->is_published ? 'Package published.' : 'Package unpublished.') : ($package->is_featured ? 'Package featured on the home page.' : 'Package removed from featured.'));
    }

    public function duplicate(Package $package)
    {
        $copy = $package->replicate();
        $copy->title = $package->title.' (copy)';
        $copy->slug = $this->slug(Package::class, $package->slug.'-copy', 'package-copy');
        $copy->is_published = false;
        $copy->is_featured = false;
        $copy->save();
        $copy->travelStyles()->sync($package->travelStyles()->pluck('travel_styles.id'));
        foreach ($package->images as $img) {
            PackageImage::create(['package_id' => $copy->id, 'path' => $img->path, 'alt' => $img->alt, 'sort' => $img->sort]);
        }
        foreach ($package->faqs as $faq) {
            Faq::create(['package_id' => $copy->id, 'question' => $faq->question, 'answer' => $faq->answer, 'sort' => $faq->sort]);
        }

        return redirect()->route('admin.packages.edit', $copy)->with('status', 'Duplicated as an unpublished draft.');
    }

    public function reorder(Request $request)
    {
        foreach ((array) $request->input('sort', []) as $id => $n) {
            Package::whereKey($id)->update(['sort' => (int) $n]);
        }
        \App\Models\ActivityLog::record('updated', null, null, 'Changed the order of the packages');

        return back()->with('status', 'Order saved.');
    }

    private function formData(Package $package): array
    {
        return [
            'package' => $package, 'types' => TourType::orderBy('sort')->pluck('name', 'id')->all(),
            'destinations' => Destination::orderBy('sort')->pluck('name', 'id')->all(),
            'styles' => \App\Models\TravelStyle::orderBy('sort')->pluck('name', 'id')->all(),
            'styleIds' => $package->exists ? $package->travelStyles()->pluck('travel_styles.id')->all() : [],
        ];
    }

    private function save(Request $request, Package $package): void
    {
        $rules = [
            'title' => ['required', 'string', 'max:150'],
            'slug' => ['nullable', 'string', 'max:150'],
            'tour_type_id' => ['required', 'exists:tour_types,id'],
            'destination_id' => ['required', 'exists:destinations,id'],
            'travel_styles' => ['nullable', 'array'],
            'travel_styles.*' => ['integer', 'exists:travel_styles,id'],
            'nights' => ['required', 'integer', 'min:1', 'max:60'],
            'price_from' => ['nullable', 'numeric', 'min:0', 'max:1000000'],
            'price_was' => ['nullable', 'numeric', 'min:0', 'max:1000000', function ($attr, $value, $fail) use ($request) {
                $current = $request->input('price_from');
                if (filled($value) && (! is_numeric($current) || (float) $value <= (float) $current)) {
                    $fail('The From price is the old price shown crossed out, so it must be higher than the current price (and the current price must be filled in). To show a discount, put the higher price here and the lower price in Current price per person. Otherwise leave this empty.');
                }
            }],
            'rating' => ['nullable', 'numeric', 'min:0', 'max:5'],
            'review_count' => ['nullable', 'integer', 'min:0', 'max:1000000'],
            'currency' => ['required', 'in:USD,EUR,GBP,CAD,AUD'],
            'summary' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string', 'max:50000'],
            'highlights' => ['nullable', 'string', 'max:5000'],
            'inclusions' => ['nullable', 'string', 'max:5000'],
            'exclusions' => ['nullable', 'string', 'max:5000'],
            'sort' => ['nullable', 'integer', 'min:0'],
            'seo_title' => ['nullable', 'string', 'max:70'],
            'meta_description' => ['nullable', 'string', 'max:320'],
            'og_image' => ['nullable', 'string', 'max:255'],
            'itinerary' => ['nullable', 'array', 'max:60'],
            'itinerary.*.title' => ['nullable', 'string', 'max:150'],
            'itinerary.*.body' => ['nullable', 'string', 'max:3000'],
            'faqs' => ['nullable', 'array', 'max:30'],
            'faqs.*.question' => ['nullable', 'string', 'max:250'],
            'faqs.*.answer' => ['nullable', 'string', 'max:3000'],
            'images' => ['nullable', 'array'],
            'images.*.alt' => ['required', 'string', 'max:200'],
            'images.*.sort' => ['nullable', 'integer'],
            'new_images' => ['nullable', 'array', 'max:6'],
            'new_images.*.file' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:8192', 'dimensions:min_width=400,min_height=300'],
            'new_images.*.alt' => ['required_with:new_images.*.file', 'nullable', 'string', 'max:200'],
        ];
        $this->stripBrandFromSeoTitle($request);
        $data = $request->validate($rules);

        $package->fill([
            'title' => $data['title'],
            'slug' => $this->slug(Package::class, $data['slug'] ?? null, $data['title'], $package->id),
            'tour_type_id' => $data['tour_type_id'], 'destination_id' => $data['destination_id'],
            'nights' => $data['nights'], 'price_from' => $data['price_from'] ?? null, 'price_was' => $data['price_was'] ?? null, 'rating' => $data['rating'] ?? null, 'review_count' => $data['review_count'] ?? null, 'currency' => $data['currency'],
            'summary' => $data['summary'] ?? null,
            'description' => $this->clean($data['description'] ?? null),
            'highlights' => $this->lines($data['highlights'] ?? ''),
            'inclusions' => $this->lines($data['inclusions'] ?? ''),
            'exclusions' => $this->lines($data['exclusions'] ?? ''),
            'itinerary' => collect($data['itinerary'] ?? [])->filter(fn ($d) => filled($d['title'] ?? null))
                ->map(fn ($d) => ['title' => $d['title'], 'body' => $d['body'] ?? ''])->values()->all(),
            'is_published' => $request->boolean('is_published'), 'is_featured' => $request->boolean('is_featured'),
            'is_sample' => $request->boolean('is_sample'), 'noindex' => $request->boolean('noindex'),
            'sort' => $data['sort'] ?? 0,
            'seo_title' => $data['seo_title'] ?? null, 'meta_description' => $data['meta_description'] ?? null,
            'og_image' => $data['og_image'] ?? null,
        ])->save();

        $package->travelStyles()->sync($data['travel_styles'] ?? []);

        // FAQs: replaced wholesale
        $package->faqs()->delete();
        foreach (collect($data['faqs'] ?? [])->filter(fn ($f) => filled($f['question'] ?? null) && filled($f['answer'] ?? null))->values() as $i => $f) {
            Faq::create(['package_id' => $package->id, 'question' => $f['question'], 'answer' => $f['answer'], 'sort' => $i]);
        }

        // Existing gallery images: alt, order, delete
        foreach ($package->images()->get() as $img) {
            $row = $request->input("images.{$img->id}");
            if (! $row || ! empty($row['delete'])) {
                $img->delete();

                continue;
            }
            $img->update(['alt' => $row['alt'], 'sort' => (int) ($row['sort'] ?? 0)]);
        }
        // New uploads
        $next = (int) $package->images()->max('sort') + 1;
        foreach ($request->file('new_images', []) as $i => $row) {
            if (! isset($row['file'])) {
                continue;
            }
            $media = app(ImageService::class)->store($row['file'], (string) $request->input("new_images.{$i}.alt"));
            PackageImage::create(['package_id' => $package->id, 'path' => $media->path, 'alt' => $media->alt, 'sort' => $next++]);
        }
    }
}
