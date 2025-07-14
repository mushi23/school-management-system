<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckIfUserIsActive
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        // Auto-logout if user is missing (deleted) or inactive
        if (!$user || !$user->active) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'email' => 'Your account is no longer available. Please contact support.',
            ]);
        }

        return $next($request);
    }
}
