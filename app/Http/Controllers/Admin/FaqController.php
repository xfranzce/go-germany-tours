<?php

namespace App\Http\Controllers\Admin;

use App\Models\Destination;
use App\Models\Faq;
use App\Models\TourType;
use App\Models\TravelStyle;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Home page, tour type, destination and travel style FAQs (package FAQs are edited inside each package). */
class FaqController extends AdminController
{
    public function index()
    {
        return view('admin.faqs.index', ['items' => Faq::with(['tourType', 'destination', 'travelStyle'])->whereNull('package_id')
            ->orderByRaw('COALESCE(tour_type_id, destination_id, travel_style_id) IS NOT NULL')
            ->orderBy('tour_type_id')->orderBy('destination_id')->orderBy('travel_style_id')->orderBy('sort')->get()]);
    }

    public function create()
    {
        return view('admin.faqs.form', ['item' => new Faq(['is_active' => true, 'sort' => (int) Faq::whereNull('package_id')->max('sort') + 1]), 'places' => $this->places()]);
    }

    public function store(Request $request)
    {
        Faq::create($this->data($request));

        return redirect()->route('admin.faqs.index')->with('status', 'FAQ added.');
    }

    public function edit(Faq $faq)
    {
        abort_if($faq->package_id, 404);

        return view('admin.faqs.form', ['item' => $faq, 'places' => $this->places()]);
    }

    public function update(Request $request, Faq $faq)
    {
        abort_if($faq->package_id, 404);
        $faq->update($this->data($request));

        return redirect()->route('admin.faqs.index')->with('status', 'FAQ saved.');
    }

    public function destroy(Faq $faq)
    {
        abort_if($faq->package_id, 404);
        $faq->delete();

        return back()->with('status', 'FAQ deleted.');
    }

    /** "Shows on" choices: '' = home page, 'tour_type_id:3' = that tour type page, and so on. */
    private function places(): array
    {
        $places = ['' => 'Home page (general)'];
        foreach ([[TourType::class, 'tour_type_id'], [Destination::class, 'destination_id'], [TravelStyle::class, 'travel_style_id']] as [$model, $col]) {
            foreach ($model::orderBy('name')->pluck('name', 'id') as $id => $name) {
                $places["$col:$id"] = Faq::PAGES[$col].': '.$name;
            }
        }

        return $places;
    }

    private function data(Request $request): array
    {
        $d = $request->validate([
            'question' => ['required', 'string', 'max:250'], 'answer' => ['required', 'string', 'max:3000'], 'sort' => ['nullable', 'integer', 'min:0'],
            'place' => ['nullable', 'string', Rule::in(array_keys($this->places()))],
        ]);

        $links = array_fill_keys(array_keys(Faq::PAGES), null);
        if (! empty($d['place'])) {
            [$col, $id] = explode(':', $d['place']);
            $links[$col] = (int) $id;
        }
        unset($d['place']);

        return $d + $links + ['sort' => $d['sort'] ?? 0, 'is_active' => $request->boolean('is_active')];
    }
}
