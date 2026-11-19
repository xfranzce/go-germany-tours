<?php

namespace App\Http\Controllers\Admin;

use App\Models\CodeSnippet;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Header & Footer code (scripts, styles) for the public site. Administrators only: this code runs on every visitor's browser. */
class SnippetController extends AdminController
{
    public function index()
    {
        return view('admin.snippets.index', ['items' => CodeSnippet::orderBy('position')->orderBy('sort')->orderBy('id')->get()]);
    }

    public function create()
    {
        return view('admin.snippets.form', ['item' => new CodeSnippet(['is_active' => true, 'type' => 'script', 'position' => 'head', 'scope' => 'all', 'sort' => (int) CodeSnippet::max('sort') + 1])]);
    }

    public function store(Request $request)
    {
        CodeSnippet::create($this->data($request));

        return redirect()->route('admin.snippets.index')->with('status', 'Code added.');
    }

    public function edit(CodeSnippet $snippet)
    {
        return view('admin.snippets.form', ['item' => $snippet]);
    }

    public function update(Request $request, CodeSnippet $snippet)
    {
        $snippet->update($this->data($request));

        return redirect()->route('admin.snippets.index')->with('status', 'Code saved.');
    }

    public function destroy(CodeSnippet $snippet)
    {
        $snippet->delete();

        return redirect()->route('admin.snippets.index')->with('status', 'Code deleted.');
    }

    private function data(Request $request): array
    {
        $d = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'type' => ['required', Rule::in(array_keys(CodeSnippet::TYPES))],
            'code' => ['required', 'string', 'max:60000'],
            'position' => ['required', Rule::in(array_keys(CodeSnippet::POSITIONS))],
            'scope' => ['required', Rule::in(array_keys(CodeSnippet::SCOPES))],
            'pages' => ['nullable', 'required_if:scope,specific', 'string', 'max:5000'],
            'sort' => ['nullable', 'integer', 'min:0'],
        ], ['pages.required_if' => 'List at least one page, or choose Entire website.']);

        return $d + ['sort' => $d['sort'] ?? 0, 'pages' => $d['scope'] === 'specific' ? ($d['pages'] ?? null) : null, 'is_active' => $request->boolean('is_active')];
    }
}
