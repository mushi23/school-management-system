<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();
        $school = null;
        
        // Load school data for school admin users
        if ($user && $user->hasRole('school_admin')) {
            $school = $user->school;
            if ($school) {
                $school = [
                    'id' => $school->id,
                    'name' => $school->name,
                    'slogan' => $school->slogan,
                    'brand_color' => $school->brand_color,
                    'logo' => $school->logo ? \Storage::url($school->logo) : null,
                    'background' => $school->background ? \Storage::url($school->background) : null,
                ];
            }
        }
        
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user,
            ],
            'school' => $school,
        ];
    }
}
