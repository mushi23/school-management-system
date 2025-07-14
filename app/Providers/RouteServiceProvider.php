<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class RouteServiceProvider extends ServiceProvider
{
    public const HOME = '/redirect';
    

    public function boot()
    {
        $this->configureRateLimiting();

        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });

        parent::boot();

        Route::middleware('web')->group(function () {
            Route::get('/redirect', function () {
                $user = auth()->user();

                       if ($user->hasRole('school_admin')) {
                           return redirect('/school-admin/dashboard');
                       } elseif ($user->hasRole('admin')) {
                           return redirect('/admin/dashboard');
                       } elseif ($user->hasRole('teacher')) {
                           return redirect()->route('teacher.dashboard');
                       } elseif ($user->hasRole('student')) {
                           return redirect()->route('student.dashboard');
                       }


                return abort(403, 'Unauthorized.');
            });
        });
    }

    
    protected function configureRateLimiting()
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by(optional($request->user())->id ?: $request->ip());
        });
    }

}

