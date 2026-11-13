<?php

namespace App\Http\Controllers\Admin;

use App\Models\QuizOption;
use App\Models\QuizStep;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class QuizController extends AdminController
{
    public function index()
    {
        return view('admin.quiz.index', ['steps' => QuizStep::with('options')->orderBy('sort')->get()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'key' => ['required', 'string', 'max:40', 'regex:/^[a-z][a-z0-9_]*$/', 'unique:quiz_steps,key'],
            'question' => ['required', 'string', 'max:200'],
            'type' => ['required', 'in:tiles,dropdown'],
        ], ['key.regex' => 'The answer key may only use lowercase letters, numbers and underscores.']);

        QuizStep::create($data + ['sort' => (int) QuizStep::max('sort') + 1, 'is_active' => false]);

        return back()->with('status', 'Step added (inactive). Add its answer options, then switch it on.');
    }

    public function update(Request $request, QuizStep $step)
    {
        $data = $request->validate([
            'question' => ['required', 'string', 'max:200'],
            'type' => ['required', 'in:tiles,dropdown'],
            'sort' => ['nullable', 'integer', 'min:0'],
            'options' => ['nullable', 'array', 'max:20'],
            'options.*.id' => ['nullable', 'integer'],
            'options.*.is_active' => ['nullable'],
            'options.*.label' => ['nullable', 'string', 'max:80'],
            'options.*.icon' => ['nullable', 'string', 'max:10'],
            'options.*.sort' => ['nullable', 'integer', 'min:0'],
        ]);

        $step->update(['question' => $data['question'], 'type' => $data['type'], 'sort' => $data['sort'] ?? 0, 'is_active' => $request->boolean('is_active')]);

        $keep = [];
        foreach ($data['options'] ?? [] as $row) {
            if (! filled($row['label'] ?? null)) {
                continue;
            }
            $attrs = ['label' => $row['label'], 'icon' => $row['icon'] ?? null, 'sort' => (int) ($row['sort'] ?? 0), 'is_active' => ! empty($row['is_active'])];
            if (! empty($row['id']) && ($opt = QuizOption::where('quiz_step_id', $step->id)->find($row['id']))) {
                $opt->update($attrs);
            } else {
                $opt = QuizOption::create($attrs + ['quiz_step_id' => $step->id]);
            }
            $keep[] = $opt->id;
        }
        $step->options()->whereNotIn('id', $keep)->delete();

        return back()->with('status', 'Step "'.Str::limit($step->question, 40).'" saved.');
    }

    public function destroy(QuizStep $step)
    {
        $step->delete();

        return back()->with('status', 'Step deleted.');
    }
}
