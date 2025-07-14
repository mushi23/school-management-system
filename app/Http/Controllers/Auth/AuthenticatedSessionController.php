<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => session('status'),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request)
    {
        $request->authenticate();
        $request->session()->regenerate();

        $user = Auth::user()->load('roles', 'permissions', 'school');

        // Check if the user is active
        if (!$user->active) {
            return $this->logoutWithError($request, 'Your account is currently disabled.');
        }

        // Check if the user's school is inactive
        if ($user->school && !$user->school->active) {
            return $this->logoutWithError($request, 'Your school account is currently disabled.');
        }

        // Redirect based on role
        if ($user->hasRole('admin')) {
            return redirect()->intended(route('admin.dashboard'));
        } elseif ($user->hasRole('teacher')) {
            return redirect()->intended(route('teacher.dashboard'));
        } elseif ($user->hasRole('student')) {
            return redirect()->intended(route('student.dashboard'));
        } elseif ($user->hasRole('school_admin')) {
            return redirect()->intended(route('schooladmin.dashboard'));
        } else {
            return redirect()->intended(route('home'));
        }
    }

    // Reusable logout logic with error
    protected function logoutWithError(Request $request, string $message): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->withErrors(['email' => $message]);
    }


    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
