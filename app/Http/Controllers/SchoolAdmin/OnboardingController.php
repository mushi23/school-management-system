<?php

namespace App\Http\Controllers\SchoolAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use App\Models\School;

class OnboardingController extends Controller
{
    public function index()
    {
        $user = auth()->user()->load('school.structures');

        return Inertia::render('SchoolAdmin/Onboarding', [
            'auth' => [
                'user' => $user
            ]
        ]);
    }


    public function store(Request $request)
    {

        // 🧩 Decode JSON-encoded fields from FormData
        $request->merge([
            'curriculum_names' => is_string($request->input('curriculum_names'))
                ? json_decode($request->input('curriculum_names'), true)
                : $request->input('curriculum_names'),

            'services' => is_string($request->input('services'))
                ? json_decode($request->input('services'), true)
                : $request->input('services'),

            'facilities' => is_string($request->input('facilities'))
                ? json_decode($request->input('facilities'), true)
                : $request->input('facilities'),

            'structure' => is_string($request->input('structure'))
                ? json_decode($request->input('structure'), true)
                : $request->input('structure'),
        ]);
        
        $structure = $request->input('structure');

        if (!$structure || !is_array($structure) || array_filter($structure) === []) {
            \Log::warning('⛔️ Skipping structure: empty or invalid');
            return back()->with('warning', 'No valid structure submitted.');
        }


        // Just for debug
        \Log::info('📥 Decoded structure:', [$request->input('structure')]);

        try {
            $validated = $request->validate([
                'slogan'           => 'nullable|string|max:255',
                'brand_color'      => 'nullable|string|max:20',
                'curriculum_key'   => 'nullable|string|max:255',
                'curriculum_names' => 'nullable|array',
                'services'         => 'nullable|array',
                'facilities'       => 'nullable|array',
                'structure'        => 'nullable|array',
                'logo'             => 'nullable|file|image|max:2048',
                'background'       => 'nullable|file|image|max:4096',
            ]);

            $school = auth()->user()->school;

            if (!$school) {
                \Log::error('🛑 No school found for user', ['user_id' => auth()->id()]);
                return back()->withErrors(['school' => 'No school linked to this user'])->withInput();
            }

            // 📤 Upload image files if present
            $logoPath = $request->hasFile('logo')
                ? $request->file('logo')->store('logos', 'public')
                : null;

            $backgroundPath = $request->hasFile('background')
                ? $request->file('background')->store('backgrounds', 'public')
                : null;

            // 📝 Update school fields
            $school->update([
                'slogan'           => $validated['slogan'] ?? '',
                'brand_color'      => $validated['brand_color'] ?? '#2563eb',
                'curriculum_key'   => $validated['curriculum_key'] ?? null,
                'curriculum_names' => $validated['curriculum_names'] ?? [],
                'services'         => $validated['services'] ?? [],
                'facilities'       => $validated['facilities'] ?? [],
                'logo'             => $logoPath,
                'background'       => $backgroundPath,
                'is_setup_complete'=> true,
            ]);

            // ✅ Safely validate structure array before updating
            $structure = $validated['structure'] ?? [];

            if (
                is_array($structure) &&
                !(count($structure) === 1 && isset($structure[0]) && is_null($structure[0]))
            ) {
                $school->structures()->delete();

                foreach ($structure as $levelName => $levelData) {
                    if (!is_array($levelData) || empty($levelData['selected'])) {
                        continue;
                    }

                    $title = !is_numeric($levelName) && !empty($levelName)
                        ? $levelName
                        : ($levelData['title'] ?? 'Untitled');

                    \Log::info('📦 Structure insert payload', [
                        'title'        => $title,
                        'structure'    => $levelData,
                        'levelName'    => $levelName,
                        'levelData'    => $levelData,
                    ]);

                    $school->structures()->create([
                        'title'        => $title,
                        'abbreviation' => $levelData['abbreviation'] ?? null,
                        'description'  => $levelData['description'] ?? null,
                        'order'        => $levelData['order'] ?? null,
                        'structure'    => $levelData,
                    ]);

                    \Log::info('✅ Structure created successfully');
                }
            } else {
                \Log::warning('⛔️ Skipping structure update: empty or [null] structure received.');
            }

            return redirect()->route('schooladmin.dashboard')->with('success', 'School setup complete.');
        } catch (ValidationException $e) {
            if ($request->hasHeader('X-Inertia')) {
                return back()->withErrors($e->errors())->withInput();
            }

            return response()->json([
                'message' => 'Validation Error',
                'errors'  => $e->errors(),
            ], 422);
        }
    }

    public function update(Request $request)
    {
        return $this->store($request);
    }
}
