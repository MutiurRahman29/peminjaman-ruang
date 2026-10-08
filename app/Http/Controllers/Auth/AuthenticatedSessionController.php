<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the admin login form.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Authenticate the admin user and start a session.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $admin = $request->user();

        if (! $admin instanceof User || $admin->role !== UserRole::Admin) {
            Auth::logout();

            return redirect()->route('admin.login')->withErrors([
                'username' => 'Akun ini tidak memiliki akses admin.',
            ]);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('admin.dashboard', absolute: false));
    }

    /**
     * End the authenticated user's session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
