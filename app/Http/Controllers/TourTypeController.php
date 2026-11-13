<?php

namespace App\Http\Controllers;

use App\Models\TourType;
use App\Support\Seo;

class TourTypeController extends Controller
{
    public function show(string $slug)
    {
        $type = TourType::where('slug', $slug)->where('is_active', true)->firstOrFail();
        $packages = $type->packages()->published()->with(['tourType', 'destination', 'images'])->get();

        return view('tours.show', [
            'type' => $type, 'packages' => $packages, 'others' => TourType::active()->where('id', '!=', $type->id)->get(),
            'seo' => Seo::make($type->seo_title ?: "{$type->name} Tours", $type->meta_description ?: $type->summary, [
                'image' => $type->image,
                'breadcrumbs' => [['name' => 'Home', 'url' => url('/')], ['name' => 'Tours', 'url' => route('packages.index')], ['name' => $type->name, 'url' => route('tours.show', $type->slug)]],
            ]),
        ]);
    }
}
