<?php

namespace App\Providers;

use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);

        Inertia::share([
            'auth.user' => fn () => Auth::user() ? [
                'id' => Auth::user()->id,
                'name' => Auth::user()->name,
                'email' => Auth::user()->email,
                'roles' => Auth::user()->getRoleNames(),
            ] : null,
            // You can add flash messages here as well if needed
            'flash' => [
                'success' => session('success'),
                'error' => session('error'),
            ],
            // Share student globally
            'student' => function () {
                $user = Auth::user();
                if ($user && $user->student) {
                    $student = $user->student;
                    $student->append('profile_picture_url');
                    return $student;
                }
                return null;
            },
            // Share school globally
            'school' => function () {
                $user = Auth::user();
                $school = $user && $user->student ? $user->student->school : null;
                return $school ? [
                    'name' => $school->name,
                    'logo' => $school->logo_url,
                    'background' => $school->background_url,
                    'slogan' => $school->slogan,
                ] : null;
            },
        ]);
    }
}
