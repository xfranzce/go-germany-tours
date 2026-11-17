<?php

namespace App\Http\Controllers\Admin;

use App\Models\Faq;
use App\Models\TourType;
use Illuminate\Http\Request;

/** Home page and tour-type FAQs (package FAQs are edited inside each package). */
class FaqController extends AdminController
{
    public function index()
    {
        return view('admin.faqs.index', ['items' => Faq::with('tourType')->whereNull('package_id')->orderBy('tour_type_id')->orderBy('sort')->get()]);
    }

    public function create()
    {
        return view('admin.faqs.form', ['item' => new Faq(['is_active' => true, 'sort' => (int) Faq::whereNull('package_id')->max('sort') + 1]), 'tourTypes' => TourType::active()->pluck('name', 'id')]);
    }

    public function store(Request $request)
    {
        Faq::create($this->data($request));

        return redirect()->route('admin.faqs.index')->with('status', 'FAQ added.');
    }

    public function edit(Faq $faq)
    {
        abort_if($faq->package_id, 404);

        return view('admin.faqs.form', ['item' => $faq, 'tourTypes' => TourType::active()->pluck('name', 'id')]);
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

    private function data(Request $request): array
    {
        $d = $request->validate(['question' => ['required', 'string', 'max:250'], 'answer' => ['required', 'string', 'max:3000'], 'sort' => ['nullable', 'integer', 'min:0'], 'tour_type_id' => ['nullable', 'integer', 'exists:tour_types,id']]);

        return $d + ['tour_type_id' => $d['tour_type_id'] ?? null, 'sort' => $d['sort'] ?? 0, 'is_active' => $request->boolean('is_active')];
    }
}
