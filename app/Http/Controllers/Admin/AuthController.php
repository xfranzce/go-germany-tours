<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('admin.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate(['email' => ['required', 'email'], 'password' => ['required', 'string']]);

        if (Auth::guard('admin')->attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            Auth::guard('admin')->user()->forceFill(['last_login_at' => now()])->save();
            ActivityLog::record('login', null, null, 'Logged in');

            return redirect()->intended(route('admin.dashboard'));
        }

        ActivityLog::record('login_failed', null, null, 'Failed login attempt for '.$credentials['email']);

        // Same message whether the email or the password is wrong.
        return back()->withInput($request->only('email'))->withErrors(['email' => 'These details do not match our records.']);
    }

    public function logout(Request $request)
    {
        ActivityLog::record('logout', null, null, 'Logged out');
        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
