<?php

namespace App\Http\Controllers\Admin;

use App\Models\AdminUser;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;

class AdminUserController extends AdminController
{
    public function index()
    {
        return view('admin.users.index', ['users' => AdminUser::orderBy('name')->get()]);
    }

    public function create()
    {
        return view('admin.users.form', ['user' => new AdminUser()]);
    }

    public function store(Request $request)
    {
        $d = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150', 'unique:admin_users,email'],
            'password' => ['required', 'confirmed', Password::min(12)],
        ]);
        AdminUser::create($d);

        return redirect()->route('admin.users.index')->with('status', 'Admin user added.');
    }

    public function edit(AdminUser $user)
    {
        return view('admin.users.form', ['user' => $user]);
    }

    public function update(Request $request, AdminUser $user)
    {
        $d = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150', 'unique:admin_users,email,'.$user->id],
            'password' => ['nullable', 'confirmed', Password::min(12)],
        ]);
        $user->fill(['name' => $d['name'], 'email' => $d['email']]);
        if (filled($d['password'] ?? null)) {
            $user->password = $d['password'];
        }
        $user->save();

        return redirect()->route('admin.users.index')->with('status', 'Admin user saved.');
    }

    public function destroy(Request $request, AdminUser $user)
    {
        if ($user->is($request->user('admin'))) {
            return back()->withErrors(['delete' => 'You cannot delete your own account.']);
        }
        if (AdminUser::count() <= 1) {
            return back()->withErrors(['delete' => 'You cannot delete the last admin user.']);
        }
        $user->delete();

        return redirect()->route('admin.users.index')->with('status', 'Admin user removed.');
    }
}
