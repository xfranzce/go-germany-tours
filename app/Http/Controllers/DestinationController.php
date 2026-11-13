<?php

namespace App\Http\Controllers;

use App\Models\Destination;
use App\Support\Seo;

class DestinationController extends Controller
{
    public function show(string $slug)
    {
        $destination = Destination::where('slug', $slug)->where('is_active', true)->firstOrFail();
        $packages = $destination->packages()->published()->with(['tourType', 'destination', 'images'])->get();

        return view('destinations.show', [
            'destination' => $destination, 'packages' => $packages,
            'others' => Destination::active()->where('id', '!=', $destination->id)->get(),
            'seo' => Seo::make($destination->seo_title ?: $destination->name, $destination->meta_description ?: $destination->summary, [
                'image' => $destination->image,
                'breadcrumbs' => [['name' => 'Home', 'url' => url('/')], ['name' => 'Destinations', 'url' => route('packages.index')], ['name' => $destination->name, 'url' => route('destinations.show', $destination->slug)]],
            ]),
        ]);
    }
}
