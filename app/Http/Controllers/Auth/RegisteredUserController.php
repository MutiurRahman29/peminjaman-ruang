<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class RegisteredUserController extends Controller
{
    /**
     * Create a new user account and start the session.
     */
    public function store(RegisterRequest $request): RedirectResponse
    {
        $user = User::query()->create([
            'nama' => $request->validated('nama'),
            'username' => $request->validated('username'),
            'password' => $request->validated('password'),
            'role' => UserRole::Peminjam,
        ]);

        Auth::login($user);

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard', absolute: false));
    }
}
