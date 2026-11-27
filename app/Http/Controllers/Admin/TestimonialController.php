<?php

namespace App\Http\Controllers\Admin;

use App\Models\Destination;
use App\Models\Testimonial;
use App\Models\TourType;
use App\Models\TravelStyle;
use Illuminate\Http\Request;

class TestimonialController extends AdminController
{
    public function index()
    {
        return view('admin.testimonials.index', ['items' => Testimonial::with(['destination', 'tourType', 'travelStyle'])->orderBy('sort')->get()]);
    }

    public function create()
    {
        return view('admin.testimonials.form', $this->formData(new Testimonial(['is_active' => true, 'is_placeholder' => false, 'sort' => 0])));
    }

    public function store(Request $request)
    {
        Testimonial::create($this->data($request));

        return redirect()->route('admin.testimonials.index')->with('status', 'Testimonial added.');
    }

    public function edit(Testimonial $testimonial)
    {
        return view('admin.testimonials.form', $this->formData($testimonial));
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
            'destination_id' => ['nullable', 'integer', 'exists:destinations,id'],
            'tour_type_id' => ['nullable', 'integer', 'exists:tour_types,id'],
            'travel_style_id' => ['nullable', 'integer', 'exists:travel_styles,id'],
        ]);

        foreach (Testimonial::LINKS as $col) {
            $d[$col] = $d[$col] ?? null;
        }

        return $d + ['sort' => $d['sort'] ?? 0, 'is_active' => $request->boolean('is_active'), 'is_placeholder' => $request->boolean('is_placeholder')];
    }

    /** The item plus the dropdown lists for "Which page is this review about?". */
    private function formData(Testimonial $item): array
    {
        return [
            'item' => $item,
            'destinations' => Destination::orderBy('name')->pluck('name', 'id'),
            'tourTypes' => TourType::orderBy('name')->pluck('name', 'id'),
            'travelStyles' => TravelStyle::orderBy('name')->pluck('name', 'id'),
        ];
    }
}
