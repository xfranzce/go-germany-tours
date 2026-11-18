<?php

namespace App\Http\Controllers\Admin;

use App\Models\TeamMember;
use Illuminate\Http\Request;

/** Cards on the Meet Our Team page. */
class TeamController extends AdminController
{
    public function index()
    {
        return view('admin.team.index', ['items' => TeamMember::orderBy('sort')->orderBy('id')->get()]);
    }

    public function create()
    {
        return view('admin.team.form', ['item' => new TeamMember(['is_active' => true, 'sort' => (int) TeamMember::max('sort') + 1])]);
    }

    public function store(Request $request)
    {
        $this->save($request, new TeamMember());

        return redirect()->route('admin.team.index')->with('status', 'Team member added.');
    }

    public function edit(TeamMember $team)
    {
        return view('admin.team.form', ['item' => $team]);
    }

    public function update(Request $request, TeamMember $team)
    {
        $this->save($request, $team);

        return redirect()->route('admin.team.index')->with('status', 'Team member saved.');
    }

    public function destroy(TeamMember $team)
    {
        $team->delete();

        return back()->with('status', 'Team member deleted.');
    }

    private function save(Request $request, TeamMember $m): void
    {
        $d = $request->validate([
            'name' => ['required', 'string', 'max:120'], 'role' => ['nullable', 'string', 'max:120'],
            'bio' => ['nullable', 'string', 'max:600'], 'profile' => ['nullable', 'string', 'max:4000'], 'sort' => ['nullable', 'integer', 'min:0'],
        ] + $this->imageRules());

        $m->fill([
            'name' => $d['name'], 'role' => $d['role'] ?? null, 'bio' => $d['bio'] ?? null, 'profile' => $d['profile'] ?? null, 'sort' => $d['sort'] ?? 0,
            'is_active' => $request->boolean('is_active'), 'image' => $this->handleImage($request, 'image', $m->image),
        ])->save();
    }
}
