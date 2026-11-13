<?php

namespace App\Http\Controllers\Admin;

use App\Models\Media;
use App\Services\ImageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MediaController extends AdminController
{
    public function index()
    {
        return view('admin.media.index', ['items' => Media::latest()->paginate(24)]);
    }

    public function store(Request $request, ImageService $images)
    {
        $request->validate([
            'file' => ['required', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:8192', 'dimensions:min_width=400,min_height=300'],
            'alt' => ['required', 'string', 'max:200'],
        ]);
        $images->store($request->file('file'), $request->input('alt'));

        return back()->with('status', 'Image uploaded, resized and converted to WebP.');
    }

    public function update(Request $request, Media $media)
    {
        $media->update($request->validate(['alt' => ['required', 'string', 'max:200']]));

        return back()->with('status', 'Alt text saved.');
    }

    public function destroy(Media $media, ImageService $images)
    {
        if ($this->inUse($media->path)) {
            return back()->withErrors(['delete' => 'That image is still used on the site. Replace it there first.']);
        }
        $images->delete($media);

        return back()->with('status', 'Image deleted.');
    }

    private function inUse(string $path): bool
    {
        $checks = [['package_images', 'path'], ['packages', 'og_image'], ['destinations', 'image'], ['tour_types', 'image'],
            ['posts', 'image'], ['pages', 'og_image'], ['settings', 'value']];
        foreach ($checks as [$table, $col]) {
            if (DB::table($table)->where($col, $path)->exists()) {
                return true;
            }
        }

        return DB::table('pages')->where('data', 'like', '%'.$path.'%')->exists();
    }
}
