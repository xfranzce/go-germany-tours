<?php

namespace App\Http\Controllers\Admin;

use App\Models\Testimonial;
use Illuminate\Http\Request;

class TestimonialController extends AdminController
{
    public function index()
    {
        return view('admin.testimonials.index', ['items' => Testimonial::orderBy('sort')->get()]);
    }

    public function create()
    {
        return view('admin.testimonials.form', ['item' => new Testimonial(['is_active' => true, 'is_placeholder' => false, 'sort' => 0])]);
    }

    public function store(Request $request)
    {
        Testimonial::create($this->data($request));

        return redirect()->route('admin.testimonials.index')->with('status', 'Testimonial added.');
    }

    public function edit(Testimonial $testimonial)
    {
        return view('admin.testimonials.form', ['item' => $testimonial]);
    }

    public function update(Request $request, Testimonial $testimonial)
    {
        $testimonial->update($this->data($request));

        return redirect()->route('admin.testimonials.index')->with('status', 'Testimonial saved.');
    }

    public function destroy(Testimonial $testimonial)
    {
        $testimonial->delete();

        return back()->with('status', 'Testimonial deleted.');
    }

    private function data(Request $request): array
    {
        $d = $request->validate([
            'name' => ['required', 'string', 'max:100'], 'location' => ['nullable', 'string', 'max:100'], 'source' => ['nullable', 'string', 'max:100'],
            'quote' => ['required', 'string', 'max:1000'], 'sort' => ['nullable', 'integer', 'min:0'],
        ]);

        return $d + ['sort' => $d['sort'] ?? 0, 'is_active' => $request->boolean('is_active'), 'is_placeholder' => $request->boolean('is_placeholder')];
    }
}
