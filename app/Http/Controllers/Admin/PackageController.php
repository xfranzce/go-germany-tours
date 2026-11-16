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
        $packages = Package::with(['tourType', 'destination'])
            ->when($request->filled('q'), fn ($q) => $q->where('title', 'like', '%'.$request->input('q').'%'))
            ->orderBy('sort')->orderBy('title')->get();

        return view('admin.packages.index', compact('packages'));
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

        return redirect()->route('admin.packages.index')->with('status', 'Package deleted.');
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

        return back()->with('status', 'Order saved.');
    }

    private function formData(Package $package): array
    {
        return [
            'package' => $package, 'types' => TourType::orderBy('sort')->pluck('name', 'id')->all(),
            'destinations' => Destination::orderBy('sort')->pluck('name', 'id')->all(),
        ];
    }

    private function save(Request $request, Package $package): void
    {
        $rules = [
            'title' => ['required', 'string', 'max:150'],
            'slug' => ['nullable', 'string', 'max:150'],
            'tour_type_id' => ['required', 'exists:tour_types,id'],
            'destination_id' => ['required', 'exists:destinations,id'],
            'nights' => ['required', 'integer', 'min:1', 'max:60'],
            'price_from' => ['nullable', 'numeric', 'min:0', 'max:1000000'],
            'price_was' => ['nullable', 'numeric', 'min:0', 'max:1000000'],
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
