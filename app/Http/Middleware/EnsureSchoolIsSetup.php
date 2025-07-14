<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSchoolIsSetup
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->hasRole('school_admin')) {
            $school = $user->school;

            if ($school && !$school->is_setup_complete) {
                if (
                    !str_starts_with($request->route()->getName(), 'schooladmin.onboarding') &&
                    !$request->routeIs('logout')
                ) {
                    return redirect()->route('schooladmin.onboarding');
                }
            }
        }

        return $next($request);
    }
}
