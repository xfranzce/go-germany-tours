<?php

namespace App\Http\Controllers;

use App\Models\Destination;
use App\Models\Package;
use App\Models\TourType;
use App\Support\Seo;
use Illuminate\Http\Request;

class PackageController extends Controller
{
    public function index(Request $request)
    {
        $types = TourType::active()->get();
        $destinations = Destination::active()->get();

        $query = Package::published()->with(['tourType', 'destination', 'images']);
        $type = $types->firstWhere('slug', $request->query('type'));
        $dest = $destinations->firstWhere('slug', $request->query('destination'));
        $type && $query->where('tour_type_id', $type->id);
        $dest && $query->where('destination_id', $dest->id);

        $filtered = $type || $dest;

        return view('packages.index', [
            'packages' => $query->get(), 'types' => $types, 'destinations' => $destinations,
            'type' => $type, 'dest' => $dest,
            'seo' => Seo::make('Germany Tour Packages: Self Drive, Chauffeur and Coach',
                'Browse Germany tour packages for Quedlinburg, Saxon Switzerland, Cochem, Hiddensee and Gengenbach. Filter by tour type and destination, then request a free quote.',
                ['canonical' => route('packages.index'), 'noindex' => $filtered,
                    'breadcrumbs' => [['name' => 'Home', 'url' => url('/')], ['name' => 'Packages', 'url' => route('packages.index')]]]),
        ]);
    }

    public function show(Package $package)
    {
        abort_unless($package->is_published, 404);
        $package->load(['tourType', 'destination', 'images', 'faqs']);

        $schema = [
            '@context' => 'https://schema.org', '@type' => 'TouristTrip',
            'name' => $package->title, 'description' => $package->summary,
            'touristType' => $package->tourType->name,
            'itinerary' => ['@type' => 'ItemList', 'itemListElement' => collect($package->itinerary ?? [])->values()->map(fn ($d, $i) => [
                '@type' => 'ListItem', 'position' => $i + 1, 'name' => $d['title'] ?? '', 'description' => $d['body'] ?? '',
            ])->all()],
        ];
        // Sample prices are placeholders: never publish them as an Offer.
        if ($package->price_from && ! $package->is_sample) {
            $schema['offers'] = ['@type' => 'Offer', 'price' => (string) $package->price_from, 'priceCurrency' => $package->currency, 'url' => $package->url()];
        }

        return view('packages.show', [
            'package' => $package,
            'related' => Package::published()->where('id', '!=', $package->id)->where('destination_id', $package->destination_id)->with(['tourType', 'destination', 'images'])->take(3)->get(),
            'seo' => Seo::make($package->seo_title ?: $package->title, $package->meta_description ?: $package->summary, [
                'image' => $package->og_image ?: $package->coverPath(), 'noindex' => $package->noindex, 'type' => 'article',
                'breadcrumbs' => [['name' => 'Home', 'url' => url('/')], ['name' => 'Packages', 'url' => route('packages.index')], ['name' => $package->title, 'url' => $package->url()]],
                'schema' => [$schema, Seo::faqSchema($package->faqs)],
            ]),
        ]);
    }
}
